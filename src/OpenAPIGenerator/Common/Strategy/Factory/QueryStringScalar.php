<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common\QueryStringScalarAware;
use Psr\Container\ContainerInterface;

class QueryStringScalar extends QueryStringScalarAware implements PM\PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): DT\Strategy\SerializableValue
	{
		$type = $options['type'] ?? null;
		[$serializer, $unserializer] = self::getScalarCoder($type);

		$valueStrategyName = $options['strategy']['name'] ?? DT\Strategy\Whatever::class;
		$valueStrategyOptions = $options['strategy']['options'] ?? [];
		$valueStrategy = $this->getStrategyManager($container)($valueStrategyName, $valueStrategyOptions);

		return new DT\Strategy\SerializableValue($valueStrategy, $serializer, $unserializer);
	}

	protected function getStrategyManager(ContainerInterface $container): PM\PluginManagerInterface
	{
		return $container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER);
	}
}
