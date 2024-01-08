<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\DateMap::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates map strategy with date item strategy', function ()
	{
		$dateStrategy = mock(DT\Strategy\StrategyInterface::class);
		$strategyManager = mock(PM\PluginManagerInterface::class);
		$strategyManager->shouldReceive('__invoke')->with(OAGC\Strategy\Factory\PluginManager::P_DATE, [])->andReturn($dateStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();

		$factory = new OAGC\Strategy\Factory\DateMap();

		$strategy = $factory($container, 'test');
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($dateStrategy);
		expect(propertyByPath($strategy, ['extractStdClass']))->toBeFalsy();
	});
	it('creates map strategy with date item strategy passing extract_std_class flag', function ()
	{
		$dateStrategy = mock(DT\Strategy\StrategyInterface::class);
		$strategyManager = mock(PM\PluginManagerInterface::class);
		$strategyManager->shouldReceive('__invoke')->with(OAGC\Strategy\Factory\PluginManager::P_DATE, [])->andReturn($dateStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();
		$extractStdClass = true;

		$factory = new OAGC\Strategy\Factory\DateMap();

		$strategy = $factory($container, 'test', ['extract_std_class' => $extractStdClass]);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($dateStrategy);
		expect(propertyByPath($strategy, ['extractStdClass']))->toBe($extractStdClass);
	});
	it('creates map strategy that extracts date array', function ()
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

		$factory = new OAGC\Strategy\Factory\DateMap();

		$strategy = $factory($container, 'test');
		expect($strategy->extract(new ArrayObject()))->toBe([]);
		expect($strategy->extract(new ArrayObject(['a' => $dateObj1, 'b' => $dateObj2])))->toBe(['a' => $dateStr1, 'b' => $dateStr2]);
	});
	it('creates list strategy that hydrates date array', function ()
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

		$factory = new OAGC\Strategy\Factory\DateMap();

		$strategy = $factory($container, 'test');
		$destination = new ArrayObject(['a' => $dateObj1, 'b' => $dateObj2]);
		$source = ['b' => $dateStr2, 'c' => $dateStr3];
		$strategy->hydrate($source, $destination);
		expect($destination->getArrayCopy())->toEqual(['b' => $dateObj2, 'c' => $dateObj3]);
	});
	it('creates list strategy that merges date array', function ()
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

		$factory = new OAGC\Strategy\Factory\DateMap();

		$strategy = $factory($container, 'test');
		$destination = ['a' => $dateStr1, 'b' => $dateStr2];
		$source = ['b' => $dateStr2, 'c' => $dateStr3];
		$strategy->merge($source, $destination);
		expect($destination)->toBe($source);
	});
});
