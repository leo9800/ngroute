<?php

use Leo\NgRoute\PatternMatcher;
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
		$this->assertSame('(\d+)', $vs->matches());
	}

	/**
	 * @testdox Get variable matching regex with PatternMatcher injection
	 */
	public function testGetMatchesWithPatternMatcher(): void
	{
		$pm = (new PatternMatcher())
			->addPattern('integer', '\d+');

		$vs = new VariableSegment('param', 'integer');
		$this->assertSame('(\d+)', $vs->matches(pattern_matcher:$pm));
	}

	/**
	 * @testdox __toString()
	 */
	public function testString(): void
	{
		$vs = new VariableSegment('param', '\d+');
		$this->assertSame('{param:\d+}', (string) $vs);
	}
}
