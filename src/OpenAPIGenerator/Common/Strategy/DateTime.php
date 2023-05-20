<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy;

use Articus\DataTransfer\Strategy\StrategyInterface;
use DateTimeInterface;

class DateTime implements StrategyInterface
{
	/**
	 * @var callable
	 */
	protected $formatter;

	/**
	 * @var callable
	 */
	protected $parser;

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
		return ($this->parser)($dateTimeStr);
	}
}
