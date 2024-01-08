<?php
declare(strict_types=1);

use Articus\DataTransfer\Exception as DTException;
use OpenAPIGenerator\Common as OAGC;
use spec\Example\InvokableInterface;

describe(OAGC\Strategy\DateTime::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	context('->extract', function ()
	{
		it('extracts from null', function ()
		{
			$formatter = mock(InvokableInterface::class);
			$formatter->shouldReceive('__invoke')->never();
			$parser = mock(InvokableInterface::class);
			$parser->shouldReceive('__invoke')->never();

			$strategy = new OAGC\Strategy\DateTime($formatter, $parser);
			expect($strategy->extract(null))->toBeNull();
		});
		it('extracts from DateTimeInterface with formatter', function ()
		{
			$dateObj = new DateTime();
			$dateStr = 'test123';

			$formatter = mock(InvokableInterface::class);
			$formatter->shouldReceive('__invoke')->with($dateObj)->andReturn($dateStr)->once();
			$parser = mock(InvokableInterface::class);
			$parser->shouldReceive('__invoke')->never();

			$strategy = new OAGC\Strategy\DateTime($formatter, $parser);
			expect($strategy->extract($dateObj))->toBe($dateStr);
		});
	});
	context('->hydrate', function ()
	{
		it('hydrates from null', function ()
		{
			$formatter = mock(InvokableInterface::class);
			$formatter->shouldReceive('__invoke')->never();
			$parser = mock(InvokableInterface::class);
			$parser->shouldReceive('__invoke')->never();

			$strategy = new OAGC\Strategy\DateTime($formatter, $parser);
			$result = mock();
			$strategy->hydrate(null, $result);
			expect($result)->toBeNull();
		});
		it('hydrates to DateTimeInterface with parser', function ()
		{
			$dateObj = new DateTime();
			$dateStr = 'test123';

			$formatter = mock(InvokableInterface::class);
			$formatter->shouldReceive('__invoke')->never();
			$parser = mock(InvokableInterface::class);
			$parser->shouldReceive('__invoke')->with($dateStr)->andReturn($dateObj)->once();;

			$strategy = new OAGC\Strategy\DateTime($formatter, $parser);
			$result = mock();
			$strategy->hydrate($dateStr, $result);
			expect($result)->toBe($dateObj);
		});
		it('throws if parser returns null', function ()
		{
			$dateStr = 'test123';

			$formatter = mock(InvokableInterface::class);
			$formatter->shouldReceive('__invoke')->never();
			$parser = mock(InvokableInterface::class);
			$parser->shouldReceive('__invoke')->with($dateStr)->andReturn(null)->once();;

			$strategy = new OAGC\Strategy\DateTime($formatter, $parser);
			$result = mock();
			try
			{
				$strategy->hydrate($dateStr, $result);
				throw new LogicException('No expected exception');
			}
			catch (DTException\InvalidData $e)
			{
				expect($e->getViolations())->toBe(DTException\InvalidData::DEFAULT_VIOLATION);
				expect($e->getPrevious())->toBeAnInstanceOf(InvalidArgumentException::class);
				expect($e->getPrevious()->getMessage())->toBe('Invalid date/time string format.');
			}
		});
	});
	context('->merge', function ()
	{
		it('merges by replacing "to" with "from"', function ()
		{
			$formatter = mock(InvokableInterface::class);
			$formatter->shouldReceive('__invoke')->never();
			$parser = mock(InvokableInterface::class);
			$parser->shouldReceive('__invoke')->never();

			$strategy = new OAGC\Strategy\DateTime($formatter, $parser);
			$from = mock();
			$to = mock();
			$strategy->merge($from, $to);
			expect($to)->toBe($from);
		});
	});
});
