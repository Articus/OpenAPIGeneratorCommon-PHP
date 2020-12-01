<?php
declare(strict_types=1);

namespace spec\OpenAPIGenerator\Common\Strategy;

use OpenAPIGenerator\Common as OAGC;
use Interop\Container\ContainerInterface;

\describe(OAGC\Strategy\Factory\ImmutableDate::class, function ()
{
	\afterEach(function ()
	{
		\Mockery::close();
	});
	\it('creates strategy that extracts date', function ()
	{
		$container = \mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ImmutableDate();
		$strategy = $factory($container, 'test');
		\expect($strategy)->toBeAnInstanceOf(OAGC\Strategy\DateTime::class);
		\expect($strategy->extract(new \DateTimeImmutable('2020-11-30T00:00:00+00:00')))->toBe('2020-11-30');
	});
	\it('creates strategy that hydrates date', function ()
	{
		$container = \mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ImmutableDate();
		$strategy = $factory($container, 'test');
		\expect($strategy)->toBeAnInstanceOf(OAGC\Strategy\DateTime::class);
		$date = null;
		$strategy->hydrate('2020-11-30', $date);
		\expect($date)->toEqual(new \DateTimeImmutable('2020-11-30T00:00:00+00:00'));
	});
});
