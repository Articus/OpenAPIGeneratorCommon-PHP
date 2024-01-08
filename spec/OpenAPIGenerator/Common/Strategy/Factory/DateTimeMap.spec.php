<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\DateTimeMap::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates map strategy with date item strategy', function ()
	{
		$dateStrategy = mock(DT\Strategy\StrategyInterface::class);
		$strategyManager = mock(PM\PluginManagerInterface::class);
		$strategyManager->shouldReceive('__invoke')->with(OAGC\Strategy\Factory\PluginManager::P_DATE_TIME, [])->andReturn($dateStrategy)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();

		$factory = new OAGC\Strategy\Factory\DateTimeMap();

		$strategy = $factory($container, 'test');
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBe($dateStrategy);
		expect(propertyByPath($strategy, ['extractStdClass']))->toBeFalsy();
	});
});
