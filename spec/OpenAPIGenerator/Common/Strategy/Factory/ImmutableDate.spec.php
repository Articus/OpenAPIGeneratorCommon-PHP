<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\ImmutableDate::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates strategy that extracts date', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ImmutableDate();
		$strategy = $factory($container, 'test');
		expect($strategy->extract(new DateTimeImmutable('2020-11-30T00:00:00+00:00')))->toBe('2020-11-30');
	});
	it('creates strategy that hydrates date', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ImmutableDate();
		$strategy = $factory($container, 'test');
		$date = null;
		$strategy->hydrate('2020-11-30', $date);
		expect($date)->toEqual(new DateTimeImmutable('2020-11-30T00:00:00+00:00'));
	});
});
