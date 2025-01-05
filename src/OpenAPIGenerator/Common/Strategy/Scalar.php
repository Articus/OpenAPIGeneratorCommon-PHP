<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy;

use Articus\DataTransfer\Strategy\StrategyInterface;
use InvalidArgumentException;
use OpenAPIGenerator\Common\ScalarType;
use function sprintf;

class Scalar implements StrategyInterface
{
	protected string $type;

	public function __construct(array $options)
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new InvalidArgumentException('Option "type" is required.');
		}
		switch ($type)
		{
			case ScalarType::BOOL:
			case ScalarType::INT:
			case ScalarType::FLOAT:
			case ScalarType::STRING:
				$this->type = $type;
				break;
			default:
				throw new InvalidArgumentException(sprintf('Unknown type "%s".', $type));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		return $from;
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($from, &$to): void
	{
		if ($from !== null)
		{
			switch ($this->type)
			{
				case ScalarType::BOOL:
					$to = (bool) $from;
					break;
				case ScalarType::INT:
					$to = (int) $from;
					break;
				case ScalarType::FLOAT:
					$to = (float) $from;
					break;
				case ScalarType::STRING:
					$to = (string) $from;
					break;
			}
		}
		else
		{
			$to = null;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function merge($from, &$to): void
	{
		$to = $from;
	}
}
