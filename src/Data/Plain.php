<?php

namespace Leo980\NgRoute\Data;

use Leo980\NgRoute\DataInterface;
use Leo980\NgRoute\Exceptions\Data\DuplicatedRouteNameException;
use Leo980\NgRoute\PatternMatcher;
use Leo980\NgRoute\Route;
use Leo980\NgRoute\Segments\VariableSegment;
use Psr\Http\Message\UriInterface;

class Plain implements DataInterface
{
	/**
	 * All routes
	 * @var array<Route>
	 */
	private array $routes = [];

	/**
	 * Reverse index of endpoint name to route object
	 * @var array<string, Route>
	 */
	private array $name_index = [];

	public function addRoute(Route $route): void
	{
		$this->routes[] = $route;

		// Add route to name reverse index if it has an endpoint name
		if (null !== ($name = $route->getName())) {
			// Check for duplication ...
			if (isset($this->name_index[$name]))
				throw new DuplicatedRouteNameException();

			$this->name_index[$name] = $route;
		}
	}

	public function findRouteByUri(UriInterface $uri, ?PatternMatcher $pattern_matcher=null, ?array &$params=null): ?Route
	{
		foreach ($this->routes as $route)
			if (
				// We need uri to be matched
				preg_match($route->matches(pattern_matcher:$pattern_matcher), $uri->getPath(), $matches) === 1 &&
				// And no violation of constraints ...
				$this->checkConstraints($uri, $route)
			) {
				// First element is the entire matching string, we need to drop it
				array_shift($matches);
				$key = [];

				foreach ($route->getSegments() as $s)
					if ($s instanceof VariableSegment)
						$key[] = $s->name();

				$params = array_combine($key, $matches);
				return $route;
			}

		$params = [];
		return NULL;
	}

	public function findRouteByName(string $name): ?Route
	{
		return $this->name_index[(string) $name] ?? null;
	}

	private function checkConstraints(UriInterface $uri, Route $route): bool
	{
		if ($route->getConstraints() === [])
			return true;

		foreach ($route->getConstraints() as $c) {
			if (
				($c->host === null || $c->host === $uri->getHost()) &&
				($c->port === null || $c->port === $uri->getPort()) &&
				($c->scheme === null || $c->scheme === $uri->getScheme())
			)
				return true;
		}

		return false;
	}
}
