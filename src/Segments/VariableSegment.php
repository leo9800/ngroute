<?php

namespace Leo\NgRoute\Segments;

use Leo\NgRoute\PatternMatcher;
use Leo\NgRoute\SegmentInterface;

class VariableSegment implements SegmentInterface
{
	public const DEFAULT_MATCH = '[^/]+';

	public function __construct(
		private string $name,
		private string $match,
	)
	{

	}

	public function name(): string
	{
		return $this->name;
	}

	public function matches(?PatternMatcher $pattern_matcher = null): string
	{
		$o = is_null($pattern_matcher) ? $this->match : $pattern_matcher->replace($this->match);
		$o = str_replace('/', "\\/", $o);
		return "($o)";
	}
}
