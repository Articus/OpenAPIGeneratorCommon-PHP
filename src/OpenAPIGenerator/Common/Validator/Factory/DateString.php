<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator\Factory;

use Articus\PluginManager\PluginFactoryInterface;
use DateTime;
use DateTimeInterface;
use OpenAPIGenerator\Common\Validator;
use Psr\Container\ContainerInterface;

class DateString implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Validator\DateTimeString
	{
		$parser = static function (string $dateTimeStr): ?DateTimeInterface
		{
			return DateTime::createFromFormat(DateTime::RFC3339, $dateTimeStr . 'T00:00:00+00:00') ?: null;
		};
		return new Validator\DateTimeString($parser);
	}
}
