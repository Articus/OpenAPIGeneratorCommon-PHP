<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use OpenAPIGenerator\Common\Strategy;

class MutableDateTime implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$formatter = static function (\DateTimeInterface $dateTimeObj): string
		{
			return $dateTimeObj->format(\DateTime::RFC3339);
		};
		$parser = static function (string $dateTimeStr): \DateTimeInterface
		{
			return \DateTime::createFromFormat(\DateTime::RFC3339, $dateTimeStr);
		};
		return new Strategy\DateTime($formatter, $parser);
	}
}
