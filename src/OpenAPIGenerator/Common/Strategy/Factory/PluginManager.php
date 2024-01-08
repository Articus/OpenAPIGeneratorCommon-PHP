<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Articus\PluginManager as PM;
use OpenAPIGenerator\Common\Strategy;
use Psr\Container\ContainerInterface;

class PluginManager implements PM\ServiceFactoryInterface
{
	public const P_DATE = 'Date';
	public const P_DATE_TIME = 'DateTime';
	public const P_DATE_LIST = 'DateList';
	public const P_DATE_TIME_LIST = 'DateTimeList';
	public const P_DATE_MAP = 'DateMap';
	public const P_DATE_TIME_MAP = 'DateTimeMap';
	public const P_ENUM = 'Enum';
	public const P_OBJECT_LIST = 'ObjectList';
	public const P_OBJECT_MAP = 'ObjectMap';
	public const P_QUERY_STRING_SCALAR = 'QueryStringScalar';
	public const P_QUERY_STRING_SCALAR_ARRAY = 'QueryStringScalarArray';
	public const P_SCALAR = 'Scalar';
	public const P_SCALAR_LIST = 'ScalarList';
	public const P_SCALAR_MAP = 'ScalarMap';

	public function __invoke(ContainerInterface $container, string $name): PM\Simple
	{
		return self::withImmutableDateTime($container);
	}

	public static function withImmutableDateTime(ContainerInterface $container): PM\Simple
	{
		$factories = [
			self::P_DATE => new ImmutableDate(),
			self::P_DATE_TIME => new ImmutableDateTime(),
		];
		$shares = [
			self::P_DATE => true,
			self::P_DATE_TIME => true,
		];
		[$defaultFactories, $defaultShares] = self::getDefaultFactoriesAndShares();
		return new PM\Simple($container, $factories + $defaultFactories, [], $shares + $defaultShares);
	}

	public static function withMutableDateTime(ContainerInterface $container): PM\Simple
	{
		$factories = [
			self::P_DATE => new MutableDate(),
			self::P_DATE_TIME => new MutableDateTime(),
		];
		$shares = [
			self::P_DATE => true,
			self::P_DATE_TIME => true,
		];
		[$defaultFactories, $defaultShares] = self::getDefaultFactoriesAndShares();
		return new PM\Simple($container, $factories + $defaultFactories, [], $shares + $defaultShares);
	}

	/**
	 * @return array{0: array<string, callable|PM\PluginFactoryInterface>, 1: array<string, bool>}
	 */
	protected static function getDefaultFactoriesAndShares(): array
	{
		$factories = [
			self::P_DATE_LIST => new DateList(),
			self::P_DATE_TIME_LIST => new DateTimeList(),
			self::P_DATE_MAP => new DateMap(),
			self::P_DATE_TIME_MAP => new DateTimeMap(),
			self::P_ENUM => new PM\Factory\InvokablePlugin(Strategy\Enumeration::class),
			self::P_OBJECT_LIST => new NoArgObjectList(),
			self::P_OBJECT_MAP => new NoArgObjectMap(),
			self::P_QUERY_STRING_SCALAR => new PM\Factory\InvokablePlugin(Strategy\QueryStringScalar::class),
			self::P_QUERY_STRING_SCALAR_ARRAY => new PM\Factory\InvokablePlugin(Strategy\QueryStringScalarArray::class),
			self::P_SCALAR => new PM\Factory\InvokablePlugin(Strategy\Scalar::class),
			self::P_SCALAR_LIST => new ScalarList(),
			self::P_SCALAR_MAP => new ScalarMap(),
		];
		$shares = [
			self::P_DATE_LIST => true,
			self::P_DATE_TIME_LIST => true,
			self::P_DATE_MAP => true,
			self::P_DATE_TIME_MAP => true,
			self::P_ENUM => true,
			self::P_OBJECT_LIST => true,
			self::P_OBJECT_MAP => true,
			self::P_QUERY_STRING_SCALAR => true,
			self::P_QUERY_STRING_SCALAR_ARRAY => true,
			self::P_SCALAR => true,
			self::P_SCALAR_LIST => true,
			self::P_SCALAR_MAP => true,
		];
		return [$factories, $shares];
	}
}
