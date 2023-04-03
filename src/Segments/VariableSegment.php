<?php

namespace Leo\NgRoute\Segments;

use Leo\NgRoute\SegmentInterface;

class VariableSegment implements SegmentInterface
{
	public const DEFAULT_MATCH = '[^/]+';

	public function __construct(
		private string $name,
		private string $match,
	)
	{
		if (!$this->match)
			$this->match = self::DEFAULT_MATCH;
	}

	public function name(): string
	{
		return $this->name;
	}

	public function matches(string $delimiter = '/'): string
	{
		return "($this->match)";
	}
}
