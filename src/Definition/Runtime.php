<?php
/**
 * @license http://opensource.org/licenses/osl-3.0.php
 */

namespace Magento\Di\Definition;

use Magento\Di\Definition;

class Runtime implements Definition
{
    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @param string $requestedType
     * @return array|null
     * @throws \Exception
     */
    public function getParameters($requestedType)
    {
        if (!array_key_exists($requestedType, $this->parameters)) {
            $this->parameters[$requestedType] = $this->parseParameters($requestedType);
        }
        return $this->parameters[$requestedType];
    }

    /**
     * @param string $requesterType
     * @return array|null
     * @throws \Exception
     */
    protected function parseParameters($requesterType)
    {
        try {
            $classReflection = new \ReflectionClass($requesterType);
            if (!$classReflection->hasMethod('__construct')) {
                return null;
            }
            $constructReflection = $classReflection->getMethod('__construct');
            $parameters = [];
            foreach ($constructReflection->getParameters() as $parameter) {
                $parameters[] = [
                    $parameter->getName(),
                    !$parameter->isOptional(),
                    $parameter->getClass() ? $parameter->getClass()->getName() : null,
                    $parameter->isOptional() ? $parameter->getDefaultValue() : null
                ];
            }
            return $parameters;
        } catch (\Exception $e) {
            throw new \UnexpectedValueException(
                $requesterType . ' class not loaded. Include path: ' . get_include_path()
            );
        }
    }
}
