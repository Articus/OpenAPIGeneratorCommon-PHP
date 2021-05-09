<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use OpenAPIGenerator\Common\Strategy;

class ScalarList implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new \LogicException('Option "type" is required');
		}
		$valueStrategy = new Strategy\Scalar($type);
		$nullIdentifier = static function ($value): ?string
		{
			return null;
		};
		$typedValueAdder = static function &(\ArrayObject &$list, $untypedValue)
		{
			$defaultValue = null;
			$array = $list->getArrayCopy();
			$array[] = &$defaultValue;
			$list->exchangeArray($array);
			return $defaultValue;
		};
		$typedValueRemover = static function (\ArrayObject &$list, $typedValue): void
		{
			$index = \array_search($typedValue, $list->getArrayCopy(), true);
			if ($index !== false)
			{
				unset($list[$index]);
			}
		};
		$untypedValueConstructor = static function ($value)
		{
			return null;
		};

		return new DT\Strategy\IdentifiableValueList(
			$valueStrategy,
			$nullIdentifier,
			$nullIdentifier,
			$typedValueAdder,
			$typedValueRemover,
			$untypedValueConstructor
		);
	}
}
