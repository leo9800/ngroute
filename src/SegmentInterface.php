<?php

namespace Leo\NgRoute;

interface SegmentInterface
{
	/**
	 * Return regex representation of this route segment
	 * @param  string $delimiter Regex delimiter (refers to preg_quote)
	 * @return string            Regex of segment
	 */
	public function matches(string $delimiter='/', PatternMatcher $pattern_matcher=null): string;
}