<?php
/**
 * @license http://opensource.org/licenses/osl-3.0.php
 */

namespace Magento\Di;

interface Di
{
    /**
     * Retrieve cached object instance
     *
     * @param string $requestedType
     * @return object
     */
    public function get($requestedType);

    /**
     * Create new object instance
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     */
    public function create($requestedType, $arguments = []);
}
