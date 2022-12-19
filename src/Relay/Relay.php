<?php

namespace Leo\NgRoute\Relay;

use Leo\NgRoute\Relay\Exceptions\EmptyQueueException;
use Leo\NgRoute\Relay\Exceptions\InvalidMiddlewareException;
use Leo\NgRoute\Relay\Exceptions\InvalidRequestHandlerException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Chaining a PSR-15 request handler and multiple optional 
 * PSR-15 middlewares into a new PSR-15 request handler
 */
class Relay implements RequestHandlerInterface
{
	/**
	 * @var RequestHandlerInterface Top-level middleware wrapper
	 */
	private RequestHandlerInterface $handler;

	/**
	 * @param iterable<MiddlewareInterface|RequestHandlerInterface> $queue
	 */
	public function __construct(iterable $queue)
	{
		if (!is_array($queue))
			$queue = iterator_to_array($queue);

		if (empty($queue))
			throw new EmptyQueueException();

		$handler = array_pop($queue);

		if (!($handler instanceof RequestHandlerInterface))
			throw new InvalidRequestHandlerException();

		foreach (array_reverse($queue) as $middleware) {
			if (!($middleware instanceof MiddlewareInterface))
				throw new InvalidMiddlewareException();

			$handler = new MiddlewareWrapper($middleware, $handler);
		}

		$this->handler = $handler;
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return $this->handler->handle($request);
	}
}
