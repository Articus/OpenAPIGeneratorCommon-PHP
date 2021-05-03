<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class NoArgObjectList implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new \LogicException('Option "type" is required');
		}
		elseif (!\class_exists($type))
		{
			throw new \LogicException(\sprintf('Type "%s" does not exist', $type));
		}
		$subset = $options['subset'] ?? '';
		$valueStrategy = $this->getStrategyManager($container)->get(...$this->getMetadataProvider($container)->getClassStrategy($type, $subset));
		$nullIdentifier = static function ($value): ?string
		{
			return null;
		};
		$typedValueAdder = static function &(\ArrayObject &$list, $untypedValue) use ($type)
		{
			$defaultValue = new $type();
			$list[\max(\array_keys($list->getArrayCopy())) + 1] = &$defaultValue;
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

	protected function getStrategyManager(ContainerInterface $container): DT\Strategy\PluginManager
	{
		return $container->get(DT\Strategy\PluginManager::class);
	}
}
