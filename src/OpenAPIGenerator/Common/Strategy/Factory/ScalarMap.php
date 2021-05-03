<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use OpenAPIGenerator\Common\Strategy;

class ScalarMap implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new \LogicException('Option "type" is required');
		}
		$extractStdClass = $options['extract_std_class'] ?? false;

		$valueStrategy = new Strategy\Scalar($type);
		$nullIdentifier = static function ($value): ?string
		{
			return null;
		};
		$typedValueSetter = static function &(\ArrayObject &$map, $key, $untypedValue)
		{
			$defaultValue = null;
			$map[$key] = &$defaultValue;
			return $defaultValue;
		};
		$typedValueRemover = static function (\ArrayObject &$map, $key): void
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
