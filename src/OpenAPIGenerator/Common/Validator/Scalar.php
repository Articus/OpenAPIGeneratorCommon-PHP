<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use Articus\DataTransfer\Validator\ValidatorInterface;

class Scalar implements ValidatorInterface
{
	const ERROR_INVALID_TYPE = 'typeInvalid';

	const TYPE_INT = 'int';
	const TYPE_FLOAT = 'float';
	const TYPE_BOOL = 'bool';
	const TYPE_STRING = 'string';

	/**
	 * @var string
	 */
	protected $type;

	/**
	 */
	public function __construct(array $options)
	{
		$type = $options['type'] ?? null;
		switch ($type)
		{
			case self::TYPE_INT:
			case self::TYPE_FLOAT:
			case self::TYPE_BOOL:
			case self::TYPE_STRING:
				$this->type = $type;
				break;
			default:
				throw new \InvalidArgumentException(\sprintf('Unknown type "%s".', $type));
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

	protected function hasValidType($value): bool
	{
		switch ($this->type)
		{
			case self::TYPE_INT:
				return \is_int($value);
			case self::TYPE_FLOAT:
				return \is_float($value) || \is_int($value);
			case self::TYPE_BOOL:
				return \is_bool($value);
			case self::TYPE_STRING:
				return \is_string($value);
			default:
				return false;
		}
	}

	protected function getInvalidTypeMessage(): string
	{
		return \sprintf('Invalid type: expecting %s.', $this->type);
	}
}
