<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\QueryStringScalarArray::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('throws on no type', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();

		$exception = new InvalidArgumentException('Option "type" is required.');
		expect(static fn () => $factory($container, ''))->toThrow($exception);
	});
	it('throws on invalid type', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();

		$exception = new InvalidArgumentException('Unknown type "test".');
		expect(static fn () => $factory($container, '', ['type' => 'test']))->toThrow($exception);
	});
	it('throws on no format', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();

		$exception = new InvalidArgumentException('Option "format" is required.');
		expect(static fn () => $factory($container, '', ['type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL]))->toThrow($exception);
	});
	it('throws on invalid format', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();

		$exception = new InvalidArgumentException('Unknown format "test".');
		expect(static fn () => $factory($container, '', ['type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL, 'format' => 'test']))->toThrow($exception);
	});
	it('uses Articus\DataTransfer\Strategy\Whatever on no strategy', function ()
	{
		$options = [
			'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
			'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
		];

		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$manager = mock(PM\PluginManagerInterface::class);
		$manager->shouldReceive('__invoke')->with(DT\Strategy\Whatever::class, [])->andReturn($valueStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

		$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
		$obj = $factory($container, '', $options);

		$source = mock();
		$valueStrategy->shouldReceive('extract')->with($source)->andReturn([false, true]);
		expect($obj->extract($source))->toBe('false,true');
	});
	context('strategy for boolean', function ()
	{
		context('joined with ","', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([false]))->toBe('false');
					expect($extract([false,true]))->toBe('false,true');
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarArrayAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('0.0'))->toBe($error);
					expect($hydrate('123.0'))->toBe($error);
					expect($hydrate('-123.0'))->toBe($error);
					expect($hydrate('123.456'))->toBe($error);
					expect($hydrate('-123.456'))->toBe($error);
					expect($hydrate('0'))->toBe($error);
					expect($hydrate('123'))->toBe($error);
					expect($hydrate('-123'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate('false,abc'))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('', [], $value))->toBe($value);
					expect($hydrate('false', [false], $value))->toBe($value);
					expect($hydrate('true', [true], $value))->toBe($value);
					expect($hydrate('false,true', [false, true], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = 'true';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('0'))->toBe($error);
					expect($merge('123'))->toBe($error);
					expect($merge('-123'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('false,abc'))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = 'true';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('0'))->toBe($error);
					expect($merge('123'))->toBe($error);
					expect($merge('-123'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('false,abc'))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					//No third unique value for bool to test properly
					expect($merge('false', [false], 'true', [true], [false]))->toBe('false');
					expect($merge('false,false', [false, false], 'true,true', [true, true], [false, true]))->toBe('false,true');
				});
			});
		});
		context('joined with " "', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([false]))->toBe('false');
					expect($extract([false,true]))->toBe('false true');
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarArrayAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('0.0'))->toBe($error);
					expect($hydrate('123.0'))->toBe($error);
					expect($hydrate('-123.0'))->toBe($error);
					expect($hydrate('123.456'))->toBe($error);
					expect($hydrate('-123.456'))->toBe($error);
					expect($hydrate('0'))->toBe($error);
					expect($hydrate('123'))->toBe($error);
					expect($hydrate('-123'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate('false abc'))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('', [], $value))->toBe($value);
					expect($hydrate('false', [false], $value))->toBe($value);
					expect($hydrate('true', [true], $value))->toBe($value);
					expect($hydrate('false true', [false, true], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = 'true';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('0'))->toBe($error);
					expect($merge('123'))->toBe($error);
					expect($merge('-123'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('false abc'))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = 'true';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('0'))->toBe($error);
					expect($merge('123'))->toBe($error);
					expect($merge('-123'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('false abc'))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					//No third unique value for bool to test properly
					expect($merge('false', [false], 'true', [true], [false]))->toBe('false');
					expect($merge('false false', [false, false], 'true true', [true, true], [false, true]))->toBe('false true');
				});
			});
		});
		context('joined with "\t"', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([false]))->toBe('false');
					expect($extract([false,true]))->toBe("false\ttrue");
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarArrayAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('0.0'))->toBe($error);
					expect($hydrate('123.0'))->toBe($error);
					expect($hydrate('-123.0'))->toBe($error);
					expect($hydrate('123.456'))->toBe($error);
					expect($hydrate('-123.456'))->toBe($error);
					expect($hydrate('0'))->toBe($error);
					expect($hydrate('123'))->toBe($error);
					expect($hydrate('-123'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate("false\tabc"))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('', [], $value))->toBe($value);
					expect($hydrate('false', [false], $value))->toBe($value);
					expect($hydrate('true', [true], $value))->toBe($value);
					expect($hydrate("false\ttrue", [false, true], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = 'true';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('0'))->toBe($error);
					expect($merge('123'))->toBe($error);
					expect($merge('-123'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge("false\tabc"))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = 'true';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('0'))->toBe($error);
					expect($merge('123'))->toBe($error);
					expect($merge('-123'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge("false\tabc"))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					//No third unique value for bool to test properly
					expect($merge('false', [false], 'true', [true], [false]))->toBe('false');
					expect($merge("false\tfalse", [false, false], "true\ttrue", [true, true], [false, true]))->toBe("false\ttrue");
				});
			});
		});
		context('joined with "|"', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([false]))->toBe('false');
					expect($extract([false,true]))->toBe('false|true');
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarArrayAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('0.0'))->toBe($error);
					expect($hydrate('123.0'))->toBe($error);
					expect($hydrate('-123.0'))->toBe($error);
					expect($hydrate('123.456'))->toBe($error);
					expect($hydrate('-123.456'))->toBe($error);
					expect($hydrate('0'))->toBe($error);
					expect($hydrate('123'))->toBe($error);
					expect($hydrate('-123'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate('false|abc'))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('', [], $value))->toBe($value);
					expect($hydrate('false', [false], $value))->toBe($value);
					expect($hydrate('true', [true], $value))->toBe($value);
					expect($hydrate('false|true', [false, true], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = 'true';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('0'))->toBe($error);
					expect($merge('123'))->toBe($error);
					expect($merge('-123'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('false|abc'))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = 'true';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('0'))->toBe($error);
					expect($merge('123'))->toBe($error);
					expect($merge('-123'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('false|abc'))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_BOOL,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					//No third unique value for bool to test properly
					expect($merge('false', [false], 'true', [true], [false]))->toBe('false');
					expect($merge('false|false', [false, false], 'true|true', [true, true], [false, true]))->toBe('false|true');
				});
			});
		});
	});
	context('strategy for integer', function ()
	{
		context('joined with ","', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([123]))->toBe('123');
					expect($extract([0, 123, -123]))->toBe('0,123,-123');
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('false'))->toBe($error);
					expect($hydrate('true'))->toBe($error);
					expect($hydrate('0.0'))->toBe($error);
					expect($hydrate('123.0'))->toBe($error);
					expect($hydrate('-123.0'))->toBe($error);
					expect($hydrate('123.456'))->toBe($error);
					expect($hydrate('-123.456'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate('123,abc'))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('0', [0], $value))->toBe($value);
					expect($hydrate('123', [123], $value))->toBe($value);
					expect($hydrate('-123', [-123], $value))->toBe($value);
					expect($hydrate('0,123,-123', [0, 123, -123], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = '123';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123,abc'))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = '123';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123,abc'))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('111', [111], '222', [222], [333]))->toBe('333');
					expect($merge('11,1111', [11, 1111], '22,2222', [22, 2222], [33, 3333]))->toBe('33,3333');
				});
			});
		});
		context('joined with " "', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([123]))->toBe('123');
					expect($extract([0, 123, -123]))->toBe('0 123 -123');
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('false'))->toBe($error);
					expect($hydrate('true'))->toBe($error);
					expect($hydrate('0.0'))->toBe($error);
					expect($hydrate('123.0'))->toBe($error);
					expect($hydrate('-123.0'))->toBe($error);
					expect($hydrate('123.456'))->toBe($error);
					expect($hydrate('-123.456'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate('123 abc'))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('0', [0], $value))->toBe($value);
					expect($hydrate('123', [123], $value))->toBe($value);
					expect($hydrate('-123', [-123], $value))->toBe($value);
					expect($hydrate('0 123 -123', [0, 123, -123], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = '123';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123 abc'))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = '123';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123 abc'))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('111', [111], '222', [222], [333]))->toBe('333');
					expect($merge('11 1111', [11, 1111], '22 2222', [22, 2222], [33, 3333]))->toBe('33 3333');
				});
			});
		});
		context('joined with "\t"', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([123]))->toBe('123');
					expect($extract([0, 123, -123]))->toBe("0\t123\t-123");
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('false'))->toBe($error);
					expect($hydrate('true'))->toBe($error);
					expect($hydrate('0.0'))->toBe($error);
					expect($hydrate('123.0'))->toBe($error);
					expect($hydrate('-123.0'))->toBe($error);
					expect($hydrate('123.456'))->toBe($error);
					expect($hydrate('-123.456'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate("123\tabc"))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('0', [0], $value))->toBe($value);
					expect($hydrate('123', [123], $value))->toBe($value);
					expect($hydrate('-123', [-123], $value))->toBe($value);
					expect($hydrate("0\t123\t-123", [0, 123, -123], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = '123';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge("123\tabc"))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = '123';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge("123\tabc"))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('111', [111], '222', [222], [333]))->toBe('333');
					expect($merge("11\t1111", [11, 1111], "22\t2222", [22, 2222], [33, 3333]))->toBe("33\t3333");
				});
			});
		});
		context('joined with "|"', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([123]))->toBe('123');
					expect($extract([0, 123, -123]))->toBe('0|123|-123');
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('false'))->toBe($error);
					expect($hydrate('true'))->toBe($error);
					expect($hydrate('0.0'))->toBe($error);
					expect($hydrate('123.0'))->toBe($error);
					expect($hydrate('-123.0'))->toBe($error);
					expect($hydrate('123.456'))->toBe($error);
					expect($hydrate('-123.456'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate('123|abc'))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('0', [0], $value))->toBe($value);
					expect($hydrate('123', [123], $value))->toBe($value);
					expect($hydrate('-123', [-123], $value))->toBe($value);
					expect($hydrate('0|123|-123', [0, 123, -123], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = '123';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123|abc'))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = '123';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('0.0'))->toBe($error);
					expect($merge('123.0'))->toBe($error);
					expect($merge('-123.0'))->toBe($error);
					expect($merge('123.456'))->toBe($error);
					expect($merge('-123.456'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123|abc'))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('111', [111], '222', [222], [333]))->toBe('333');
					expect($merge('11|1111', [11, 1111], '22|2222', [22, 2222], [33, 3333]))->toBe('33|3333');
				});
			});
		});
	});
	context('strategy for float', function ()
	{
		context('joined with ","', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([123.567]))->toBe('123.567');
					expect($extract([0, 123, -123, 0.0, 123.0, -123.0, 123.567, -123.567]))->toBe('0,123,-123,0,123,-123,123.567,-123.567');
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarArrayAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('false'))->toBe($error);
					expect($hydrate('true'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate('123.567,abc'))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('0', [0.0], $value))->toBe($value);
					expect($hydrate('123', [123.0], $value))->toBe($value);
					expect($hydrate('-123', [-123.0], $value))->toBe($value);
					expect($hydrate('0.0', [0.0], $value))->toBe($value);
					expect($hydrate('123.0', [123.0], $value))->toBe($value);
					expect($hydrate('-123.0', [-123.0], $value))->toBe($value);
					expect($hydrate('123.567', [123.567], $value))->toBe($value);
					expect($hydrate('-123.567', [-123.567], $value))->toBe($value);
					expect($hydrate('0,123,-123,0.0,123.0,-123.0,123.567,-123.567', [0.0, 123.0, -123.0, 0.0, 123.0, -123.0, 123.567, -123.567], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = '123.567';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123.567,abc'))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = '123.567';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123.567,abc'))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('111.111', [111.111], '222.222', [222.222], [333.333]))->toBe('333.333');
					expect($merge('111.11,11.111', [111.11, 11.111], '222.22,22.222', [222.22, 22.222], [333.33, 33.333]))->toBe('333.33,33.333');
				});
			});
		});
		context('joined with " "', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([123.567]))->toBe('123.567');
					expect($extract([0, 123, -123, 0.0, 123.0, -123.0, 123.567, -123.567]))->toBe('0 123 -123 0 123 -123 123.567 -123.567');
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarArrayAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('false'))->toBe($error);
					expect($hydrate('true'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate('123.567 abc'))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('0', [0.0], $value))->toBe($value);
					expect($hydrate('123', [123.0], $value))->toBe($value);
					expect($hydrate('-123', [-123.0], $value))->toBe($value);
					expect($hydrate('0.0', [0.0], $value))->toBe($value);
					expect($hydrate('123.0', [123.0], $value))->toBe($value);
					expect($hydrate('-123.0', [-123.0], $value))->toBe($value);
					expect($hydrate('123.567', [123.567], $value))->toBe($value);
					expect($hydrate('-123.567', [-123.567], $value))->toBe($value);
					expect($hydrate('0 123 -123 0.0 123.0 -123.0 123.567 -123.567', [0.0, 123.0, -123.0, 0.0, 123.0, -123.0, 123.567, -123.567], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = '123.567';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123.567 abc'))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = '123.567';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123.567 abc'))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('111.111', [111.111], '222.222', [222.222], [333.333]))->toBe('333.333');
					expect($merge('111.11 11.111', [111.11, 11.111], '222.22 22.222', [222.22, 22.222], [333.33, 33.333]))->toBe('333.33 33.333');
				});
			});
		});
		context('joined with "\t"', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([123.567]))->toBe('123.567');
					expect($extract([0, 123, -123, 0.0, 123.0, -123.0, 123.567, -123.567]))->toBe("0\t123\t-123\t0\t123\t-123\t123.567\t-123.567");
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarArrayAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('false'))->toBe($error);
					expect($hydrate('true'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate('123.567,abc'))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('0', [0.0], $value))->toBe($value);
					expect($hydrate('123', [123.0], $value))->toBe($value);
					expect($hydrate('-123', [-123.0], $value))->toBe($value);
					expect($hydrate('0.0', [0.0], $value))->toBe($value);
					expect($hydrate('123.0', [123.0], $value))->toBe($value);
					expect($hydrate('-123.0', [-123.0], $value))->toBe($value);
					expect($hydrate('123.567', [123.567], $value))->toBe($value);
					expect($hydrate('-123.567', [-123.567], $value))->toBe($value);
					expect($hydrate("0\t123\t-123\t0.0\t123.0\t-123.0\t123.567\t-123.567", [0.0, 123.0, -123.0, 0.0, 123.0, -123.0, 123.567, -123.567], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = '123.567';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge("123.567\tabc"))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = '123.567';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge("123.567\tabc"))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('111.111', [111.111], '222.222', [222.222], [333.333]))->toBe('333.333');
					expect($merge("111.11\t11.111", [111.11, 11.111], "222.22\t22.222", [222.22, 22.222], [333.33, 33.333]))->toBe("333.33\t33.333");
				});
			});
		});
		context('joined with "|"', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract([123.567]))->toBe('123.567');
					expect($extract([0, 123, -123, 0.0, 123.0, -123.0, 123.567, -123.567]))->toBe('0|123|-123|0|123|-123|123.567|-123.567');
				});
			});
			context('->hydrate', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarArrayAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = mock();
							$obj->hydrate($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($hydrate('false'))->toBe($error);
					expect($hydrate('true'))->toBe($error);
					expect($hydrate('abc'))->toBe($error);
					expect($hydrate('123.567|abc'))->toBe($error);
				});
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('0', [0.0], $value))->toBe($value);
					expect($hydrate('123', [123.0], $value))->toBe($value);
					expect($hydrate('-123', [-123.0], $value))->toBe($value);
					expect($hydrate('0.0', [0.0], $value))->toBe($value);
					expect($hydrate('123.0', [123.0], $value))->toBe($value);
					expect($hydrate('-123.0', [-123.0], $value))->toBe($value);
					expect($hydrate('123.567', [123.567], $value))->toBe($value);
					expect($hydrate('-123.567', [-123.567], $value))->toBe($value);
					expect($hydrate('0|123|-123|0.0|123.0|-123.0|123.567|-123.567', [0.0, 123.0, -123.0, 0.0, 123.0, -123.0, 123.567, -123.567], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('throws on unparsable source', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$destination = '123.567';
							$obj->merge($value, $destination);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123.567|abc'))->toBe($error);
				});
				it('throws on unparsable destination', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$error = [OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $value) use ($obj): array
					{
						try
						{
							$source = '123.567';
							$obj->merge($source, $value);
						}
						catch (DT\Exception\InvalidData $e)
						{
							return $e->getViolations();
						}
						throw new LogicException('No expected exception');
					};
					expect($merge('false'))->toBe($error);
					expect($merge('true'))->toBe($error);
					expect($merge('abc'))->toBe($error);
					expect($merge('123.567|abc'))->toBe($error);
				});
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_FLOAT,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('111.111', [111.111], '222.222', [222.222], [333.333]))->toBe('333.333');
					expect($merge('111.11|11.111', [111.11, 11.111], '222.22|22.222', [222.22, 22.222], [333.33, 33.333]))->toBe('333.33|33.333');
				});
			});
		});
	});
	context('strategy for string', function ()
	{
		context('joined with ","', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract(['abc']))->toBe('abc');
					expect($extract(['abc', 'def']))->toBe('abc,def');
				});
			});
			context('->hydrate', function ()
			{
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('', [], $value))->toBe($value);
					expect($hydrate('abc', ['abc'], $value))->toBe($value);
					expect($hydrate('abc,def', ['abc', 'def'], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('abc', ['abc'], 'def', ['def'], ['ghi']))->toBe('ghi');
					expect($merge('aa,aaaa', ['aa', 'aaaa'], 'bb,bbbb', ['bb', 'bbbb'], ['cc', 'cccc']))->toBe('cc,cccc');
				});
			});
		});
		context('joined with " "', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract(['abc']))->toBe('abc');
					expect($extract(['abc', 'def']))->toBe('abc def');
				});
			});
			context('->hydrate', function ()
			{
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('', [], $value))->toBe($value);
					expect($hydrate('abc', ['abc'], $value))->toBe($value);
					expect($hydrate('abc def', ['abc', 'def'], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('abc', ['abc'], 'def', ['def'], ['ghi']))->toBe('ghi');
					expect($merge('aa aaaa', ['aa', 'aaaa'], 'bb bbbb', ['bb', 'bbbb'], ['cc', 'cccc']))->toBe('cc cccc');
				});
			});
		});
		context('joined with "\t"', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract(['abc']))->toBe('abc');
					expect($extract(['abc', 'def']))->toBe("abc\tdef");
				});
			});
			context('->hydrate', function ()
			{
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('', [], $value))->toBe($value);
					expect($hydrate('abc', ['abc'], $value))->toBe($value);
					expect($hydrate("abc\tdef", ['abc', 'def'], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('abc', ['abc'], 'def', ['def'], ['ghi']))->toBe('ghi');
					expect($merge("aa\taaaa", ['aa', 'aaaa'], "bb\tbbbb", ['bb', 'bbbb'], ['cc', 'cccc']))->toBe("cc\tcccc");
				});
			});
		});
		context('joined with "|"', function ()
		{
			context('->extract', function ()
			{
				it('extracts source with value strategy and serializes it', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$extract = static function (array $value) use ($valueStrategy, $obj): string
					{
						$source = mock();
						$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
						return $obj->extract($source);
					};
					expect($extract([]))->toBe('');
					expect($extract(['abc']))->toBe('abc');
					expect($extract(['abc', 'def']))->toBe('abc|def');
				});
			});
			context('->hydrate', function ()
			{
				it('parses source and hydrates it to destination with value strategy', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarArrayAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];
					$value = mock();

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$hydrate = static function (string $source, array $parsedSource, $newDestination) use ($valueStrategy, $obj)
					{
						$destination = mock();
						$valueStrategy->shouldReceive('hydrate')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$destination, &$newDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $destination);
								if ($result)
								{
									$b = $newDestination;
								}
								return $result;
							}
						)->once();
						$obj->hydrate($source, $destination);
						return $destination;
					};
					expect($hydrate('', [], $value))->toBe($value);
					expect($hydrate('abc', ['abc'], $value))->toBe($value);
					expect($hydrate('abc|def', ['abc', 'def'], $value))->toBe($value);
				});
			});
			context('->merge', function ()
			{
				it('parses source and destination, merges them with value strategy and serializes merge result', function ()
				{
					$valueStrategyName = 'test_strategy';
					$valueStrategyOptions = ['aaa' => 111];
					$options = [
						'type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_STRING,
						'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
						'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
					];

					$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
					$manager = mock(PM\PluginManagerInterface::class);
					$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
					$container = mock(ContainerInterface::class);
					$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

					$factory = new OAGC\Strategy\Factory\QueryStringScalarArray();
					$obj = $factory($container, '', $options);

					$merge = static function (string $source, array $parsedSource, string $destination, array $parsedDestination, array $newParsedDestination) use ($valueStrategy, $obj)
					{
						$valueStrategy->shouldReceive('merge')->withArgs(
							static function ($a, &$b) use (&$parsedSource, &$parsedDestination, &$newParsedDestination): bool
							{
								$result = ($a === $parsedSource) && ($b === $parsedDestination);
								if ($result)
								{
									$b = $newParsedDestination;
								}
								return $result;
							}
						)->once();
						$obj->merge($source, $destination);
						return $destination;
					};
					expect($merge('abc', ['abc'], 'def', ['def'], ['ghi']))->toBe('ghi');
					expect($merge('aa|aaaa', ['aa', 'aaaa'], 'bb|bbbb', ['bb', 'bbbb'], ['cc', 'cccc']))->toBe('cc|cccc');
				});
			});
		});
	});
});
