<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy\Factory;

use Articus\PluginManager\PluginFactoryInterface;
use DateTimeImmutable;
use DateTimeInterface;
use OpenAPIGenerator\Common\Strategy;
use Psr\Container\ContainerInterface;

class ImmutableDate implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Strategy\DateTime
	{
		$formatter = static function (DateTimeInterface $dateTimeObj): string
		{
			return $dateTimeObj->format('Y-m-d');
		};
		$parser = static function (string $dateTimeStr): ?DateTimeInterface
		{
			return DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339, $dateTimeStr . 'T00:00:00+00:00') ?: null;
		};
		return new Strategy\DateTime($formatter, $parser);
	}
}
