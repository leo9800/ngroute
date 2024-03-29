<?php

use Leo980\RequestHandlerFixture\RequestHandler;
use Leo980\NgRoute\Constraint;
use Leo980\NgRoute\Exceptions\Route\InvalidSegmentException;
use Leo980\NgRoute\Route;
use Leo980\NgRoute\Segments\FixedSegment;
use Leo980\NgRoute\Segments\VariableSegment;
use PHPUnit\Framework\TestCase;

/**
 * @testdox Leo980\NgRoute\Route
 */
class RouteTest extends TestCase
{
	/**
	 * @testdox getHandler()
	 */
	public function testGetHandler(): void
	{
		$h = new RequestHandler();
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
			handler:new RequestHandler(),
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
			handler:new RequestHandler(),
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
			handler:new RequestHandler(),
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
			handler:new RequestHandler(),
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
			handler:new RequestHandler(),
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
			handler:new RequestHandler(),
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
			handler:new RequestHandler(),
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
			handler:new RequestHandler(),
			constraints:$c,
		);

		$this->assertSame($c, $r->getConstraints());
	}
}
