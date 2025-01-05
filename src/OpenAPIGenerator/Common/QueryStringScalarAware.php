<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common;

use Articus\DataTransfer\Exception as DTException;
use InvalidArgumentException;
use function is_numeric;
use function sprintf;

/**
 * TODO replace with trait after PHP 8.2 migration?
 */
abstract class QueryStringScalarAware
{
	public const ERROR_BOOL = 'notQueryStringBool';
	public const ERROR_INT = 'notQueryStringInt';
	public const ERROR_FLOAT = 'notQueryStringFloat';

	/**
	 * @param ScalarType::* $type
	 * @return array{0: callable(mixed): string, 1: callable(string): mixed}
	 */
	protected static function getScalarCoder($type): array
	{
		$encoder = null;
		$decoder = null;
		switch ($type)
		{
			case ScalarType::BOOL:
				$encoder = static fn (bool $value): string => $value ? 'true' : 'false';
				$decoder = static function (string $value): bool
				{
					switch ($value)
					{
						case 'true':
							return true;
						case 'false':
							return false;
						default:
							throw new DTException\InvalidData([self::ERROR_BOOL => 'Invalid query string parameter type: expecting bool.']);
					}
				};
				break;
			case ScalarType::INT:
				$encoder = static fn (int $value): string => (string) $value;
				$decoder = static function (string $value): int
				{
					//Faster than regular expression or is_numeric + interim float cast + comparison with floor
					$result = (int) $value;
					if ($value !== (string) $result)
					{
						throw new DTException\InvalidData([self::ERROR_INT => 'Invalid query string parameter type: expecting int.']);
					}
					return $result;
				};
				break;
			case ScalarType::FLOAT:
				$encoder = static fn (float $value): string => (string) $value;
				$decoder = static function (string $value): float
				{
					//Faster than regular expression or comparison with string cast
					if (!is_numeric($value))
					{
						throw new DTException\InvalidData([self::ERROR_FLOAT => 'Invalid query string parameter type: expecting float.']);
					}
					return (float) $value;
				};
				break;
			case ScalarType::STRING:
				$encoder = $decoder = static fn (string $value): string => $value;
				break;
			case null:
				throw new InvalidArgumentException('Option "type" is required.');
			default:
				throw new InvalidArgumentException(sprintf('Unknown type "%s".', $type));
		}
		return [$encoder, $decoder];
	}
}
