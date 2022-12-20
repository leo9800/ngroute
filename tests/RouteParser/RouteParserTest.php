<?php

use Leo\NgRoute\RouteParser\Exceptions\BracketsMismatchException;
use Leo\NgRoute\RouteParser\Exceptions\EmptyOptionalSegmentException;
use Leo\NgRoute\RouteParser\Exceptions\OptionalSegmentsInMiddleException;
use Leo\NgRoute\RouteParser\RouteParser;
use PHPUnit\Framework\TestCase;

/**
 * @testdox Leo\NgRoute\RouteParser\RouteParser
 */
class RouteParserTest extends TestCase
{
	/**
	 * @dataProvider provideValidRouteData
	 */
	public function testParseValidRoute(string $route, array $route_data): void
	{
		$parser = new RouteParser();
		$this->assertSame($route_data, $parser->parse($route));
	}

	/**
	 * @dataProvider provideInvalidRouteData
	 */
	public function testThrowExceptionOnInvalidRoute(string $route, string $exception_class): void
	{
		$this->expectException($exception_class);

		$parser = new RouteParser();
		$parser->parse($route);
	}

	public function provideValidRouteData(): array
	{
		return [
			[
				'/test',
				[
					['/test'],
				],
			],
			[
				'/test/{param}',
				[
					['/test/', ['param', '[^/]+']],
				],
			],
			[
				'/te{ param }st',
				[
					['/te', ['param', '[^/]+'], 'st'],
				],
			],
			[
				'/test/{param1}/test2/{param2}',
				[
					['/test/', ['param1', '[^/]+'], '/test2/', ['param2', '[^/]+']],
				],
			],
			[
				'/test/{param:\d+}',
				[
					['/test/', ['param', '\d+']],
				],
			],
			[
				'/test/{ param : \d{1,9} }',
				[
					['/test/', ['param', '\d{1,9}']],
				],
			],
			[
				'/test[opt]',
				[
					['/test'],
					['/testopt'],
				],
			],
			[
				'/test[/{param}]',
				[
					['/test'],
					['/test/', ['param', '[^/]+']],
				],
			],
			[
				'/{param}[opt]',
				[
					['/', ['param', '[^/]+']],
					['/', ['param', '[^/]+'], 'opt'],
				],
			],
			[
				'/test[/{name}[/{id:[0-9]+}]]',
				[
					['/test'],
					['/test/', ['name', '[^/]+']],
					['/test/', ['name', '[^/]+'], '/', ['id', '[0-9]+']],
				],
			],
			[
				'',
				[
					[''],
				],
			],
			[
				'[test]',
				[
					[''],
					['test'],
				],
			],
			[
				'/{foo-bar}',
				[
					['/', ['foo-bar', '[^/]+']],
				],
			],
			[
				'/{_foo:.*}',
				[
					['/', ['_foo', '.*']],
				],
			],
		];
	}

	public function provideInvalidRouteData(): array
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
				EmptyOptionalSegmentException::class,
			],
			[
				'/test[[opt]]',
				EmptyOptionalSegmentException::class,
			],
			[
				'[[test]]',
				EmptyOptionalSegmentException::class,
			],
			[
				'/test[/opt]/required',
				OptionalSegmentsInMiddleException::class,
			],
		];
	}
}
