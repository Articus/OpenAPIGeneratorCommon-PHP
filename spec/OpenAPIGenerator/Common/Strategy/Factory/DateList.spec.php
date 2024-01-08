<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\DateList::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates list strategy with date strategy', function ()
	{
		$dateStrategy = mock(DT\Strategy\StrategyInterface::class);
		$strategyManager = mock(PM\PluginManagerInterface::class);
		$strategyManager->shouldReceive('__invoke')->with(OAGC\Strategy\Factory\PluginManager::P_DATE, [])->andReturn($dateStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();

		$factory = new OAGC\Strategy\Factory\DateList();

		$strategy = $factory($container, 'test');
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueList::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($dateStrategy);
	});
	it('creates list strategy that extracts date array', function ()
	{
		$dateStr1 = '2023-12-25';
		$dateObj1 = DateTime::createFromFormat(DateTime::RFC3339, '2023-12-25T00:00:00+00:00');
		$dateStr2 = '2023-12-26';
		$dateObj2 = DateTime::createFromFormat(DateTime::RFC3339, '2023-12-26T00:00:00+00:00');
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withMutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\DateList();

		$strategy = $factory($container, 'test');
		$list = [$dateStr1, $dateStr2];
		expect($strategy->extract(new ArrayObject()))->toBe([]);
		expect($strategy->extract(new ArrayObject([$dateObj1, $dateObj2])))->toBe($list);
		expect($strategy->extract(new ArrayObject([2 => $dateObj1, 3 => $dateObj2])))->toBe($list);
	});
	it('creates list strategy that hydrates to empty date array', function ()
	{
		$dateStr1 = '2023-12-25';
		$dateObj1 = DateTime::createFromFormat(DateTime::RFC3339, '2023-12-25T00:00:00+00:00');
		$dateStr2 = '2023-12-26';
		$dateObj2 = DateTime::createFromFormat(DateTime::RFC3339, '2023-12-26T00:00:00+00:00');
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withMutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\DateList();

		$strategy = $factory($container, 'test');
		$destination = new ArrayObject();
		$strategy->hydrate([$dateStr1, $dateStr2], $destination);
		expect($destination->getArrayCopy())->toEqual([0 => $dateObj1, 1 => $dateObj2]);
	});
	it('creates list strategy that hydrates to date array with items', function ()
	{
		$dateObj1 = DateTime::createFromFormat(DateTime::RFC3339, '2023-12-25T00:00:00+00:00');
		$dateStr2 = '2023-12-26';
		$dateObj2 = DateTime::createFromFormat(DateTime::RFC3339, '2023-12-26T00:00:00+00:00');
		$dateStr3 = '2023-12-27';
		$dateObj3 = DateTime::createFromFormat(DateTime::RFC3339, '2023-12-27T00:00:00+00:00');
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withMutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\DateList();

		$strategy = $factory($container, 'test');
		$destination = new ArrayObject([$dateObj1, $dateObj2]);
		$strategy->hydrate([$dateStr2, $dateStr3], $destination);
		expect($destination->getArrayCopy())->toEqual([2 => $dateObj2, 3 => $dateObj3]);
	});
	it('creates list strategy that merges to empty date array', function ()
	{
		$dateStr1 = '2023-12-25';
		$dateStr2 = '2023-12-26';
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withMutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\DateList();

		$strategy = $factory($container, 'test');
		$destination = [];
		$strategy->merge([$dateStr1, $dateStr2], $destination);
		expect($destination)->toBe([0 => $dateStr1, 1 => $dateStr2]);
	});
	it('creates list strategy that merges to date array with items', function ()
	{
		$dateStr1 = '2023-12-25';
		$dateStr2 = '2023-12-26';
		$dateStr3 = '2023-12-27';
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn(OAGC\Strategy\Factory\PluginManager::withMutableDateTime($container))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\DateList();

		$strategy = $factory($container, 'test');
		$destination = [$dateStr1, $dateStr2];
		$strategy->merge([$dateStr2, $dateStr3], $destination);
		expect($destination)->toBe([0 => $dateStr2, 1 => $dateStr3]);
	});
});
