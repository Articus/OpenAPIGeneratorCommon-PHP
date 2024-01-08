<?php
declare(strict_types=1);

namespace OpenAPIGenerator\Common\Validator;

use Articus\DataTransfer\Validator\ValidatorInterface;
use DateTimeInterface;
use function is_string;

class DateTimeString implements ValidatorInterface
{
	public const ERROR_STRING = 'notString';
	public const ERROR_DATE_TIME_FORMAT = 'notDateTimeFormat';

	/**
	 * @var callable(string): DateTimeInterface|null
	 */
	protected $parser;

	public function __construct(callable $parser)
	{
		$this->parser = $parser;
	}

	/**
	 * @inheritDoc
	 */
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
				$dateObj = ($this->parser)($data);
				if (!($dateObj instanceof DateTimeInterface))
				{
					$result[self::ERROR_DATE_TIME_FORMAT] = 'Invalid format.';
				}
				//TODO add strict check?
			}
		}
		return $result;
	}
}
