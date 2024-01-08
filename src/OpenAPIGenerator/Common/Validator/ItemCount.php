<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use Articus\DataTransfer\Validator\ValidatorInterface;
use function count;
use function is_countable;
use function sprintf;

class ItemCount implements ValidatorInterface
{
	public const ERROR_COUNTABLE = 'notCountable';
	public const ERROR_MIN_COUNT = 'notMinCount';
	public const ERROR_MAX_COUNT = 'notMaxCount';

	protected ?int $min;
	protected ?int $max;

	public function __construct(array $options)
	{
		$this->min = $options['min'] ?? null;
		$this->max = $options['max'] ?? null;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];

		if ($data !== null)
		{
			if (!is_countable($data))
			{
				$result[self::ERROR_COUNTABLE] = 'Value is not countable.';
			}
			else
			{
				$count = count($data);
				if (($this->min !== null) && ($count < $this->min))
				{
					$result[self::ERROR_MIN_COUNT] = sprintf('Item count is less than %s.', $this->min);
				}
				if (($this->max !== null) && ($this->max < $count))
				{
					$result[self::ERROR_MAX_COUNT] = sprintf('Item count is greater than %s.', $this->max);
				}
			}
		}
		return $result;
	}
}
