<?php

namespace Leo\NgRoute\RouteSegments;

class RegexRouteSegment implements RouteSegmentInterface
{
	public const DEFAULT_REGEX = '[^/]+';

	public function __construct(
		private string $varname,
		private string $regex,
	)
	{
		if ($this->varname === NULL)
			$this->varname = self::DEFAULT_REGEX;
	}

	public function matches(): string
	{
		return $this->regex;
	}

	public function varname(): string
	{
		return $this->varname;
	}
}