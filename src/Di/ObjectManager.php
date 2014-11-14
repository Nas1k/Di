<?php
/**
 * @license http://opensource.org/licenses/osl-3.0.php
 */

namespace Magento\Di\Di;

use Magento\Di\Config;
use Magento\Di\Di;
use Magento\Di\Factory;

class ObjectManager implements Di
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $sharedInstances;

    /**
     * @param Config $config
     * @param Factory $factory
     */
    public function __construct(
        Config $config,
        Factory $factory
    ) {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * Retrieve cached object instance
     *
     * @param string $requestedType
     * @return object
     */
    public function get($requestedType)
    {
        $requestedType = $this->config->getPreference($requestedType);
        if (!isset($this->sharedInstances[$requestedType])) {
            $this->sharedInstances[$requestedType] = $this->factory->create($requestedType);
        }
        return $this->sharedInstances[$requestedType];
    }

    /**
     * Create new object instance
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     */
    public function create($requestedType, $arguments = [])
    {
        return $this->factory->create($this->config->getPreference($requestedType), $arguments);
    }
}
