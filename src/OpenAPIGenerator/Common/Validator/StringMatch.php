<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use Articus\DataTransfer\Validator\ValidatorInterface;
use InvalidArgumentException;
use function is_string;
use function preg_match;
use function sprintf;

class StringMatch implements ValidatorInterface
{
	public const ERROR_STRING = 'notString';
	public const ERROR_PATTERN = 'notPattern';

	protected string $pattern;

	public function __construct(array $options)
	{
		$pattern = $options['pattern'] ?? null;
		if ($pattern === null)
		{
			throw new InvalidArgumentException('Option "pattern" is required.');
		}
		$this->pattern = $pattern;
	}

	public function validate($data): array
	{
		$result = [];
		if ($data !== null)
		{
			if (!is_string($data))
			{
				$result[self::ERROR_STRING] = 'Invalid type - expecting string.';
			}
			else
			{
				if (preg_match($this->pattern, $data) !== 1)
				{
					$result[self::ERROR_PATTERN] = sprintf('String violates pattern %s.', $this->pattern);
				}
			}
		}
		return $result;
	}
}
