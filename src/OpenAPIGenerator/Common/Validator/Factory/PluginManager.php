<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator\Factory;

use Articus\PluginManager as PM;
use OpenAPIGenerator\Common\Validator;
use Psr\Container\ContainerInterface;

class PluginManager implements PM\ServiceFactoryInterface
{
	public const P_COUNT = 'Count';
	public const P_DATE = 'Date';
	public const P_DATE_TIME = 'DateTime';
	public const P_ENUM = 'Enum';
	public const P_ENUM_ANON = 'EnumAnon';
	public const P_LENGTH = 'Length';
	public const P_MATCH = 'Match';
	public const P_QUERY_STRING_SCALAR = 'QueryStringScalar';
	public const P_QUERY_STRING_SCALAR_ARRAY = 'QueryStringScalarArray';
	public const P_RANGE = 'Range';
	public const P_SCALAR = 'Scalar';

	public function __invoke(ContainerInterface $container, string $name): PM\Simple
	{
		$factories = [
			self::P_COUNT => new PM\Factory\InvokablePlugin(Validator\ItemCount::class),
			self::P_DATE => new DateString(),
			self::P_DATE_TIME => new DateTimeString(),
			self::P_ENUM => new PM\Factory\InvokablePlugin(Validator\Enumeration::class),
			self::P_ENUM_ANON => new PM\Factory\InvokablePlugin(Validator\AnonymousEnumeration::class),
			self::P_LENGTH => new PM\Factory\InvokablePlugin(Validator\StringLength::class),
			self::P_MATCH => new PM\Factory\InvokablePlugin(Validator\StringMatch::class),
			self::P_QUERY_STRING_SCALAR => new PM\Factory\InvokablePlugin(Validator\QueryStringScalar::class),
			self::P_QUERY_STRING_SCALAR_ARRAY => new PM\Factory\InvokablePlugin(Validator\QueryStringScalarArray::class),
			self::P_RANGE => new PM\Factory\InvokablePlugin(Validator\NumberRange::class),
			self::P_SCALAR => new PM\Factory\InvokablePlugin(Validator\Scalar::class),
		];
		$shares = [
			self::P_COUNT => true,
			self::P_DATE => true,
			self::P_DATE_TIME => true,
			self::P_ENUM => true,
			self::P_ENUM_ANON => true,
			self::P_LENGTH => true,
			self::P_MATCH => true,
			self::P_QUERY_STRING_SCALAR => true,
			self::P_QUERY_STRING_SCALAR_ARRAY => true,
			self::P_RANGE => true,
			self::P_SCALAR => true,
		];
		return new PM\Simple($container, $factories, [], $shares);
	}
}
