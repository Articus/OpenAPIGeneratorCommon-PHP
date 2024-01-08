<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator\Factory;

use Articus\PluginManager\PluginFactoryInterface;
use DateTime;
use DateTimeInterface;
use OpenAPIGenerator\Common\Validator;
use Psr\Container\ContainerInterface;
use function strpos;

class DateTimeString implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Validator\DateTimeString
	{
		$parser = static function (string $dateTimeStr): ?DateTimeInterface
		{
			$format = (strpos($dateTimeStr, '.') === false) ? DateTimeInterface::RFC3339 : 'Y-m-d\TH:i:s.uP';
			return DateTime::createFromFormat($format, $dateTimeStr) ?: null;
		};
		return new Validator\DateTimeString($parser);
	}
}
