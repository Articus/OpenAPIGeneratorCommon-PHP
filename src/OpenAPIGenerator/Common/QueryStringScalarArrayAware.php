<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common;

use InvalidArgumentException;
use function array_map;
use function explode;
use function implode;
use function sprintf;

/**
 * TODO replace with trait after PHP 8.2 migration?
 */
abstract class QueryStringScalarArrayAware extends QueryStringScalarAware
{
	public const ARRAY_FORMAT_CSV = 'csv'; //comma separated values foo,bar.
	public const ARRAY_FORMAT_SSV = 'ssv'; //space separated values foo bar.
	public const ARRAY_FORMAT_TSV = 'tsv'; //tab separated values foo\tbar.
	public const ARRAY_FORMAT_PIPES = 'pipes'; //pipe separated values foo|bar.

	/**
	 * @param self::ARRAY_FORMAT_* $format
	 * @param ScalarType::* $type
	 * @return array{0: callable(mixed): string, 1: callable(string): mixed}
	 */
	protected static function getScalarArrayCoder($format, $type): array
	{
		[$itemEncoder, $itemDecoder] = self::getScalarCoder($type);
		$delimiter = null;
		switch ($format)
		{
			case self::ARRAY_FORMAT_CSV:
				$delimiter = ',';
				break;
			case self::ARRAY_FORMAT_SSV:
				$delimiter = ' ';
				break;
			case self::ARRAY_FORMAT_TSV:
				$delimiter = "\t";
				break;
			case self::ARRAY_FORMAT_PIPES:
				$delimiter = '|';
				break;
			case null:
				throw new InvalidArgumentException('Option "format" is required.');
			default:
				throw new InvalidArgumentException(sprintf('Unknown format "%s".', $format));
		}

		$encoder = static fn (array $value): string => implode($delimiter, array_map($itemEncoder, $value));
		//TODO allow to choose how to treat '' for strings - as [] (same as other types) or ['']
		$decoder = static fn (string $value): array => ($value === '') ? [] : array_map($itemDecoder, explode($delimiter, $value));

		return [$encoder, $decoder];
	}
}
