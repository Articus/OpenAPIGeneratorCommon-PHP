<?php
declare(strict_types=1);

use Articus\DataTransfer\Exception as DTException;
use OpenAPIGenerator\Common as OAGC;
use spec\Example\TestEnum;

describe(OAGC\Strategy\Enumeration::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	it('throws if there is no type option', function ()
	{
		skipIf(PHP_VERSION_ID < 80100);

		$exception = new InvalidArgumentException('Option "type" is required');
		expect(static fn () => new OAGC\Strategy\Enumeration([]))->toThrow($exception);
	});
	context('->__construct', function ()
	{
		it('throws on invalid type', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$type = stdClass::class;
			$exception = new InvalidArgumentException(sprintf('"%s" is not a backed enum.', $type));
			expect(static fn () => new OAGC\Strategy\Enumeration(['type' => $type]))->toThrow($exception);
		});
	});
	context('->extract', function ()
	{
		it('extracts from null', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$strategy = new OAGC\Strategy\Enumeration(['type' => TestEnum::class]);
			expect($strategy->extract(null))->toBeNull();
		});
		it('extracts from value of valid type', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$strategy = new OAGC\Strategy\Enumeration(['type' => TestEnum::class]);
			expect($strategy->extract(TestEnum::ABC))->toBe('abc'/*TestEnum::ABC->value*/);
		});
		it('throws on value of invalid type', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$type = TestEnum::class;
			$source = mock();
			$strategy = new OAGC\Strategy\Enumeration(['type' => $type]);

			try
			{
				$strategy->extract($source);
				throw new LogicException('No expected exception');
			}
			catch (DTException\InvalidData $e)
			{
				expect($e->getViolations())->toBe(DTException\InvalidData::DEFAULT_VIOLATION);
				expect($e->getPrevious())->toBeAnInstanceOf(InvalidArgumentException::class);
				expect($e->getPrevious()->getMessage())->toBe(
					sprintf('Extraction can be done only from %s, not %s', $type, get_class($source))
				);
			}
		});
	});
	context('->hydrate', function ()
	{
		it('hydrates from null', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$strategy = new OAGC\Strategy\Enumeration(['type' => TestEnum::class]);
			$result = mock();
			$strategy->hydrate(null, $result);
			expect($result)->toBeNull();
		});
		it('hydrates to backed enum value', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$valueStr = 'abc'/*TestEnum::ABC->value*/;
			$valueEnum = TestEnum::ABC;

			$strategy = new OAGC\Strategy\Enumeration(['type' => TestEnum::class]);
			$result = mock();
			$strategy->hydrate($valueStr, $result);
			expect($result)->toBe($valueEnum);
		});
	});
	context('->merge', function ()
	{
		it('merges by replacing "to" with "from"', function ()
		{
			skipIf(PHP_VERSION_ID < 80100);

			$strategy = new OAGC\Strategy\Enumeration(['type' => TestEnum::class]);
			$from = mock();
			$to = mock();
			$strategy->merge($from, $to);
			expect($to)->toBe($from);
		});
	});
});
