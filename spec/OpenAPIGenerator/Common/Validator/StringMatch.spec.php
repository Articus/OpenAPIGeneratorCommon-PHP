<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;

describe(OAGC\Validator\StringMatch::class, function ()
{
	context('->__construct', function ()
	{
		it('throws on no pattern', function ()
		{
			$exception = new InvalidArgumentException('Option "pattern" is required.');
			expect(static fn () => new OAGC\Validator\StringMatch([]))->toThrow($exception);
		});
	});
	context('->validate', function ()
	{
		it('allows null', function ()
		{
			$validator = new OAGC\Validator\StringMatch(['pattern' => '/^.+$/']);
			expect($validator->validate(null))->toBe([]);
		});
		it('denies non-string', function ()
		{
			$error = [OAGC\Validator\StringMatch::ERROR_STRING => 'Invalid type - expecting string.'];
			$validator = new OAGC\Validator\StringMatch(['pattern' => '/^.+$/']);
			expect($validator->validate(false))->toBe($error);
			expect($validator->validate(123))->toBe($error);
			expect($validator->validate(123.123))->toBe($error);
			expect($validator->validate([]))->toBe($error);
			expect($validator->validate(new stdClass()))->toBe($error);
		});
		it('denies string that does not match pattern', function ()
		{
			$value = 'abc';
			$pattern = '/^\d+$/';
			$error = [OAGC\Validator\StringMatch::ERROR_PATTERN => sprintf('String violates pattern %s.', $pattern)];

			$validator = new OAGC\Validator\StringMatch(['pattern' => $pattern]);
			expect($validator->validate($value))->toBe($error);
		});
		it('allows string that matches pattern', function ()
		{
			$value = '123';
			$pattern = '/^\d+$/';

			$validator = new OAGC\Validator\StringMatch(['pattern' => $pattern]);
			expect($validator->validate($value))->toBe([]);
		});
	});
});
