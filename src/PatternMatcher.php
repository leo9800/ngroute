<?php

namespace Leo\NgRoute;

class PatternMatcher
{
	/**
	 * @var array<string, string>
	 */
	private array $matches = [];

	public function addPattern(string $pattern, string $replace): self
	{
		$this->matches[$pattern] = $replace;
		return $this;
	}

	public function replace(string $input): string
	{
		foreach ($this->matches as $p => $r)
			if ($input === $p)
				return $r;

		return $input;
	}
}
