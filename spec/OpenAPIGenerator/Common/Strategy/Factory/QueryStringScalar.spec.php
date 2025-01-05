<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\QueryStringScalar::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('throws on no type', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\QueryStringScalar();

		$exception = new InvalidArgumentException('Option "type" is required.');
		expect(static fn () => $factory($container, ''))->toThrow($exception);
	});
	it('throws on invalid type', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\QueryStringScalar();

		$exception = new InvalidArgumentException('Unknown type "test".');
		expect(static fn () => $factory($container, '', ['type' => 'test']))->toThrow($exception);
	});
	it('uses Articus\DataTransfer\Strategy\Whatever on no strategy', function ()
	{
		$options = [
			'type' => OAGC\ScalarType::BOOL,
		];

		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$manager = mock(PM\PluginManagerInterface::class);
		$manager->shouldReceive('__invoke')->with(DT\Strategy\Whatever::class, [])->andReturn($valueStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

		$factory = new OAGC\Strategy\Factory\QueryStringScalar();
		$obj = $factory($container, '', $options);

		$source = mock();
		$valueStrategy->shouldReceive('extract')->with($source)->andReturn(false);
		expect($obj->extract($source))->toBe('false');
	});
	context('strategy for boolean', function ()
	{
		context('->extract', function ()
		{
			it('extracts source with value strategy and serializes it', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$extract = static function (bool $value) use ($valueStrategy, $obj): string
				{
					$source = mock();
					$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
					return $obj->extract($source);
				};
				expect($extract(false))->toBe('false');
				expect($extract(true))->toBe('true');
			});
		});
		context('->hydrate', function ()
		{
			it('throws on unparsable source', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$error = [OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
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
				expect($hydrate(''))->toBe($error);
				expect($hydrate('0.0'))->toBe($error);
				expect($hydrate('123.0'))->toBe($error);
				expect($hydrate('-123.0'))->toBe($error);
				expect($hydrate('123.456'))->toBe($error);
				expect($hydrate('-123.456'))->toBe($error);
				expect($hydrate('0'))->toBe($error);
				expect($hydrate('123'))->toBe($error);
				expect($hydrate('-123'))->toBe($error);
				expect($hydrate('abc'))->toBe($error);
			});
			it('parses source and hydrates it to destination with value strategy', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$value = mock();

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$hydrate = static function (string $source, bool $parsedSource, $newDestination) use ($valueStrategy, $obj)
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
				expect($hydrate('false', false, $value))->toBe($value);
				expect($hydrate('true', true, $value))->toBe($value);
			});
		});
		context('->merge', function ()
		{
			it('throws on unparsable source', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$error = [OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
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
				expect($merge(''))->toBe($error);
				expect($merge('0.0'))->toBe($error);
				expect($merge('123.0'))->toBe($error);
				expect($merge('-123.0'))->toBe($error);
				expect($merge('123.456'))->toBe($error);
				expect($merge('-123.456'))->toBe($error);
				expect($merge('0'))->toBe($error);
				expect($merge('123'))->toBe($error);
				expect($merge('-123'))->toBe($error);
				expect($merge('abc'))->toBe($error);
			});
			it('throws on unparsable destination', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$error = [OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.'];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
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
				expect($merge(''))->toBe($error);
				expect($merge('0.0'))->toBe($error);
				expect($merge('123.0'))->toBe($error);
				expect($merge('-123.0'))->toBe($error);
				expect($merge('123.456'))->toBe($error);
				expect($merge('-123.456'))->toBe($error);
				expect($merge('0'))->toBe($error);
				expect($merge('123'))->toBe($error);
				expect($merge('-123'))->toBe($error);
				expect($merge('abc'))->toBe($error);
			});
			it('parses source and destination, merges them with value strategy and serializes merge result', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$merge = static function (string $source, bool $parsedSource, string $destination, bool $parsedDestination, bool $newParsedDestination) use ($valueStrategy, $obj)
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
				expect($merge('false', false, 'true', true, false))->toBe('false');
			});
		});
	});
	context('strategy for integer', function ()
	{
		context('->extract', function ()
		{
			it('extracts source with value strategy and serializes it', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$extract = static function (int $value) use ($valueStrategy, $obj): string
				{
					$source = mock();
					$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
					return $obj->extract($source);
				};
				expect($extract(0))->toBe('0');
				expect($extract(123))->toBe('123');
				expect($extract(-123))->toBe('-123');
			});
		});
		context('->hydrate', function ()
		{
			it('throws on unparsable source', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
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
				expect($hydrate(''))->toBe($error);
				expect($hydrate('false'))->toBe($error);
				expect($hydrate('true'))->toBe($error);
				expect($hydrate('0.0'))->toBe($error);
				expect($hydrate('123.0'))->toBe($error);
				expect($hydrate('-123.0'))->toBe($error);
				expect($hydrate('123.456'))->toBe($error);
				expect($hydrate('-123.456'))->toBe($error);
				expect($hydrate('abc'))->toBe($error);
			});
			it('parses source and hydrates it to destination with value strategy', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$value = mock();

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$hydrate = static function (string $source, int $parsedSource, $newDestination) use ($valueStrategy, $obj)
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
				expect($hydrate('0', 0, $value))->toBe($value);
				expect($hydrate('123', 123, $value))->toBe($value);
				expect($hydrate('-123', -123, $value))->toBe($value);
			});
		});
		context('->merge', function ()
		{
			it('throws on unparsable source', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
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
				expect($merge(''))->toBe($error);
				expect($merge('false'))->toBe($error);
				expect($merge('true'))->toBe($error);
				expect($merge('0.0'))->toBe($error);
				expect($merge('123.0'))->toBe($error);
				expect($merge('-123.0'))->toBe($error);
				expect($merge('123.456'))->toBe($error);
				expect($merge('-123.456'))->toBe($error);
				expect($merge('abc'))->toBe($error);
			});
			it('throws on unparsable destination', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$error = [OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.'];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
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
				expect($merge(''))->toBe($error);
				expect($merge('false'))->toBe($error);
				expect($merge('true'))->toBe($error);
				expect($merge('0.0'))->toBe($error);
				expect($merge('123.0'))->toBe($error);
				expect($merge('-123.0'))->toBe($error);
				expect($merge('123.456'))->toBe($error);
				expect($merge('-123.456'))->toBe($error);
				expect($merge('abc'))->toBe($error);
			});
			it('parses source and destination, merges them with value strategy and serializes merge result', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$merge = static function (string $source, int $parsedSource, string $destination, int $parsedDestination, int $newParsedDestination) use ($valueStrategy, $obj)
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
				expect($merge('111', 111, '222', 222, 333))->toBe('333');
			});
		});
	});
	context('strategy for float', function ()
	{
		context('->extract', function ()
		{
			it('extracts source with value strategy and serializes it', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$extract = static function (float $value) use ($valueStrategy, $obj): string
				{
					$source = mock();
					$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
					return $obj->extract($source);
				};
				expect($extract(0.0))->toBe('0');
				expect($extract(123.0))->toBe('123');
				expect($extract(-123.0))->toBe('-123');
				expect($extract(123.456))->toBe('123.456');
				expect($extract(-123.456))->toBe('-123.456');
			});
		});
		context('->hydrate', function ()
		{
			it('throws on unparsable source', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$error = [OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
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
				expect($hydrate(''))->toBe($error);
				expect($hydrate('false'))->toBe($error);
				expect($hydrate('true'))->toBe($error);
				expect($hydrate('abc'))->toBe($error);
			});
			it('parses source and hydrates it to destination with value strategy', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$value = mock();

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$hydrate = static function (string $source, float $parsedSource, $newDestination) use ($valueStrategy, $obj)
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
				expect($hydrate('0', 0.0, $value))->toBe($value);
				expect($hydrate('123', 123.0, $value))->toBe($value);
				expect($hydrate('-123', -123.0, $value))->toBe($value);
				expect($hydrate('0.0', 0.0, $value))->toBe($value);
				expect($hydrate('123', 123.0, $value))->toBe($value);
				expect($hydrate('-123', -123.0, $value))->toBe($value);
				expect($hydrate('123.456', 123.456, $value))->toBe($value);
				expect($hydrate('-123.456', -123.456, $value))->toBe($value);
			});
		});
		context('->merge', function ()
		{
			it('throws on unparsable source', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$error = [OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$merge = static function (string $value) use ($obj): array
				{
					try
					{
						$destination = '123.456';
						$obj->merge($value, $destination);
					}
					catch (DT\Exception\InvalidData $e)
					{
						return $e->getViolations();
					}
					throw new LogicException('No expected exception');
				};
				expect($merge(''))->toBe($error);
				expect($merge('false'))->toBe($error);
				expect($merge('true'))->toBe($error);
				expect($merge('abc'))->toBe($error);
			});
			it('throws on unparsable destination', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$error = [OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.'];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$merge = static function (string $value) use ($obj): array
				{
					try
					{
						$source = '123.456';
						$obj->merge($source, $value);
					}
					catch (DT\Exception\InvalidData $e)
					{
						return $e->getViolations();
					}
					throw new LogicException('No expected exception');
				};
				expect($merge(''))->toBe($error);
				expect($merge('false'))->toBe($error);
				expect($merge('true'))->toBe($error);
				expect($merge('abc'))->toBe($error);
			});
			it('parses source and destination, merges them with value strategy and serializes merge result', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$merge = static function (string $source, float $parsedSource, string $destination, float $parsedDestination, float $newParsedDestination) use ($valueStrategy, $obj)
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
				expect($merge('111', 111.0, '222', 222.0, 333.0))->toBe('333');
				expect($merge('111.0', 111.0, '222.0', 222.0, 333.0))->toBe('333');
				expect($merge('111.111', 111.111, '222.222', 222.222, 333.333))->toBe('333.333');
			});
		});
	});
	context('strategy for string', function ()
	{
		context('->extract', function ()
		{
			it('extracts source with value strategy and serializes it', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::STRING,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$extract = static function (string $value) use ($valueStrategy, $obj): string
				{
					$source = mock();
					$valueStrategy->shouldReceive('extract')->with($source)->andReturn($value)->once();
					return $obj->extract($source);
				};
				expect($extract(''))->toBe('');
				expect($extract('abc'))->toBe('abc');
			});
		});
		context('->hydrate', function ()
		{
			it('parses source and hydrates it to destination with value strategy', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::STRING,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];
				$value = mock();

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$hydrate = static function (string $source, string $parsedSource, $newDestination) use ($valueStrategy, $obj)
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
				expect($hydrate('', '', $value))->toBe($value);
				expect($hydrate('abc', 'abc', $value))->toBe($value);
			});
		});
		context('->merge', function ()
		{
			it('parses source and destination, merges them with value strategy and serializes merge result', function ()
			{
				$valueStrategyName = 'test_strategy';
				$valueStrategyOptions = ['aaa' => 111];
				$options = [
					'type' => OAGC\ScalarType::STRING,
					'strategy' => ['name' => $valueStrategyName, 'options' => $valueStrategyOptions],
				];

				$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with($valueStrategyName, $valueStrategyOptions)->andReturn($valueStrategy)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Strategy\Factory\QueryStringScalar();
				$obj = $factory($container, '', $options);

				$merge = static function (string $source, string $parsedSource, string $destination, string $parsedDestination, string $newParsedDestination) use ($valueStrategy, $obj)
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
				expect($merge('aaa', 'aaa', 'bbb', 'bbb', 'ccc'))->toBe('ccc');
			});
		});
	});
});
