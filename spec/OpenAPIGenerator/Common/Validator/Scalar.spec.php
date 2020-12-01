<?php
declare(strict_types=1);

namespace spec\OpenAPIGenerator\Common\Validator;

use OpenAPIGenerator\Common as OAGC;
use function Eloquent\Phony\Kahlan\mock;

\describe(OAGC\Validator\Scalar::class, function ()
{
	\context('->__construct', function ()
	{
		\it('throws on no type', function ()
		{
			$exception = new \InvalidArgumentException('Unknown type "".');
			\expect(function ()
			{
				$obj = new OAGC\Validator\Scalar([]);
			})->toThrow($exception);
		});
		\it('throws on invalid type', function ()
		{
			$exception = new \InvalidArgumentException('Unknown type "test".');
			\expect(function ()
			{
				$obj = new OAGC\Validator\Scalar(['type' => 'test']);
			})->toThrow($exception);
		});
	});

	\context('->validate', function ()
	{
		\it('validates if value is integer', function ()
		{
			$obj = new OAGC\Validator\Scalar(['type' => OAGC\Validator\Scalar::TYPE_INT]);
			$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting int.'];

			\expect($obj->validate([]))->toBe($error);
			\expect($obj->validate(false))->toBe($error);
			\expect($obj->validate(true))->toBe($error);
			\expect($obj->validate(0.0))->toBe($error);
			\expect($obj->validate(123.0))->toBe($error);
			\expect($obj->validate(-123.0))->toBe($error);
			\expect($obj->validate(123.456))->toBe($error);
			\expect($obj->validate(-123.456))->toBe($error);
			\expect($obj->validate(0))->toBe([]);
			\expect($obj->validate(123))->toBe([]);
			\expect($obj->validate(-123))->toBe([]);
			\expect($obj->validate(new \stdClass()))->toBe($error);
			\expect($obj->validate(''))->toBe($error);
			\expect($obj->validate('false'))->toBe($error);
			\expect($obj->validate('true'))->toBe($error);
			\expect($obj->validate('0.0'))->toBe($error);
			\expect($obj->validate('123.0'))->toBe($error);
			\expect($obj->validate('-123.0'))->toBe($error);
			\expect($obj->validate('123.456'))->toBe($error);
			\expect($obj->validate('-123.456'))->toBe($error);
			\expect($obj->validate('0'))->toBe($error);
			\expect($obj->validate('123'))->toBe($error);
			\expect($obj->validate('-123'))->toBe($error);
			\expect($obj->validate('abc'))->toBe($error);
		});
		\it('validates if value is float', function ()
		{
			$obj = new OAGC\Validator\Scalar(['type' => OAGC\Validator\Scalar::TYPE_FLOAT]);
			$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting float.'];

			\expect($obj->validate([]))->toBe($error);
			\expect($obj->validate(false))->toBe($error);
			\expect($obj->validate(true))->toBe($error);
			\expect($obj->validate(0.0))->toBe([]);
			\expect($obj->validate(123.0))->toBe([]);
			\expect($obj->validate(-123.0))->toBe([]);
			\expect($obj->validate(123.456))->toBe([]);
			\expect($obj->validate(-123.456))->toBe([]);
			\expect($obj->validate(0))->toBe([]);
			\expect($obj->validate(123))->toBe([]);
			\expect($obj->validate(-123))->toBe([]);
			\expect($obj->validate(new \stdClass()))->toBe($error);
			\expect($obj->validate(''))->toBe($error);
			\expect($obj->validate('false'))->toBe($error);
			\expect($obj->validate('true'))->toBe($error);
			\expect($obj->validate('0.0'))->toBe($error);
			\expect($obj->validate('123.0'))->toBe($error);
			\expect($obj->validate('-123.0'))->toBe($error);
			\expect($obj->validate('123.456'))->toBe($error);
			\expect($obj->validate('-123.456'))->toBe($error);
			\expect($obj->validate('0'))->toBe($error);
			\expect($obj->validate('123'))->toBe($error);
			\expect($obj->validate('-123'))->toBe($error);
			\expect($obj->validate('abc'))->toBe($error);
		});
		\it('validates if value is boolean', function ()
		{
			$obj = new OAGC\Validator\Scalar(['type' => OAGC\Validator\Scalar::TYPE_BOOL]);
			$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting bool.'];

			\expect($obj->validate([]))->toBe($error);
			\expect($obj->validate(false))->toBe([]);
			\expect($obj->validate(true))->toBe([]);
			\expect($obj->validate(0.0))->toBe($error);
			\expect($obj->validate(123.0))->toBe($error);
			\expect($obj->validate(-123.0))->toBe($error);
			\expect($obj->validate(123.456))->toBe($error);
			\expect($obj->validate(-123.456))->toBe($error);
			\expect($obj->validate(0))->toBe($error);
			\expect($obj->validate(123))->toBe($error);
			\expect($obj->validate(-123))->toBe($error);
			\expect($obj->validate(new \stdClass()))->toBe($error);
			\expect($obj->validate(''))->toBe($error);
			\expect($obj->validate('false'))->toBe($error);
			\expect($obj->validate('true'))->toBe($error);
			\expect($obj->validate('0.0'))->toBe($error);
			\expect($obj->validate('123.0'))->toBe($error);
			\expect($obj->validate('-123.0'))->toBe($error);
			\expect($obj->validate('123.456'))->toBe($error);
			\expect($obj->validate('-123.456'))->toBe($error);
			\expect($obj->validate('0'))->toBe($error);
			\expect($obj->validate('123'))->toBe($error);
			\expect($obj->validate('-123'))->toBe($error);
			\expect($obj->validate('abc'))->toBe($error);
		});
		\it('validates if value is string', function ()
		{
			$obj = new OAGC\Validator\Scalar(['type' => OAGC\Validator\Scalar::TYPE_STRING]);
			$error = [OAGC\Validator\Scalar::ERROR_INVALID_TYPE => 'Invalid type: expecting string.'];

			\expect($obj->validate([]))->toBe($error);
			\expect($obj->validate(false))->toBe($error);
			\expect($obj->validate(true))->toBe($error);
			\expect($obj->validate(0.0))->toBe($error);
			\expect($obj->validate(123.0))->toBe($error);
			\expect($obj->validate(-123.0))->toBe($error);
			\expect($obj->validate(123.456))->toBe($error);
			\expect($obj->validate(-123.456))->toBe($error);
			\expect($obj->validate(0))->toBe($error);
			\expect($obj->validate(123))->toBe($error);
			\expect($obj->validate(-123))->toBe($error);
			\expect($obj->validate(new \stdClass()))->toBe($error);
			\expect($obj->validate(''))->toBe([]);
			\expect($obj->validate('false'))->toBe([]);
			\expect($obj->validate('true'))->toBe([]);
			\expect($obj->validate('0.0'))->toBe([]);
			\expect($obj->validate('123.0'))->toBe([]);
			\expect($obj->validate('-123.0'))->toBe([]);
			\expect($obj->validate('123.456'))->toBe([]);
			\expect($obj->validate('-123.456'))->toBe([]);
			\expect($obj->validate('0'))->toBe([]);
			\expect($obj->validate('123'))->toBe([]);
			\expect($obj->validate('-123'))->toBe([]);
			\expect($obj->validate('abc'))->toBe([]);
		});
	});
});
