<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use ArrayObject;
use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use Psr\Container\ContainerInterface;
use function array_search;

class DateList implements PM\PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): DT\Strategy\IdentifiableValueList
	{
		$valueStrategy = $this->getValueStrategy($container);
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
			$index = array_search($typedValue, $list->getArrayCopy(), false);
			if ($index !== false)
			{
				unset($list[$index]);
			}
		};
		$untypedValueConstructor = static fn ($value) => null;

		return new DT\Strategy\IdentifiableValueList(
			$valueStrategy,
			$nullIdentifier,
			$nullIdentifier,
			$typedValueAdder,
			$typedValueRemover,
			$untypedValueConstructor
		);
	}

	protected function getValueStrategy(ContainerInterface $container): DT\Strategy\StrategyInterface
	{
		return $this->getStrategyManager($container)(PluginManager::P_DATE, []);
	}

	protected function getStrategyManager(ContainerInterface $container): PM\PluginManagerInterface
	{
		return $container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER);
	}
}
