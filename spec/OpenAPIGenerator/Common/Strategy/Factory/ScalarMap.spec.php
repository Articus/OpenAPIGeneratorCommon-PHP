<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\ScalarMap::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates map strategy with scalar item strategy using specified options', function ()
	{
		$options = ['aaa' => 111];
		$scalarStrategy = mock(DT\Strategy\StrategyInterface::class);
		$strategyManager = mock(PM\PluginManagerInterface::class);
		$strategyManager->shouldReceive('__invoke')->with(OAGC\Strategy\Factory\PluginManager::P_SCALAR, $options)->andReturn($scalarStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();

		$factory = new OAGC\Strategy\Factory\ScalarMap();

		$strategy = $factory($container, 'test', $options);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($scalarStrategy);
	});
	it('creates map strategy with scalar item strategy using specified options and passing extract_std_class flag', function ()
	{
		$extractStdClass = true;
		$options = ['type' => OAGC\ScalarType::INT, 'extract_std_class' => $extractStdClass];
		$scalarStrategy = mock(DT\Strategy\StrategyInterface::class);
		$strategyManager = mock(PM\PluginManagerInterface::class);
		$strategyManager->shouldReceive('__invoke')->with(OAGC\Strategy\Factory\PluginManager::P_SCALAR, $options)->andReturn($scalarStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();

		$factory = new OAGC\Strategy\Factory\ScalarMap();

		$strategy = $factory($container, 'test', $options);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($scalarStrategy);
		expect(propertyByPath($strategy, ['extractStdClass']))->toBe($extractStdClass);
	});
	it('creates map strategy that extracts scalar array', function ()
	{
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn((new OAGC\Strategy\Factory\PluginManager)($container, 'test'))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\ScalarMap();

		$strategy = $factory($container, 'test', ['type' => OAGC\ScalarType::INT]);
		$map = ['a' => 1, 'b' => 2, 'c' => 3];
		expect($strategy->extract(new ArrayObject($map)))->toBe($map);
	});
	it('creates list strategy that hydrates scalar array', function ()
	{
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn((new OAGC\Strategy\Factory\PluginManager)($container, 'test'))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\ScalarMap();

		$strategy = $factory($container, 'test', ['type' => OAGC\ScalarType::INT]);
		$destination = new ArrayObject(['a' => 1, 'b' => 2, 'c' => 3]);
		$source = ['b' => 4, 'd' => 5];
		$strategy->hydrate($source, $destination);
		expect($destination->getArrayCopy())->toBe($source);
	});
	it('creates list strategy that merges scalar array', function ()
	{
		$container = mock(ContainerInterface::class);
		$container
			->shouldReceive('get')
			->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
			->andReturn((new OAGC\Strategy\Factory\PluginManager)($container, 'test'))
			->once()
		;

		$factory = new OAGC\Strategy\Factory\ScalarMap();

		$strategy = $factory($container, 'test', ['type' => OAGC\ScalarType::INT]);
		$destination = ['a' => 1, 'b' => 2, 'c' => 3];
		$source = ['b' => 4, 'd' => 5];
		$strategy->merge($source, $destination);
		expect($destination)->toBe($source);
	});
});
