<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use Articus\DataTransfer\Validator\ValidatorInterface;
use BackedEnum;
use InvalidArgumentException;
use function array_map;
use function implode;
use function is_int;
use function is_string;
use function is_subclass_of;
use function sprintf;

class Enumeration implements ValidatorInterface
{
	public const ERROR_ENUM = 'notEnum';

	/**
	 * @var class-string<BackedEnum>
	 */
	protected string $type;

	public function __construct(array $options)
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new InvalidArgumentException('Option "type" is required.');
		}
		elseif (!is_subclass_of($type, BackedEnum::class))
		{
			throw new InvalidArgumentException(sprintf('"%s" is not a backed enum.', $type));
		}
		$this->type = $type;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		if ($data !== null)
		{
			if ((!(is_int($data) || is_string($data))) || ($this->type::tryFrom($data) === null))
			{
				$result[self::ERROR_ENUM] = sprintf(
					'Allowed values: %s.',
					implode(', ', array_map(static fn (BackedEnum $value) => $value->value, $this->type::cases()))
				);
			}
		}
		return $result;
	}
}
