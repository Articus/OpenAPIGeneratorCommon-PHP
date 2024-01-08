<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;
use Psr\Container\ContainerInterface;

describe(OAGC\Validator\Factory\DateTimeString::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('creates validator for date-time', function ()
	{
		$container = mock(ContainerInterface::class);
		$factory = new OAGC\Validator\Factory\DateTimeString();
		$validator = $factory($container, 'test');
		expect($validator->validate('2023-12-30T23:59:59+04:00'))->toBe([]);
		expect($validator->validate('2023-12-30T23:59:59.123456+04:00'))->toBe([]);
		expect($validator->validate('abc'))->toBe([OAGC\Validator\DateTimeString::ERROR_DATE_TIME_FORMAT => 'Invalid format.']);
	});
});
