<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

class QueryStringScalar extends Scalar
{
	const RE_INT = '0|-?[1-9]\d*';
	const RE_FLOAT = '(' . self::RE_INT . ')(\.\d+)?';
	const RE_BOOL = 'true|false';

	protected function hasValidType($value): bool
	{
		switch ($this->type)
		{
			case self::TYPE_INT:
				return \is_string($value) && (\preg_match('/^(' . self::RE_INT . ')$/', $value) === 1);
			case self::TYPE_FLOAT:
				return \is_string($value) && (\preg_match('/^(' . self::RE_FLOAT . ')$/', $value) === 1);
			case self::TYPE_BOOL:
				return \is_string($value) && (\preg_match('/^(' . self::RE_BOOL . ')$/', $value) === 1);
			case self::TYPE_STRING:
				return \is_string($value);
			default:
				return false;
		}
	}
}
