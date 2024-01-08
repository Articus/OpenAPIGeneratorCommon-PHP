<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy;

use Articus\DataTransfer\Exception as DTException;
use Articus\DataTransfer\Strategy\StrategyInterface;
use BackedEnum;
use InvalidArgumentException;
use function get_class;
use function gettype;
use function is_object;
use function is_subclass_of;
use function sprintf;

class Enumeration implements StrategyInterface
{
	/**
	 * @var class-string<BackedEnum>
	 */
	protected string $type;

	public function __construct(array $options)
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new InvalidArgumentException('Option "type" is required');
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
	public function extract($from)
	{
		$result = null;
		if ($from !== null)
		{
			if (!($from instanceof $this->type))
			{
				throw new DTException\InvalidData(
					DTException\InvalidData::DEFAULT_VIOLATION,
					new InvalidArgumentException(sprintf(
						'Extraction can be done only from %s, not %s',
						$this->type, is_object($from) ? get_class($from) : gettype($from)
					))
				);
			}
			$result = $from->value;
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($from, &$to): void
	{
		$to = ($from === null) ? null : $this->type::from($from);
	}

	/**
	 * @inheritDoc
	 */
	public function merge($from, &$to): void
	{
		$to = $from;
	}
}
