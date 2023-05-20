<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use ArrayObject;
use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginFactoryInterface;
use Articus\PluginManager\PluginManagerInterface;
use LogicException;
use Psr\Container\ContainerInterface;
use function array_search;
use function class_exists;
use function sprintf;

class NoArgObjectList implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): DT\Strategy\IdentifiableValueList
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new LogicException('Option "type" is required');
		}
		elseif (!class_exists($type))
		{
			throw new LogicException(sprintf('Type "%s" does not exist', $type));
		}
		$subset = $options['subset'] ?? '';
		$valueStrategy = $this->getStrategyManager($container)(...$this->getMetadataProvider($container)->getClassStrategy($type, $subset));
		$nullIdentifier = static fn ($value): ?string => null;
		$typedValueAdder = static function &(ArrayObject &$list, $untypedValue) use ($type)
		{
			$defaultValue = new $type();
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
		$untypedValueConstructor = static function ($value) use ($type, $valueStrategy)
		{
			$defaultValue = new $type();
			return $valueStrategy->extract($defaultValue);
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

	protected function getMetadataProvider(ContainerInterface $container): DT\ClassMetadataProviderInterface
	{
		return $container->get(DT\ClassMetadataProviderInterface::class);
	}

	protected function getStrategyManager(ContainerInterface $container): PluginManagerInterface
	{
		return $container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER);
	}
}
