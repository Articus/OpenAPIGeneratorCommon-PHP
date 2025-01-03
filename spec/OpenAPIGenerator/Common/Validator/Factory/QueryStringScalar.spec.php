<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use Mockery\Matcher\IsSame;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Validator\Factory\QueryStringScalar::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('throws on no type', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Validator\Factory\QueryStringScalar();

		$exception = new InvalidArgumentException('Option "type" is required.');
		expect(static fn () => $factory($container, ''))->toThrow($exception);
	});
	it('throws on invalid type', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Validator\Factory\QueryStringScalar();

		$exception = new InvalidArgumentException('Unknown type "test".');
		expect(static fn () => $factory($container, '', ['type' => 'test']))->toThrow($exception);
	});
	it('uses [] on no validators', function ()
	{
		$chain = mock(DT\Validator\ValidatorInterface::class);
		$manager = mock(PM\PluginManagerInterface::class);
		$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => []])->andReturn($chain)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

		$factory = new OAGC\Validator\Factory\QueryStringScalar();
		$obj = $factory($container, '', ['type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_BOOL]);

		$chain->shouldReceive('validate')->with(new IsSame(false))->andReturn([])->once();
		expect($obj->validate('false'))->toBe([]);
	});
	context('creates validator for boolean', function ()
	{
		it('denies unparsable string', function ()
		{
			$links = ['abc' => 123];
			$chain = mock(DT\Validator\ValidatorInterface::class);
			$manager = mock(PM\PluginManagerInterface::class);
			$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
			$container = mock(ContainerInterface::class);
			$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

			$factory = new OAGC\Validator\Factory\QueryStringScalar();
			$obj = $factory($container, '', ['type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_BOOL, 'validators' => $links]);
			$error = [
				DT\Validator\SerializableValue::INVALID_INNER => [
					OAGC\QueryStringScalarAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.',
				]
			];
			expect($obj->validate(''))->toBe($error);
			expect($obj->validate('0.0'))->toBe($error);
			expect($obj->validate('123.0'))->toBe($error);
			expect($obj->validate('-123.0'))->toBe($error);
			expect($obj->validate('123.456'))->toBe($error);
			expect($obj->validate('-123.456'))->toBe($error);
			expect($obj->validate('0'))->toBe($error);
			expect($obj->validate('123'))->toBe($error);
			expect($obj->validate('-123'))->toBe($error);
			expect($obj->validate('abc'))->toBe($error);
		});
		it('validates parsed string with value validator', function ()
		{
			$links = ['abc' => 123];
			$chain = mock(DT\Validator\ValidatorInterface::class);
			$manager = mock(PM\PluginManagerInterface::class);
			$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
			$container = mock(ContainerInterface::class);
			$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

			$factory = new OAGC\Validator\Factory\QueryStringScalar();
			$obj = $factory($container, '', ['type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_BOOL, 'validators' => $links]);

			$error1 = ['a' => 1];
			$chain->shouldReceive('validate')->with(new IsSame(false))->andReturn($error1)->once();
			expect($obj->validate('false'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

			$error2 = ['b' => 2];
			$chain->shouldReceive('validate')->with(new IsSame(true))->andReturn($error2)->once();
			expect($obj->validate('true'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);
		});
	});
	context('creates validator for integer', function ()
	{
		it('denies unparsable string', function ()
		{
			$links = ['abc' => 123];
			$chain = mock(DT\Validator\ValidatorInterface::class);
			$manager = mock(PM\PluginManagerInterface::class);
			$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
			$container = mock(ContainerInterface::class);
			$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

			$factory = new OAGC\Validator\Factory\QueryStringScalar();
			$obj = $factory($container, '', ['type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT, 'validators' => $links]);
			$error = [
				DT\Validator\SerializableValue::INVALID_INNER => [
					OAGC\QueryStringScalarAware::ERROR_INT => 'Invalid query string parameter type: expecting int.',
				]
			];
			expect($obj->validate(''))->toBe($error);
			expect($obj->validate('false'))->toBe($error);
			expect($obj->validate('true'))->toBe($error);
			expect($obj->validate('0.0'))->toBe($error);
			expect($obj->validate('123.0'))->toBe($error);
			expect($obj->validate('-123.0'))->toBe($error);
			expect($obj->validate('123.456'))->toBe($error);
			expect($obj->validate('-123.456'))->toBe($error);
			expect($obj->validate('abc'))->toBe($error);
		});
		it('validates parsed string with value validator', function ()
		{
			$links = ['abc' => 123];
			$chain = mock(DT\Validator\ValidatorInterface::class);
			$manager = mock(PM\PluginManagerInterface::class);
			$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
			$container = mock(ContainerInterface::class);
			$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

			$factory = new OAGC\Validator\Factory\QueryStringScalar();
			$obj = $factory($container, '', ['type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_INT, 'validators' => $links]);

			$error1 = ['a' => 1];
			$chain->shouldReceive('validate')->with(new IsSame(0))->andReturn($error1)->once();
			expect($obj->validate('0'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

			$error2 = ['b' => 2];
			$chain->shouldReceive('validate')->with(new IsSame(123))->andReturn($error2)->once();
			expect($obj->validate('123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

			$error3 = ['c' => 3];
			$chain->shouldReceive('validate')->with(new IsSame(-123))->andReturn($error3)->once();
			expect($obj->validate('-123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
		});
	});
	context('creates validator for float', function ()
	{
		it('denies unparsable string', function ()
		{
			$links = ['abc' => 123];
			$chain = mock(DT\Validator\ValidatorInterface::class);
			$manager = mock(PM\PluginManagerInterface::class);
			$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
			$container = mock(ContainerInterface::class);
			$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

			$factory = new OAGC\Validator\Factory\QueryStringScalar();
			$obj = $factory($container, '', ['type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_FLOAT, 'validators' => $links]);
			$error = [
				DT\Validator\SerializableValue::INVALID_INNER => [
					OAGC\QueryStringScalarAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.',
				]
			];
			expect($obj->validate(''))->toBe($error);
			expect($obj->validate('false'))->toBe($error);
			expect($obj->validate('true'))->toBe($error);
			expect($obj->validate('abc'))->toBe($error);
		});
		it('validates parsed string with value validator', function ()
		{
			$links = ['abc' => 123];
			$chain = mock(DT\Validator\ValidatorInterface::class);
			$manager = mock(PM\PluginManagerInterface::class);
			$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
			$container = mock(ContainerInterface::class);
			$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

			$factory = new OAGC\Validator\Factory\QueryStringScalar();
			$obj = $factory($container, '', ['type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_FLOAT, 'validators' => $links]);

			$error1 = ['a' => 1];
			$chain->shouldReceive('validate')->with(new IsSame(0.0))->andReturn($error1)->once();
			expect($obj->validate('0.0'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

			$error2 = ['b' => 2];
			$chain->shouldReceive('validate')->with(new IsSame(123.0))->andReturn($error2)->once();
			expect($obj->validate('123.0'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

			$error3 = ['c' => 3];
			$chain->shouldReceive('validate')->with(new IsSame(-123.0))->andReturn($error3)->once();
			expect($obj->validate('-123.0'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);

			$error4 = ['d' => 4];
			$chain->shouldReceive('validate')->with(new IsSame(123.456))->andReturn($error4)->once();
			expect($obj->validate('123.456'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error4]);

			$error5 = ['e' => 5];
			$chain->shouldReceive('validate')->with(new IsSame(-123.456))->andReturn($error5)->once();
			expect($obj->validate('-123.456'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error5]);

			$error6 = ['f' => 6];
			$chain->shouldReceive('validate')->with(new IsSame(0.0))->andReturn($error6)->once();
			expect($obj->validate('0'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error6]);

			$error7 = ['g' => 7];
			$chain->shouldReceive('validate')->with(new IsSame(123.0))->andReturn($error7)->once();
			expect($obj->validate('123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error7]);

			$error8 = ['h' => 8];
			$chain->shouldReceive('validate')->with(new IsSame(-123.0))->andReturn($error8)->once();
			expect($obj->validate('-123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error8]);
		});
	});
	context('creates validator for string', function ()
	{
		it('validates string with value validator', function ()
		{
			$links = ['abc' => 123];
			$chain = mock(DT\Validator\ValidatorInterface::class);
			$manager = mock(PM\PluginManagerInterface::class);
			$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
			$container = mock(ContainerInterface::class);
			$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

			$factory = new OAGC\Validator\Factory\QueryStringScalar();
			$obj = $factory($container, '', ['type' => OAGC\QueryStringScalarAware::SCALAR_TYPE_STRING, 'validators' => $links]);

			$error = ['a' => 1];
			$chain->shouldReceive('validate')->with(new IsSame('abc'))->andReturn($error)->once();
			expect($obj->validate('abc'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error]);
		});
	});
});
