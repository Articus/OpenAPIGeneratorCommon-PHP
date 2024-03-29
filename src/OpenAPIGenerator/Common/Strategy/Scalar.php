<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy;

use Articus\DataTransfer\Strategy\StrategyInterface;
use InvalidArgumentException;
use OpenAPIGenerator\Common\Validator;
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
			case Validator\Scalar::TYPE_INT:
			case Validator\Scalar::TYPE_FLOAT:
			case Validator\Scalar::TYPE_BOOL:
			case Validator\Scalar::TYPE_STRING:
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
				case Validator\Scalar::TYPE_INT:
					$to = (int) $from;
					break;
				case Validator\Scalar::TYPE_FLOAT:
					$to = (float) $from;
					break;
				case Validator\Scalar::TYPE_BOOL:
					$to = (bool) $from;
					break;
				case Validator\Scalar::TYPE_STRING:
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
