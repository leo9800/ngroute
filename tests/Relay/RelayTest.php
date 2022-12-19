<?php

use Leo\NgRoute\Relay\Exceptions\EmptyQueueException;
use Leo\NgRoute\Relay\Exceptions\InvalidMiddlewareException;
use Leo\NgRoute\Relay\Exceptions\InvalidRequestHandlerException;
use Leo\NgRoute\Relay\Relay;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'DummyMiddleware.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'DummyRequestHandler.php';

/**
 * @testdox Leo\NgRoute\Relay\Relay
 */
class RelayTest extends TestCase
{
	public function testCreateRelayWithArrayQueue(): void
	{
		$r = new Relay([
			new DummyMiddleware('TestField', '456'),
			new DummyMiddleware('TestField', '123'),
			new DummyRequestHandler('TestField'),
		]);

		$this->assertInstanceOf(Relay::class, $r);
	}

	public function testCreateRelayWithIteratorQueue(): void
	{
		$i = new ArrayIterator([
			new DummyMiddleware('TestField', '456'),
			new DummyMiddleware('TestField', '123'),
			new DummyRequestHandler('TestField'),
		]);

		$r = new Relay($i);
		$this->assertInstanceOf(Relay::class, $r);
	}

	public function testThrowExceptionOnInvalidRequestHandler(): void
	{
		$this->expectException(InvalidRequestHandlerException::class);

		new Relay([
			new DummyMiddleware('TestField', '789'),
			new DummyMiddleware('TestField', '456'),
			new DummyMiddleware('TestField', '123'),
			// The request handler at bottom is missing ...
		]);
	}

	public function testThrowExceptionOnInvalidMiddleware(): void
	{
		$this->expectException(InvalidMiddlewareException::class);

		new Relay([
			new DummyMiddleware('TestField', '456'),
			// We got a request handler in middle, that's illegal
			new DummyRequestHandler('Boo'),
			new DummyMiddleware('TestField', '123'),
			new DummyRequestHandler('TestField'),
		]);
	}

	public function testThrowExceptionOnEmptyQueue(): void
	{
		$this->expectException(EmptyQueueException::class);

		new Relay([]); // The queue is an empty array ...
	}

	public function testHandleChainWithRequestHandlerOnly(): void
	{
		$r = new Relay([new DummyRequestHandler('TestField')]);

		$resp = $r->handle(new \Nyholm\Psr7\ServerRequest(
			method:'GET',
			uri:'https://domain.tld/',
		));

		$this->assertSame('', $resp->getHeaderLine('TestField'));
	}

	public function testHandleChainWithRequestHandlerAndMiddlewares(): void
	{
		$r = new Relay([
			new DummyMiddleware('TestField', '789'),
			new DummyMiddleware('TestField', '456'),
			new DummyMiddleware('TestField', '123'),
			new DummyRequestHandler('TestField'),
		]);

		$resp = $r->handle(new \Nyholm\Psr7\ServerRequest(
			method:'GET',
			uri:'https://domain.tld/'
		));

		$this->assertSame('789, 456, 123', $resp->getHeaderLine('TestField'));
	}
}
