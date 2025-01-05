<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common;

/**
 * TODO replace with enum after PHP 8.1+ migration
 */
interface ScalarType
{
	public const BOOL = 'bool';
	public const INT = 'int';
	public const FLOAT = 'float';
	public const STRING = 'string';
}