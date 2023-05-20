<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use InvalidArgumentException;
use function array_key_exists;
use function count;
use function explode;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

class QueryStringScalarArray extends QueryStringScalar
{
	public const FORMAT_CSV = 'csv'; //comma separated values foo,bar.
	public const FORMAT_SSV = 'ssv'; //space separated values foo bar.
	public const FORMAT_TSV = 'tsv'; //tab separated values foo\tbar.
	public const FORMAT_PIPES = 'pipes'; //pipe separated values foo|bar.
	public const FORMAT_MULTI = 'multi'; //corresponds to multiple parameter instances instead of multiple values for a single instance foo[]=bar&foo[]=baz.

	public const DELIMITER_MAP = [
		self::FORMAT_CSV => ',',
		self::FORMAT_SSV => ' ',
		self::FORMAT_TSV => "\t",
		self::FORMAT_PIPES => '|',
		self::FORMAT_MULTI => null,
	];

	protected ?string $delimiter;

	protected int $minItems;

	protected ?int $maxItems;

	public function __construct(array $options)
	{
		parent::__construct($options);
		$format = $options['format'] ?? null;
		if (!array_key_exists($format, self::DELIMITER_MAP))
		{
			throw new InvalidArgumentException(sprintf('Unknown format "%s".', $format));
		}
		$this->delimiter = self::DELIMITER_MAP[$format];

		$minItems = $options['min_items'] ?? $options['minItems'] ?? 0;
		if ((!is_int($minItems)) || ($minItems < 0))
		{
			throw new InvalidArgumentException('Invalid "min_items" option: expecting non-negative integer.');
		}
		$this->minItems = $minItems;

		$maxItems = $options['max_items'] ?? $options['maxItems'] ?? null;
		if (($maxItems !== null) && ((!is_int($maxItems)) || ($maxItems < $this->minItems)))
		{
			throw new InvalidArgumentException('Invalid "max_items" option: expecting integer greater than or equal to "min_items".');
		}
		$this->maxItems = $maxItems;
	}

	/**
	 * @inheritDoc
	 */
	protected function hasValidType($value): bool
	{
		$result = false;
		$items = null;
		if ($this->delimiter === null)
		{
			if (is_array($value))
			{
				$items = $value;
			}
		}
		elseif (is_string($value))
		{
			//TODO allow to choose how to treat '' for strings - as [] (same other types) or ['']
			$items = ($value === '') ? [] : explode($this->delimiter, $value);
		}
		if ($items !== null)
		{
			$itemCount = count($items);
			if (($this->minItems <= $itemCount) && (($this->maxItems === null) || ($itemCount <= $this->maxItems)))
			{
				$result = true;
				foreach ($items as $item)
				{
					$result = $result && parent::hasValidType($item);
				}
			}
		}
		return $result;
	}

	protected function getInvalidTypeMessage(): string
	{
		$message = sprintf('Invalid query string scalar array type: expecting list of %s', $this->type);
		if ($this->minItems > 0)
		{
			$message .= sprintf(', at least %s elements', $this->minItems);
		}
		if ($this->maxItems !== null)
		{
			$message .= sprintf(', at most %s elements', $this->maxItems);
		}
		$message .= '.';
		return $message;
	}
}
