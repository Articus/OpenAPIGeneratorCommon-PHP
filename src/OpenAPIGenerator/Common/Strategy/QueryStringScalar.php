<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Strategy;

use OpenAPIGenerator\Common\Validator;

class QueryStringScalar extends Scalar
{
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
				case Validator\Scalar::TYPE_BOOL:
					$result = $from ? 'true' : 'false';
					break;
				default:
					$result = (string)$from;
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
				case Validator\Scalar::TYPE_INT:
					$to = (int)$from;
					break;
				case Validator\Scalar::TYPE_FLOAT:
					$to = (float)$from;
					break;
				case Validator\Scalar::TYPE_BOOL:
					$to = ($from === 'true') ? true : false;
					break;
				case Validator\Scalar::TYPE_STRING:
					$to = (string)$from;
					break;
			}
		}
		else
		{
			$to = null;
		}
	}
}
