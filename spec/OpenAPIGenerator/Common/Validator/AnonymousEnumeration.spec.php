<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;

describe(OAGC\Validator\AnonymousEnumeration::class, function ()
{
	context('->__construct', function ()
	{
		it('throws on no values', function ()
		{
			$exception = new InvalidArgumentException('Option "values" is required.');
			expect(static fn () => new OAGC\Validator\AnonymousEnumeration([]))->toThrow($exception);
		});
	});
	context('->validate', function ()
	{
		it('allows null', function ()
		{
			$validator = new OAGC\Validator\AnonymousEnumeration(['values' => ['abc', 123]]);
			expect($validator->validate(null))->toBe([]);
		});
		it('allows specified value', function ()
		{
			$validator = new OAGC\Validator\AnonymousEnumeration(['values' => ['abc', 123]]);
			expect($validator->validate('abc'))->toBe([]);
			expect($validator->validate(123))->toBe([]);
		});
		it('denies non-specified value', function ()
		{
			$validator = new OAGC\Validator\AnonymousEnumeration(['values' => ['abc', 123]]);
			$error = [OAGC\Validator\AnonymousEnumeration::ERROR_ENUM => 'Allowed values: abc, 123.'];
			expect($validator->validate('def'))->toBe($error);
			expect($validator->validate(456))->toBe($error);
			expect($validator->validate('123'))->toBe($error);
		});
	});
});
