<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Articus\PluginManager\PluginFactoryInterface;
use DateTime;
use DateTimeInterface;
use OpenAPIGenerator\Common\Strategy;
use Psr\Container\ContainerInterface;
use function strpos;

class MutableDateTime implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Strategy\DateTime
	{
		$formatter = static function (DateTimeInterface $dateTimeObj): string
		{
			//TODO support microseconds?
			return $dateTimeObj->format(DateTimeInterface::RFC3339);
		};
		$parser = static function (string $dateTimeStr): ?DateTimeInterface
		{
			$format = (strpos($dateTimeStr, '.') === false) ? DateTimeInterface::RFC3339 : 'Y-m-d\TH:i:s.uP';
			return DateTime::createFromFormat($format, $dateTimeStr) ?: null;
		};
		return new Strategy\DateTime($formatter, $parser);
	}
}
