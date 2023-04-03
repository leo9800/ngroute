<?php

namespace Leo\NgRoute;

use Leo\NgRoute\Exceptions\Route\InvalidSegmentException;
use Leo\NgRoute\Segments\VariableSegment;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Route
{
	/**
	 * @param array<SegmentInterface> $route_segments
	 * @param array<string>           $methods
	 * @param RequestHandlerInterface $handler
	 */
	public function __construct(
		private array $route_segments,
		private array $methods,
		private RequestHandlerInterface $handler,
	)
	{
		foreach ($this->route_segments as $rs)
			if (!($rs instanceof SegmentInterface))
				throw new InvalidSegmentException();

		// HTTP methods should be in uppercase, and should not duplicate
		$this->methods = array_values(array_unique(
			array_map('strtoupper', $this->methods)
		));
	}

	/**
	 * Extract variables from URI, NULL is returned when failed matching.
	 * @param  UriInterface         $uri Input URI
	 * @return ?array<mixed, mixed>      Associated array of variable => value
	 */
	public function variablesFromUri(UriInterface $uri): ?array
	{
		// Return NULL if no matching
		if (preg_match(
			pattern:$this->matches(),
			subject:$uri->getPath(),
			matches:$results,
		) !== 1)
			return NULL;

		$var = [];
		$val = [];

		foreach ($this->route_segments as $rs)
			if ($rs instanceof VariableSegment)
				$var[] = $rs->name();

		foreach ($results as $i => $r) {
			if ($i == 0)
				continue;

			$val[] = $r;
		}

		return array_combine($var, $val);
	}

	/**
	 * Get regex matched by route
	 * @param  string $delimiter Regex delimiter
	 * @return string
	 */
	public function matches(string $delimiter = '/'): string
	{
		$regex = '';

		foreach ($this->route_segments as $rs)
			$regex .= $rs->matches($delimiter);

		return "$delimiter^$regex$$delimiter";
	}

	/**
	 * Get allowed HTTP methods of route
	 * @return array<string>
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

	/**
	 * Get PSR-15 request handler associated to route
	 * @return RequestHandlerInterface
	 */
	public function getHandler(): RequestHandlerInterface
	{
		return $this->handler;
	}
}
