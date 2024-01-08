<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;

describe(OAGC\Validator\ItemCount::class, function ()
{
	context('->validate', function ()
	{
		it('allows null', function ()
		{
			$validator = new OAGC\Validator\ItemCount(['min' => 1]);
			expect($validator->validate(null))->toBe([]);
		});
		it('denies uncountable', function ()
		{
			$error = [OAGC\Validator\ItemCount::ERROR_COUNTABLE => 'Value is not countable.'];
			$validator = new OAGC\Validator\ItemCount(['min' => 1]);
			expect($validator->validate(false))->toBe($error);
			expect($validator->validate(123))->toBe($error);
			expect($validator->validate(123.123))->toBe($error);
			expect($validator->validate('abc'))->toBe($error);
			expect($validator->validate(new stdClass()))->toBe($error);
		});
		it('denies item count less than minimum', function ()
		{
			$min = 5;
			$error = [OAGC\Validator\ItemCount::ERROR_MIN_COUNT => sprintf('Item count is less than %s.', $min)];
			$validator = new OAGC\Validator\ItemCount(['min' => $min]);
			expect($validator->validate([]))->toBe($error);
			expect($validator->validate([1,2,3]))->toBe($error);
			expect($validator->validate([1,2,3,4]))->toBe($error);
		});
		it('denies item count greater than maximum', function ()
		{
			$max = 5;
			$error = [OAGC\Validator\ItemCount::ERROR_MAX_COUNT => sprintf('Item count is greater than %s.', $max)];
			$validator = new OAGC\Validator\ItemCount(['max' => $max]);
			expect($validator->validate([1,2,3,4,5,6]))->toBe($error);
			expect($validator->validate([1,2,3,4,5,6,7,8]))->toBe($error);
		});
		it('allows item count greater than or equal to minimum', function ()
		{
			$validator = new OAGC\Validator\ItemCount(['min' => 5]);
			expect($validator->validate([1,2,3,4,5]))->toBe([]);
			expect($validator->validate([1,2,3,4,5,6,7]))->toBe([]);
		});
		it('allows item count less than or equal to maximum', function ()
		{
			$validator = new OAGC\Validator\ItemCount(['max' => 5]);
			expect($validator->validate([1,2,3]))->toBe([]);
			expect($validator->validate([1,2,3,4,5]))->toBe([]);
		});
		it('allows item count within range', function ()
		{
			$validator = new OAGC\Validator\ItemCount(['min' => 4, 'max' => 6]);
			expect($validator->validate([1,2,3,4]))->toBe([]);
			expect($validator->validate([1,2,3,4,5]))->toBe([]);
			expect($validator->validate([1,2,3,4,5,6]))->toBe([]);
		});
	});
});
