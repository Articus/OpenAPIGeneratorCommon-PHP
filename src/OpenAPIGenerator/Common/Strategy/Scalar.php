<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy;

use Articus\DataTransfer\Strategy\StrategyInterface;
use OpenAPIGenerator\Common\Validator;

class Scalar implements StrategyInterface
{
	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @param string $type
	 */
	public function __construct(string $type)
	{
		switch ($type)
		{
			case Validator\Scalar::TYPE_INT:
			case Validator\Scalar::TYPE_FLOAT:
			case Validator\Scalar::TYPE_BOOL:
			case Validator\Scalar::TYPE_STRING:
				$this->type = $type;
				break;
			default:
				throw new \InvalidArgumentException(\sprintf('Unknown type "%s".', $type));
		}
	}

	public function extract($from)
	{
		return $from;
	}

	public function hydrate($from, &$to): void
	{
		$this->merge($from, $to);
	}

	public function merge($from, &$to): void
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
}
