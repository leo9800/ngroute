<?php

namespace Leo\NgRoute;

interface RouteParserInterface
{
	/**
	 * Parse route into segments
	 * @param  string       $raw_route Raw route input
	 * @return array<mixed>            Routing data
	 */
	public function parse(string $raw_route): array;
}
