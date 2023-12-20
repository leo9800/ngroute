<?php

use Leo980\NgRoute\Exceptions\Parser\BracketsMismatchException;
use Leo980\NgRoute\Exceptions\Parser\EmptyRouteException;
use Leo980\NgRoute\Exceptions\Parser\OptionalSegmentInMiddleException;
use Leo980\NgRoute\Parser\StdParser;
use Leo980\NgRoute\Segments\FixedSegment;
use Leo980\NgRoute\Segments\VariableSegment;
use PHPUnit\Framework\TestCase;

/**
 * @testdox Leo980\NgRoute\Parser\StdParser
 */
class StdParserTest extends TestCase
{
	/**
	 * @testdox Parse valid route string: $raw_route
	 * @dataProvider provideValidRouteData
	 * @param array<mixed> $route
	 */
	public function testParseValidRoute(string $raw_route, array $route): void
	{
		$sp = new StdParser();
		$this->assertEquals($route, $sp->parse($raw_route));
	}

	/**
	 * @testdox Throw exception on invalid route string: $raw_route
	 * @dataProvider provideInvalidRouteData
	 * @param class-string<Throwable> $exception
	 */
	public function testThrowExceptionOnInvalidRoute(string $raw_route, string $exception): void
	{
		$this->expectException($exception);
		$sp = new StdParser();
		$sp->parse($raw_route);
	}

	/**
	 * @return array<mixed>
	 */
	public static function provideValidRouteData(): array
	{
		return [
			[
				'/test',
				[
					[
						new FixedSegment('/test'),
					]
				]
			],
			[
				'/test/{param}',
				[
					[
						new FixedSegment('/test/'),
						new VariableSegment('param', VariableSegment::DEFAULT_MATCH),
					],
				],
			],
			[
				'/te{ param }st',
				[
					[
						new FixedSegment('/te'),
						new VariableSegment('param', VariableSegment::DEFAULT_MATCH),
						new FixedSegment('st'),
					],
				],
			],
			[
				'/test/{param1}/test2/{param2}',
				[
					[
						new FixedSegment('/test/'),
						new VariableSegment('param1', VariableSegment::DEFAULT_MATCH),
						new FixedSegment('/test2/'),
						new VariableSegment('param2', VariableSegment::DEFAULT_MATCH),
					],
				],
			],
			[
				'/test/{param:\d+}',
				[
					[
						new FixedSegment('/test/'),
						new VariableSegment('param', '\d+'),
					],
				],
			],
			[
				'/test/{ param : \d{1,9} }',
				[
					[
						new FixedSegment('/test/'),
						new VariableSegment('param', '\d{1,9}'),
					],
				],
			],
			[
				'/test[opt]',
				[
					[
						new FixedSegment('/test'),
					],
					[
						new FixedSegment('/testopt'),
					],
				],
			],
			[
				'/test[/{param}]',
				[
					[
						new FixedSegment('/test'),
					],
					[
						new FixedSegment('/test/'),
						new VariableSegment('param', VariableSegment::DEFAULT_MATCH),
					],
				],
			],
			[
				'/{param}[opt]',
				[
					[
						new FixedSegment('/'),
						new VariableSegment('param', VariableSegment::DEFAULT_MATCH),
					],
					[
						new FixedSegment('/'),
						new VariableSegment('param', VariableSegment::DEFAULT_MATCH),
						new FixedSegment('opt'),
					],
				],
			],
			[
				'/test[/{name}[/{id:[0-9]+}]]',
				[
					[
						new FixedSegment('/test'),
					],
					[
						new FixedSegment('/test/'),
						new VariableSegment('name', VariableSegment::DEFAULT_MATCH),
					],
					[
						new FixedSegment('/test/'),
						new VariableSegment('name', VariableSegment::DEFAULT_MATCH),
						new FixedSegment('/'),
						new VariableSegment('id', '[0-9]+')
					],
				],
			],
			[
				'',
				[
					[
						new FixedSegment(''),
					],
				],
			],
			[
				'[test]',
				[
					[
						new FixedSegment(''),
					],
					[
						new FixedSegment('test'),
					],
				],
			],
			[
				'/{foo-bar}',
				[
					[
						new FixedSegment('/'),
						new VariableSegment('foo-bar', VariableSegment::DEFAULT_MATCH),
					],
				],
			],
			[
				'/{_foo:.*}',
				[
					[
						new FixedSegment('/'),
						new VariableSegment('_foo', '.*'),
					],
				],
			],
		];
	}

	/**
	 * @return array<mixed>
	 */
	public static function provideInvalidRouteData(): array
	{
		return [
			[
				'/test[opt',
				BracketsMismatchException::class,
			],
			[
				'/test[opt[opt2]',
				BracketsMismatchException::class,
			],
			[
				'/testopt]',
				BracketsMismatchException::class,
			],
			[
				'/test[]',
				EmptyRouteException::class,
			],
			[
				'/test[[opt]]',
				EmptyRouteException::class,
			],
			[
				'[[test]]',
				EmptyRouteException::class,
			],
			[
				'/test[/opt]/required',
				OptionalSegmentInMiddleException::class,
			],
		];
	}
}
