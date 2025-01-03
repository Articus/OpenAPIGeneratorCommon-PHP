<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator\Factory;

use Articus\DataTransfer as DT;
use Articus\PluginManager as PM;
use OpenAPIGenerator\Common\QueryStringScalarArrayAware;
use Psr\Container\ContainerInterface;

class QueryStringScalarArray extends QueryStringScalarArrayAware implements PM\PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): DT\Validator\SerializableValue
	{
		$type = $options['type'] ?? null;
		$format = $options['format'] ?? null;
		[, $unserializer] = self::getScalarArrayCoder($format, $type);

		$valueValidatorLinks = $options['validators'] ?? [];
		$valueValidator = $this->getValidatorManager($container)(DT\Validator\Chain::class, ['links' => $valueValidatorLinks]);

		return new DT\Validator\SerializableValue($valueValidator, $unserializer);
	}

	protected function getValidatorManager(ContainerInterface $container): PM\PluginManagerInterface
	{
		return $container->get(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER);
	}
}
