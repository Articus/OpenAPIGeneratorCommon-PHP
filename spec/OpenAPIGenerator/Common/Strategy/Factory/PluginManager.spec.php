<?php
declare(strict_types=1);

use Articus\PluginManager as PM;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\PluginManager::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates simple plugin manager with immutable date strategies by default', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\PluginManager();
		$manager = $factory($container, 'test');
		expect($manager)->toBeAnInstanceOf(PM\Simple::class);
		$factories = propertyByPath($manager, ['factories']);
		expect(array_keys($factories))->toBe([
			OAGC\Strategy\Factory\PluginManager::P_DATE,
			OAGC\Strategy\Factory\PluginManager::P_DATE_TIME,
			OAGC\Strategy\Factory\PluginManager::P_DATE_LIST,
			OAGC\Strategy\Factory\PluginManager::P_DATE_TIME_LIST,
			OAGC\Strategy\Factory\PluginManager::P_DATE_MAP,
			OAGC\Strategy\Factory\PluginManager::P_DATE_TIME_MAP,
			OAGC\Strategy\Factory\PluginManager::P_ENUM,
			OAGC\Strategy\Factory\PluginManager::P_OBJECT_LIST,
			OAGC\Strategy\Factory\PluginManager::P_OBJECT_MAP,
			OAGC\Strategy\Factory\PluginManager::P_QUERY_STRING_SCALAR,
			OAGC\Strategy\Factory\PluginManager::P_QUERY_STRING_SCALAR_ARRAY,
			OAGC\Strategy\Factory\PluginManager::P_SCALAR,
			OAGC\Strategy\Factory\PluginManager::P_SCALAR_LIST,
			OAGC\Strategy\Factory\PluginManager::P_SCALAR_MAP,
		]);
		expect($factories[OAGC\Strategy\Factory\PluginManager::P_DATE])->toBeAnInstanceOf(OAGC\Strategy\Factory\ImmutableDate::class);
		expect($factories[OAGC\Strategy\Factory\PluginManager::P_DATE_TIME])->toBeAnInstanceOf(OAGC\Strategy\Factory\ImmutableDateTime::class);
	});
	it('creates simple plugin manager with immutable date strategies', function ()
	{
		$container = mock(ContainerInterface::class);
		$manager = OAGC\Strategy\Factory\PluginManager::withImmutableDateTime($container);
		expect($manager)->toBeAnInstanceOf(PM\Simple::class);
		$factories = propertyByPath($manager, ['factories']);
		expect($factories[OAGC\Strategy\Factory\PluginManager::P_DATE])->toBeAnInstanceOf(OAGC\Strategy\Factory\ImmutableDate::class);
		expect($factories[OAGC\Strategy\Factory\PluginManager::P_DATE_TIME])->toBeAnInstanceOf(OAGC\Strategy\Factory\ImmutableDateTime::class);
	});
	it('creates simple plugin manager with mutable date strategies', function ()
	{
		$container = mock(ContainerInterface::class);
		$manager = OAGC\Strategy\Factory\PluginManager::withMutableDateTime($container);
		expect($manager)->toBeAnInstanceOf(PM\Simple::class);
		$factories = propertyByPath($manager, ['factories']);
		expect($factories[OAGC\Strategy\Factory\PluginManager::P_DATE])->toBeAnInstanceOf(OAGC\Strategy\Factory\MutableDate::class);
		expect($factories[OAGC\Strategy\Factory\PluginManager::P_DATE_TIME])->toBeAnInstanceOf(OAGC\Strategy\Factory\MutableDateTime::class);
	});
});
