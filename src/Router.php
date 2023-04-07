<?php

namespace Leo\NgRoute;

use Leo\NgRoute\Exceptions\Router\MethodMismatchException;
use Leo\NgRoute\Exceptions\Router\MissingParameterException;
use Leo\NgRoute\Exceptions\Router\NoMatchingRouteException;
use Leo\NgRoute\Segments\FixedSegment;
use Leo\NgRoute\Segments\VariableSegment;
use Nyholm\Psr7\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Router implements RequestHandlerInterface
{
	private const PARAMS_ATTRIBUTE = 'NGROUTE_PARAMS';

	public function __construct(
		private ParserInterface $parser,
		private DataInterface $data,
		private ?PatternMatcher $pattern_matcher = null,
		private string|null $host = null,
		private int|null $port = null,
		private string|null $scheme = null,
	)
	{

	}

	/**
	 * Add route to router
	 * @param array<string>           $methods
	 * @param string                  $path
	 * @param RequestHandlerInterface $handler
	 * @param string|null             $name
	 */
	public function addRoute(
		array $methods,
		string $path,
		RequestHandlerInterface $handler,
		string|null $name = null
		// TBD: override
	): self
	{
		foreach ($this->parser->parse($path) as $s) {
			$this->data->addRoute(new Route(
				route_segments:$s,
				methods:$methods,
				handler:$handler,
				name:$name,
				host:$this->host,
				port:$this->port,
				scheme:$this->scheme,
			));
		}

		return $this;
	}

	/**
	 * Get URI with given endpoint and parameters
	 * @param  string                $endpoint
	 * @param  array<string, string> $params
	 * @return UriInterface|null
	 */
	public function endpointUri(string $endpoint, array $params): UriInterface|null
	{
		if (null === $route = $this->data->findRouteByName($endpoint))
			return null;

		$uri = new Uri();

		if (null !== $route->getScheme())
			$uri = $uri->withScheme($route->getScheme());

		if (null !== $route->getHost())
			$uri = $uri->withHost($route->getHost());

		if (null !== $route->getPort())
			$uri = $uri->withPort($route->getPort());

		$path = "";

		foreach ($route->getSegments() as $seg) {
			if ($seg instanceof FixedSegment)
				$path .= $seg;

			if ($seg instanceof VariableSegment) {
				if (null === $r = $params[$seg->name()] ?? null)
					throw new MissingParameterException();

				$path .= $r;
			}
		}

		return $uri->withPath($path);
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		if (null === $route = $this->data->findRouteByUri(
			uri:$request->getUri(),
			pattern_matcher:$this->pattern_matcher,
			params:$params,
		))
			throw new NoMatchingRouteException();

		if (!in_array($request->getMethod(), $route->getMethods(), strict:true))
			throw new MethodMismatchException();

		return $route->getHandler()->handle($request->withAttribute(self::PARAMS_ATTRIBUTE, $params));
	}
}
