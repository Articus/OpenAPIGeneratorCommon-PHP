<?php
declare(strict_types=1);

namespace spec\OpenAPIGenerator\Common\Strategy;

use OpenAPIGenerator\Common as OAGC;
use Interop\Container\ContainerInterface;

\describe(OAGC\Strategy\Factory\MutableDateTime::class, function ()
{
	\afterEach(function ()
	{
		\Mockery::close();
	});
	\it('creates strategy that extracts date-time', function ()
	{
		$container = \mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\MutableDateTime();
		$strategy = $factory($container, 'test');
		\expect($strategy)->toBeAnInstanceOf(OAGC\Strategy\DateTime::class);
		\expect($strategy->extract(new \DateTime('2020-11-30T23:59:30+04:00')))->toBe('2020-11-30T23:59:30+04:00');
	});
	\it('creates strategy that hydrates date-time', function ()
	{
		$container = \mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\MutableDateTime();
		$strategy = $factory($container, 'test');
		\expect($strategy)->toBeAnInstanceOf(OAGC\Strategy\DateTime::class);
		$date = null;
		$strategy->hydrate('2020-11-30T23:59:30+00:00', $date);
		\expect($date)->toEqual(new \DateTime('2020-11-30T23:59:30+00:00'));
	});
});
