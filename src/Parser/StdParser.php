<?php

namespace Leo980\NgRoute\Parser;

use Leo980\NgRoute\Exceptions\Parser\BracketsMismatchException;
use Leo980\NgRoute\Exceptions\Parser\EmptyRouteException;
use Leo980\NgRoute\Exceptions\Parser\OptionalSegmentInMiddleException;
use Leo980\NgRoute\Exceptions\Parser\RegexException;
use Leo980\NgRoute\ParserInterface;
use Leo980\NgRoute\SegmentInterface;
use Leo980\NgRoute\Segments\FixedSegment;
use Leo980\NgRoute\Segments\VariableSegment;

class StdParser implements ParserInterface
{
	private const VARIABLE_REGEX = '\{\s*([a-zA-Z_][a-zA-Z0-9_-]*)\s*(?::\s*([^{}]*(?:\{(?-1)\}[^{}]*)*))?\}';
	public function parse(string $raw_route): array
	{
		return array_map(
			callback:[$this, 'parseSingleRoute'],
			array:$this->expandOptionalSegments($raw_route)
		);
	}

	/**
	 * Expand route with optional segments into multiple 
	 * routes without optional segments.
	 * e.g.
	 * '/user/{id:\d+}' -> ['/user/{id:\d+}']
	 * '/tag/{name}[/page/{page:\d+}]' -> ['/tag/{name}', '/tag/{name}/page/{page:\d+}']
	 * '/cas[ca[ded]]' -> ['/cas', '/casca', '/cascaded']
	 * @param  string        $raw_route Raw route string with optional segments
	 * @return array<string>            Raw route strings without optional segments
	 */
	private function expandOptionalSegments(string $raw_route): array
	{
		$route_without_closing_optional = rtrim($raw_route, ']');
		$num_optionals = strlen($raw_route) - strlen($route_without_closing_optional);

		// Split on '[' wile skipping other slices
		// i.e. variable segments, fixed segments
		$slices = preg_split(
			pattern:'~'.self::VARIABLE_REGEX.'(*SKIP)(*F) | \[~x',
			subject:$route_without_closing_optional,
		);

		// @codeCoverageIgnoreStart
		if ($slices === false)
			throw new RegexException();
		// @codeCoverageIgnoreEnd

		if ($num_optionals != count($slices) - 1) {
			// If there are any ']' in the middle of the route ...
			if (preg_match(
				pattern:'~'.self::VARIABLE_REGEX.'(*SKIP)(*F)  | \]~x',
				subject:$route_without_closing_optional,
			))
				throw new OptionalSegmentInMiddleException();

			// Otherwise, there's at lease a '[]' brackets not closed ...
			throw new BracketsMismatchException();
		}

		$glued = [];

		foreach ($slices as $i => $s1) {
			if ($s1 === '' && $i !== 0)
				throw new EmptyRouteException();

			$o = '';

			foreach ($slices as $j => $s2) {
				$o .= $s2;
				if ($i == $j)
					break;
			}

			$glued[] = $o;
		}

		return $glued;
	}

	/**
	 * Parse single route without optional segment into 
	 * array of route segments
	 * @param  string                  $route Route string without optional segment
	 * @return array<SegmentInterface>        Route segments
	 */
	private function parseSingleRoute(string $route): array
	{
		// Find all variable segments in route string
		$r = preg_match_all(
			pattern:'~'.self::VARIABLE_REGEX.'~',
			subject:$route,
			matches:$matches,
			flags:PREG_OFFSET_CAPTURE | PREG_SET_ORDER,
		);

		// @codeCoverageIgnoreStart
		if ($r === false)
			throw new RegexException();
		// @codeCoverageIgnoreEnd

		// No variable segment matched, only 1 fixed segment in this route.
		if ($r === 0)
			return [new FixedSegment($route)];

		$offset = 0;
		$segments = [];

		foreach ($matches as $set) {
			// A fixed segment is found before the current variable segment
			if ($set[0][1] > $offset)
				$segments[] = new FixedSegment(substr(
					string:$route,
					offset:$offset,
					length:$set[0][1] - $offset,
				));

			// Parse the current variable segment
			$segments[] = new VariableSegment(
				name:$set[1][0],
				// use default if not specified
				match:trim($set[2][0] ?? VariableSegment::DEFAULT_MATCH),
			);

			// Move offset to the end of current variable segment
			$offset = $set[0][1] + strlen($set[0][0]);
		}

		// Parse fixed segment at the end of route string if it exist
		if ($offset !== strlen($route))
			$segments[] = new FixedSegment(substr(
				string:$route,
				offset:$offset,
			));

		return $segments;
	}
}
