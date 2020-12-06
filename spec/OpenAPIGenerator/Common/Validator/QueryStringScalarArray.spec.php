<?php
declare(strict_types=1);

namespace spec\OpenAPIGenerator\Common\Validator;

use OpenAPIGenerator\Common as OAGC;

\describe(OAGC\Validator\QueryStringScalarArray::class, function ()
{
	\context('->__construct', function ()
	{
		\it('throws on no type', function ()
		{
			$exception = new \InvalidArgumentException('Unknown type "".');
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['format' => 'csv']);
			})->toThrow($exception);
		});
		\it('throws on invalid type', function ()
		{
			$exception = new \InvalidArgumentException('Unknown type "test".');
			\expect(function ()
			{
				$obj = new OAGC\Validator\Scalar(['type' => 'test', 'format' => 'csv']);
			})->toThrow($exception);
		});
		\it('throws on no format', function ()
		{
			$exception = new \InvalidArgumentException('Unknown format "".');
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int']);
			})->toThrow($exception);
		});
		\it('throws on invalid format', function ()
		{
			$exception = new \InvalidArgumentException('Unknown format "test".');
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int', 'format' => 'test']);
			})->toThrow($exception);
		});
		\it('throws on invalid min items', function ()
		{
			$exception = new \InvalidArgumentException('Invalid "min_items" option: expecting non-negative integer.');
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int', 'format' => 'csv', 'min_items' => '1']);
			})->toThrow($exception);
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int', 'format' => 'csv', 'minItems' => '1']);
			})->toThrow($exception);
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int', 'format' => 'csv', 'min_items' => -1]);
			})->toThrow($exception);
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int', 'format' => 'csv', 'minItems' => -1]);
			})->toThrow($exception);
		});
		\it('throws on invalid max items', function ()
		{
			$exception = new \InvalidArgumentException('Invalid "max_items" option: expecting integer greater than or equal to "min_items".');
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int', 'format' => 'csv', 'max_items' => '1']);
			})->toThrow($exception);
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int', 'format' => 'csv', 'maxItems' => '1']);
			})->toThrow($exception);
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int', 'format' => 'csv', 'max_items' => -1]);
			})->toThrow($exception);
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int', 'format' => 'csv', 'maxItems' => -1]);
			})->toThrow($exception);
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int', 'format' => 'csv', 'min_items' => 10, 'max_items' => 1]);
			})->toThrow($exception);
			\expect(function ()
			{
				$obj = new OAGC\Validator\QueryStringScalarArray(['type' => 'int', 'format' => 'csv', 'min_items' => 10, 'maxItems' => -1]);
			})->toThrow($exception);
		});
	});

	\context('->validate', function ()
	{
		\context('no item quantity limit', function ()
		{
			\context('no separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate([]))->toBe([]);
					\expect($obj->validate(['false', 'true']))->toBe($error);
					\expect($obj->validate(['0', '123', '-123']))->toBe([]);
					\expect($obj->validate(['0.0', '123.0', '-123.0', '123.456', '-123.456']))->toBe($error);
					\expect($obj->validate(['', 'abc']))->toBe($error);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate([]))->toBe([]);
					\expect($obj->validate(['false', 'true']))->toBe($error);
					\expect($obj->validate(['0', '123', '-123']))->toBe([]);
					\expect($obj->validate(['0.0', '123.0', '-123.0', '123.456', '-123.456']))->toBe([]);
					\expect($obj->validate(['', 'abc']))->toBe($error);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate([]))->toBe([]);
					\expect($obj->validate(['false', 'true']))->toBe([]);
					\expect($obj->validate(['0', '123', '-123']))->toBe($error);
					\expect($obj->validate(['0.0', '123.0', '-123.0', '123.456', '-123.456']))->toBe($error);
					\expect($obj->validate(['', 'abc']))->toBe($error);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate([]))->toBe([]);
					\expect($obj->validate(['false', 'true']))->toBe([]);
					\expect($obj->validate(['0', '123', '-123']))->toBe([]);
					\expect($obj->validate(['0.0', '123.0', '-123.0', '123.456', '-123.456']))->toBe([]);
					\expect($obj->validate(['', 'abc']))->toBe([]);
				});
			});
			\context('"," separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false,true'))->toBe($error);
					\expect($obj->validate('0,123,-123'))->toBe([]);
					\expect($obj->validate('0.0,123.0,-123.0,123.456,-123.456'))->toBe($error);
					\expect($obj->validate(',abc'))->toBe($error);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false,true'))->toBe($error);
					\expect($obj->validate('0,123,-123'))->toBe([]);
					\expect($obj->validate('0.0,123.0,-123.0,123.456,-123.456'))->toBe([]);
					\expect($obj->validate(',abc'))->toBe($error);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false,true'))->toBe([]);
					\expect($obj->validate('0,123,-123'))->toBe($error);
					\expect($obj->validate('0.0,123.0,-123.0,123.456,-123.456'))->toBe($error);
					\expect($obj->validate(',abc'))->toBe($error);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false,true'))->toBe([]);
					\expect($obj->validate('0,123,-123'))->toBe([]);
					\expect($obj->validate('0.0,123.0,-123.0,123.456,-123.456'))->toBe([]);
					\expect($obj->validate(',abc'))->toBe([]);
				});
			});
			\context('" " separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false true'))->toBe($error);
					\expect($obj->validate('0 123 -123'))->toBe([]);
					\expect($obj->validate('0.0 123.0 -123.0 123.456 -123.456'))->toBe($error);
					\expect($obj->validate(' abc'))->toBe($error);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false true'))->toBe($error);
					\expect($obj->validate('0 123 -123'))->toBe([]);
					\expect($obj->validate('0.0 123.0 -123.0 123.456 -123.456'))->toBe([]);
					\expect($obj->validate(' abc'))->toBe($error);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false true'))->toBe([]);
					\expect($obj->validate('0 123 -123'))->toBe($error);
					\expect($obj->validate('0.0 123.0 -123.0 123.456 -123.456'))->toBe($error);
					\expect($obj->validate(' abc'))->toBe($error);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_SSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false true'))->toBe([]);
					\expect($obj->validate('0 123 -123'))->toBe([]);
					\expect($obj->validate('0.0 123.0 -123.0 123.456 -123.456'))->toBe([]);
					\expect($obj->validate(' abc'))->toBe([]);
				});
			});
			\context('"\t" separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate("false\ttrue"))->toBe($error);
					\expect($obj->validate("0\t123\t-123"))->toBe([]);
					\expect($obj->validate("0.0\t123.0\t-123.0\t123.456\t-123.456"))->toBe($error);
					\expect($obj->validate("\tabc"))->toBe($error);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate("false\ttrue"))->toBe($error);
					\expect($obj->validate("0\t123\t-123"))->toBe([]);
					\expect($obj->validate("0.0\t123.0\t-123.0\t123.456\t-123.456"))->toBe([]);
					\expect($obj->validate("\tabc"))->toBe($error);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate("false\ttrue"))->toBe([]);
					\expect($obj->validate("0\t123\t-123"))->toBe($error);
					\expect($obj->validate("0.0\t123.0\t-123.0\t123.456\t-123.456"))->toBe($error);
					\expect($obj->validate("\tabc"))->toBe($error);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_TSV
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate("false\ttrue"))->toBe([]);
					\expect($obj->validate("0\t123\t-123"))->toBe([]);
					\expect($obj->validate("0.0\t123.0\t-123.0\t123.456\t-123.456"))->toBe([]);
					\expect($obj->validate("\tabc"))->toBe([]);
				});
			});
			\context('"|" separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false|true'))->toBe($error);
					\expect($obj->validate('0|123|-123'))->toBe([]);
					\expect($obj->validate('0.0|123.0|-123.0|123.456|-123.456'))->toBe($error);
					\expect($obj->validate('|abc'))->toBe($error);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false|true'))->toBe($error);
					\expect($obj->validate('0|123|-123'))->toBe([]);
					\expect($obj->validate('0.0|123.0|-123.0|123.456|-123.456'))->toBe([]);
					\expect($obj->validate('|abc'))->toBe($error);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false|true'))->toBe([]);
					\expect($obj->validate('0|123|-123'))->toBe($error);
					\expect($obj->validate('0.0|123.0|-123.0|123.456|-123.456'))->toBe($error);
					\expect($obj->validate('|abc'))->toBe($error);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_PIPES
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string.'];

					\expect($obj->validate(null))->toBe([]);
					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('false|true'))->toBe([]);
					\expect($obj->validate('0|123|-123'))->toBe([]);
					\expect($obj->validate('0.0|123.0|-123.0|123.456|-123.456'))->toBe([]);
					\expect($obj->validate('|abc'))->toBe([]);
				});
			});
		});
		\context('minimum item quantity limit', function ()
		{
			\context('no separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int, at least 2 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['123']))->toBe($error);
					\expect($obj->validate(['123', '456']))->toBe([]);
					\expect($obj->validate(['123', '456', '789']))->toBe([]);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float, at least 2 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['123.456']))->toBe($error);
					\expect($obj->validate(['123.456', '456.789']))->toBe([]);
					\expect($obj->validate(['123.456', '456.789', '789.123']))->toBe([]);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool, at least 2 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['true']))->toBe($error);
					\expect($obj->validate(['true', 'false']))->toBe([]);
					\expect($obj->validate(['true', 'false', 'true']))->toBe([]);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string, at least 2 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['abc']))->toBe($error);
					\expect($obj->validate(['abc', 'def']))->toBe([]);
					\expect($obj->validate(['abc', 'def', 'ghi']))->toBe([]);
				});
			});
			\context('"," separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int, at least 2 elements.'];

					\expect($obj->validate(''))->toBe($error);
					\expect($obj->validate('123'))->toBe($error);
					\expect($obj->validate('123,456'))->toBe([]);
					\expect($obj->validate('123,456,789'))->toBe([]);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float, at least 2 elements.'];

					\expect($obj->validate(''))->toBe($error);
					\expect($obj->validate('123.456'))->toBe($error);
					\expect($obj->validate('123.456,456.789'))->toBe([]);
					\expect($obj->validate('123.456,456.789,789.123'))->toBe([]);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool, at least 2 elements.'];

					\expect($obj->validate(''))->toBe($error);
					\expect($obj->validate('true'))->toBe($error);
					\expect($obj->validate('true,false'))->toBe([]);
					\expect($obj->validate('true,false,true'))->toBe([]);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string, at least 2 elements.'];

					\expect($obj->validate(''))->toBe($error);
					\expect($obj->validate('abc'))->toBe($error);
					\expect($obj->validate('abc,def'))->toBe([]);
					\expect($obj->validate('abc,def,ghi'))->toBe([]);
				});
			});
			//TODO add test for other separators?
		});
		\context('maximum item quantity limit', function ()
		{
			\context('no separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int, at most 2 elements.'];

					\expect($obj->validate([]))->toBe([]);
					\expect($obj->validate(['123']))->toBe([]);
					\expect($obj->validate(['123', '456']))->toBe([]);
					\expect($obj->validate(['123', '456', '789']))->toBe($error);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float, at most 2 elements.'];

					\expect($obj->validate([]))->toBe([]);
					\expect($obj->validate(['123.456']))->toBe([]);
					\expect($obj->validate(['123.456', '456.789']))->toBe([]);
					\expect($obj->validate(['123.456', '456.789', '789.123']))->toBe($error);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool, at most 2 elements.'];

					\expect($obj->validate([]))->toBe([]);
					\expect($obj->validate(['true']))->toBe([]);
					\expect($obj->validate(['true', 'false']))->toBe([]);
					\expect($obj->validate(['true', 'false', 'true']))->toBe($error);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string, at most 2 elements.'];

					\expect($obj->validate([]))->toBe([]);
					\expect($obj->validate(['abc']))->toBe([]);
					\expect($obj->validate(['abc', 'def']))->toBe([]);
					\expect($obj->validate(['abc', 'def', 'ghi']))->toBe($error);
				});
			});
			\context('"," separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int, at most 2 elements.'];

					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('123'))->toBe([]);
					\expect($obj->validate('123,456'))->toBe([]);
					\expect($obj->validate('123,456,789'))->toBe($error);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float, at most 2 elements.'];

					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('123.456'))->toBe([]);
					\expect($obj->validate('123.456,456.789'))->toBe([]);
					\expect($obj->validate('123.456,456.789,789.123'))->toBe($error);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool, at most 2 elements.'];

					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('true'))->toBe([]);
					\expect($obj->validate('true,false'))->toBe([]);
					\expect($obj->validate('true,false,true'))->toBe($error);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string, at most 2 elements.'];

					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('abc'))->toBe([]);
					\expect($obj->validate('abc,def'))->toBe([]);
					\expect($obj->validate('abc,def,ghi'))->toBe($error);
				});
			});
			//TODO add test for other separators?
		});
		\context('one item quantity limit', function ()
		{
			\context('no separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 1,
						'max_items' => 1,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int, at least 1 elements, at most 1 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['123']))->toBe([]);
					\expect($obj->validate(['123', '456']))->toBe($error);
					\expect($obj->validate(['123', '456', '789']))->toBe($error);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 1,
						'max_items' => 1,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float, at least 1 elements, at most 1 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['123.456']))->toBe([]);
					\expect($obj->validate(['123.456', '456.789']))->toBe($error);
					\expect($obj->validate(['123.456', '456.789', '789.123']))->toBe($error);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 1,
						'max_items' => 1,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool, at least 1 elements, at most 1 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['true']))->toBe([]);
					\expect($obj->validate(['true', 'false']))->toBe($error);
					\expect($obj->validate(['true', 'false', 'true']))->toBe($error);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 1,
						'max_items' => 1,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string, at least 1 elements, at most 1 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['abc']))->toBe([]);
					\expect($obj->validate(['abc', 'def']))->toBe($error);
					\expect($obj->validate(['abc', 'def', 'ghi']))->toBe($error);
				});
			});
			\context('"," separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 1,
						'max_items' => 1,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int, at least 1 elements, at most 1 elements.'];

					\expect($obj->validate(''))->toBe($error);
					\expect($obj->validate('123'))->toBe([]);
					\expect($obj->validate('123,456'))->toBe($error);
					\expect($obj->validate('123,456,789'))->toBe($error);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 1,
						'max_items' => 1,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float, at least 1 elements, at most 1 elements.'];

					\expect($obj->validate(''))->toBe($error);
					\expect($obj->validate('123.456'))->toBe([]);
					\expect($obj->validate('123.456,456.789'))->toBe($error);
					\expect($obj->validate('123.456,456.789,789.123'))->toBe($error);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 1,
						'max_items' => 1,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool, at least 1 elements, at most 1 elements.'];

					\expect($obj->validate(''))->toBe($error);
					\expect($obj->validate('true'))->toBe([]);
					\expect($obj->validate('true,false'))->toBe($error);
					\expect($obj->validate('true,false,true'))->toBe($error);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 1,
						'max_items' => 1,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string, at least 1 elements, at most 1 elements.'];

					\expect($obj->validate(''))->toBe([]);
					\expect($obj->validate('abc'))->toBe([]);
					\expect($obj->validate('abc,def'))->toBe($error);
					\expect($obj->validate('abc,def,ghi'))->toBe($error);
				});
			});
			//TODO add test for other separators?
		});
		\context('several items quantity limit', function ()
		{
			\context('no separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 2,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int, at least 2 elements, at most 2 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['123']))->toBe($error);
					\expect($obj->validate(['123', '456']))->toBe([]);
					\expect($obj->validate(['123', '456', '789']))->toBe($error);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 2,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float, at least 2 elements, at most 2 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['123.456']))->toBe($error);
					\expect($obj->validate(['123.456', '456.789']))->toBe([]);
					\expect($obj->validate(['123.456', '456.789', '789.123']))->toBe($error);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 2,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool, at least 2 elements, at most 2 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['true']))->toBe($error);
					\expect($obj->validate(['true', 'false']))->toBe([]);
					\expect($obj->validate(['true', 'false', 'true']))->toBe($error);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_MULTI,
						'min_items' => 2,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string, at least 2 elements, at most 2 elements.'];

					\expect($obj->validate([]))->toBe($error);
					\expect($obj->validate(['abc']))->toBe($error);
					\expect($obj->validate(['abc', 'def']))->toBe([]);
					\expect($obj->validate(['abc', 'def', 'ghi']))->toBe($error);
				});
			});
			\context('"," separator', function ()
			{
				\it('validates if value is integer', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_INT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 2,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of int, at least 2 elements, at most 2 elements.'];

					\expect($obj->validate(''))->toBe($error);
					\expect($obj->validate('123'))->toBe($error);
					\expect($obj->validate('123,456'))->toBe([]);
					\expect($obj->validate('123,456,789'))->toBe($error);
				});
				\it('validates if value is float', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_FLOAT,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 2,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of float, at least 2 elements, at most 2 elements.'];

					\expect($obj->validate(''))->toBe($error);
					\expect($obj->validate('123.456'))->toBe($error);
					\expect($obj->validate('123.456,456.789'))->toBe([]);
					\expect($obj->validate('123.456,456.789,789.123'))->toBe($error);
				});
				\it('validates if value is boolean', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_BOOL,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 2,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of bool, at least 2 elements, at most 2 elements.'];

					\expect($obj->validate(''))->toBe($error);
					\expect($obj->validate('true'))->toBe($error);
					\expect($obj->validate('true,false'))->toBe([]);
					\expect($obj->validate('true,false,true'))->toBe($error);
				});
				\it('validates if value is string', function ()
				{
					$obj = new OAGC\Validator\QueryStringScalarArray([
						'type' => OAGC\Validator\Scalar::TYPE_STRING,
						'format' => OAGC\Validator\QueryStringScalarArray::FORMAT_CSV,
						'min_items' => 2,
						'max_items' => 2,
					]);
					$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting list of string, at least 2 elements, at most 2 elements.'];

					\expect($obj->validate(''))->toBe($error);
					\expect($obj->validate('abc'))->toBe($error);
					\expect($obj->validate('abc,def'))->toBe([]);
					\expect($obj->validate('abc,def,ghi'))->toBe($error);
				});
			});
			//TODO add test for other separators?
		});
	});
});
