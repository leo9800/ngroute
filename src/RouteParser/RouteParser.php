<?php

namespace Leo\NgRoute\RouteParser;

use Leo\NgRoute\RouteParser\Exceptions\BracketsMismatchException;
use Leo\NgRoute\RouteParser\Exceptions\EmptyRouteException;
use Leo\NgRoute\RouteParser\Exceptions\OptionalSegmentsInMiddleException;
use Leo\NgRoute\RouteParserInterface;

class RouteParser implements RouteParserInterface
{
	public const DEFAULT_DISPATCH_REGEX = "[^/]+";
	public const VARIABLE_REGEX = <<<'REGEX'
\{
    \s* ([a-zA-Z_][a-zA-Z0-9_-]*) \s*
    (?:
        : \s* ([^{}]*(?:\{(?-1)\}[^{}]*)*)
    )?
\}
REGEX;

	public function parse(string $raw_route): array
	{
		$route_without_closing_optionals = rtrim($raw_route, "]");
		$num_optionals = strlen($raw_route) - strlen($route_without_closing_optionals);

		// Split on '[' while skipping placeholders
		$segments = preg_split(
			pattern:'~'.self::VARIABLE_REGEX.'(*SKIP)(*F) | \[~x',
			subject:$route_without_closing_optionals,
		);

		if ($num_optionals != count($segments) - 1) {
			// If there are any ] in the middle of the route, throw a more specific error message
			if (preg_match(
				pattern:'~'.self::VARIABLE_REGEX.'(*SKIP)(*F)  | \]~x',
				subject:$route_without_closing_optionals,
			))
				throw new OptionalSegmentsInMiddleException();

			throw new BracketsMismatchException();
		}

		$current_route = "";
		$route_datas = [];

		foreach ($segments as $n => $segment) {
			if ($segment === '' && $n !== 0)
				throw new EmptyRouteException();

			$current_route .= $segment;
			$route_datas[] = $this->parsePlaceholders($current_route);
		}

		return $route_datas;
	}

	// @phpstan-ignore-next-line
	private function parsePlaceholders(string $route): array
	{
		if (!preg_match_all(
			pattern:'~'.self::VARIABLE_REGEX.'~x',
			subject:$route,
			matches:$matches,
			flags:PREG_OFFSET_CAPTURE|PREG_SET_ORDER,
		))
			return [$route];

		$offset = 0;
		$route_data = [];

		foreach ($matches as $set) {
			if ($set[0][1] > $offset)
				$route_data[] = substr($route, $offset, $set[0][1] - $offset);

			$route_data[] = [
				$set[1][0],
				isset($set[2]) ? trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX
			];

			$offset = $set[0][1] + strlen($set[0][0]);
		}

		if ($offset !== strlen($route))
			$route_data[] = substr($route, $offset);

		return $route_data;
	}
}