<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\MutableDateTime::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates strategy that extracts date-time without time fraction', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\MutableDateTime();
		$strategy = $factory($container, 'test');
		expect($strategy->extract(new DateTime('2020-11-30T23:59:30+04:00')))->toBe('2020-11-30T23:59:30+04:00');
	});
	it('creates strategy that extracts date-time with milliseconds', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\MutableDateTime();
		$strategy = $factory($container, 'test');
		expect($strategy->extract(new DateTime('2020-11-30T23:59:30.123+04:00')))->toBe('2020-11-30T23:59:30+04:00');
	});
	it('creates strategy that extracts date-time with microseconds', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\MutableDateTime();
		$strategy = $factory($container, 'test');
		expect($strategy->extract(new DateTime('2020-11-30T23:59:30.123456+04:00')))->toBe('2020-11-30T23:59:30+04:00');
	});
	it('creates strategy that hydrates date-time without time fraction', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\MutableDateTime();
		$strategy = $factory($container, 'test');
		$date = null;
		$strategy->hydrate('2020-11-30T23:59:30+00:00', $date);
		expect($date)->toEqual(new DateTime('2020-11-30T23:59:30+00:00'));
	});
	it('creates strategy that hydrates date-time with milliseconds', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\MutableDateTime();
		$strategy = $factory($container, 'test');
		$date = null;
		$strategy->hydrate('2020-11-30T23:59:30.123+00:00', $date);
		expect($date)->toEqual(new DateTime('2020-11-30T23:59:30.123+00:00'));
	});
	it('creates strategy that hydrates date-time with microseconds', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\MutableDateTime();
		$strategy = $factory($container, 'test');
		$date = null;
		$strategy->hydrate('2020-11-30T23:59:30.123456+00:00', $date);
		expect($date)->toEqual(new DateTime('2020-11-30T23:59:30.123456+00:00'));
	});
});
