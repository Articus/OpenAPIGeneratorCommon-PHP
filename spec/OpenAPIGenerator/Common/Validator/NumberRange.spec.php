<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;

describe(OAGC\Validator\NumberRange::class, function ()
{
	context('->validate', function ()
	{
		it('allows null', function ()
		{
			$validator = new OAGC\Validator\NumberRange(['min' => 1]);
			expect($validator->validate(null))->toBe([]);
		});
		it('denies non-numeric', function ()
		{
			$error = [OAGC\Validator\NumberRange::ERROR_NUMBER => 'Invalid type - expecting number.'];
			$validator = new OAGC\Validator\NumberRange(['min' => 1]);
			expect($validator->validate(false))->toBe($error);
			expect($validator->validate('abc'))->toBe($error);
			expect($validator->validate([1,2,3]))->toBe($error);
			expect($validator->validate(new stdClass()))->toBe($error);
		});
		it('denies number less than integer minimum', function ()
		{
			$min = 5;
			$error = [OAGC\Validator\NumberRange::ERROR_MIN => sprintf('Number is less than %s.', $min)];
			$validator = new OAGC\Validator\NumberRange(['min' => $min]);
			expect($validator->validate(4))->toBe($error);
			expect($validator->validate(4.5))->toBe($error);
			expect($validator->validate('4'))->toBe($error);
			expect($validator->validate('4.5'))->toBe($error);
			expect($validator->validate($min - PHP_FLOAT_EPSILON*10))->toBe($error);
		});
		it('allows number not less than integer minimum', function ()
		{
			$validator = new OAGC\Validator\NumberRange(['min' => 5]);
			expect($validator->validate(6))->toBe([]);
			expect($validator->validate(5.5))->toBe([]);
			expect($validator->validate('6'))->toBe([]);
			expect($validator->validate('5.5'))->toBe([]);
			expect($validator->validate(5))->toBe([]);
			expect($validator->validate(5.0))->toBe([]);
			expect($validator->validate('5'))->toBe([]);
			expect($validator->validate('5.0'))->toBe([]);
		});
		it('denies number less than float minimum', function ()
		{
			$min = 5.5;
			$error = [OAGC\Validator\NumberRange::ERROR_MIN => sprintf('Number is less than %s.', $min)];
			$validator = new OAGC\Validator\NumberRange(['min' => $min]);
			expect($validator->validate(5))->toBe($error);
			expect($validator->validate(5.25))->toBe($error);
			expect($validator->validate('5'))->toBe($error);
			expect($validator->validate('5.25'))->toBe($error);
			expect($validator->validate($min - PHP_FLOAT_EPSILON*10))->toBe($error);
		});
		it('allows number not less than float minimum', function ()
		{
			$validator = new OAGC\Validator\NumberRange(['min' => 5.5]);
			expect($validator->validate(6))->toBe([]);
			expect($validator->validate(5.75))->toBe([]);
			expect($validator->validate('6'))->toBe([]);
			expect($validator->validate('5.75'))->toBe([]);
			expect($validator->validate(5.5))->toBe([]);
			expect($validator->validate('5.5'))->toBe([]);
		});
		it('denies number not greater than exclusive integer minimum', function ()
		{
			$min = 5;
			$error = [OAGC\Validator\NumberRange::ERROR_EXCLUSIVE_MIN => sprintf('Number is not greater than %s.', $min)];
			$validator = new OAGC\Validator\NumberRange(['min' => $min, 'exclude_min' => true]);
			expect($validator->validate(4))->toBe($error);
			expect($validator->validate(4.5))->toBe($error);
			expect($validator->validate('4'))->toBe($error);
			expect($validator->validate('4.5'))->toBe($error);
			expect($validator->validate(5))->toBe($error);
			expect($validator->validate(5.0))->toBe($error);
			expect($validator->validate('5'))->toBe($error);
			expect($validator->validate('5.0'))->toBe($error);
		});
		it('allows number greater than exclusive integer minimum', function ()
		{
			$validator = new OAGC\Validator\NumberRange(['min' => 5, 'exclude_min' => true]);
			expect($validator->validate(6))->toBe([]);
			expect($validator->validate(5.5))->toBe([]);
			expect($validator->validate('6'))->toBe([]);
			expect($validator->validate('5.5'))->toBe([]);
		});
		it('denies number not greater than exclusive float minimum', function ()
		{
			$min = 5.5;
			$error = [OAGC\Validator\NumberRange::ERROR_EXCLUSIVE_MIN => sprintf('Number is not greater than %s.', $min)];
			$validator = new OAGC\Validator\NumberRange(['min' => $min, 'exclude_min' => true]);
			expect($validator->validate(5))->toBe($error);
			expect($validator->validate(5.25))->toBe($error);
			expect($validator->validate('5'))->toBe($error);
			expect($validator->validate('5.25'))->toBe($error);
			expect($validator->validate(5.5))->toBe($error);
			expect($validator->validate('5.5'))->toBe($error);
		});
		it('allows number greater than exclusive float minimum', function ()
		{
			$validator = new OAGC\Validator\NumberRange(['min' => 5.5, 'exclude_min' => true]);
			expect($validator->validate(6))->toBe([]);
			expect($validator->validate(5.75))->toBe([]);
			expect($validator->validate('6'))->toBe([]);
			expect($validator->validate('5.75'))->toBe([]);
		});
		it('denies number greater than integer maximum', function ()
		{
			$max = 5;
			$error = [OAGC\Validator\NumberRange::ERROR_MAX => sprintf('Number is greater than %s.', $max)];
			$validator = new OAGC\Validator\NumberRange(['max' => $max]);
			expect($validator->validate(6))->toBe($error);
			expect($validator->validate(5.5))->toBe($error);
			expect($validator->validate('6'))->toBe($error);
			expect($validator->validate('5.5'))->toBe($error);
			expect($validator->validate($max + PHP_FLOAT_EPSILON*10))->toBe($error);
		});
		it('allows number not greater than integer maximum', function ()
		{
			$validator = new OAGC\Validator\NumberRange(['max' => 5]);
			expect($validator->validate(4))->toBe([]);
			expect($validator->validate(4.5))->toBe([]);
			expect($validator->validate('4'))->toBe([]);
			expect($validator->validate('4.5'))->toBe([]);
			expect($validator->validate(5))->toBe([]);
			expect($validator->validate(5.0))->toBe([]);
			expect($validator->validate('5'))->toBe([]);
			expect($validator->validate('5.0'))->toBe([]);
		});
		it('denies number greater than float maximum', function ()
		{
			$max = 5.5;
			$error = [OAGC\Validator\NumberRange::ERROR_MAX => sprintf('Number is greater than %s.', $max)];
			$validator = new OAGC\Validator\NumberRange(['max' => $max]);
			expect($validator->validate(6))->toBe($error);
			expect($validator->validate(5.75))->toBe($error);
			expect($validator->validate('6'))->toBe($error);
			expect($validator->validate('5.75'))->toBe($error);
			expect($validator->validate($max + PHP_FLOAT_EPSILON*10))->toBe($error);
		});
		it('allows number not greater than float maximum', function ()
		{
			$validator = new OAGC\Validator\NumberRange(['max' => 5.5]);
			expect($validator->validate(5))->toBe([]);
			expect($validator->validate(5.25))->toBe([]);
			expect($validator->validate('5'))->toBe([]);
			expect($validator->validate('5.25'))->toBe([]);
			expect($validator->validate(5.5))->toBe([]);
			expect($validator->validate('5.5'))->toBe([]);
		});
		it('denies number not less than exclusive integer maximum', function ()
		{
			$max = 5;
			$error = [OAGC\Validator\NumberRange::ERROR_EXCLUSIVE_MAX => sprintf('Number is not less than %s.', $max)];
			$validator = new OAGC\Validator\NumberRange(['max' => $max, 'exclude_max' => true]);
			expect($validator->validate(6))->toBe($error);
			expect($validator->validate(5.5))->toBe($error);
			expect($validator->validate('6'))->toBe($error);
			expect($validator->validate('5.5'))->toBe($error);
			expect($validator->validate(5))->toBe($error);
			expect($validator->validate(5.0))->toBe($error);
			expect($validator->validate('5'))->toBe($error);
			expect($validator->validate('5.0'))->toBe($error);
		});
		it('allows number less than exclusive integer maximum', function ()
		{
			$validator = new OAGC\Validator\NumberRange(['max' => 5, 'exclude_max' => true]);
			expect($validator->validate(4))->toBe([]);
			expect($validator->validate(4.5))->toBe([]);
			expect($validator->validate('4'))->toBe([]);
			expect($validator->validate('4.5'))->toBe([]);
		});
		it('denies number not less than exclusive float maximum', function ()
		{
			$max = 5.5;
			$error = [OAGC\Validator\NumberRange::ERROR_EXCLUSIVE_MAX => sprintf('Number is not less than %s.', $max)];
			$validator = new OAGC\Validator\NumberRange(['max' => $max, 'exclude_max' => true]);
			expect($validator->validate(6))->toBe($error);
			expect($validator->validate(5.75))->toBe($error);
			expect($validator->validate('6'))->toBe($error);
			expect($validator->validate('5.75'))->toBe($error);
			expect($validator->validate(5.5))->toBe($error);
			expect($validator->validate('5.5'))->toBe($error);
		});
		it('allows number less than exclusive float maximum', function ()
		{
			$validator = new OAGC\Validator\NumberRange(['max' => 5.5, 'exclude_max' => true]);
			expect($validator->validate(5))->toBe([]);
			expect($validator->validate(5.25))->toBe([]);
			expect($validator->validate('5'))->toBe([]);
			expect($validator->validate('5.25'))->toBe([]);
		});
	});
});
