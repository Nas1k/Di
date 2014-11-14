<?php
/**
 * @license http://opensource.org/licenses/osl-3.0.php
 */

namespace Magento\Di\Factory;

use Magento\Di\Config;
use Magento\Di\Definition;
use Magento\Di\Definition\Runtime;
use Magento\Di\Di;
use Magento\Di\Factory;

class Base implements Factory
{
    /**
     * @var Definition
     */
    protected $definitions;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Di
     */
    protected $di;

    /**
     * @var array
     */
    protected $globalArguments;

    /**
     * @var array
     */
    protected $creationStack;

    /**
     * @param Config $config
     * @param Di $di
     * @param Definition $definitions
     * @param array $globalArguments
     */
    public function __construct(
        Config $config,
        Di $di = null,
        Definition $definitions = null,
        $globalArguments = []
    ) {
        $this->config = $config;
        $this->di = $di;
        $this->definitions = $definitions ?: new Runtime();
        $this->globalArguments = $globalArguments;
    }

    /**
     * Set object manager
     *
     * @param Di $di
     * @return void
     */
    public function setObjectManager(Di $di)
    {
        $this->di = $di;
    }

    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function create($requestedType, $arguments = [])
    {
        $parameters = $this->definitions->getParameters($requestedType);
        if (empty($parameters)) {
            return new $requestedType();
        }

        if (isset($this->creationStack[$requestedType])) {
            $lastFound = end($this->creationStack);
            $this->creationStack = array();
            throw new \LogicException("Circular dependency: {$requestedType} depends on {$lastFound} and vice versa.");
        }
        $this->creationStack[$requestedType] = $requestedType;
        try {
            $args = $this->resolveArguments($requestedType, $parameters, $arguments);
            unset($this->creationStack[$requestedType]);
        } catch (\Exception $e) {
            unset($this->creationStack[$requestedType]);
            throw $e;
        }

        return new $requestedType(...$args);
    }

    /**
     * Resolve constructor arguments
     *
     * @param string $requestedType
     * @param array $parameters
     * @param array $arguments
     * @return array
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function resolveArguments($requestedType, $parameters, $arguments)
    {
        $requestedArguments = [];
        $arguments = count($arguments)
            ? array_replace($this->config->getArguments($requestedType), $arguments)
            : $this->config->getArguments($requestedType);

        foreach ($parameters as $parameter) {
            list($paramName, $paramType, $paramRequired, $paramDefault) = $parameter;
            $argument = null;
            if (array_key_exists($paramName, $arguments)) {
                $argument = $arguments[$paramName];
            } elseif ($paramRequired) {
                if ($paramType) {
                    $argument = ['instance' => $paramType];
                } else {
                    $this->creationStack = array();
                    throw new \BadMethodCallException(
                        'Missing required argument $' . $paramName . ' of ' . $requestedType . '.'
                    );
                }
            } else {
                $argument = $paramDefault;
            }
            if ($paramType && $argument !== $paramDefault && !is_object($argument)) {
                if (!isset($argument['instance']) || !is_array($argument)) {
                    throw new \UnexpectedValueException(
                        'Invalid parameter configuration provided for $' . $paramName . ' argument of ' . $requestedType
                    );
                }
                $argumentType = $argument['instance'];
                $isShared = (isset($argument['shared']) ? $argument['shared'] : $this->config->isShared($argumentType));
                $argument = $isShared
                    ? $this->di->get($argumentType)
                    : $this->di->create($argumentType);
            } elseif (is_array($argument)) {
                if (isset($argument['argument'])) {
                    $argument = isset($this->globalArguments[$argument['argument']])
                        ? $this->globalArguments[$argument['argument']]
                        : $paramDefault;
                } elseif (!empty($argument)) {
                    $this->parseArray($argument);
                }
            }
            $resolvedArguments[] = $argument;
        }

        return $requestedArguments;
    }

    /**
     * Parse array argument
     *
     * @param array $array
     * @return void
     */
    protected function parseArray(&$array)
    {
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                if (isset($item['instance'])) {
                    $itemType = $item['instance'];
                    $isShared = (isset($item['shared'])) ? $item['shared'] : $this->config->isShared($itemType);
                    $array[$key] = $isShared
                        ? $this->di->get($itemType)
                        : $this->di->create($itemType);
                } elseif (isset($item['argument'])) {
                    $array[$key] = isset($this->globalArguments[$item['argument']])
                        ? $this->globalArguments[$item['argument']]
                        : null;
                } else {
                    $this->parseArray($array[$key]);
                }
            }
        }
    }
}
