<?php

namespace Leo\NgRoute;

interface SegmentInterface
{
	/**
	 * Return regex representation of this route segment
	 * @param  PatternMatcher|null $pattern_matcher
	 * @return string
	 */
	public function matches(?PatternMatcher $pattern_matcher=null): string;
}