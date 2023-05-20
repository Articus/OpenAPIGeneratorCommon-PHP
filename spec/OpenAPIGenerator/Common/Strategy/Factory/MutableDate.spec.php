<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\MutableDate::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates strategy that extracts date', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\MutableDate();
		$strategy = $factory($container, 'test');
		expect($strategy->extract(new DateTime('2020-11-30T00:00:00+00:00')))->toBe('2020-11-30');
	});
	it('creates strategy that hydrates date', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\MutableDate();
		$strategy = $factory($container, 'test');
		$date = null;
		$strategy->hydrate('2020-11-30', $date);
		expect($date)->toEqual(new DateTime('2020-11-30T00:00:00+00:00'));
	});
});
