<?php

namespace Leo\NgRoute\Segments;

use Leo\NgRoute\SegmentInterface;

class FixedSegment implements SegmentInterface
{
	public function __construct(
		private string $string
	)
	{

	}

	public function matches(string $delimiter = '/'): string
	{
		return preg_quote($this->string, delimiter:$delimiter);
	}
}
