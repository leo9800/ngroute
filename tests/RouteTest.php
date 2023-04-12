<?php

use Leo\Fixtures\DummyRequestHandler;
use Leo\NgRoute\Constraint;
use Leo\NgRoute\Exceptions\Route\InvalidSegmentException;
use Leo\NgRoute\Route;
use Leo\NgRoute\Segments\FixedSegment;
use Leo\NgRoute\Segments\VariableSegment;
use PHPUnit\Framework\TestCase;

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
			// @phpstan-ignore-next-line
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
	 * @testdox getSegments()
	 */
	public function testGetSegments(): void
	{
		$s = [new FixedSegment('/')];
		$r = new Route(
			route_segments:$s,
			methods:['GET', 'POST'],
			handler:new DummyRequestHandler(),
		);

		$this->assertSame($s, $r->getSegments());
	}

	/**
	 * @testdox getName()
	 */
	public function testGetName(): void
	{
		$r = new Route(
			route_segments:[new FixedSegment('/')],
			methods:['GET', 'POST'],
			handler:new DummyRequestHandler(),
			name:'homepage'
		);

		$this->assertSame('homepage', $r->getName());
	}

	public function getConstaints(): void
	{
		$c = [
			new Constraint(host:'domain.tld'),
			new Constraint(host:'admin.domain.tld', scheme:'https'),
		];

		$r = new Route(
			route_segments:[new FixedSegment('/')],
			methods:['GET', 'POST'],
			handler:new DummyRequestHandler(),
			constraints:$c,
		);

		$this->assertSame($c, $r->getConstraints());
	}
}
