<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use Articus\DataTransfer\Validator\ValidatorInterface;
use InvalidArgumentException;
use function implode;
use function in_array;
use function sprintf;

class AnonymousEnumeration implements ValidatorInterface
{
	public const ERROR_ENUM = 'notEnum';

	/**
	 * @var array<int|float|string>
	 */
	protected array $values;

	public function __construct(array $options)
	{
		$values = $options['values'] ?? null;
		if ($values === null)
		{
			throw new InvalidArgumentException('Option "values" is required.');
		}
		$this->values = $values;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		if ($data !== null)
		{
			if (!in_array($data, $this->values, true))
			{
				$result[self::ERROR_ENUM] = sprintf('Allowed values: %s.', implode(', ', $this->values));
			}
		}
		return $result;
	}
}
