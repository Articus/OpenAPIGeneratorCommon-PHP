<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;
use spec\Example\TestEnum;

describe(OAGC\Strategy\Factory\EnumerationList::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates list strategy with enum strategy', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$options = ['aaa' => 111];
		$enumStrategy = mock(DT\Strategy\StrategyInterface::class);
		$strategyManager = mock(PM\PluginManagerInterface::class);
		$strategyManager->shouldReceive('__invoke')->with(OAGC\Strategy\Factory\PluginManager::P_ENUM, $options)->andReturn($enumStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();

		$factory = new OAGC\Strategy\Factory\EnumerationList();

		$strategy = $factory($container, 'test', $options);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueList::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($enumStrategy);
	});
	it('creates list strategy that extracts enum array', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withImmutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\EnumerationList();

		$strategy = $factory($container, 'test', ['type' => TestEnum::class]);
		$list = ['abc'/*TestEnum::ABC->value*/, 'def'/*TestEnum::DEF->value*/];
		expect($strategy->extract(new ArrayObject()))->toBe([]);
		expect($strategy->extract(new ArrayObject([TestEnum::ABC, TestEnum::DEF])))->toBe($list);
		expect($strategy->extract(new ArrayObject([2 => TestEnum::ABC, 3 => TestEnum::DEF])))->toBe($list);
	});
	it('creates list strategy that hydrates to empty enum array', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withImmutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\EnumerationList();

		$strategy = $factory($container, 'test', ['type' => TestEnum::class]);
		$destination = new ArrayObject();
		$strategy->hydrate(['abc'/*TestEnum::ABC->value*/, 'def'/*TestEnum::DEF->value*/], $destination);
		expect($destination->getArrayCopy())->toEqual([0 => TestEnum::ABC, 1 => TestEnum::DEF]);
	});
	it('creates list strategy that hydrates to enum array with items', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withImmutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\EnumerationList();

		$strategy = $factory($container, 'test', ['type' => TestEnum::class]);
		$destination = new ArrayObject([TestEnum::ABC, TestEnum::DEF]);
		$strategy->hydrate(['def'/*TestEnum::DEF->value*/, 'ghi'/*TestEnum::GHI->value*/], $destination);
		expect($destination->getArrayCopy())->toEqual([2 => TestEnum::DEF, 3 => TestEnum::GHI]);
	});
	it('creates list strategy that merges to empty enum array', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withMutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\EnumerationList();

		$strategy = $factory($container, 'test', ['type' => TestEnum::class]);
		$destination = [];
		$strategy->merge(['abc'/*TestEnum::ABC->value*/, 'def'/*TestEnum::DEF->value*/], $destination);
		expect($destination)->toBe([0 => 'abc'/*TestEnum::ABC->value*/, 1 => 'def'/*TestEnum::DEF->value*/]);
	});
	it('creates list strategy that merges to enum array with items', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withMutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\EnumerationList();

		$strategy = $factory($container, 'test', ['type' => TestEnum::class]);
		$destination = ['abc'/*TestEnum::ABC->value*/, 'def'/*TestEnum::DEF->value*/];
		$strategy->merge(['def'/*TestEnum::DEF->value*/, 'ghi'/*TestEnum::GHI->value*/], $destination);
		expect($destination)->toBe([0 => 'def'/*TestEnum::DEF->value*/, 1 => 'ghi'/*TestEnum::GHI->value*/]);
	});
});
