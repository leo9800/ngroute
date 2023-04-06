<?php

namespace Leo\NgRoute;

use Psr\Http\Message\UriInterface;

interface DataInterface
{
	/**
	 * Add route to dataset
	 * @param Route $route
	 */
	public function addRoute(Route $route): void;

	/**
	 * Find route by given URI, NULL is returned when no matching
	 * @param  UriInterface $uri
	 * @return ?Route
	 */
	/**
	 * Find route by given URI, NULL is returned when no matching
	 * @param  UriInterface        $uri
	 * @param  PatternMatcher|null $pattern_matcher
	 * @return Route|null
	 */
	public function findRouteByUri(UriInterface $uri, ?PatternMatcher $pattern_matcher=null): ?Route;

	/**
	 * Get route by endpoint name, NULL is returned when no matching
	 * @param  string $name
	 * @return ?Route
	 */
	public function findRouteByName(string $name): ?Route;
}
