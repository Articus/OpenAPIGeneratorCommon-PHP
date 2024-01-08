<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy;

use Articus\DataTransfer\Exception as DTException;
use Articus\DataTransfer\Strategy\StrategyInterface;
use DateTimeInterface;
use InvalidArgumentException;

class DateTime implements StrategyInterface
{
	/**
	 * @var callable(DateTimeInterface): string
	 */
	protected $formatter;

	/**
	 * @var callable(string): (DateTimeInterface|null)
	 */
	protected $parser;

	/**
	 * @param callable(DateTimeInterface): string $formatter
	 * @param callable(string): (DateTimeInterface|null) $parser
	 */
	public function __construct(callable $formatter, callable $parser)
	{
		$this->formatter = $formatter;
		$this->parser = $parser;
	}

	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		return ($from === null) ? null : $this->formatDateTime($from);
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($from, &$to): void
	{
		$to = ($from === null) ? null : $this->parseDateTime($from);
	}

	/**
	 * @inheritDoc
	 */
	public function merge($from, &$to): void
	{
		$to = $from;
	}

	protected function formatDateTime(DateTimeInterface $dateTimeObj): string
	{
		return ($this->formatter)($dateTimeObj);
	}

	protected function parseDateTime(string $dateTimeStr): DateTimeInterface
	{
		$dateTimeObj = ($this->parser)($dateTimeStr);
		if (!($dateTimeObj instanceof DateTimeInterface))
		{
			throw new DTException\InvalidData(
				DTException\InvalidData::DEFAULT_VIOLATION,
				new InvalidArgumentException('Invalid date/time string format.')
			);

		}
		return $dateTimeObj;
	}
}
