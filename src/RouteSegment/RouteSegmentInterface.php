<?php

namespace Leo\NgRoute\RouteSegment;

interface RouteSegmentInterface
{
	public function matches(): string;
}