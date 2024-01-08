<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use Articus\DataTransfer\Validator\ValidatorInterface;
use InvalidArgumentException;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;

class Scalar implements ValidatorInterface
{
	public const ERROR_INVALID_TYPE = 'typeInvalid';

	public const TYPE_INT = 'int';
	public const TYPE_FLOAT = 'float';
	public const TYPE_BOOL = 'bool';
	public const TYPE_STRING = 'string';

	protected string $type;

	public function __construct(array $options)
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new InvalidArgumentException('Option "type" is required.');
		}
		switch ($type)
		{
			case self::TYPE_INT:
			case self::TYPE_FLOAT:
			case self::TYPE_BOOL:
			case self::TYPE_STRING:
				$this->type = $type;
				break;
			default:
				throw new InvalidArgumentException(sprintf('Unknown type "%s".', $type));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		if (($data !== null) && (!$this->hasValidType($data)))
		{
			$result[self::ERROR_INVALID_TYPE] = $this->getInvalidTypeMessage();
		}
		return $result;
	}

	/**
	 * @param bool|int|float|string|array|\stdClass $value
	 * @return bool
	 */
	protected function hasValidType($value): bool
	{
		switch ($this->type)
		{
			case self::TYPE_INT:
				return is_int($value);
			case self::TYPE_FLOAT:
				return is_float($value) || is_int($value);
			case self::TYPE_BOOL:
				return is_bool($value);
			case self::TYPE_STRING:
				return is_string($value);
			default:
				return false;
		}
	}

	protected function getInvalidTypeMessage(): string
	{
		return sprintf('Invalid scalar type: expecting %s.', $this->type);
	}
}
