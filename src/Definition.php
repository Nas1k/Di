<?php
/**
 * @license http://opensource.org/licenses/osl-3.0.php
 */

namespace Magento\Di;

interface Definition
{
    /**
     * @param string $requestedType
     * @return array|null
     * @throws \Exception
     */
    public function getParameters($requestedType);
}
