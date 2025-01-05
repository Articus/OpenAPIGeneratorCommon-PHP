<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use Mockery\Matcher\IsSame;
use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Validator\Factory\QueryStringScalarArray::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('throws on no type', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Validator\Factory\QueryStringScalarArray();

		$exception = new InvalidArgumentException('Option "type" is required.');
		expect(static fn () => $factory($container, ''))->toThrow($exception);
	});
	it('throws on invalid type', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Validator\Factory\QueryStringScalarArray();

		$exception = new InvalidArgumentException('Unknown type "test".');
		expect(static fn () => $factory($container, '', ['type' => 'test']))->toThrow($exception);
	});
	it('throws on no format', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Validator\Factory\QueryStringScalarArray();

		$exception = new InvalidArgumentException('Option "format" is required.');
		expect(static fn () => $factory($container, '', ['type' => OAGC\ScalarType::BOOL]))->toThrow($exception);
	});
	it('throws on invalid format', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Validator\Factory\QueryStringScalarArray();

		$exception = new InvalidArgumentException('Unknown format "test".');
		expect(static fn () => $factory($container, '', ['type' => OAGC\ScalarType::BOOL, 'format' => 'test']))->toThrow($exception);
	});
	it('uses [] on no validators', function ()
	{
		$options = [
			'type' => OAGC\ScalarType::BOOL,
			'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
		];
		$chain = mock(DT\Validator\ValidatorInterface::class);
		$manager = mock(PM\PluginManagerInterface::class);
		$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => []])->andReturn($chain)->once();
		$container = mock(ContainerInterface::class);
		$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

		$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
		$obj = $factory($container, '', $options);

		$chain->shouldReceive('validate')->with(new IsSame([false, true]))->andReturn([])->once();
		expect($obj->validate('false,true'))->toBe([]);
	});
	context('creates validator for boolean', function ()
	{
		context('joined with ","', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate('false,abc'))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([false]))->andReturn($error2)->once();
				expect($obj->validate('false'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([false, true]))->andReturn($error3)->once();
				expect($obj->validate('false,true'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with " "', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate('false abc'))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([false]))->andReturn($error2)->once();
				expect($obj->validate('false'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([false, true]))->andReturn($error3)->once();
				expect($obj->validate('false true'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with "\t"', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate("false\tabc"))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([false]))->andReturn($error2)->once();
				expect($obj->validate('false'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([false, true]))->andReturn($error3)->once();
				expect($obj->validate("false\ttrue"))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with "|"', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate('false|abc'))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::BOOL,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([false]))->andReturn($error2)->once();
				expect($obj->validate('false'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([false, true]))->andReturn($error3)->once();
				expect($obj->validate('false|true'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
	});
	context('creates validator for integer', function ()
	{
		context('joined with ","', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_INT => 'Invalid query string parameter type: expecting int.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate('123,abc'))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([123]))->andReturn($error2)->once();
				expect($obj->validate('123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([0, 123, -123]))->andReturn($error3)->once();
				expect($obj->validate('0,123,-123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with " "', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_INT => 'Invalid query string parameter type: expecting int.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate('123 abc'))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([123]))->andReturn($error2)->once();
				expect($obj->validate('123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([0, 123, -123]))->andReturn($error3)->once();
				expect($obj->validate('0 123 -123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with "\t"', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_INT => 'Invalid query string parameter type: expecting int.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate("123\tabc"))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([123]))->andReturn($error2)->once();
				expect($obj->validate('123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([0, 123, -123]))->andReturn($error3)->once();
				expect($obj->validate("0\t123\t-123"))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with "|"', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_INT => 'Invalid query string parameter type: expecting int.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate('123|abc'))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::INT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([123]))->andReturn($error2)->once();
				expect($obj->validate('123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([0, 123, -123]))->andReturn($error3)->once();
				expect($obj->validate('0|123|-123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
	});
	context('creates validator for float', function ()
	{
		context('joined with ","', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate('123.456,abc'))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([123.456]))->andReturn($error2)->once();
				expect($obj->validate('123.456'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([0.0, 123.0, -123.0, 123.456, -123.456, 0.0, 123.0, -123.0]))->andReturn($error3)->once();
				expect($obj->validate('0.0,123.0,-123.0,123.456,-123.456,0,123,-123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with " "', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate('123.456 abc'))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([123.456]))->andReturn($error2)->once();
				expect($obj->validate('123.456'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([0.0, 123.0, -123.0, 123.456, -123.456, 0.0, 123.0, -123.0]))->andReturn($error3)->once();
				expect($obj->validate('0.0 123.0 -123.0 123.456 -123.456 0 123 -123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with "\t"', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate("123.456\tabc"))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([123.456]))->andReturn($error2)->once();
				expect($obj->validate('123.456'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([0.0, 123.0, -123.0, 123.456, -123.456, 0.0, 123.0, -123.0]))->andReturn($error3)->once();
				expect($obj->validate("0.0\t123.0\t-123.0\t123.456\t-123.456\t0\t123\t-123"))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with "|"', function ()
		{
			it('denies unparsable string', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);
				$error = [
					DT\Validator\SerializableValue::INVALID_INNER => [
						OAGC\QueryStringScalarArrayAware::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.',
					]
				];
				expect($obj->validate('abc'))->toBe($error);
				expect($obj->validate('123.456|abc'))->toBe($error);
			});
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::FLOAT,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame([123.456]))->andReturn($error2)->once();
				expect($obj->validate('123.456'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame([0.0, 123.0, -123.0, 123.456, -123.456, 0.0, 123.0, -123.0]))->andReturn($error3)->once();
				expect($obj->validate('0.0|123.0|-123.0|123.456|-123.456|0|123|-123'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
	});
	context('creates validator for string', function ()
	{
		context('joined with ","', function ()
		{
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::STRING,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_CSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame(['abc']))->andReturn($error2)->once();
				expect($obj->validate('abc'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame(['', 'abc']))->andReturn($error3)->once();
				expect($obj->validate(',abc'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with " "', function ()
		{
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::STRING,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_SSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame(['abc']))->andReturn($error2)->once();
				expect($obj->validate('abc'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame(['', 'abc']))->andReturn($error3)->once();
				expect($obj->validate(' abc'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with "\t"', function ()
		{
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::STRING,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_TSV,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame(['abc']))->andReturn($error2)->once();
				expect($obj->validate('abc'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame(['', 'abc']))->andReturn($error3)->once();
				expect($obj->validate("\tabc"))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
		context('joined with "|"', function ()
		{
			it('validates parsed string with value validator', function ()
			{
				$links = ['abc' => 123];
				$options = [
					'type' => OAGC\ScalarType::STRING,
					'format' => OAGC\QueryStringScalarArrayAware::ARRAY_FORMAT_PIPES,
					'validators' => $links,
				];
				$chain = mock(DT\Validator\ValidatorInterface::class);
				$manager = mock(PM\PluginManagerInterface::class);
				$manager->shouldReceive('__invoke')->with(DT\Validator\Chain::class, ['links' => $links])->andReturn($chain)->once();
				$container = mock(ContainerInterface::class);
				$container->shouldReceive('get')->with(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->andReturn($manager)->once();

				$factory = new OAGC\Validator\Factory\QueryStringScalarArray();
				$obj = $factory($container, '', $options);

				$error1 = ['a' => 1];
				$chain->shouldReceive('validate')->with(new IsSame([]))->andReturn($error1)->once();
				expect($obj->validate(''))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error1]);

				$error2 = ['b' => 2];
				$chain->shouldReceive('validate')->with(new IsSame(['abc']))->andReturn($error2)->once();
				expect($obj->validate('abc'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error2]);

				$error3 = ['c' => 3];
				$chain->shouldReceive('validate')->with(new IsSame(['', 'abc']))->andReturn($error3)->once();
				expect($obj->validate('|abc'))->toBe([DT\Validator\SerializableValue::INVALID_INNER => $error3]);
			});
		});
	});
});
