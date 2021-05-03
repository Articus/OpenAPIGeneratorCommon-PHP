<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class NoArgObjectMap implements FactoryInterface
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
		$extractStdClass = $options['extract_std_class'] ?? false;
		$valueStrategy = $this->getStrategyManager($container)->get(...$this->getMetadataProvider($container)->getClassStrategy($type, $subset));
		$nullIdentifier = static function ($value): ?string
		{
			return null;
		};
		$typedValueSetter = static function &(\ArrayObject &$map, $key, $untypedValue) use ($type)
		{
			$defaultValue = new $type();
			$map[$key] = &$defaultValue;
			return $defaultValue;
		};
		$typedValueRemover = static function (\ArrayObject &$map, $key): void
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

	protected function getStrategyManager(ContainerInterface $container): DT\Strategy\PluginManager
	{
		return $container->get(DT\Strategy\PluginManager::class);
	}
}
