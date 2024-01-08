<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Articus\DataTransfer as DT;
use Psr\Container\ContainerInterface;

class DateTimeList extends DateList
{
	protected function getValueStrategy(ContainerInterface $container): DT\Strategy\StrategyInterface
	{
		return $this->getStrategyManager($container)(PluginManager::P_DATE_TIME, []);
	}
}
