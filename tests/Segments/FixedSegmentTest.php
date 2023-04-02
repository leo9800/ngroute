<?php

use Leo\NgRoute\Segments\FixedSegment;
use PHPUnit\Framework\TestCase;

/**
 * @testdox Leo\NgRoute\Segments\FixedSegment
 */
class FixedSegmentTest extends TestCase
{
	/**
	 * @testdox Escape regex with '/' as delimiter
	 */
	public function testRegex1(): void
	{
		$fs = new FixedSegment('/test/+123');
		$this->assertSame('\/test\/\+123', $fs->matches());
	}

	/**
	 * @testdox Escape regex with '~' as delimiter
	 */
	public function testRegex2(): void
	{
		$fs = new FixedSegment('/test/~123');
		$this->assertSame('/test/\~123', $fs->matches('~'));
	}
}
