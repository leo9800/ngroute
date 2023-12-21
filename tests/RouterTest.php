<?php

use Leo980\RequestHandlerFixture\RequestHandler;
use Leo980\NgRoute\Constraint;
use Leo980\NgRoute\Data\Plain;
use Leo980\NgRoute\Exceptions\Router\MethodMismatchException;
use Leo980\NgRoute\Exceptions\Router\MissingParameterException;
use Leo980\NgRoute\Exceptions\Router\NoMatchingRouteException;
use Leo980\NgRoute\Parser\StdParser;
use Leo980\NgRoute\Router;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

use function Leo980\Reflector\reflect_get_property;

/**
 * @testdox Leo980\NgRoute\Router
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

		$this->assertIsArray(reflect_get_property($r, 'constraints'));
		$this->assertSame($c, reflect_get_property($r, 'constraints')[0]);
	}

	public function testOverrideGlobalConstraint(): void
	{

	}

	public function testRoutingFixedRoute(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/', new RequestHandler(), 'home');

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
		))->addRoute(['GET'], '/', new RequestHandler(), 'home');
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
		))->addRoute(['GET'], '/', new RequestHandler(), 'home');
		$r->handle(
			new ServerRequest('PUT', new Uri("https://domain.tld/"))
		);
	}

	public function testRoutingVariableRoute(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/user/{name}', new RequestHandler(), 'user');

		$this->assertInstanceOf(ResponseInterface::class, $r->handle(
			new ServerRequest('GET', new Uri("https://domain.tld/user/Leo"))
		));
	}

	public function testRoutingWithPrefix(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
			prefix:'/some/prefix',
		))->addRoute(['GET'], '/user/{name}', new RequestHandler(), 'user');

		$this->assertInstanceOf(ResponseInterface::class, $r->handle(
			new ServerRequest('GET', new Uri("https://domain.tld/some/prefix/user/Leo"))
		));
	}

	public function testCustomAttributeNameForParams(): void
	{
		$rh = new RequestHandler();
		$r = (new Router(
			new StdParser(),
			new Plain(),
			params_attribute: 'URLPARAMS',
		))
			->addRoute(['GET'], '/user/{name}', $rh, 'user')
			->handle(new ServerRequest('GET', new Uri("https://domain.tld/user/Leo")));

		$this->assertIsArray($rh->getRequest()->getAttribute('URLPARAMS'));
		$this->assertSame('Leo', $rh->getRequest()->getAttribute('URLPARAMS')['name']);
	}

	public function testGetUriFromName(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/', new RequestHandler(), 'home');

		$uri = $r->endpointUri('home', []);

		$this->assertEquals(new Uri('/'), $uri);
	}

	public function testGetUriFromNameWithParams(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/user/{name}', new RequestHandler(), 'user');

		$uri = $r->endpointUri('user', ['name' => 'Leo']);

		$this->assertEquals(new Uri('/user/Leo'), $uri);
	}

	public function testGetUriFromNameWithPrefix(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
			prefix:'/some/prefix',
		))->addRoute(['GET'], '/user/{name}', new RequestHandler(), 'user');

		$uri = $r->endpointUri('user', ['name' => 'Leo']);

		$this->assertEquals(new Uri('/some/prefix/user/Leo'), $uri);
	}

	public function testGetUriFromNameWithAbsentParams(): void
	{
		$this->expectException(MissingParameterException::class);

		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/user/{name}', new RequestHandler(), 'user');

		$r->endpointUri('user', ['nonsense' => 'Leo']);
	}

	public function testGetUriFromNonExistName(): void
	{
		$r = (new Router(
			new StdParser(),
			new Plain(),
		))->addRoute(['GET'], '/', new RequestHandler(), 'home');

		$uri = $r->endpointUri('some-page', []);

		$this->assertNull($uri);
	}
}
