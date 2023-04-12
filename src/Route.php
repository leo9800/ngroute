<?php

namespace Leo\NgRoute;

use Leo\NgRoute\Exceptions\Route\InvalidSegmentException;
use Psr\Http\Server\RequestHandlerInterface;

class Route
{
	/**
	 * @param array<SegmentInterface> $route_segments
	 * @param array<string>           $methods
	 * @param RequestHandlerInterface $handler
	 * @param ?string                 $name
	 * @param array<Constraint>       $constraints
	 */
	public function __construct(
		private array $route_segments,
		private array $methods,
		private RequestHandlerInterface $handler,
		private ?string $name=null,
		private array $constraints = [],
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
	 * Get regex matched by route
	 * @param  PatternMatcher $pattern_matcher
	 * @return string
	 */
	public function matches(PatternMatcher $pattern_matcher = null): string
	{
		$regex = '';

		foreach ($this->route_segments as $rs)
			$regex .= $rs->matches($pattern_matcher);

		return "/^$regex$/";
	}

	/**
	 * Get route segments
	 * @return array<SegmentInterface>
	 */
	public function getSegments(): array
	{
		return $this->route_segments;
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

	/**
	 * Get endpoint name of route, name is nullable
	 * @return ?string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Get allowed host, port and scheme constraints of route.
	 * @return array<Constraint>
	 */
	public function getConstraints(): array
	{
		return $this->constraints;
	}
}
