<?php
declare(strict_types=1);

namespace spec\OpenAPIGenerator\Common\Strategy;

use Articus\DataTransfer as DT;
use OpenAPIGenerator\Common as OAGC;
use Interop\Container\ContainerInterface;

\describe(OAGC\Strategy\Factory\ScalarList::class, function ()
{
	\afterEach(function ()
	{
		\Mockery::close();
	});
	\it('throws if there is no type option', function ()
	{
		$exception = new \LogicException('Option "type" is required');
		\expect(function()
		{
			$container = \mock(ContainerInterface::class);
			$factory = new OAGC\Strategy\Factory\ScalarList();
			$strategy = $factory($container, 'test');
		})->toThrow($exception);
	});
	\it('creates list strategy with scalar item strategy using specified type', function ()
	{
		$container = \mock(ContainerInterface::class);
		$type = OAGC\Validator\Scalar::TYPE_INT;
		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', ['type' => $type]);
		\expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueList::class);
		\expect(\propertyByPath($strategy, ['valueStrategy']))->toBeAnInstanceOf(OAGC\Strategy\Scalar::class);
		\expect(\propertyByPath($strategy, ['valueStrategy', 'type']))->toBe($type);
	});
	\it('creates list strategy that extracts scalar array', function ()
	{
		$container = \mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', ['type' => OAGC\Validator\Scalar::TYPE_INT]);
		$list = [1, 2, 3];
		\expect($strategy->extract(new \ArrayObject()))->toBe([]);
		\expect($strategy->extract(new \ArrayObject($list)))->toBe($list);
		\expect($strategy->extract(new \ArrayObject([3 => 1, 4 => 2, 5 => 3])))->toBe($list);
	});
	\it('creates list strategy that hydrates to empty scalar array', function ()
	{
		$container = \mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', ['type' => OAGC\Validator\Scalar::TYPE_INT]);
		$destination = new \ArrayObject();
		$strategy->hydrate([4, 5], $destination);
		\expect($destination->getArrayCopy())->toBe([0 => 4, 1 => 5]);
	});
	\it('creates list strategy that hydrates to scalar array with items', function ()
	{
		$container = \mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', ['type' => OAGC\Validator\Scalar::TYPE_INT]);
		$destination = new \ArrayObject([1, 2, 3]);
		$strategy->hydrate([4, 5], $destination);
		\expect($destination->getArrayCopy())->toBe([3 => 4, 4 => 5]);
	});
	\it('creates list strategy that merges to empty scalar array', function ()
	{
		$container = \mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', ['type' => OAGC\Validator\Scalar::TYPE_INT]);
		$destination = [];
		$strategy->merge([4, 5], $destination);
		\expect($destination)->toBe([0 => 4, 1 => 5]);
	});
	\it('creates list strategy that merges to scalar array with items', function ()
	{
		$container = \mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ScalarList();

		$strategy = $factory($container, 'test', ['type' => OAGC\Validator\Scalar::TYPE_INT]);
		$destination = [1, 2, 3];
		$strategy->merge([4, 5], $destination);
		\expect($destination)->toBe([0 => 4, 1 => 5]);
	});
});
