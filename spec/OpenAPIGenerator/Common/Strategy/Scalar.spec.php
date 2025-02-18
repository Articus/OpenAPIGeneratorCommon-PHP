<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;

describe(OAGC\Strategy\Scalar::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	context('->__construct', function ()
	{
		it('throws on no type', function ()
		{
			$exception = new InvalidArgumentException('Option "type" is required.');
			expect(static fn () => new OAGC\Strategy\Scalar([]))->toThrow($exception);
		});
		it('throws on unknown type', function ()
		{
			$exception = new InvalidArgumentException('Unknown type "test".');
			expect(static fn () => new OAGC\Strategy\Scalar(['type' => 'test']))->toThrow($exception);
		});
	});
	context('->extract', function ()
	{
		it('extracts same value', function ()
		{
			$value = mock();

			$intStrategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::INT]);
			expect($intStrategy->extract($value))->toBe($value);
			$floatStrategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::FLOAT]);
			expect($floatStrategy->extract($value))->toBe($value);
			$boolStrategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::BOOL]);
			expect($boolStrategy->extract($value))->toBe($value);
			$stringStrategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::STRING]);
			expect($stringStrategy->extract($value))->toBe($value);
		});
	});
	context('->hydrate', function ()
	{
		context('int type', function ()
		{
			it('hydrates from null', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::INT]);
				$result = mock();
				$strategy->hydrate(null, $result);
				expect($result)->toBeNull();
			});
			it('hydrates from not null by casting to type', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::INT]);
				$result = mock();
				$strategy->hydrate(0, $result);
				expect($result)->toBe(0);
				$strategy->hydrate(123, $result);
				expect($result)->toBe(123);
				$strategy->hydrate(0.0, $result);
				expect($result)->toBe(0);
				$strategy->hydrate(123.456, $result);
				expect($result)->toBe(123);
				$strategy->hydrate(false, $result);
				expect($result)->toBe(0);
				$strategy->hydrate(true, $result);
				expect($result)->toBe(1);
				$strategy->hydrate('', $result);
				expect($result)->toBe(0);
				$strategy->hydrate('abc', $result);
				expect($result)->toBe(0);
				$strategy->hydrate('123', $result);
				expect($result)->toBe(123);
				$strategy->hydrate('123.456', $result);
				expect($result)->toBe(123);
			});
		});
		context('float type', function ()
		{
			it('hydrates from null', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::FLOAT]);
				$result = mock();
				$strategy->hydrate(null, $result);
				expect($result)->toBeNull();
			});
			it('hydrates from not null by casting to type', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::FLOAT]);
				$result = mock();
				$strategy->hydrate(0, $result);
				expect($result)->toBe(0.0);
				$strategy->hydrate(123, $result);
				expect($result)->toBe(123.0);
				$strategy->hydrate(0.0, $result);
				expect($result)->toBe(0.0);
				$strategy->hydrate(123.456, $result);
				expect($result)->toBe(123.456);
				$strategy->hydrate(false, $result);
				expect($result)->toBe(0.0);
				$strategy->hydrate(true, $result);
				expect($result)->toBe(1.0);
				$strategy->hydrate('', $result);
				expect($result)->toBe(0.0);
				$strategy->hydrate('abc', $result);
				expect($result)->toBe(0.0);
				$strategy->hydrate('123', $result);
				expect($result)->toBe(123.0);
				$strategy->hydrate('123.456', $result);
				expect($result)->toBe(123.456);
			});
		});
		context('bool type', function ()
		{
			it('hydrates from null', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::BOOL]);
				$result = mock();
				$strategy->hydrate(null, $result);
				expect($result)->toBeNull();
			});
			it('hydrates from not null by casting to type', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::BOOL]);
				$result = mock();
				$strategy->hydrate(0, $result);
				expect($result)->toBe(false);
				$strategy->hydrate(123, $result);
				expect($result)->toBe(true);
				$strategy->hydrate(0.0, $result);
				expect($result)->toBe(false);
				$strategy->hydrate(123.456, $result);
				expect($result)->toBe(true);
				$strategy->hydrate(false, $result);
				expect($result)->toBe(false);
				$strategy->hydrate(true, $result);
				expect($result)->toBe(true);
				$strategy->hydrate('', $result);
				expect($result)->toBe(false);
				$strategy->hydrate('abc', $result);
				expect($result)->toBe(true);
				$strategy->hydrate('123', $result);
				expect($result)->toBe(true);
				$strategy->hydrate('123.456', $result);
				expect($result)->toBe(true);
			});
		});
		context('string type', function ()
		{
			it('hydrates from null', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::STRING]);
				$result = mock();
				$strategy->hydrate(null, $result);
				expect($result)->toBeNull();
			});
			it('hydrates from not null by casting to type', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::STRING]);
				$result = mock();
				$strategy->hydrate(0, $result);
				expect($result)->toBe('0');
				$strategy->hydrate(123, $result);
				expect($result)->toBe('123');
				$strategy->hydrate(0.0, $result);
				expect($result)->toBe('0');
				$strategy->hydrate(123.456, $result);
				expect($result)->toBe('123.456');
				$strategy->hydrate(false, $result);
				expect($result)->toBe('');
				$strategy->hydrate(true, $result);
				expect($result)->toBe('1');
				$strategy->hydrate('', $result);
				expect($result)->toBe('');
				$strategy->hydrate('abc', $result);
				expect($result)->toBe('abc');
				$strategy->hydrate('123', $result);
				expect($result)->toBe('123');
				$strategy->hydrate('123.456', $result);
				expect($result)->toBe('123.456');
			});
		});
	});
	context('->merge', function ()
	{
		it('copies source to destination', function ()
		{
			$source = mock();
			$destination = mock();
			$strategy = new OAGC\Strategy\Scalar(['type' => OAGC\ScalarType::INT]);
			$strategy->merge($source, $destination);
			expect($destination)->toBe($source);
		});
	});
});
