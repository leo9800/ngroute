<?php

namespace Leo\NgRoute\RouteSegments;

class FixedRouteSegment implements RouteSegmentInterface
{
	public function __construct(
		private string $segment
	)
	{

	}

	public function matches(): string
	{
		return preg_quote($this->segment, delimiter:'/');
	}
}
