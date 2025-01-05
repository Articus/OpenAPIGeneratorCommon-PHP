<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use Articus\DataTransfer\Validator\ValidatorInterface;
use InvalidArgumentException;
use OpenAPIGenerator\Common\ScalarType;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;

class Scalar implements ValidatorInterface
{
	public const ERROR_INVALID_TYPE = 'typeInvalid';

	/**
	 * @var ScalarType::*
	 */
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
			case ScalarType::BOOL:
			case ScalarType::INT:
			case ScalarType::FLOAT:
			case ScalarType::STRING:
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
			$result[self::ERROR_INVALID_TYPE] = sprintf('Invalid scalar type: expecting %s.', $this->type);
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
			case ScalarType::BOOL:
				return is_bool($value);
			case ScalarType::INT:
				return is_int($value);
			case ScalarType::FLOAT:
				return is_float($value) || is_int($value);
			case ScalarType::STRING:
				return is_string($value);
			default:
				return false;
		}
	}
}
