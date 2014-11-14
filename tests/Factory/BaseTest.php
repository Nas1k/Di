<?php
/**
 * @license http://opensource.org/licenses/osl-3.0.php
 */

namespace Magento\Di\Factory;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Di\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Di\Definition|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $definition;

    /**
     * @var \Magento\Di\Di|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $di;

    public function setUp()
    {
        $this->config = $this->getMockBuilder('Magento\Di\Config')->getMock();
        $this->definition = $this->getMockBuilder('Magento\Di\Definition')->getMock();
        $this->di = $this->getMockBuilder('Magento\Di\Di')->getMock();
    }

    public function testCreateWithoutParameters()
    {
        $requestedType = 'stdClass';
        $this->assertInstanceOf(
            'StdClass',
            (new Base($this->config, $this->di, $this->definition))->create($requestedType)
        );
    }

    /**
     * @dataProvider createCircularDependencyDataProvider
     * @expectedException \LogicException
     */
    public function testCreateCircularDependency($method, $isShared)
    {
        $firstRequestedType  = 'FirstObject';
        $secondRequestedType = 'SecondObject';
        $factory = new Base($this->config, $this->di, $this->definition);
        $firstParams = [['object', $secondRequestedType, true, null]];
        $secondParams = [['object', $firstRequestedType, true, null]];

        $this->definition->expects($this->at(0))
            ->method('getParameters')
            ->with($firstRequestedType)
            ->willReturn($firstParams);
        $this->definition->expects($this->at(1))
            ->method('getParameters')
            ->with($secondRequestedType)
            ->willReturn($secondParams);
        $this->definition->expects($this->at(2))
            ->method('getParameters')
            ->with($firstRequestedType)
            ->willReturn($firstParams);

        $this->config->expects($this->any())
            ->method('getArguments')
            ->willReturn([]);
        $this->config->expects($this->any())
            ->method('isShared')
            ->willReturn($isShared);

        $this->di->expects($this->any())
            ->method($method)
            ->willReturnCallback(function ($requestedType) use ($factory) {
                return $factory->create($requestedType);
            });

        $factory->create($firstRequestedType);
    }

    public function createCircularDependencyDataProvider()
    {
        return [
            'get' => ['get', true],
            'create' => ['create', false],
        ];
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testCreateMissingRequiredParameter()
    {
        $requestedType = 'object';
        $factory = new Base($this->config, $this->di, $this->definition);
        $params = [['object', null, true, null]];

        $this->definition->expects($this->once())
            ->method('getParameters')
            ->with($requestedType)
            ->willReturn($params);

        $this->config->expects($this->once())
            ->method('getArguments')
            ->willReturn([]);

        $factory->create($requestedType);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testCreateInvalidConfigurationParameter()
    {
        $requestedType = 'object';
        $factory = new Base($this->config, $this->di, $this->definition);
        $params = [['object', 'StrangeType', true, null]];

        $this->definition->expects($this->once())
            ->method('getParameters')
            ->with($requestedType)
            ->willReturn($params);

        $this->config->expects($this->once())
            ->method('getArguments')
            ->willReturn(['object' => []]);

        $factory->create($requestedType);
    }

    public function testCreateWithDefaultValue()
    {
        $requestedType = 'Magento\Di\Factory\EmptyStub';
        $factory = new Base($this->config, $this->di, $this->definition);
        $params = [['string', null, false, 'test']];

        $this->definition->expects($this->once())
            ->method('getParameters')
            ->with($requestedType)
            ->willReturn($params);

        $this->config->expects($this->once())
            ->method('getArguments')
            ->willReturn([]);

        $this->isInstanceOf($requestedType, $factory->create($requestedType));
    }

    public function testCreateWithConfiguration()
    {
        $requestedType = 'Magento\Di\Factory\EmptyStub';
        $factory = new Base($this->config, $this->di, $this->definition);
        $params = [['array', null, true, null]];

        $this->definition->expects($this->once())
            ->method('getParameters')
            ->with($requestedType)
            ->willReturn($params);

        $this->config->expects($this->once())
            ->method('getArguments')
            ->willReturn(
                [
                    'array' => [
                        'new' => [
                            'sub' => [
                                'instance' => 'Magento\Di\Factory\EmptyStub',
                            ],
                        ],
                    ],
                ]
            );

        $this->isInstanceOf($requestedType, $factory->create($requestedType));
    }

    public function testCreateWithGlobalArguments()
    {
        $requestedType = 'Magento\Di\Factory\EmptyStub';
        $factory = new Base(
            $this->config,
            null,
            $this->definition,
            ['db.host' => 'localhost']
        );
        $factory->setObjectManager($this->di);
        $params = [['array', null, true, null]];

        $this->definition->expects($this->once())
            ->method('getParameters')
            ->with($requestedType)
            ->willReturn($params);

        $this->config->expects($this->once())
            ->method('getArguments')
            ->willReturn(
                [
                    'array' => [
                        'argument' => 'db.host',
                    ],
                ]
            );

        $this->isInstanceOf($requestedType, $factory->create($requestedType));
    }

    public function testCreateWithGlobalArgumentsInArray()
    {
        $requestedType = 'Magento\Di\Factory\EmptyStub';
        $factory = new Base(
            $this->config,
            $this->di,
            $this->definition,
            ['db.host' => 'localhost']
        );
        $params = [['array', null, true, null]];

        $this->definition->expects($this->once())
            ->method('getParameters')
            ->with($requestedType)
            ->willReturn($params);

        $this->config->expects($this->once())
            ->method('getArguments')
            ->willReturn(
                [
                    'array' => [
                        'sub' => [
                            'argument' => 'db.host',
                        ],
                    ],
                ]
            );

        $this->isInstanceOf($requestedType, $factory->create($requestedType));
    }
}
