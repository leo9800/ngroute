<?php

use Leo980\NgRoute\Segments\FixedSegment;
use PHPUnit\Framework\TestCase;

/**
 * @testdox Leo980\NgRoute\Segments\FixedSegment
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

	/**
	 * @testdox __toString()
	 */
	public function testString(): void
	{
		$fs = new FixedSegment('/test/+123');
		$this->assertSame('/test/+123', (string) $fs);
	}
}
