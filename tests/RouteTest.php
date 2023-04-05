<?php

use Leo\Fixtures\DummyRequestHandler;
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

	/**
	 * @testdox getHost()
	 */
	public function testGetHost(): void
	{
		$r = new Route(
			route_segments:[new FixedSegment('/')],
			methods:['GET', 'POST'],
			handler:new DummyRequestHandler(),
			host:"domain.tld",
		);

		$this->assertSame("domain.tld", $r->getHost());
	}

	/**
	 * @testdox getPort()
	 */
	public function testGetPort(): void
	{
		$r = new Route(
			route_segments:[new FixedSegment('/')],
			methods:['GET', 'POST'],
			handler:new DummyRequestHandler(),
			port:8443,
		);

		$this->assertSame(8443, $r->getPort());
	}

	/**
	 * @testdox getScheme()
	 */
	public function testGetScheme(): void
	{
		$r = new Route(
			route_segments:[new FixedSegment('/')],
			methods:['GET', 'POST'],
			handler:new DummyRequestHandler(),
			scheme:'https',
		);

		$this->assertSame("https", $r->getScheme());
	}
}
