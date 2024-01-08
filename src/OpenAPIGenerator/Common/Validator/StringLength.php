<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use Articus\DataTransfer\Validator\ValidatorInterface;
use function is_string;
use function mb_detect_encoding;
use function mb_strlen;
use function strlen;

class StringLength implements ValidatorInterface
{
	public const ERROR_STRING = 'notString';
	public const ERROR_MIN_LENGTH = 'notMinLength';
	public const ERROR_MAX_LENGTH = 'notMaxLength';

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
			if (!is_string($data))
			{
				$result[self::ERROR_STRING] = 'Invalid type - expecting string.';
			}
			else
			{
				$length = $this->getLength($data);
				if (($this->min !== null) && ($length < $this->min))
				{
					$result[self::ERROR_MIN_LENGTH] = sprintf('String length is less than %s.', $this->min);;
				}
				if (($this->max !== null) && ($this->max < $length))
				{
					$result[self::ERROR_MAX_LENGTH] = sprintf('String length is greater than %s.', $this->max);;
				}
			}
		}
		return $result;
	}

	protected function getLength(string $str): int
	{
		$encoding = mb_detect_encoding($str);
		return ($encoding === false) ? strlen($str) : mb_strlen($str, $encoding);
	}
}
