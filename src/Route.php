<?php

namespace Leo\NgRoute;

use Leo\NgRoute\Exceptions\Route\InvalidSegmentException;
use Leo\NgRoute\Segments\VariableSegment;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Route
{
	/**
	 * @param array<SegmentInterface> $route_segments
	 * @param array<string>           $methods
	 * @param RequestHandlerInterface $handler
	 * @param ?string                 $name
	 * @param ?string                 $host
	 * @param ?int                    $port
	 * @param ?string                 $scheme
	 */
	public function __construct(
		private array $route_segments,
		private array $methods,
		private RequestHandlerInterface $handler,
		private ?string $name=null,
		private ?string $host=null,
		private ?int $port=null,
		private ?string $scheme=null,
	)
	{
		foreach ($this->route_segments as $rs)
			if (!($rs instanceof SegmentInterface))
				throw new InvalidSegmentException();

		// HTTP methods should be in uppercase, and should not duplicate
		$this->methods = array_values(array_unique(
			array_map('strtoupper', $this->methods)
		));
	}

	/**
	 * Get regex matched by route
	 * @param  string $delimiter Regex delimiter
	 * @return string
	 */
	public function matches(string $delimiter = '/'): string
	{
		$regex = '';

		foreach ($this->route_segments as $rs)
			$regex .= $rs->matches($delimiter);

		return "$delimiter^$regex$$delimiter";
	}

	/**
	 * Get route segments
	 * @return array<SegmentInterface>
	 */
	public function getSegments(): array
	{
		return $this->route_segments;
	}

	/**
	 * Get allowed HTTP methods of route
	 * @return array<string>
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

	/**
	 * Get PSR-15 request handler associated to route
	 * @return RequestHandlerInterface
	 */
	public function getHandler(): RequestHandlerInterface
	{
		return $this->handler;
	}

	/**
	 * Get endpoint name of route, name is nullable
	 * @return ?string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Get hostname constraint of route, NULL is no constraint
	 * @return ?string
	 */
	public function getHost(): ?string
	{
		return $this->host;
	}

	/**
	 * Get port constraint of route, NULL is no constraint
	 * @return ?int
	 */
	public function getPort(): ?int
	{
		return $this->port;
	}

	/**
	 * Get scheme constraint of route, NULL is no constraint
	 * @return ?string
	 */
	public function getScheme(): ?string
	{
		return $this->scheme;
	}
}
