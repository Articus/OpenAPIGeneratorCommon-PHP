<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

class QueryStringScalarArray extends QueryStringScalar
{
	const FORMAT_CSV = 'csv'; //comma separated values foo,bar.
	const FORMAT_SSV = 'ssv'; //space separated values foo bar.
	const FORMAT_TSV = 'tsv'; //tab separated values foo\tbar.
	const FORMAT_PIPES = 'pipes'; //pipe separated values foo|bar.
	const FORMAT_MULTI = 'multi'; //corresponds to multiple parameter instances instead of multiple values for a single instance foo[]=bar&foo[]=baz.

	const DELIMITER_MAP = [
		self::FORMAT_CSV => ',',
		self::FORMAT_SSV => ' ',
		self::FORMAT_TSV => "\t",
		self::FORMAT_PIPES => '|',
		self::FORMAT_MULTI => null,
	];

	/**
	 * @var string|null
	 */
	protected $delimiter;

	/**
	 * @var int
	 */
	protected $minItems;

	/**
	 * @var int|null
	 */
	protected $maxItems;

	public function __construct(array $options)
	{
		parent::__construct($options);
		$format = $options['format'] ?? null;
		if (!\array_key_exists($format, self::DELIMITER_MAP))
		{
			throw new \InvalidArgumentException(\sprintf('Unknown format "%s".', $format));
		}
		$this->delimiter = self::DELIMITER_MAP[$format];

		$minItems = $options['min_items'] ?? $options['minItems'] ?? 0;
		if ((!\is_int($minItems)) || ($minItems < 0))
		{
			throw new \InvalidArgumentException('Invalid "min_items" option: expecting non-negative integer.');
		}
		$this->minItems = $minItems;

		$maxItems = $options['max_items'] ?? $options['maxItems'] ?? null;
		if (($maxItems !== null) && ((!\is_int($maxItems)) || ($maxItems < $this->minItems)))
		{
			throw new \InvalidArgumentException('Invalid "max_items" option: expecting integer greater than or equal to "min_items".');
		}
		$this->maxItems = $maxItems;
	}

	protected function hasValidType($value): bool
	{
		$result = false;
		if ($this->delimiter === null)
		{
			if (\is_array($value))
			{
				$itemCount = \count($value);
				if (($this->minItems <= $itemCount) && (($this->maxItems === null) || ($itemCount <= $this->maxItems)))
				{
					$result = true;
					foreach ($value as $item)
					{
						$result = $result && parent::hasValidType($item);
					}
				}
			}
		}
		elseif (\is_string($value))
		{
			switch ($this->type)
			{
				case self::TYPE_INT:
					$result = (\preg_match($this->prepareRepeatingTypeRegExp(self::RE_INT), $value) === 1);
					break;
				case self::TYPE_BOOL:
					$result = (\preg_match($this->prepareRepeatingTypeRegExp(self::RE_BOOL), $value) === 1);
					break;
				case self::TYPE_FLOAT:
					$result = (\preg_match($this->prepareRepeatingTypeRegExp(self::RE_FLOAT), $value) === 1);
					break;
				case self::TYPE_STRING:
					$result = (\preg_match($this->prepareRepeatingTypeRegExp(null), $value) === 1);
					break;
			}
		}
		return $result;
	}

	protected function prepareRepeatingTypeRegExp(?string $typeRegExp): string
	{
		$escapedDelimiter = \preg_quote($this->delimiter, '/');
		$mask = $typeRegExp ?? '[^' . $escapedDelimiter . ']*';
		if (($this->maxItems === null) || ($this->maxItems > 1))
		{
			$limits = [
				($this->minItems > 1) ? (string) ($this->minItems - 1) : '0',
				($this->maxItems === null) ? '' : (string) ($this->maxItems - 1),
			];
			$repeater = '{' . \implode(',', $limits) . '}';
			$mask = '(' . $mask . ')(' . $escapedDelimiter . '(' . $mask . '))' . $repeater;
		}
		if ($this->minItems < 1)
		{
			$mask = '(' . $mask . ')?';
		}
		return '/^(' . $mask . ')$/';
	}

	protected function getInvalidTypeMessage(): string
	{
		$message = \sprintf('Invalid type: expecting list of %s', $this->type);
		if ($this->minItems > 0)
		{
			$message .= \sprintf(', at least %s elements', $this->minItems);
		}
		if ($this->maxItems !== null)
		{
			$message .= \sprintf(', at most %s elements', $this->maxItems);
		}
		$message .= '.';
		return $message;
	}
}
