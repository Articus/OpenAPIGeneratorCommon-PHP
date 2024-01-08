<?php
declare(strict_types=1);

use OpenAPIGenerator\Common as OAGC;

describe(OAGC\Strategy\QueryStringScalar::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	context('->__construct', function ()
	{
		it('throws on no type', function ()
		{
			$exception = new InvalidArgumentException('Option "type" is required.');
			expect(static fn () => new OAGC\Strategy\QueryStringScalar([]))->toThrow($exception);
		});
		it('throws on invalid type', function ()
		{
			$exception = new InvalidArgumentException('Unknown type "test".');
			expect(static fn () => new OAGC\Strategy\QueryStringScalar(['type' => 'test']))->toThrow($exception);
		});
	});
	context('->extract', function ()
	{
		it('extracts nullable integer', function ()
		{
			$strategy = new OAGC\Strategy\QueryStringScalar(['type' => OAGC\Validator\QueryStringScalar::TYPE_INT]);
			expect($strategy->extract(null))->toBeNull();
			expect($strategy->extract(0))->toBe('0');
			expect($strategy->extract(123))->toBe('123');
			expect($strategy->extract(-123))->toBe('-123');
		});
		it('extracts nullable float', function ()
		{
			$strategy = new OAGC\Strategy\QueryStringScalar(['type' => OAGC\Validator\QueryStringScalar::TYPE_FLOAT]);
			expect($strategy->extract(null))->toBeNull();
			expect($strategy->extract(0.0))->toBe('0');
			expect($strategy->extract(123.0))->toBe('123');
			expect($strategy->extract(-123.0))->toBe('-123');
			expect($strategy->extract(123.567))->toBe('123.567');
			expect($strategy->extract(-123.567))->toBe('-123.567');
		});
		it('extracts nullable boolean', function ()
		{
			$strategy = new OAGC\Strategy\QueryStringScalar(['type' => OAGC\Validator\QueryStringScalar::TYPE_BOOL]);
			expect($strategy->extract(null))->toBeNull();
			expect($strategy->extract(false))->toBe('false');
			expect($strategy->extract(true))->toBe('true');
		});
		it('extracts nullable string', function ()
		{
			$strategy = new OAGC\Strategy\QueryStringScalar(['type' => OAGC\Validator\QueryStringScalar::TYPE_STRING]);
			expect($strategy->extract(null))->toBeNull();
			expect($strategy->extract(''))->toBe('');
			expect($strategy->extract('abc'))->toBe('abc');
		});
	});
	context('->hydrate', function ()
	{
		it('hydrates nullable integer', function ()
		{
			$strategy = new OAGC\Strategy\QueryStringScalar(['type' => OAGC\Validator\QueryStringScalar::TYPE_INT]);
			$result = new stdClass();
			$strategy->hydrate(null, $result);
			expect($result)->toBeNull();
			$strategy->hydrate('0', $result);
			expect($result)->toBe(0);
			$strategy->hydrate('123', $result);
			expect($result)->toBe(123);
			$strategy->hydrate('-123', $result);
			expect($result)->toBe(-123);
		});
		it('hydrates nullable float', function ()
		{
			$strategy = new OAGC\Strategy\QueryStringScalar(['type' => OAGC\Validator\QueryStringScalar::TYPE_FLOAT]);
			$result = new stdClass();
			$strategy->hydrate(null, $result);
			expect($result)->toBeNull();
			$strategy->hydrate('0', $result);
			expect($result)->toBe(0.0);
			$strategy->hydrate('123', $result);
			expect($result)->toBe(123.0);
			$strategy->hydrate('-123', $result);
			expect($result)->toBe(-123.0);
			$strategy->hydrate('0.0', $result);
			expect($result)->toBe(0.0);
			$strategy->hydrate('123.0', $result);
			expect($result)->toBe(123.0);
			$strategy->hydrate('-123.0', $result);
			expect($result)->toBe(-123.0);
			$strategy->hydrate('123.567', $result);
			expect($result)->toBe(123.567);
			$strategy->hydrate('-123.567', $result);
			expect($result)->toBe(-123.567);
		});
		it('hydrates nullable boolean', function ()
		{
			$strategy = new OAGC\Strategy\QueryStringScalar(['type' => OAGC\Validator\QueryStringScalar::TYPE_BOOL]);
			$result = new stdClass();
			$strategy->hydrate(null, $result);
			expect($result)->toBeNull();
			$strategy->hydrate('false', $result);
			expect($result)->toBe(false);
			$strategy->hydrate('true', $result);
			expect($result)->toBe(true);
		});
		it('hydrates nullable string', function ()
		{
			$strategy = new OAGC\Strategy\QueryStringScalar(['type' => OAGC\Validator\QueryStringScalar::TYPE_STRING]);
			$result = new stdClass();
			$strategy->hydrate(null, $result);
			expect($result)->toBeNull();
			$strategy->hydrate('', $result);
			expect($result)->toBe('');
			$strategy->hydrate('abc', $result);
			expect($result)->toBe('abc');
		});
	});
	context('->merge', function ()
	{
		it('merges by replacing "to" with "from"', function ()
		{
			$strategy = new OAGC\Strategy\QueryStringScalar(['type' => OAGC\Validator\QueryStringScalar::TYPE_INT]);
			$from = mock();
			$to = mock();
			$strategy->merge($from, $to);
			expect($to)->toBe($from);
		});
		//TODO add other types
	});
});
