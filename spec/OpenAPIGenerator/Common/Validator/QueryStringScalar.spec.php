<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;

describe(OAGC\Validator\QueryStringScalar::class, function ()
{
	context('->__construct', function ()
	{
		it('throws on no type', function ()
		{
			$exception = new InvalidArgumentException('Unknown type "".');
			expect(static fn () => new OAGC\Validator\QueryStringScalar([]))->toThrow($exception);
		});
		it('throws on invalid type', function ()
		{
			$exception = new InvalidArgumentException('Unknown type "test".');
			expect(static fn () => new OAGC\Validator\QueryStringScalar(['type' => 'test']))->toThrow($exception);
		});
	});

	context('->validate', function ()
	{
		it('validates if value is integer', function ()
		{
			$obj = new OAGC\Validator\QueryStringScalar(['type' => OAGC\Validator\Scalar::TYPE_INT]);
			$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid query string scalar type: expecting int.'];

			expect($obj->validate(null))->toBe([]);
			expect($obj->validate([]))->toBe($error);
			expect($obj->validate(false))->toBe($error);
			expect($obj->validate(true))->toBe($error);
			expect($obj->validate(0.0))->toBe($error);
			expect($obj->validate(123.0))->toBe($error);
			expect($obj->validate(-123.0))->toBe($error);
			expect($obj->validate(123.456))->toBe($error);
			expect($obj->validate(-123.456))->toBe($error);
			expect($obj->validate(0))->toBe($error);
			expect($obj->validate(123))->toBe($error);
			expect($obj->validate(-123))->toBe($error);
			expect($obj->validate(new stdClass()))->toBe($error);
			expect($obj->validate(''))->toBe($error);
			expect($obj->validate('false'))->toBe($error);
			expect($obj->validate('true'))->toBe($error);
			expect($obj->validate('0.0'))->toBe($error);
			expect($obj->validate('123.0'))->toBe($error);
			expect($obj->validate('-123.0'))->toBe($error);
			expect($obj->validate('123.456'))->toBe($error);
			expect($obj->validate('-123.456'))->toBe($error);
			expect($obj->validate('0'))->toBe([]);
			expect($obj->validate('123'))->toBe([]);
			expect($obj->validate('-123'))->toBe([]);
			expect($obj->validate('abc'))->toBe($error);
		});
		it('validates if value is float', function ()
		{
			$obj = new OAGC\Validator\QueryStringScalar(['type' => OAGC\Validator\Scalar::TYPE_FLOAT]);
			$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid query string scalar type: expecting float.'];

			expect($obj->validate(null))->toBe([]);
			expect($obj->validate([]))->toBe($error);
			expect($obj->validate(false))->toBe($error);
			expect($obj->validate(true))->toBe($error);
			expect($obj->validate(0.0))->toBe($error);
			expect($obj->validate(123.0))->toBe($error);
			expect($obj->validate(-123.0))->toBe($error);
			expect($obj->validate(123.456))->toBe($error);
			expect($obj->validate(-123.456))->toBe($error);
			expect($obj->validate(0))->toBe($error);
			expect($obj->validate(123))->toBe($error);
			expect($obj->validate(-123))->toBe($error);
			expect($obj->validate(new stdClass()))->toBe($error);
			expect($obj->validate(''))->toBe($error);
			expect($obj->validate('false'))->toBe($error);
			expect($obj->validate('true'))->toBe($error);
			expect($obj->validate('0.0'))->toBe([]);
			expect($obj->validate('123.0'))->toBe([]);
			expect($obj->validate('-123.0'))->toBe([]);
			expect($obj->validate('123.456'))->toBe([]);
			expect($obj->validate('-123.456'))->toBe([]);
			expect($obj->validate('0'))->toBe([]);
			expect($obj->validate('123'))->toBe([]);
			expect($obj->validate('-123'))->toBe([]);
			expect($obj->validate('abc'))->toBe($error);
		});
		it('validates if value is boolean', function ()
		{
			$obj = new OAGC\Validator\QueryStringScalar(['type' => OAGC\Validator\Scalar::TYPE_BOOL]);
			$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid query string scalar type: expecting bool.'];

			expect($obj->validate(null))->toBe([]);
			expect($obj->validate([]))->toBe($error);
			expect($obj->validate(false))->toBe($error);
			expect($obj->validate(true))->toBe($error);
			expect($obj->validate(0.0))->toBe($error);
			expect($obj->validate(123.0))->toBe($error);
			expect($obj->validate(-123.0))->toBe($error);
			expect($obj->validate(123.456))->toBe($error);
			expect($obj->validate(-123.456))->toBe($error);
			expect($obj->validate(0))->toBe($error);
			expect($obj->validate(123))->toBe($error);
			expect($obj->validate(-123))->toBe($error);
			expect($obj->validate(new stdClass()))->toBe($error);
			expect($obj->validate(''))->toBe($error);
			expect($obj->validate('false'))->toBe([]);
			expect($obj->validate('true'))->toBe([]);
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
		it('validates if value is string', function ()
		{
			$obj = new OAGC\Validator\QueryStringScalar(['type' => OAGC\Validator\Scalar::TYPE_STRING]);
			$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid query string scalar type: expecting string.'];

			expect($obj->validate(null))->toBe([]);
			expect($obj->validate([]))->toBe($error);
			expect($obj->validate(false))->toBe($error);
			expect($obj->validate(true))->toBe($error);
			expect($obj->validate(0.0))->toBe($error);
			expect($obj->validate(123.0))->toBe($error);
			expect($obj->validate(-123.0))->toBe($error);
			expect($obj->validate(123.456))->toBe($error);
			expect($obj->validate(-123.456))->toBe($error);
			expect($obj->validate(0))->toBe($error);
			expect($obj->validate(123))->toBe($error);
			expect($obj->validate(-123))->toBe($error);
			expect($obj->validate(new stdClass()))->toBe($error);
			expect($obj->validate(''))->toBe([]);
			expect($obj->validate('false'))->toBe([]);
			expect($obj->validate('true'))->toBe([]);
			expect($obj->validate('0.0'))->toBe([]);
			expect($obj->validate('123.0'))->toBe([]);
			expect($obj->validate('-123.0'))->toBe([]);
			expect($obj->validate('123.456'))->toBe([]);
			expect($obj->validate('-123.456'))->toBe([]);
			expect($obj->validate('0'))->toBe([]);
			expect($obj->validate('123'))->toBe([]);
			expect($obj->validate('-123'))->toBe([]);
			expect($obj->validate('abc'))->toBe([]);
		});
	});
});
