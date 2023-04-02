<?php

use Leo\NgRoute\Segments\VariableSegment;
use PHPUnit\Framework\TestCase;

/**
 * @testdox Leo\NgRoute\Segments\VariableSegment
 */
class VariableSegmentTest extends TestCase
{
	/**
	 * @testdox Get variable name
	 */
	public function testGetName(): void
	{
		$vs = new VariableSegment('param', '\d+');
		$this->assertSame('param', $vs->name());
	}

	/**
	 * @testdox Get variable matching regex
	 */
	public function testGetMatches(): void
	{
		$vs = new VariableSegment('param', '\d+');
		$this->assertSame('\d+', $vs->matches());
	}

	/**
	 * @testdox Set default matching regex if not specified
	 */
	public function testDefaultMatch(): void
	{
		$vs = new VariableSegment('param', '');
		$this->assertSame(VariableSegment::DEFAULT_MATCH, $vs->matches());
	}
}
