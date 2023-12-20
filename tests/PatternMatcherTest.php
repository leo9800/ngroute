<?php

use Leo980\NgRoute\PatternMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @testdox Leo980\NgRoute\PatternMatcher
 */
class PatternMatcherTest extends TestCase
{
	public function testPatternHit(): void
	{
		$pm = (new PatternMatcher())
			->addPattern('int', '\d+')
			->addPattern('word', '\w+');

		$this->assertSame('\d+', $pm->replace('int'));
	}

	public function testPatternMiss(): void
	{
		$pm = (new PatternMatcher())
			->addPattern('int', '\d+')
			->addPattern('word', '\w+');

		$this->assertSame('.*\.com', $pm->replace('.*\.com'));
	}
}
