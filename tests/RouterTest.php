<?php

use Leo\Fixtures\DummyRequestHandler;
use Leo\NgRoute\Constraint;
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
	public function testSetGlobalConstraint(): void
	{
		$c = new Constraint(scheme:'https', host:'domain.tld', port:8443);
		$r = new Router(
			new StdParser(),
			new Plain(),
			constraints:[$c],
		);

		$this->assertIsArray(reflect_property($r, 'constraints'));
		$this->assertSame($c, reflect_property($r, 'constraints')[0]);
	}

	public function testOverrideGlobalConstraint(): void
	{

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
