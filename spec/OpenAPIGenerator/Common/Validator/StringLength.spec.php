<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;

describe(OAGC\Validator\StringLength::class, function ()
{
	context('->validate', function ()
	{
		it('allows null', function ()
		{
			$validator = new OAGC\Validator\StringLength(['min' => 1]);
			expect($validator->validate(null))->toBe([]);
		});
		it('denies non-string', function ()
		{
			$error = [OAGC\Validator\StringLength::ERROR_STRING => 'Invalid type - expecting string.'];
			$validator = new OAGC\Validator\StringLength(['min' => 1]);
			expect($validator->validate(false))->toBe($error);
			expect($validator->validate(123))->toBe($error);
			expect($validator->validate(123.123))->toBe($error);
			expect($validator->validate([]))->toBe($error);
			expect($validator->validate(new stdClass()))->toBe($error);
		});
		it('denies string length less than minimum', function ()
		{
			$min = 5;
			$error = [OAGC\Validator\StringLength::ERROR_MIN_LENGTH => sprintf('String length is less than %s.', $min)];
			$validator = new OAGC\Validator\StringLength(['min' => $min]);
			expect($validator->validate(''))->toBe($error);
			expect($validator->validate('abc'))->toBe($error);
			expect($validator->validate('abcd'))->toBe($error);
		});
		it('denies string length greater than maximum', function ()
		{
			$max = 5;
			$error = [OAGC\Validator\StringLength::ERROR_MAX_LENGTH => sprintf('String length is greater than %s.', $max)];
			$validator = new OAGC\Validator\StringLength(['max' => $max]);
			expect($validator->validate('abcdef'))->toBe($error);
			expect($validator->validate('abcdefg'))->toBe($error);
		});
		it('allows string length greater than or equal to minimum', function ()
		{
			$validator = new OAGC\Validator\StringLength(['min' => 5]);
			expect($validator->validate('abcde'))->toBe([]);
			expect($validator->validate('abcdef'))->toBe([]);
		});
		it('allows string length less than or equal to maximum', function ()
		{
			$validator = new OAGC\Validator\StringLength(['max' => 5]);
			expect($validator->validate('abc'))->toBe([]);
			expect($validator->validate('abcde'))->toBe([]);
		});
		it('allows string length within range', function ()
		{
			$validator = new OAGC\Validator\StringLength(['min' => 4, 'max' => 6]);
			expect($validator->validate('abcd'))->toBe([]);
			expect($validator->validate('abcde'))->toBe([]);
			expect($validator->validate('abcdef'))->toBe([]);
		});
	});
});
