<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use ArrayObject;
use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginFactoryInterface;
use Articus\PluginManager\PluginManagerInterface;
use Psr\Container\ContainerInterface;

class DateMap implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): DT\Strategy\IdentifiableValueMap
	{
		$extractStdClass = $options['extract_std_class'] ?? false;
		$valueStrategy = $this->getValueStrategy($container);
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
		$untypedValueConstructor = static fn ($value) => null;

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

	protected function getValueStrategy(ContainerInterface $container): DT\Strategy\StrategyInterface
	{
		return $this->getStrategyManager($container)(PluginManager::P_DATE, []);
	}

	protected function getStrategyManager(ContainerInterface $container): PluginManagerInterface
	{
		return $container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER);
	}
}
