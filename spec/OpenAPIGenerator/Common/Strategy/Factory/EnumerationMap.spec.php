<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;
use spec\Example\TestEnum;

describe(OAGC\Strategy\Factory\EnumerationMap::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates map strategy with enum item strategy', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$options = ['aaa' => 111];
		$enumStrategy = mock(DT\Strategy\StrategyInterface::class);
		$strategyManager = mock(PM\PluginManagerInterface::class);
		$strategyManager->shouldReceive('__invoke')->with(OAGC\Strategy\Factory\PluginManager::P_ENUM, $options)->andReturn($enumStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();

		$factory = new OAGC\Strategy\Factory\EnumerationMap();

		$strategy = $factory($container, 'test', $options);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($enumStrategy);
		expect(propertyByPath($strategy, ['extractStdClass']))->toBeFalsy();
	});
	it('creates map strategy with enum item strategy passing extract_std_class flag', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$extractStdClass = true;
		$options = ['aaa' => 111, 'extract_std_class' => $extractStdClass];
		$enumStrategy = mock(DT\Strategy\StrategyInterface::class);
		$strategyManager = mock(PM\PluginManagerInterface::class);
		$strategyManager->shouldReceive('__invoke')->with(OAGC\Strategy\Factory\PluginManager::P_ENUM, $options)->andReturn($enumStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();

		$factory = new OAGC\Strategy\Factory\EnumerationMap();

		$strategy = $factory($container, 'test', $options);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($enumStrategy);
		expect(propertyByPath($strategy, ['extractStdClass']))->toBe($extractStdClass);
	});
	it('creates map strategy that extracts enum array', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withImmutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\EnumerationMap();

		$strategy = $factory($container, 'test', ['type' => TestEnum::class]);
		expect($strategy->extract(new ArrayObject()))->toBe([]);
		expect($strategy->extract(new ArrayObject(['a' => TestEnum::ABC, 'b' => TestEnum::DEF])))->toBe(['a' => 'abc'/*TestEnum::ABC->value*/, 'b' => 'def'/*TestEnum::DEF->value*/]);
	});
	it('creates list strategy that hydrates enum array', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withImmutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\EnumerationMap();

		$strategy = $factory($container, 'test', ['type' => TestEnum::class]);
		$destination = new ArrayObject(['a' => TestEnum::ABC, 'b' => TestEnum::DEF]);
		$source = ['b' => 'def'/*TestEnum::DEF->value*/, 'c' => 'ghi'/*TestEnum::GHI->value*/];
		$strategy->hydrate($source, $destination);
		expect($destination->getArrayCopy())->toEqual(['b' => TestEnum::DEF, 'c' => TestEnum::GHI]);
	});
	it('creates list strategy that merges date array', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withImmutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\EnumerationMap();

		$strategy = $factory($container, 'test', ['type' => TestEnum::class]);
		$destination = ['a' => 'abc'/*TestEnum::ABC->value*/, 'b' => 'def'/*TestEnum::DEF->value*/];
		$source = ['b' => 'def'/*TestEnum::DEF->value*/, 'c' => 'ghi'/*TestEnum::GHI->value*/];
		$strategy->merge($source, $destination);
		expect($destination)->toBe($source);
	});
});
