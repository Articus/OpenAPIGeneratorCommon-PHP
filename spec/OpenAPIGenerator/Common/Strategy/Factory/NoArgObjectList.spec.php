<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginManagerInterface;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;
use spec\Example;

describe(OAGC\Strategy\Factory\NoArgObjectList::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('throws if there is no type option', function ()
	{
		$exception = new LogicException('Option "type" is required');
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectList();
		expect(static fn () => $factory($container, 'test'))->toThrow($exception);
	});
	it('throws if specified type is invalid', function ()
	{
		$exception = new LogicException('Type "unknown type" does not exist');
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectList();
		expect(static fn () => $factory($container, 'test', ['type' => 'unknown type']))->toThrow($exception);
	});
	it('creates list strategy with item strategy declared for specified type', function ()
	{
		$type = Example\TestClass::class;
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectList();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, '')->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();

		$strategy = $factory($container, 'test_service', ['type' => $type]);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueList::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($valueStrategy);
	});
	it('creates list strategy with item strategy declared for specified type and subset', function ()
	{
		$type = Example\TestClass::class;
		$subset = 'test_subset';
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectList();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, $subset)->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();

		$strategy = $factory($container, 'test_service', ['type' => $type, 'subset' => $subset]);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueList::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($valueStrategy);
	});
	it('creates list strategy that extracts array of extracted items', function ()
	{
		$source = [mock(), mock(), mock()];
		$destination = [1, 2, 3];

		$type = Example\TestClass::class;
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectList();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, '')->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();
		$valueStrategy->shouldReceive('extract')->with($source[0])->andReturn($destination[0])->twice();
		$valueStrategy->shouldReceive('extract')->with($source[1])->andReturn($destination[1])->twice();
		$valueStrategy->shouldReceive('extract')->with($source[2])->andReturn($destination[2])->twice();

		$strategy = $factory($container, 'test', ['type' => $type]);
		expect($strategy->extract(new ArrayObject()))->toBe([]);
		expect($strategy->extract(new ArrayObject($source)))->toBe($destination);
		expect($strategy->extract(new ArrayObject(array_combine([3, 4, 5], $source))))->toBe($destination);
	});
	it('creates list strategy that hydrates to empty array', function ()
	{
		$source = [1, 2];
		$destination = new ArrayObject();
		$newDestination = [mock(), mock()];

		$type = Example\TestClass::class;
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectList();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, '')->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();
		$valueStrategy->shouldReceive('hydrate')->withArgs(
			function ($a, &$b) use ($type, &$source, &$newDestination)
			{
				$sourceIndex = array_search($a, $source, true);
				$result = (($sourceIndex !== false) && ($b instanceof $type));
				if ($result)
				{
					$b = $newDestination[$sourceIndex];
				}
				return $result;
			}
		)->times(count($source));

		$strategy = $factory($container, 'test', ['type' => $type]);
		$strategy->hydrate($source, $destination);
		expect($destination->getArrayCopy())->toBe($newDestination);
	});
	it('creates list strategy that hydrates to array with items', function ()
	{
		$source = [1, 2];
		$destination = new ArrayObject([mock(), mock(), mock()]);
		$newDestination = [mock(), mock()];

		$type = Example\TestClass::class;
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectList();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, '')->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();
		$valueStrategy->shouldReceive('hydrate')->withArgs(
			function ($a, &$b) use ($type, &$source, &$newDestination)
			{
				$sourceIndex = array_search($a, $source, true);
				$result = (($sourceIndex !== false) && ($b instanceof $type));
				if ($result)
				{
					$b = $newDestination[$sourceIndex];
				}
				return $result;
			}
		)->times(count($source));

		$strategy = $factory($container, 'test', ['type' => $type]);
		$strategy->hydrate($source, $destination);
		expect($destination->getArrayCopy())->toBe(array_combine([3, 4], $newDestination));
	});
	it('creates list strategy that merges to empty array', function ()
	{
		$source = [1, 2];
		$destination = [];
		$extractions = [6, 7];
		$newDestination = [8, 9];

		$type = Example\TestClass::class;
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectList();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, '')->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();
		$valueStrategy->shouldReceive('extract')->with(Mockery::type($type))->andReturnValues($extractions)->times(count($source));
		$valueStrategy->shouldReceive('merge')->withArgs(
			function ($a, &$b) use ($type, &$source, &$extractions, &$newDestination)
			{
				$sourceIndex = array_search($a, $source, true);
				$result = (($sourceIndex !== false) && ($b === $extractions[$sourceIndex]));
				if ($result)
				{
					$b = $newDestination[$sourceIndex];
				}
				return $result;
			}
		)->times(count($source));

		$strategy = $factory($container, 'test', ['type' => $type]);
		$strategy->merge($source, $destination);
		expect($destination)->toBe($newDestination);
	});
	it('creates list strategy that merges to array with items', function ()
	{
		$source = [1, 2];
		$destination = [3, 4, 5];
		$extractions = [6, 7];
		$newDestination = [8, 9];

		$type = Example\TestClass::class;
		$container = mock(ContainerInterface::class);
		$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
		$strategyPluginManager = mock(PluginManagerInterface::class);
		$valueStrategyMetadata = ['test_strategy_name', ['test_option_name' => 'test_option_value']];
		$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
		$factory = new OAGC\Strategy\Factory\NoArgObjectList();

		$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyPluginManager)->once();
		$metadataProvider->shouldReceive('getClassStrategy')->with($type, '')->andReturn($valueStrategyMetadata)->once();
		$strategyPluginManager->shouldReceive('__invoke')->with(...$valueStrategyMetadata)->andReturn($valueStrategy)->once();
		$valueStrategy->shouldReceive('extract')->with(Mockery::type($type))->andReturnValues($extractions)->times(count($source));
		$valueStrategy->shouldReceive('merge')->withArgs(
			function ($a, &$b) use ($type, &$source, &$extractions, &$newDestination)
			{
				$sourceIndex = array_search($a, $source, true);
				$result = (($sourceIndex !== false) && ($b === $extractions[$sourceIndex]));
				if ($result)
				{
					$b = $newDestination[$sourceIndex];
				}
				return $result;
			}
		)->times(count($source));

		$strategy = $factory($container, 'test', ['type' => $type]);
		$strategy->merge($source, $destination);
		expect($destination)->toBe($newDestination);
	});
});
