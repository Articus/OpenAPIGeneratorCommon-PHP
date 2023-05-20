<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use ArrayObject;
use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginFactoryInterface;
use LogicException;
use OpenAPIGenerator\Common\Strategy;
use Psr\Container\ContainerInterface;

class ScalarMap implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): DT\Strategy\IdentifiableValueMap
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new LogicException('Option "type" is required');
		}
		$extractStdClass = $options['extract_std_class'] ?? false;

		$valueStrategy = new Strategy\Scalar($type);
		$nullIdentifier = static fn ($value): ?string => null;
		$typedValueSetter = static function &(ArrayObject &$map, $key, $untypedValue)
		{
			$defaultValue = null;
			$map[$key] = &$defaultValue;
			return $defaultValue;
		};
		$typedValueRemover = static function (ArrayObject &$map, $key): void
		{
			unset($map[$key]);
		};
		$untypedValueConstructor = static function ($value)
		{
			return null;
		};

		return new DT\Strategy\IdentifiableValueMap(
			$valueStrategy,
			$nullIdentifier,
			$nullIdentifier,
			$typedValueSetter,
			$typedValueRemover,
			$untypedValueConstructor,
			$extractStdClass
		);
	}
}
