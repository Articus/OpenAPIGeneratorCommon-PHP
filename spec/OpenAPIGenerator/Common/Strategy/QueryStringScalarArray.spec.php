<?php
declare(strict_types=1);

namespace spec\OpenAPIGenerator\Common\Strategy;

use OpenAPIGenerator\Common as OAGC;

\describe(OAGC\Strategy\QueryStringScalarArray::class, function ()
{
	\afterEach(function ()
	{
		\Mockery::close();
	});
	\context('->__construct', function ()
	{
		\it('throws on no type', function ()
		{
			$exception = new \InvalidArgumentException('Unknown type "".');
			\expect(function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([]);
			})->toThrow($exception);
		});
		\it('throws on invalid type', function ()
		{
			$exception = new \InvalidArgumentException('Unknown type "test".');
			\expect(function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray(['type' => 'test']);
			})->toThrow($exception);
		});
		\it('throws on no format', function ()
		{
			$exception = new \InvalidArgumentException('Unknown format "".');
			\expect(function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray(['type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT]);
			})->toThrow($exception);
		});
		\it('throws on invalid format', function ()
		{
			$exception = new \InvalidArgumentException('Unknown format "test".');
			\expect(function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray(['type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT, 'format' => 'test']);
			})->toThrow($exception);
		});
	});
	\context('->extract', function ()
	{
		\it('throws on non array', function ()
		{
			$exception = new \InvalidArgumentException('Extraction can be done only from array, not integer');
			\expect(function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
				]);
				$strategy->extract(123);
			})->toThrow($exception);
		});
		\context('no separator', function ()
		{
			\it('extracts nullable integer list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe([]);
				\expect($strategy->extract([0, 123, -123]))->toBe(['0', '123', '-123']);
			});
			\it('extracts nullable float list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_FLOAT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe([]);
				\expect($strategy->extract([0.0, 123.0, -123.0, 123.567, -123.567]))->toBe(['0', '123', '-123', '123.567', '-123.567']);
			});
			\it('extracts nullable boolean list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_BOOL,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe([]);
				\expect($strategy->extract([true, false]))->toBe(['true', 'false']);
			});
			\it('extracts nullable string list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe([]);
				\expect($strategy->extract(['', 'abc']))->toBe(['', 'abc']);
			});
		});
		\context('"," separator', function ()
		{
			\it('extracts nullable integer list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([0, 123, -123]))->toBe('0,123,-123');
			});
			\it('extracts nullable float list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_FLOAT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([0.0, 123.0, -123.0, 123.567, -123.567]))->toBe('0,123,-123,123.567,-123.567');
			});
			\it('extracts nullable boolean list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_BOOL,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([true, false]))->toBe('true,false');
			});
			\it('extracts nullable string list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract(['', 'abc']))->toBe(',abc');
			});
			\it('throws if string contains ","', function ()
			{
				$exception = new \InvalidArgumentException('Item at index 0 contains delimiter symbol and should be encoded.');
				\expect(function ()
				{
					$strategy = new OAGC\Strategy\QueryStringScalarArray([
						'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
					]);
					$strategy->extract(['abc,def']);
				})->toThrow($exception);
			});
		});
		\context('" " separator', function ()
		{
			\it('extracts nullable integer list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([0, 123, -123]))->toBe('0 123 -123');
			});
			\it('extracts nullable float list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_FLOAT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([0.0, 123.0, -123.0, 123.567, -123.567]))->toBe('0 123 -123 123.567 -123.567');
			});
			\it('extracts nullable boolean list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_BOOL,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([true, false]))->toBe('true false');
			});
			\it('extracts nullable string list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract(['', 'abc']))->toBe(' abc');
			});
			\it('throws if string contains " "', function ()
			{
				$exception = new \InvalidArgumentException('Item at index 0 contains delimiter symbol and should be encoded.');
				\expect(function ()
				{
					$strategy = new OAGC\Strategy\QueryStringScalarArray([
						'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV,
					]);
					$strategy->extract(['abc def']);
				})->toThrow($exception);
			});
		});
		\context('"\t" separator', function ()
		{
			\it('extracts nullable integer list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([0, 123, -123]))->toBe("0\t123\t-123");
			});
			\it('extracts nullable float list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_FLOAT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([0.0, 123.0, -123.0, 123.567, -123.567]))->toBe("0\t123\t-123\t123.567\t-123.567");
			});
			\it('extracts nullable boolean list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_BOOL,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([true, false]))->toBe("true\tfalse");
			});
			\it('extracts nullable string list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract(['', 'abc']))->toBe("\tabc");
			});
			\it('throws if string contains "\t"', function ()
			{
				$exception = new \InvalidArgumentException('Item at index 0 contains delimiter symbol and should be encoded.');
				\expect(function ()
				{
					$strategy = new OAGC\Strategy\QueryStringScalarArray([
						'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV,
					]);
					$strategy->extract(["abc\tdef"]);
				})->toThrow($exception);
			});
		});
		\context('"|" separator', function ()
		{
			\it('extracts nullable integer list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([0, 123, -123]))->toBe('0|123|-123');
			});
			\it('extracts nullable float list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_FLOAT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([0.0, 123.0, -123.0, 123.567, -123.567]))->toBe('0|123|-123|123.567|-123.567');
			});
			\it('extracts nullable boolean list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_BOOL,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract([true, false]))->toBe('true|false');
			});
			\it('extracts nullable string list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES,
				]);
				\expect($strategy->extract(null))->toBeNull();
				\expect($strategy->extract([]))->toBe('');
				\expect($strategy->extract(['', 'abc']))->toBe('|abc');
			});
			\it('throws if string contains "|"', function ()
			{
				$exception = new \InvalidArgumentException('Item at index 0 contains delimiter symbol and should be encoded.');
				\expect(function ()
				{
					$strategy = new OAGC\Strategy\QueryStringScalarArray([
						'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES,
					]);
					$strategy->extract(['abc|def']);
				})->toThrow($exception);
			});
		});
	});
	\context('->hydrate', function ()
	{
		\context('no separator', function ()
		{
			\it('throws on non array', function ()
			{
				$exception = new \InvalidArgumentException('Hydration can be done only from array, not integer');
				\expect(function ()
				{
					$result = null;
					$strategy = new OAGC\Strategy\QueryStringScalarArray([
						'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
					]);
					$strategy->hydrate(123, $result);
				})->toThrow($exception);
			});
			\it('hydrates nullable integer list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate([], $result);
				\expect($result)->toBe([]);
				$strategy->hydrate(['0', '123', '-123'], $result);
				\expect($result)->toBe([0, 123, -123]);
			});
			\it('hydrates nullable float list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_FLOAT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate([], $result);
				\expect($result)->toBe([]);
				$strategy->hydrate(['0', '123', '-123', '0.0', '123.0', '-123.0', '123.456', '-123.456'], $result);
				\expect($result)->toBe([0.0, 123.0, -123.0, 0.0, 123.0, -123.0, 123.456, -123.456]);
			});
			\it('hydrates nullable boolean list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_BOOL,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate([], $result);
				\expect($result)->toBe([]);
				$strategy->hydrate(['true', 'false'], $result);
				\expect($result)->toBe([true, false]);
			});
			\it('hydrates nullable string list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate([], $result);
				\expect($result)->toBe([]);
				$strategy->hydrate(['', 'abc'], $result);
				\expect($result)->toBe(['', 'abc']);
			});
		});
		\context('"," separator', function ()
		{
			\it('throws on non string', function ()
			{
				$exception = new \InvalidArgumentException('Hydration can be done only from string, not integer');
				\expect(function ()
				{
					$result = null;
					$strategy = new OAGC\Strategy\QueryStringScalarArray([
						'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
					]);
					$strategy->hydrate(123, $result);
				})->toThrow($exception);
			});
			\it('hydrates nullable integer list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate('0,123,-123', $result);
				\expect($result)->toBe([0, 123, -123]);
			});
			\it('hydrates nullable float list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_FLOAT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate('0,123,-123,0.0,123.0,-123.0,123.456,-123.456', $result);
				\expect($result)->toBe([0.0, 123.0, -123.0, 0.0, 123.0, -123.0, 123.456, -123.456]);
			});
			\it('hydrates nullable boolean list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_BOOL,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate('true,false', $result);
				\expect($result)->toBe([true, false]);
			});
			\it('hydrates nullable string list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate(',abc', $result);
				\expect($result)->toBe(['', 'abc']);
			});
		});
		\context('" " separator', function ()
		{
			\it('throws on non string', function ()
			{
				$exception = new \InvalidArgumentException('Hydration can be done only from string, not integer');
				\expect(function ()
				{
					$result = null;
					$strategy = new OAGC\Strategy\QueryStringScalarArray([
						'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV,
					]);
					$strategy->hydrate(123, $result);
				})->toThrow($exception);
			});
			\it('hydrates nullable integer list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate('0 123 -123', $result);
				\expect($result)->toBe([0, 123, -123]);
			});
			\it('hydrates nullable float list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_FLOAT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate('0 123 -123 0.0 123.0 -123.0 123.456 -123.456', $result);
				\expect($result)->toBe([0.0, 123.0, -123.0, 0.0, 123.0, -123.0, 123.456, -123.456]);
			});
			\it('hydrates nullable boolean list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_BOOL,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate('true false', $result);
				\expect($result)->toBe([true, false]);
			});
			\it('hydrates nullable string list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate(' abc', $result);
				\expect($result)->toBe(['', 'abc']);
			});
		});
		\context('"\t" separator', function ()
		{
			\it('throws on non string', function ()
			{
				$exception = new \InvalidArgumentException('Hydration can be done only from string, not integer');
				\expect(function ()
				{
					$result = null;
					$strategy = new OAGC\Strategy\QueryStringScalarArray([
						'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV,
					]);
					$strategy->hydrate(123, $result);
				})->toThrow($exception);
			});
			\it('hydrates nullable integer list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate("0\t123\t-123", $result);
				\expect($result)->toBe([0, 123, -123]);
			});
			\it('hydrates nullable float list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_FLOAT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate("0\t123\t-123\t0.0\t123.0\t-123.0\t123.456\t-123.456", $result);
				\expect($result)->toBe([0.0, 123.0, -123.0, 0.0, 123.0, -123.0, 123.456, -123.456]);
			});
			\it('hydrates nullable boolean list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_BOOL,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate("true\tfalse", $result);
				\expect($result)->toBe([true, false]);
			});
			\it('hydrates nullable string list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate("\tabc", $result);
				\expect($result)->toBe(['', 'abc']);
			});
		});
		\context('"|" separator', function ()
		{
			\it('throws on non string', function ()
			{
				$exception = new \InvalidArgumentException('Hydration can be done only from string, not integer');
				\expect(function ()
				{
					$result = null;
					$strategy = new OAGC\Strategy\QueryStringScalarArray([
						'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES,
					]);
					$strategy->hydrate(123, $result);
				})->toThrow($exception);
			});
			\it('hydrates nullable integer list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate('0|123|-123', $result);
				\expect($result)->toBe([0, 123, -123]);
			});
			\it('hydrates nullable float list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_FLOAT,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate('0|123|-123|0.0|123.0|-123.0|123.456|-123.456', $result);
				\expect($result)->toBe([0.0, 123.0, -123.0, 0.0, 123.0, -123.0, 123.456, -123.456]);
			});
			\it('hydrates nullable boolean list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_BOOL,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate('true|false', $result);
				\expect($result)->toBe([true, false]);
			});
			\it('hydrates nullable string list', function ()
			{
				$strategy = new OAGC\Strategy\QueryStringScalarArray([
					'type' => OAGC\Validator\QueryStringScalarArray::TYPE_STRING,
					'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES,
				]);
				$result = new \stdClass();
				$strategy->hydrate(null, $result);
				\expect($result)->toBeNull();
				$strategy->hydrate('', $result);
				\expect($result)->toBe([]);
				$strategy->hydrate('|abc', $result);
				\expect($result)->toBe(['', 'abc']);
			});
		});
	});
	\context('->merge', function ()
	{
		\it('merges by replacing "to" with "from"', function ()
		{
			$strategy = new OAGC\Strategy\QueryStringScalarArray([
				'type' => OAGC\Validator\QueryStringScalarArray::TYPE_INT,
				'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
			]);
			$from = \mock();
			$to = \mock();
			$strategy->merge($from, $to);
			\expect($to)->toBe($from);
		});
		//TODO add other types
	});
});
