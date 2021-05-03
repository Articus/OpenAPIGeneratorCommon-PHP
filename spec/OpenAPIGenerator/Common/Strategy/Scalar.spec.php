<?php
declare(strict_types=1);

namespace spec\OpenAPIGenerator\Common\Strategy;

use OpenAPIGenerator\Common as OAGC;

\describe(OAGC\Strategy\Scalar::class, function ()
{
	\afterEach(function ()
	{
		\Mockery::close();
	});
	\context('->__construct', function ()
	{
		\it('throws on unknown type', function ()
		{
			$exception = new \InvalidArgumentException('Unknown type "test".');
			\expect(function ()
			{
				$strategy = new OAGC\Strategy\Scalar('test');
			})->toThrow($exception);
		});
	});
	\context('->extract', function ()
	{
		\it('extracts same value', function ()
		{
			$value = \mock();

			$intStrategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_INT);
			\expect($intStrategy->extract($value))->toBe($value);
			$floatStrategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_FLOAT);
			\expect($floatStrategy->extract($value))->toBe($value);
			$boolStrategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_BOOL);
			\expect($boolStrategy->extract($value))->toBe($value);
			$stringStrategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_STRING);
			\expect($stringStrategy->extract($value))->toBe($value);
		});
	});
	\context('->hydrate', function ()
	{
		\it('delegates to merge', function ()
		{
			$source = \mock();
			$destination = \mock();
			$newDestination = \mock();
			$strategy = \mock(OAGC\Strategy\Scalar::class)->makePartial();
			$strategy->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$source, &$destination, &$newDestination)
				{
					$result = (($a === $source) && ($b === $destination));
					if ($result)
					{
						$b = $newDestination;
					}
					return $result;
				}
			)->once();
			$strategy->hydrate($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
	});
	\context('->merge', function ()
	{
		\context('int type', function ()
		{
			\it('merges from null', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_INT);
				$result = \mock();
				$strategy->merge(null, $result);
				\expect($result)->toBeNull();
			});
			\it('merges from not null by casting to type', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_INT);
				$result = \mock();
				$strategy->merge(0, $result);
				\expect($result)->toBe(0);
				$strategy->merge(123, $result);
				\expect($result)->toBe(123);
				$strategy->merge(0.0, $result);
				\expect($result)->toBe(0);
				$strategy->merge(123.456, $result);
				\expect($result)->toBe(123);
				$strategy->merge(false, $result);
				\expect($result)->toBe(0);
				$strategy->merge(true, $result);
				\expect($result)->toBe(1);
				$strategy->merge('', $result);
				\expect($result)->toBe(0);
				$strategy->merge('abc', $result);
				\expect($result)->toBe(0);
				$strategy->merge('123', $result);
				\expect($result)->toBe(123);
				$strategy->merge('123.456', $result);
				\expect($result)->toBe(123);
			});
		});
		\context('float type', function ()
		{
			\it('merges from null', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_FLOAT);
				$result = \mock();
				$strategy->merge(null, $result);
				\expect($result)->toBeNull();
			});
			\it('merges from not null by casting to type', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_FLOAT);
				$result = \mock();
				$strategy->merge(0, $result);
				\expect($result)->toBe(0.0);
				$strategy->merge(123, $result);
				\expect($result)->toBe(123.0);
				$strategy->merge(0.0, $result);
				\expect($result)->toBe(0.0);
				$strategy->merge(123.456, $result);
				\expect($result)->toBe(123.456);
				$strategy->merge(false, $result);
				\expect($result)->toBe(0.0);
				$strategy->merge(true, $result);
				\expect($result)->toBe(1.0);
				$strategy->merge('', $result);
				\expect($result)->toBe(0.0);
				$strategy->merge('abc', $result);
				\expect($result)->toBe(0.0);
				$strategy->merge('123', $result);
				\expect($result)->toBe(123.0);
				$strategy->merge('123.456', $result);
				\expect($result)->toBe(123.456);
			});
		});
		\context('bool type', function ()
		{
			\it('merges from null', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_BOOL);
				$result = \mock();
				$strategy->merge(null, $result);
				\expect($result)->toBeNull();
			});
			\it('merges from not null by casting to type', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_BOOL);
				$result = \mock();
				$strategy->merge(0, $result);
				\expect($result)->toBe(false);
				$strategy->merge(123, $result);
				\expect($result)->toBe(true);
				$strategy->merge(0.0, $result);
				\expect($result)->toBe(false);
				$strategy->merge(123.456, $result);
				\expect($result)->toBe(true);
				$strategy->merge(false, $result);
				\expect($result)->toBe(false);
				$strategy->merge(true, $result);
				\expect($result)->toBe(true);
				$strategy->merge('', $result);
				\expect($result)->toBe(false);
				$strategy->merge('abc', $result);
				\expect($result)->toBe(true);
				$strategy->merge('123', $result);
				\expect($result)->toBe(true);
				$strategy->merge('123.456', $result);
				\expect($result)->toBe(true);
			});
		});
		\context('string type', function ()
		{
			\it('merges from null', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_STRING);
				$result = \mock();
				$strategy->merge(null, $result);
				\expect($result)->toBeNull();
			});
			\it('merges from not null by casting to type', function ()
			{
				$strategy = new OAGC\Strategy\Scalar(OAGC\Validator\Scalar::TYPE_STRING);
				$result = \mock();
				$strategy->merge(0, $result);
				\expect($result)->toBe('0');
				$strategy->merge(123, $result);
				\expect($result)->toBe('123');
				$strategy->merge(0.0, $result);
				\expect($result)->toBe('0');
				$strategy->merge(123.456, $result);
				\expect($result)->toBe('123.456');
				$strategy->merge(false, $result);
				\expect($result)->toBe('');
				$strategy->merge(true, $result);
				\expect($result)->toBe('1');
				$strategy->merge('', $result);
				\expect($result)->toBe('');
				$strategy->merge('abc', $result);
				\expect($result)->toBe('abc');
				$strategy->merge('123', $result);
				\expect($result)->toBe('123');
				$strategy->merge('123.456', $result);
				\expect($result)->toBe('123.456');
			});
		});
	});
});
