<?php

use Leo\Fixtures\DummyRequestHandler;
use Leo\NgRoute\Exceptions\Route\InvalidSegmentException;
use Leo\NgRoute\Route;
use Leo\NgRoute\Segments\FixedSegment;
use Leo\NgRoute\Segments\VariableSegment;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertNull;

/**
 * @testdox Leo\NgRoute\Route
 */
class RouteTest extends TestCase
{
	/**
	 * @testdox getHandler()
	 */
	public function testGetHandler(): void
	{
		$h = new DummyRequestHandler();
		$r = new Route(
			route_segments:[new FixedSegment('/')],
			methods:['GET'],
			handler:$h,
		);

		$this->assertSame($h, $r->getHandler());
	}

	/**
	 * @testdox getMethods()
	 */
	public function testGetMethods(): void
	{
		$r = new Route(
			route_segments:[new FixedSegment('/')],
			methods:['GET', 'POST'],
			handler:new DummyRequestHandler(),
		);

		$this->assertSame(['GET', 'POST'], $r->getMethods());
	}

	/**
	 * @testdox Throw exception on invalid segments passed to constructor
	 */
	public function testInvalidSegment(): void
	{
		$this->expectException(InvalidSegmentException::class);
		new Route(
			route_segments:[new FixedSegment('/'), new stdClass()],
			methods:['GET'],
			handler:new DummyRequestHandler(),
		);
	}

	/**
	 * @testdox Convert methods into uppercase
	 */
	public function testMalformedMethods(): void
	{
		$r = new Route(
			route_segments:[new FixedSegment('/')],
			methods:['gEt', 'PoSt'],
			handler:new DummyRequestHandler(),
		);

		$this->assertSame(['GET', 'POST'], $r->getMethods());
	}

	/**
	 * @testdox Remove duplicated methods
	 */
	public function testDuplicatedMethods(): void
	{
		$r = new Route(
			route_segments:[new FixedSegment('/')],
			methods:['GET', 'GET', 'GET'],
			handler:new DummyRequestHandler(),
		);

		$this->assertSame(['GET'], $r->getMethods());
	}

	public function testMatches(): void
	{
		$r = new Route(
			route_segments:[
				new FixedSegment('/fixed1/'),
				new VariableSegment('param1', '\d+'),
				new FixedSegment('/fixed2/'),
				new VariableSegment('param2', '.*'),
			],
			methods:['GET'],
			handler:new DummyRequestHandler(),
		);

		$this->assertSame('/^\/fixed1\/(\d+)\/fixed2\/(.*)$/', $r->matches());
	}

	/**
	 * @testdox variablesFromUri() with successful matching and variable segments
	 */
	public function testVariablesFromUri1(): void
	{
		$r = new Route(
			route_segments:[
				new FixedSegment('/fixed1/'),
				new VariableSegment('param1', '\d+'),
				new FixedSegment('/fixed2/'),
				new VariableSegment('param2', '.*'),
			],
			methods:['GET'],
			handler:new DummyRequestHandler(),
		);

		$this->assertSame([
			'param1' => '123',
			'param2' => 'abc',
		], $r->variablesFromUri(
			new Uri('https://domain.tld/fixed1/123/fixed2/abc')
		));
	}

	/**
	 * @testdox variablesFromUri() with successful matching but variable segments
	 */
	public function testVariablesFromUri2(): void
	{
		$r = new Route(
			route_segments:[
				new FixedSegment('/fixed1'),
				new FixedSegment('/fixed2'),
			],
			methods:['GET'],
			handler:new DummyRequestHandler(),
		);

		$this->assertSame([], $r->variablesFromUri(
			new Uri('https://domain.tld/fixed1/fixed2')
		));
	}

	/**
	 * @testdox variablesFromUri() with failed matching
	 */
	public function testFailedVariablesFromUri(): void
	{
		$r = new Route(
			route_segments:[
				new FixedSegment('/fixed1/'),
				new VariableSegment('param1', '\d+'),
				new FixedSegment('/fixed2/'),
				new VariableSegment('param2', '.*'),
			],
			methods:['GET'],
			handler:new DummyRequestHandler(),
		);

		$this->assertNull($r->variablesFromUri(
			new Uri('https://domain.tld/fixed1/not_a_number/fixed2/abc')
		));
	}
}
