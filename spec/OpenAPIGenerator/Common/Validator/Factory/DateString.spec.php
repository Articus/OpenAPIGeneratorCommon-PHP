<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Validator\Factory\DateString::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates validator for date', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Validator\Factory\DateString();
		$validator = $factory($container, 'test');
		expect($validator->validate('2023-12-30'))->toBe([]);
		expect($validator->validate('abc'))->toBe([OAGC\Validator\DateTimeString::ERROR_DATE_TIME_FORMAT => 'Invalid format.']);
	});
});
