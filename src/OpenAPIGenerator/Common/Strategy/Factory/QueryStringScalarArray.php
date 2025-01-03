<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common\QueryStringScalarArrayAware;
use Psr\Container\ContainerInterface;

class QueryStringScalarArray extends QueryStringScalarArrayAware implements PM\PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): DT\Strategy\SerializableValue
	{
		$format = $options['format'] ?? null;
		$type = $options['type'] ?? null;
		[$valueSerializer, $valueUnserializer] = self::getScalarArrayCoder($format, $type);

		$valueStrategyName = $options['strategy']['name'] ?? DT\Strategy\Whatever::class;
		$valueStrategyOptions = $options['strategy']['options'] ?? [];
		$valueStrategy = $this->getStrategyManager($container)($valueStrategyName, $valueStrategyOptions);

		return new DT\Strategy\SerializableValue($valueStrategy, $valueSerializer, $valueUnserializer);
	}

	protected function getStrategyManager(ContainerInterface $container): PM\PluginManagerInterface
	{
		return $container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER);
	}
}
