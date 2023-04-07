<?php

use Leo\Fixtures\DummyRequestHandler;
use Leo\NgRoute\Data\Plain;
use Leo\NgRoute\Exceptions\Router\MethodMismatchException;
use Leo\NgRoute\Exceptions\Router\MissingParameterException;
use Leo\NgRoute\Exceptions\Router\NoMatchingRouteException;
use Leo\NgRoute\Parser\StdParser;
use Leo\NgRoute\Router;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

use function Leo\ReflectHelper\reflect_property;

/**
 * @testdox Leo\NgRoute\Router
 */
class RouterTest extends TestCase
{
	public function testHostConstraint(): void
	{
		$r1 = new Router(
			new StdParser(),
			new Plain(),
			host:'domain.tld',
		);

		$r2 = new Router(
			new StdParser(),
			new Plain(),
		);

		$this->assertSame('domain.tld', reflect_property($r1, 'host'));
		$this->assertNull(reflect_property($r2, 'host'));
	}

	public function testPortConstraint(): void
	{
		$r1 = new Router(
			new StdParser(),
			new Plain(),
			port:8443,
		);

		$r2 = new Router(
			new StdParser(),
			new Plain(),
		);

		$this->assertSame(8443, reflect_property($r1, 'port'));
		$this->assertNull(reflect_property($r2, 'port'));
	}

	public function testSchemeConstraint(): void
	{
		$r1 = new Router(
			new StdParser(),
			new Plain(),
			scheme:'https',
		);

		$r2 = new Router(
			new StdParser(),
			new Plain(),
		);

		$this->assertSame('https', reflect_property($r1, 'scheme'));
		$this->assertNull(reflect_property($r2, 'scheme'));
	}

	public function testRoutingFixedRoute(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/', new DummyRequestHandler(), 'home');

		$this->assertInstanceOf(ResponseInterface::class, $r->handle(
			new ServerRequest('GET', new Uri("https://domain.tld/"))
		));
	}

	public function testMismatchingFixedRoute(): void
	{
		$this->expectException(NoMatchingRouteException::class);

		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/', new DummyRequestHandler(), 'home');
		$r->handle(
			new ServerRequest('GET', new Uri("https://domain.tld/some-page"))
		);
	}

	public function testMethodMismatchingFixedRoute(): void
	{
		$this->expectException(MethodMismatchException::class);

		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/', new DummyRequestHandler(), 'home');
		$r->handle(
			new ServerRequest('PUT', new Uri("https://domain.tld/"))
		);
	}

	public function testRoutingVariableRoute(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/user/{name}', new DummyRequestHandler(), 'user');

		$this->assertInstanceOf(ResponseInterface::class, $r->handle(
			new ServerRequest('GET', new Uri("https://domain.tld/user/Leo"))
		));
	}

	public function testGetUriFromName(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/', new DummyRequestHandler(), 'home');

		$uri = $r->endpointUri('home', []);

		$this->assertEquals(new Uri('/'), $uri);
	}

	public function testGetUriFromNameWithParams(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/user/{name}', new DummyRequestHandler(), 'user');

		$uri = $r->endpointUri('user', ['name' => 'Leo']);

		$this->assertEquals(new Uri('/user/Leo'), $uri);
	}

	public function testGetUriFromNameWithAbsentParams(): void
	{
		$this->expectException(MissingParameterException::class);

		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/user/{name}', new DummyRequestHandler(), 'user');

		$r->endpointUri('user', ['nonsense' => 'Leo']);
	}

	public function testGetUriFromNameWithConstraints(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
			scheme:'https',
			host:'domain.tld',
			port:8443,
		))->addRoute(['GET'], '/', new DummyRequestHandler(), 'home');

		$uri = $r->endpointUri('home', []);

		$this->assertEquals(new Uri('https://domain.tld:8443/'), $uri);
	}

	public function testGetUriFromNonExistName(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/', new DummyRequestHandler(), 'home');

		$uri = $r->endpointUri('some-page', []);

		$this->assertNull($uri);
	}
}
