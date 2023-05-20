<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Strategy\Factory\ScalarMap::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('throws if there is no type option', function ()
	{
		$exception = new LogicException('Option "type" is required');
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ScalarMap();
		expect(static fn () => $factory($container, 'test'))->toThrow($exception);
	});
	it('creates map strategy with scalar item strategy using specified type', function ()
	{
		$container = mock(ContainerInterface::class);
		$type = OAGC\Validator\Scalar::TYPE_INT;
		$factory = new OAGC\Strategy\Factory\ScalarMap();

		$strategy = $factory($container, 'test', ['type' => $type]);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBeAnInstanceOf(OAGC\Strategy\Scalar::class);
		expect(propertyByPath($strategy, ['valueStrategy', 'type']))->toBe($type);
	});
	it('creates map strategy with scalar item strategy using specified type and passing extract_std_class flag', function ()
	{
		$container = mock(ContainerInterface::class);
		$type = OAGC\Validator\Scalar::TYPE_INT;
		$extractStdClass = true;
		$factory = new OAGC\Strategy\Factory\ScalarMap();

		$strategy = $factory($container, 'test', ['type' => $type, 'extract_std_class' => $extractStdClass]);
		expect($strategy)->toBeAnInstanceOf(DT\Strategy\IdentifiableValueMap::class);
		expect(propertyByPath($strategy, ['valueStrategy']))->toBeAnInstanceOf(OAGC\Strategy\Scalar::class);
		expect(propertyByPath($strategy, ['extractStdClass']))->toBe($extractStdClass);
		expect(propertyByPath($strategy, ['valueStrategy', 'type']))->toBe($type);
	});
	it('creates map strategy that extracts scalar array', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ScalarMap();

		$strategy = $factory($container, 'test', ['type' => OAGC\Validator\Scalar::TYPE_INT]);
		$map = ['a' => 1, 'b' => 2, 'c' => 3];
		expect($strategy->extract(new \ArrayObject($map)))->toBe($map);
	});
	it('creates list strategy that hydrates scalar array', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ScalarMap();

		$strategy = $factory($container, 'test', ['type' => OAGC\Validator\Scalar::TYPE_INT]);
		$destination = new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3]);
		$source = ['b' => 4, 'd' => 5];
		$strategy->hydrate($source, $destination);
		expect($destination->getArrayCopy())->toBe($source);
	});
	it('creates list strategy that merges scalar array', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Strategy\Factory\ScalarMap();

		$strategy = $factory($container, 'test', ['type' => OAGC\Validator\Scalar::TYPE_INT]);
		$destination = ['a' => 1, 'b' => 2, 'c' => 3];
		$source = ['b' => 4, 'd' => 5];
		$strategy->merge($source, $destination);
		expect($destination)->toBe($source);
	});
});
