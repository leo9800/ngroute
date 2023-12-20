<?php

namespace Leo980\NgRoute;

use Leo980\NgRoute\Exceptions\Router\MethodMismatchException;
use Leo980\NgRoute\Exceptions\Router\MissingParameterException;
use Leo980\NgRoute\Exceptions\Router\NoMatchingRouteException;
use Leo980\NgRoute\Segments\FixedSegment;
use Leo980\NgRoute\Segments\VariableSegment;
use Leo980\Relay\Relay;
use Nyholm\Psr7\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Router implements RequestHandlerInterface
{
	private const PARAMS_ATTRIBUTE = 'NGROUTE_PARAMS';

	/**
	 * @param ParserInterface            $parser
	 * @param DataInterface              $data
	 * @param PatternMatcher|null        $pattern_matcher
	 * @param array<MiddlewareInterface> $middlewares
	 * @param array<Constraint>          $constraints
	 */
	public function __construct(
		private ParserInterface $parser,
		private DataInterface $data,
		private ?PatternMatcher $pattern_matcher = null,
		private array $middlewares = [],
		private array $constraints = [],
	)
	{

	}

	/**
	 * Add route to router
	 * @param array<string>                   $methods
	 * @param string                          $path
	 * @param RequestHandlerInterface         $handler
	 * @param string|null                     $name
	 * @param array<MiddlewareInterface>|null $override_middlewares
	 * @param array<Constraint>|null          $override_constraints
	 */
	public function addRoute(
		array $methods,
		string $path,
		RequestHandlerInterface $handler,
		string|null $name = null,
		?array $override_middlewares = null,
		?array $override_constraints = null,
	): self
	{
		foreach ($this->parser->parse($path) as $s) {
			$this->data->addRoute(new Route(
				route_segments:$s,
				methods:$methods,
				handler:new Relay([...($override_middlewares ?? $this->middlewares), $handler]),
				name:$name,
				constraints:$override_constraints ?? $this->constraints,
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

		return (new Uri())->withPath($path);
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
