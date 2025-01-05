<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\ScalarList::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates list strategy with scalar item strategy using specified options', function ()
	{
		$options = ['aaa' => 111];
		$scalarStrategy = mock(DT\Strategy\StrategyInterface::class);
		$strategyManager = mock(PM\PluginManagerInterface::class);
		$strategyManager->shouldReceive('__invoke')->with(OAGC\Strategy\Factory\PluginManager::P_SCALAR, $options)->andReturn($scalarStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();

		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', $options);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueList::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($scalarStrategy);
	});
	it('creates list strategy that extracts scalar array', function ()
	{
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn((new OAGC\Strategy\Factory\PluginManager)($container, 'test'))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', ['type' => OAGC\ScalarType::INT]);
		expect($strategy->extract(new ArrayObject()))->toBe([]);
		expect($strategy->extract(new ArrayObject([1, 2, 3])))->toBe([1, 2, 3]);
		expect($strategy->extract(new ArrayObject([3 => 1, 4 => 2, 5 => 3])))->toBe([1, 2, 3]);
	});
	it('creates list strategy that hydrates to empty list', function ()
	{
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn((new OAGC\Strategy\Factory\PluginManager)($container, 'test'))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', ['type' => OAGC\ScalarType::INT]);
		$destination = new ArrayObject();
		$strategy->hydrate([4, 5], $destination);
		expect($destination->getArrayCopy())->toBe([0 => 4, 1 => 5]);
	});
	it('creates list strategy that hydrates to list with items', function ()
	{
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn((new OAGC\Strategy\Factory\PluginManager)($container, 'test'))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', ['type' => OAGC\ScalarType::INT]);
		$destination = new ArrayObject([1, 2, 3]);
		$strategy->hydrate([4, 5], $destination);
		expect($destination->getArrayCopy())->toBe([3 => 4, 4 => 5]);
	});
	it('creates list strategy that merges to empty array', function ()
	{
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn((new OAGC\Strategy\Factory\PluginManager)($container, 'test'))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', ['type' => OAGC\ScalarType::INT]);
		$destination = [];
		$strategy->merge([4, 5], $destination);
		expect($destination)->toBe([0 => 4, 1 => 5]);
	});
	it('creates list strategy that merges to array with items', function ()
	{
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn((new OAGC\Strategy\Factory\PluginManager)($container, 'test'))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', ['type' => OAGC\ScalarType::INT]);
		$destination = [1, 2, 3];
		$strategy->merge([4, 5], $destination);
		expect($destination)->toBe([0 => 4, 1 => 5]);
	});
});
