<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use ArrayObject;
use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginFactoryInterface;
use Articus\PluginManager\PluginManagerInterface;
use LogicException;
use Psr\Container\ContainerInterface;
use function class_exists;
use function sprintf;

class NoArgObjectMap implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): DT\Strategy\IdentifiableValueMap
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
		$extractStdClass = $options['extract_std_class'] ?? false;
		$valueStrategy = $this->getStrategyManager($container)(...$this->getMetadataProvider($container)->getClassStrategy($type, $subset));
		$nullIdentifier = static fn ($value): ?string => null;
		$typedValueSetter = static function &(ArrayObject &$map, $key, $untypedValue) use ($type)
		{
			$defaultValue = new $type();
			$map[$key] = &$defaultValue;
			return $defaultValue;
		};
		$typedValueRemover = static function (ArrayObject &$map, $key): void
		{
			unset($map[$key]);
		};
		$untypedValueConstructor = static function ($value) use ($type, $valueStrategy)
		{
			$defaultValue = new $type();
			return $valueStrategy->extract($defaultValue);
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

	protected function getMetadataProvider(ContainerInterface $container): DT\ClassMetadataProviderInterface
	{
		return $container->get(DT\ClassMetadataProviderInterface::class);
	}

	protected function getStrategyManager(ContainerInterface $container): PluginManagerInterface
	{
		return $container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER);
	}
}
