<?php

use Leo\Fixtures\DummyRequestHandler;
use Leo\NgRoute\Data\Plain;
use Leo\NgRoute\Exceptions\Data\DuplicatedRouteNameException;
use Leo\NgRoute\Route;
use Leo\NgRoute\Segments\FixedSegment;
use Leo\NgRoute\Segments\VariableSegment;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @testdox Leo\NgRoute\Data\Plain
 */
class PlainTest extends TestCase
{
	public function testThrowExceptionOnRoutesWithDuplicatedName(): void
	{
		$this->expectException(DuplicatedRouteNameException::class);

		$p = new Plain();

		$p->addRoute(new Route([], [], new DummyRequestHandler(), "Test123"));
		$p->addRoute(new Route([], [], new DummyRequestHandler(), "Test123"));
	}

	/**
	 * @testdox URI matching hit, return Route object
	 */
	public function testUri1(): void
	{
		$r1 = new Route([new FixedSegment('/test123')], [], new DummyRequestHandler());
		$r2 = new Route([new FixedSegment('/test456')], [], new DummyRequestHandler());
		$p = new Plain();
		$p->addRoute($r1);
		$p->addRoute($r2);

		$this->assertSame($r2, $p->findRouteByUri(
			new Uri('https://domain.tld:8443/test456')
		));
	}

	/**
	 * @testdox URI matching missed, return NULL
	 */
	public function testUri2(): void
	{
		$r1 = new Route([new FixedSegment('/test123')], [], new DummyRequestHandler());
		$r2 = new Route([new FixedSegment('/test456')], [], new DummyRequestHandler());
		$p = new Plain();
		$p->addRoute($r1);
		$p->addRoute($r2);

		$this->assertNull($p->findRouteByUri(
			new Uri('https://domain.tld:8443/not-exist')
		));
	}

	/**
	 * @testdox Endpoint name matching hit, return Route object
	 */
	public function testName1(): void
	{
		$r1 = new Route([new FixedSegment('/test123')], [], new DummyRequestHandler(), "t123");
		$r2 = new Route([new FixedSegment('/test456')], [], new DummyRequestHandler());
		$p = new Plain();
		$p->addRoute($r1);
		$p->addRoute($r2);

		$this->assertSame($r1, $p->findRouteByName('t123'));
	}

	/**
	 * @testdox Endpoint name matching missed, return NULL
	 */
	public function testName2(): void
	{
		$r1 = new Route([new FixedSegment('/test123')], [], new DummyRequestHandler(), "t123");
		$r2 = new Route([new FixedSegment('/test456')], [], new DummyRequestHandler());
		$p = new Plain();
		$p->addRoute($r1);
		$p->addRoute($r2);

		$this->assertNull($p->findRouteByName('t000'));
	}

	public function testParameterExtraction(): void
	{
		$r = new Route([
			new FixedSegment('/user/'),
			new VariableSegment('name', VariableSegment::DEFAULT_MATCH),
			new FixedSegment('/post/'),
			new VariableSegment('post_id', '\d+'),
		], ['GET'], new DummyRequestHandler());
		$p = new Plain();
		$p->addRoute($r);

		$this->assertSame($r, $p->findRouteByUri(
			new Uri('https://domain.tld:8443/user/Leo/post/123'), params:$params
		));

		$this->assertSame([
			'name' => 'Leo',
			'post_id' => '123',
		], $params);
	}

	public function testHostnameConstraintChecking(): void
	{
		$r = new Route([new FixedSegment('/')], [], new DummyRequestHandler(), host:"domain.tld");
		$p = new Plain();
		$p->addRoute($r);

		$this->assertNull($p->findRouteByUri(
			new Uri('https://other.domain.tld:8443/')
		));
	}

	public function testPortConstraintChecking(): void
	{
		$r = new Route([new FixedSegment('/')], [], new DummyRequestHandler(), port:443);
		$p = new Plain();
		$p->addRoute($r);

		$this->assertNull($p->findRouteByUri(
			new Uri('https://domain.tld:8443/')
		));
	}

	public function testSchemeConstraintChecking(): void
	{
		$r = new Route([new FixedSegment('/')], [], new DummyRequestHandler(), scheme:'https');
		$p = new Plain();
		$p->addRoute($r);

		$this->assertNull($p->findRouteByUri(
			new Uri('http://domain.tld:8443/')
		));
	}
}
