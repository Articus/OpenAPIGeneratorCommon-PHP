<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy;

use Articus\DataTransfer\Strategy\StrategyInterface;
use OpenAPIGenerator\Common\Validator;

class QueryStringScalar implements StrategyInterface
{
	/**
	 * @var string
	 */
	protected $type;

	public function __construct(array $options)
	{
		$type = $options['type'] ?? null;
		switch ($type)
		{
			case Validator\QueryStringScalar::TYPE_INT:
			case Validator\QueryStringScalar::TYPE_FLOAT:
			case Validator\QueryStringScalar::TYPE_BOOL:
			case Validator\QueryStringScalar::TYPE_STRING:
				$this->type = $type;
				break;
			default:
				throw new \InvalidArgumentException(\sprintf('Unknown type "%s".', $type));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		$result = null;
		if ($from !== null)
		{
			switch ($this->type)
			{
				case Validator\QueryStringScalar::TYPE_BOOL:
					$result = $from ? 'true' : 'false';
					break;
				default:
					$result = (string) $from;
					break;
			}
		}
		return $result;
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
				case Validator\QueryStringScalar::TYPE_INT:
					$to = (int) $from;
					break;
				case Validator\QueryStringScalar::TYPE_FLOAT:
					$to = (float) $from;
					break;
				case Validator\QueryStringScalar::TYPE_BOOL:
					$to = ($from === 'true') ? true : false;
					break;
				case Validator\QueryStringScalar::TYPE_STRING:
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
