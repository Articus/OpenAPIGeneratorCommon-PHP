<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use Articus\DataTransfer\Validator\ValidatorInterface;
use function is_numeric;
use function sprintf;

class NumberRange implements ValidatorInterface
{
	public const ERROR_NUMBER = 'notNumber';
	public const ERROR_MIN = 'notMin';
	public const ERROR_EXCLUSIVE_MIN = 'notExclusiveMin';
	public const ERROR_MAX = 'notMax';
	public const ERROR_EXCLUSIVE_MAX = 'notExclusiveMax';

	/**
	 * @var float|int|null
	 */
	protected $min = null;
	/**
	 * @var float|int|null
	 */
	protected $max = null;
	protected bool $excludeMin = false;
	protected bool $excludeMax = false;

	public function __construct(array $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'min':
					$this->min = $value;
					break;
				case 'max':
					$this->max = $value;
					break;
				case 'exclude_min':
				case 'excludeMin':
					$this->excludeMin = $value;
					break;
				case 'exclude_max':
				case 'excludeMax':
					$this->excludeMax = $value;
					break;
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		if ($data !== null)
		{
			if (!is_numeric($data))
			{
				$result[self::ERROR_NUMBER] = 'Invalid type - expecting number.';
			}
			else
			{
				if ($this->min !== null)
				{
					if ((!$this->excludeMin) && ($data < $this->min))
					{
						$result[self::ERROR_MIN] = sprintf('Number is less than %s.', $this->min);
					}
					if ($this->excludeMin && ($data <= $this->min))
					{
						$result[self::ERROR_EXCLUSIVE_MIN] = sprintf('Number is not greater than %s.', $this->min);;
					}
				}
				if ($this->max !== null)
				{
					if ((!$this->excludeMax) && ($this->max < $data))
					{
						$result[self::ERROR_MAX] = sprintf('Number is greater than %s.', $this->max);;
					}
					if ($this->excludeMax && ($this->max <= $data))
					{
						$result[self::ERROR_EXCLUSIVE_MAX] = sprintf('Number is not less than %s.', $this->max);;
					}
				}
			}
		}
		return $result;
	}
}
