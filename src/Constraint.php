<?php

namespace Leo980\NgRoute;

// @codeCoverageIgnoreStart
readonly class Constraint
{
	public function __construct(
		public ?string $host = null,
		public ?int $port = null,
		public ?string $scheme = null,
	)
	{

	}
}
// @codeCoverageIgnoreEnd
