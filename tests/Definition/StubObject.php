<?php
/**
 * @license http://opensource.org/licenses/osl-3.0.php
 */

namespace Magento\Di\Definition;

class StubObject
{
    /**
     * @codeCoverageIgnore
     *
     * @param \StdClass $object
     * @param array $required
     * @param string $optional
     */
    public function __construct(
        \StdClass $object,
        array $required,
        $optional = 'test'
    ) {
    }
}
