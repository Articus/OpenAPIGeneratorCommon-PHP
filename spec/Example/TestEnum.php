<?php
declare(strict_types=1);

namespace spec\Example;

use function array_map;

enum TestEnum: string
{
	case ABC = 'abc';
	case DEF = 'def';
	case GHI = 'ghi';

	public static function values(): array
	{
		return array_map(static fn (self $enum) => $enum->value, self::cases());
	}
}
