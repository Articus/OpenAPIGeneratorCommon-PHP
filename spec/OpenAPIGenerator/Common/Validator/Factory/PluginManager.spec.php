<?php
declare(strict_types=1);

use Articus\PluginManager as PM;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Validator\Factory\PluginManager::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates simple plugin manager with validators', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Validator\Factory\PluginManager();
		$manager = $factory($container, 'test');
		expect($manager)->toBeAnInstanceOf(PM\Simple::class);
		$factories = propertyByPath($manager, ['factories']);
		expect(array_keys($factories))->toBe([
			OAGC\Validator\Factory\PluginManager::P_COUNT,
			OAGC\Validator\Factory\PluginManager::P_DATE,
			OAGC\Validator\Factory\PluginManager::P_DATE_TIME,
			OAGC\Validator\Factory\PluginManager::P_ENUM,
			OAGC\Validator\Factory\PluginManager::P_ENUM_ANON,
			OAGC\Validator\Factory\PluginManager::P_LENGTH,
			OAGC\Validator\Factory\PluginManager::P_MATCH,
			OAGC\Validator\Factory\PluginManager::P_QUERY_STRING_SCALAR,
			OAGC\Validator\Factory\PluginManager::P_QUERY_STRING_SCALAR_ARRAY,
			OAGC\Validator\Factory\PluginManager::P_RANGE,
			OAGC\Validator\Factory\PluginManager::P_SCALAR,
		]);
	});
});
