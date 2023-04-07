<?php

namespace Leo\NgRoute\Segments;

use Leo\NgRoute\PatternMatcher;
use Leo\NgRoute\SegmentInterface;

class FixedSegment implements SegmentInterface
{
	public function __construct(
		private string $string
	)
	{

	}

	public function __toString(): string
	{
		return $this->string;
	}

	public function matches(?PatternMatcher $pattern_matcher = null): string
	{
		return preg_quote($this->string, delimiter:'/');
	}
}
