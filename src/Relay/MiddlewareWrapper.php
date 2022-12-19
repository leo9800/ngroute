<?php

namespace Leo\NgRoute\Relay;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Wrap a PSR-15 middleware and a PSR-15 request handler
 * into a new PSR-15 request handler
 */
class MiddlewareWrapper implements RequestHandlerInterface
{
	/**
	 * @param MiddlewareInterface     $middleware   This middleware
	 * @param RequestHandlerInterface $next_handler Next handler
	 */
	public function __construct(
		private MiddlewareInterface $middleware,
		private RequestHandlerInterface $next_handler,
	)
	{

	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return $this->middleware->process($request, $this->next_handler);
	}
}
