<?php
declare(strict_types=1);

use Kahlan\Filter\Filters;

/** @var \Kahlan\Cli\Kahlan $this  */
/** @var \Kahlan\Cli\CommandLine $cli */

$cli = $this->commandLine();

//Switch to Mockery for stubbing and mocking
$cli->set('include', []);
Filters::apply($this, 'run', function ($next)
{
	Mockery::globalHelpers();
	return $next();
});

//Update Kahlan default CLI options
$cli->option('grep', 'default', '*.spec.php');
$cli->option('reporter', 'default', 'verbose');
$cli->option('coverage', 'default', 3);
$cli->option('clover', 'default', 'spec_output/kahlan.coverage.xml');

//Register custom global helper functions
if (!function_exists('propertyByPath'))
{
	function propertyByPath($object, array $path)
	{
		$pointer = $object;
		foreach ($path as $index => $propertyName)
		{
			if (!is_object($pointer))
			{
				throw new LogicException(sprintf('Can not get property %s (%s) from non object', $propertyName, $index));
			}
			$classReflection = new ReflectionClass($pointer);
			if (!$classReflection->hasProperty($propertyName))
			{
				throw new LogicException(sprintf('Class %s does not have property %s (%s)', $classReflection->getName(), $propertyName, $index));
			}
			$propertyReflection = $classReflection->getProperty($propertyName);
			$propertyReflection->setAccessible(true);
			$pointer = $propertyReflection->getValue($pointer);
		}
		return $pointer;
	}
}
