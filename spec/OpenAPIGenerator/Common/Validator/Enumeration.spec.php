<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;
use spec\Example\TestEnum;

describe(OAGC\Validator\Enumeration::class, function ()
{
	context('->__construct', function ()
	{
		it('throws on no type', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$exception = new InvalidArgumentException('Option "type" is required.');
			expect(static fn () => new OAGC\Validator\Enumeration([]))->toThrow($exception);
		});
		it('throws on invalid type', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$type = stdClass::class;
			$exception = new InvalidArgumentException('"stdClass" is not a backed enum.');
			expect(static fn () => new OAGC\Validator\Enumeration(['type' => $type]))->toThrow($exception);
		});
	});
	context('->validate', function ()
	{
		it('allows null', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$type = TestEnum::class;
			$validator = new OAGC\Validator\Enumeration(['type' => $type]);
			expect($validator->validate(null))->toBe([]);
		});
		it('denies non-int and non-string', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$type = TestEnum::class;
			$error = [OAGC\Validator\Enumeration::ERROR_ENUM => sprintf('Allowed values: %s.', implode(', ', TestEnum::values()))];
			$validator = new OAGC\Validator\Enumeration(['type' => $type]);
			expect($validator->validate(false))->toBe($error);
			expect($validator->validate(123.0))->toBe($error);
			expect($validator->validate(123.123))->toBe($error);
			expect($validator->validate([]))->toBe($error);
			expect($validator->validate(new stdClass()))->toBe($error);
		});
		it('denies string that is not in enum', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$type = TestEnum::class;
			$error = [OAGC\Validator\Enumeration::ERROR_ENUM => sprintf('Allowed values: %s.', implode(', ', TestEnum::values()))];
			$validator = new OAGC\Validator\Enumeration(['type' => $type]);
			expect($validator->validate('aaa'))->toBe($error);
		});
		it('allows string that is in enum', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$type = TestEnum::class;
			$validator = new OAGC\Validator\Enumeration(['type' => $type]);
			expect($validator->validate('abc'/*TestEnum::ABC->value*/))->toBe([]);
			expect($validator->validate('def'/*TestEnum::DEF->value*/))->toBe([]);
		});
	});
});
