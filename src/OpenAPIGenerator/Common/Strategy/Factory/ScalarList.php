<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use ArrayObject;
use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginFactoryInterface;
use LogicException;
use OpenAPIGenerator\Common\Strategy;
use Psr\Container\ContainerInterface;
use function array_search;

class ScalarList implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): DT\Strategy\IdentifiableValueList
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new LogicException('Option "type" is required');
		}
		$valueStrategy = new Strategy\Scalar($type);
		$nullIdentifier = static fn ($value): ?string => null;
		$typedValueAdder = static function &(ArrayObject &$list, $untypedValue)
		{
			$defaultValue = null;
			$array = $list->getArrayCopy();
			$array[] = &$defaultValue;
			$list->exchangeArray($array);
			return $defaultValue;
		};
		$typedValueRemover = static function (ArrayObject &$list, $typedValue): void
		{
			$index = array_search($typedValue, $list->getArrayCopy(), true);
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
