<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Articus\DataTransfer as DT;
use Psr\Container\ContainerInterface;

class QueryStringScalarList extends ScalarList
{
	protected function getValueStrategy(ContainerInterface $container, array $options): DT\Strategy\StrategyInterface
	{
		return $this->getStrategyManager($container)(PluginManager::P_QUERY_STRING_SCALAR, $options);
	}
}
