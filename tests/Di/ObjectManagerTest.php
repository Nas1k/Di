<?php
/**
 * @license http://opensource.org/licenses/osl-3.0.php
 */

namespace Magento\Di\Di\ObjectManager;

use Magento\Di\Di\ObjectManager;

class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Di\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Di\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    public function setUp()
    {
        $this->config = $this->getMockBuilder('Magento\Di\Config')->getMock();
        $this->factory = $this->getMockBuilder('Magento\Di\Factory')->getMock();
    }

    public function testGetAndCreate()
    {
        $requestedType = 'StdClass';
        $this->config->expects($this->exactly(3))
            ->method('getPreference')
            ->with($requestedType)
            ->willReturn($requestedType);
        $this->factory->expects($this->exactly(2))
            ->method('create')
            ->with($requestedType)
            ->willReturn(new \StdClass());

        $objectManager = new ObjectManager($this->config, $this->factory);
        $this->assertInstanceOf($requestedType, $objectManager->get($requestedType));
        $this->assertInstanceOf($requestedType, $objectManager->get($requestedType));
        $this->assertInstanceOf($requestedType, $objectManager->create($requestedType));
    }
}
