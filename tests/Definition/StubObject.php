<?php
/**
 * @license http://opensource.org/licenses/osl-3.0.php
 */

namespace Magento\Di\Definition;

class StubObject
{
    public function __construct(
        \StdClass $object,
        array $required,
        $optional = 'test'
    ) {
    }
}
