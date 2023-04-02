<?php

namespace Leo\NgRoute;

interface ParserInterface
{
	/**
	 * Parse route multiple array of route segments
	 * @param  string                         $raw_route Route description string
	 * @return array<array<SegmentInterface>>            Route segments
	 */
	public function parse(string $raw_route): array;
}
