<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Test\Unit\Controller\Adminhtml\Rule\Widget;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Aheadworks\Autorelated\Block\Adminhtml\Widget\Rule\Chooser as RuleWidgetChooser;
use Aheadworks\Autorelated\Controller\Adminhtml\Rule\Widget\Chooser;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\Controller\Result\Raw;

/**
 * Test for \Aheadworks\Autorelated\Controller\Adminhtml\Rule\Widget\Chooser
 */
class ChooserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Chooser
     */
    private $controller;

    /**
     * @var LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutFactoryMock;

    /**
     * @var RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRawFactoryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->layoutFactoryMock = $this->getMockBuilder(LayoutFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRawFactoryMock = $this->getMockBuilder(RawFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $contextMock = $objectManager->getObject(
            Context::class,
            ['request' => $this->requestMock]
        );

        $this->controller = $objectManager->getObject(
            Chooser::class,
            [
                'context' => $contextMock,
                'layoutFactory' => $this->layoutFactoryMock,
                'resultRawFactory' => $this->resultRawFactoryMock
            ]
        );
    }

    /**
     * Testing of execute method
     */
    public function testExecute()
    {
        $blockHtml = 'html content';
        $uniqId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('uniq_id')
            ->willReturn($uniqId);
        $ruleWidgetChooserMock = $this->getMockBuilder(RuleWidgetChooser::class)
            ->setMethods(['toHtml'])
            ->disableOriginalConstructor()
            ->getMock();
        $ruleWidgetChooserMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($blockHtml);
        $layoutMock = $this->getMockBuilder(Layout::class)
            ->setMethods(['createBlock'])
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(RuleWidgetChooser::class, '', ['data' => ['id' => $uniqId]])
            ->willReturn($ruleWidgetChooserMock);
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($layoutMock);

        $resultRawMock = $this->getMockBuilder(Raw::class)
            ->setMethods(['setContents'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRawMock->expects($this->any())
            ->method('setContents')
            ->with($blockHtml)
            ->willReturnSelf();
        $this->resultRawFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRawMock);

        $this->assertSame($resultRawMock, $this->controller->execute());
    }
}
