<?php
/**
 * @license http://opensource.org/licenses/osl-3.0.php
 */

namespace Magento\Di\Definition;

class RuntimeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetParameters()
    {
        $expected = [
            ['object', true, 'stdClass', null],
            ['required', true, null, null],
            ['optional', false, null, 'test'],
        ];

        $this->assertEquals($expected, (new Runtime())->getParameters('Magento\Di\Definition\StubObject'));
    }

    public function testGetParametersWhenParametersEmpty()
    {
        $this->assertNull((new Runtime())->getParameters('Magento\Di\Definition\StubEmpty'));
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testGetParametersWhenClassNotLoaded()
    {
        (new Runtime())->getParameters('Magento\Di\Definition\UndefinedClass');
    }
}
