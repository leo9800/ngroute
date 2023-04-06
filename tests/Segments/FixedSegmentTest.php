<?php

use Leo\NgRoute\Segments\FixedSegment;
use PHPUnit\Framework\TestCase;

/**
 * @testdox Leo\NgRoute\Segments\FixedSegment
 */
class FixedSegmentTest extends TestCase
{
	/**
	 * @testdox Escape regex
	 */
	public function testRegex(): void
	{
		$fs = new FixedSegment('/test/+123');
		$this->assertSame('\/test\/\+123', $fs->matches());
	}
}
