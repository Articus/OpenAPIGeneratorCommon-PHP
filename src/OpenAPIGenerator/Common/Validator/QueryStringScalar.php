<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use function is_string;
use function preg_match;
use function sprintf;

class QueryStringScalar extends Scalar
{
	protected const RE_INT = '0|-?[1-9]\d*';
	protected const RE_FLOAT = '(' . self::RE_INT . ')(\.\d+)?';
	protected const RE_BOOL = 'true|false';

	protected function hasValidType($value): bool
	{
		switch ($this->type)
		{
			case self::TYPE_INT:
				return is_string($value) && (preg_match('/^(' . self::RE_INT . ')$/', $value) === 1);
			case self::TYPE_FLOAT:
				return is_string($value) && (preg_match('/^(' . self::RE_FLOAT . ')$/', $value) === 1);
			case self::TYPE_BOOL:
				return is_string($value) && (preg_match('/^(' . self::RE_BOOL . ')$/', $value) === 1);
			case self::TYPE_STRING:
				return is_string($value);
			default:
				return false;
		}
	}

	protected function getInvalidTypeMessage(): string
	{
		return sprintf('Invalid query string scalar type: expecting %s.', $this->type);
	}
}
