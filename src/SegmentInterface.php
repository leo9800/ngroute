<?php

namespace Leo\NgRoute;

interface SegmentInterface extends \Stringable
{
	/**
	 * Return regex representation of this route segment
	 * @param  PatternMatcher|null $pattern_matcher
	 * @return string
	 */
	public function matches(?PatternMatcher $pattern_matcher=null): string;
}