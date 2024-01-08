<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;
use spec\Example\InvokableInterface;

describe(OAGC\Validator\DateTimeString::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	context('->validate', function ()
	{
		it('allows null', function ()
		{
			$parser = mock(InvokableInterface::class);
			$parser->shouldReceive('__invoke')->never();

			$validator = new OAGC\Validator\DateTimeString($parser);
			expect($validator->validate(null))->toBe([]);
		});
		it('denies non-string', function ()
		{
			$parser = mock(InvokableInterface::class);
			$parser->shouldReceive('__invoke')->never();
			$error = [OAGC\Validator\DateTimeString::ERROR_STRING => 'Invalid type - expecting string.'];

			$validator = new OAGC\Validator\DateTimeString($parser);
			expect($validator->validate(123))->toBe($error);
		});
		it('denies unparsable string', function ()
		{
			$value = 'abc';
			$parser = mock(InvokableInterface::class);
			$parser->shouldReceive('__invoke')->with($value)->andReturn(null)->once();
			$error = [OAGC\Validator\DateTimeString::ERROR_DATE_TIME_FORMAT => 'Invalid format.'];

			$validator = new OAGC\Validator\DateTimeString($parser);
			expect($validator->validate($value))->toBe($error);
		});
		it('allows parsable string', function ()
		{
			$value = 'abc';
			$parser = mock(InvokableInterface::class);
			$parser->shouldReceive('__invoke')->with($value)->andReturn(new DateTime())->once();

			$validator = new OAGC\Validator\DateTimeString($parser);
			expect($validator->validate($value))->toBe([]);
		});
	});
});
