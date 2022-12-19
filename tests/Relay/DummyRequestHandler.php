<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DummyRequestHandler implements RequestHandlerInterface
{
	public function __construct(private string $key)
	{

	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return new \Nyholm\Psr7\Response(
			status:200,
			reason:'OK',
			version:'1.1',
			headers:[$this->key => $request->getHeaderLine($this->key)],
		);
	}
}
