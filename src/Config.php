<?php
/**
 * @license http://opensource.org/licenses/osl-3.0.php
 */

namespace Magento\Di;

interface Config
{
    /**
     * @param string $requestedTypes
     * @return array
     */
    public function getArguments($requestedTypes);

    /**
     * @param string $requestedType
     * @return string
     */
    public function getPreference($requestedType);

    /**
     * @param $requestedType
     * @return bool
     */
    public function isShared($requestedType);
}
