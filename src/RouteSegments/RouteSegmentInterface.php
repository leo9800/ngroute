<?php

namespace Leo\NgRoute\RouteSegments;

interface RouteSegmentInterface
{
	public function matches(): string;
}