<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginManagerInterface;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;
use spec\Example;

describe(OAGC\Strategy\Factory\NoArgObjectMap::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('throws if there is no type option', function ()
	{
		$exception = new LogicException('Option "type" is required');
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectMap();
		expect(static fn () => $factory($container, 'test'))->toThrow($exception);
	});
	it('throws if specified type is invalid', function ()
	{
		$exception = new LogicException('Type "unknown type" does not exist');
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectMap();
		expect(static fn () => $factory($container, 'test', ['type' => 'unknown type']))->toThrow($exception);
	});
	it('creates map strategy with item strategy declared for specified type', function ()
	{
		$type = Example\TestClass::class;
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectMap();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, '')->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();

		$strategy = $factory($container, 'test_service', ['type' => $type]);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($valueStrategy);
	});
	it('creates map strategy with item strategy declared for specified type and subset', function ()
	{
		$type = Example\TestClass::class;
		$subset = 'test_subset';
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectMap();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, $subset)->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();

		$strategy = $factory($container, 'test_service', ['type' => $type, 'subset' => $subset]);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($valueStrategy);
	});
	it('creates map strategy with item strategy declared for specified type and passing extract_std_class flag', function ()
	{
		$type = Example\TestClass::class;
		$extractStdClass = true;
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectMap();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, '')->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();

		$strategy = $factory($container, 'test_service', ['type' => $type, 'extract_std_class' => $extractStdClass]);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($valueStrategy);
		expect(propertyByPath($strategy, ['extractStdClass']))->toBe($extractStdClass);
	});
	it('creates map strategy that extracts array of extracted items', function ()
	{
		$source = ['a' => mock(), 'b' => mock(), 'c' => mock()];
		$destination = ['a' => 1, 'b' => 2, 'c' => 3];

		$type = Example\TestClass::class;
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectMap();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, '')->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();
		$valueStrategy->shouldReceive('extract')->with($source['a'])->andReturn($destination['a'])->once();
		$valueStrategy->shouldReceive('extract')->with($source['b'])->andReturn($destination['b'])->once();
		$valueStrategy->shouldReceive('extract')->with($source['c'])->andReturn($destination['c'])->once();

		$strategy = $factory($container, 'test', ['type' => $type]);
		expect($strategy->extract(new ArrayObject($source)))->toBe($destination);
	});
	it('creates map strategy that hydrates array of hydrated items', function ()
	{
		$source = ['b' => 1, 'd' => 2];
		$destination = new ArrayObject(['a' => mock(), 'b' => mock(), 'c' => mock()]);
		$newDestination = ['b' => mock(), 'd' => mock()];

		$type = Example\TestClass::class;
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectMap();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, '')->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();
		$valueStrategy->shouldReceive('hydrate')->withArgs(
			function ($a, &$b) use ($type, &$source, &$destination, &$newDestination)
			{
				$sourceKey = array_search($a, $source, true);
				$result = (($sourceKey !== false)
					&& (($destination->offsetExists($sourceKey) && ($destination[$sourceKey] === $b))
						|| ($b instanceof $type)
					)
				);
				if ($result)
				{
					$b = $newDestination[$sourceKey];
				}
				return $result;
			}
		)->times(count($source));

		$strategy = $factory($container, 'test', ['type' => $type]);
		$strategy->hydrate($source, $destination);
		expect($destination->getArrayCopy())->toBe($newDestination);
	});
	it('creates map strategy that merges array of merged items', function ()
	{
		$source = ['b' => 1, 'd' => 2];
		$destination = ['a' => 3, 'b' => 4, 'c' => 5];
		$extractions = ['d' => 6];
		$newDestination = ['b' => 8, 'd' => 9];

		$type = Example\TestClass::class;
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectMap();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, '')->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();
		$valueStrategy->shouldReceive('extract')->with(Mockery::type($type))->andReturn($extractions['d'])->once();
		$valueStrategy->shouldReceive('merge')->withArgs(
			function ($a, &$b) use ($type, &$source, &$destination, &$extractions, &$newDestination)
			{
				$sourceKey = array_search($a, $source, true);
				$result = (($sourceKey !== false)
					&& ((array_key_exists($sourceKey, $destination) && ($destination[$sourceKey] === $b))
						|| (array_key_exists($sourceKey, $extractions) && ($extractions[$sourceKey] === $b))
					)
				);
				if ($result)
				{
					$b = $newDestination[$sourceKey];
				}
				return $result;
			}
		)->times(count($source));

		$strategy = $factory($container, 'test', ['type' => $type]);
		$strategy->merge($source, $destination);
		expect($destination)->toBe($newDestination);
	});
});
