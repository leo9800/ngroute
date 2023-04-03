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
	public function findRouteByUri(UriInterface $uri): ?Route;

	/**
	 * Get route by endpoint name, NULL is returned when no matching
	 * @param  string $name
	 * @return ?Route
	 */
	public function findRouteByName(string $name): ?Route;
}
