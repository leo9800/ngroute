<?php

use Leo\NgRoute\Relay\MiddlewareWrapper;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'DummyMiddleware.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'DummyRequestHandler.php';


/**
 * @testdox Leo\NgRoute\Relay\MiddlewareWrapper
 */
class MiddlewareWrapperTest extends TestCase
{
	public function testWrappingMiddlewareWithRequestHandler(): void
	{
		$mw = new MiddlewareWrapper(
			new DummyMiddleware('TestField', '123'),
			new DummyRequestHandler('TestField'),
		);

		$r = $mw->handle(new \Nyholm\Psr7\ServerRequest(
			method:'GET',
			uri:'https://domain.tld/',
		));

		$this->assertSame('123', $r->getHeaderLine('TestField'));
	}
}
