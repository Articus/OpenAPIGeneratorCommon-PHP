<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy;

use Articus\DataTransfer\Exception as DTException;
use InvalidArgumentException;
use OpenAPIGenerator\Common\Validator;
use function array_key_exists;
use function explode;
use function get_class;
use function gettype;
use function implode;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;

class QueryStringScalarArray extends QueryStringScalar
{
	protected ?string $delimiter;

	public function __construct(array $options)
	{
		parent::__construct($options);
		$format = $options['format'] ?? null;
		if (!array_key_exists($format, Validator\QueryStringScalarArray::DELIMITER_MAP))
		{
			throw new InvalidArgumentException(sprintf('Unknown format "%s".', $format));
		}
		$this->delimiter = Validator\QueryStringScalarArray::DELIMITER_MAP[$format];
	}

	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		$result = null;
		if ($from !== null)
		{
			if (!is_array($from))
			{
				throw new DTException\InvalidData(
					DTException\InvalidData::DEFAULT_VIOLATION,
					new InvalidArgumentException(sprintf(
						'Extraction can be done only from array, not %s',
						is_object($from) ? get_class($from) : gettype($from)
					))
				);
			}
			$list = [];
			foreach ($from as $index => $item)
			{
				$extractedItem = parent::extract($item);
				if (($this->delimiter !== null) && (\strpos($extractedItem, $this->delimiter) !== false))
				{
					throw new DTException\InvalidData(
						DTException\InvalidData::DEFAULT_VIOLATION,
						new InvalidArgumentException(
							sprintf('Item at index %s contains delimiter symbol and should be encoded.', $index)
						)
					);
				}
				$list[$index] = $extractedItem;
			}
			$result = ($this->delimiter === null) ? $list : implode($this->delimiter, $list);
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($from, &$to): void
	{
		if ($from !== null)
		{
			$list = null;
			if ($this->delimiter === null)
			{
				if (!is_array($from))
				{
					throw new DTException\InvalidData(
						DTException\InvalidData::DEFAULT_VIOLATION,
						new InvalidArgumentException(sprintf(
							'Hydration can be done only from array, not %s',
							is_object($from) ? get_class($from) : gettype($from)
						))
					);
				}
				$list = $from;
			}
			else
			{
				if (!is_string($from))
				{
					throw new DTException\InvalidData(
						DTException\InvalidData::DEFAULT_VIOLATION,
						new InvalidArgumentException(sprintf(
							'Hydration can be done only from string, not %s',
							is_object($from) ? get_class($from) : gettype($from)
						))
					);
				}
				//TODO allow to choose how to treat '' for strings - as [] (same other types) or ['']
				$list = ($from === '') ? [] : explode($this->delimiter, $from);
			}
			$to = [];
			foreach ($list as $index => $item)
			{
				$to[$index] = null;
				parent::hydrate($item, $to[$index]);
			}
		}
		else
		{
			$to = null;
		}
	}
}
