<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Test\Unit\Controller\Adminhtml\Rule;

use Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action\Context;
use Aheadworks\Autorelated\Controller\Adminhtml\Rule\ChangeStatus;
use Aheadworks\Autorelated\Model\Source\Type;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\View\LayoutInterface;
use Aheadworks\Autorelated\Model\RuleStatusManager;

/**
 * Test for \Aheadworks\Autorelated\Controller\Adminhtml\Rule\ChangeStatus
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChangeStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ChangeStatus
     */
    private $changeStatus;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewMock;

    /**
     * @var RuleStatusManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleStatusManagerMock;

    /**
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->viewMock = $this->getMockForAbstractClass(ViewInterface::class);

        $contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'view' => $this->viewMock
            ]
        );

        $this->ruleStatusManagerMock = $this->getMockBuilder(RuleStatusManager::class)
            ->setMethods(['isRuleStatusLockedByWvtavFunctionality', 'switchRuleStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->changeStatus = $objectManager->getObject(
            ChangeStatus::class,
            [
                'context' => $contextMock,
                'ruleStatusManager' => $this->ruleStatusManagerMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock
            ]
        );
    }

    /**
     * Testing of execute method when rule status isn't locked by WVTAV functionality,
     * redirect if status changed successfully without ajax
     */
    public function testExecuteChangeStatusWithoutAjax()
    {
        $ruleId = 1;
        $isAjax = false;
        $type = Type::PRODUCT_BLOCK_TYPE;
        $isRuleStatusLockedByWvtavFunctionality = false;

        $this->requestMock->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $ruleId],
                    ['type', null, $type],
                    ['isAjax', null, $isAjax]
                ]
            );

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->ruleStatusManagerMock->expects($this->once())
            ->method('isRuleStatusLockedByWvtavFunctionality')
            ->with($ruleId)
            ->willReturn($isRuleStatusLockedByWvtavFunctionality);
        $this->ruleStatusManagerMock->expects($this->once())
            ->method('switchRuleStatus')
            ->with($ruleId);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('Rule status was successfully changed'));

        $this->assertSame($resultRedirectMock, $this->changeStatus->execute());
    }

    /**
     * Testing of execute method when rule status is locked by WVTAV functionality,
     * redirect if status changed successfully without ajax
     */
    public function testExecuteStatusIsLockedWithoutAjax()
    {
        $ruleId = 1;
        $isAjax = false;
        $type = Type::PRODUCT_BLOCK_TYPE;
        $isRuleStatusLockedByWvtavFunctionality = true;

        $this->requestMock->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $ruleId],
                    ['type', null, $type],
                    ['isAjax', null, $isAjax]
                ]
            );

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->ruleStatusManagerMock->expects($this->once())
            ->method('isRuleStatusLockedByWvtavFunctionality')
            ->with($ruleId)
            ->willReturn($isRuleStatusLockedByWvtavFunctionality);
        $this->ruleStatusManagerMock->expects($this->never())
            ->method('switchRuleStatus')
            ->with($ruleId);

        $this->messageManagerMock->expects($this->once())
            ->method('addNoticeMessage')
            ->with(__('You need to enable "Who Viewed This Also Viewed" functionality in the settings first'));

        $this->assertSame($resultRedirectMock, $this->changeStatus->execute());
    }

    /**
     * Testing of execute method when rule status isn't locked by WVTAV functionality,
     * status changed and return listing html code on ajax
     */
    public function testExecuteChangeStatusOnAjax()
    {
        $ruleId = 1;
        $isAjax = true;
        $type = Type::PRODUCT_BLOCK_TYPE;
        $isRuleStatusLockedByWvtavFunctionality = false;
        $listingHtml = 'html listing content';

        $this->requestMock->expects($this->exactly(4))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $ruleId],
                    ['type', null, $type],
                    ['isAjax', null, $isAjax]
                ]
            );

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->ruleStatusManagerMock->expects($this->once())
            ->method('isRuleStatusLockedByWvtavFunctionality')
            ->with($ruleId)
            ->willReturn($isRuleStatusLockedByWvtavFunctionality);
        $this->ruleStatusManagerMock->expects($this->once())
            ->method('switchRuleStatus')
            ->with($ruleId);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('Rule status was successfully changed'));

        $listingMock = $this->getMockBuilder(Listing::class)
            ->setMethods(['render'])
            ->disableOriginalConstructor()
            ->getMock();
        $listingMock->expects($this->once())
            ->method('render')
            ->with($type)
            ->willReturn($listingHtml);
        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('container_listing_renderer')
            ->willReturn($listingMock);
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $jsonMock = $this->getMockBuilder(Json::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $jsonMock->expects($this->once())
            ->method('setData')
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);

        $this->assertSame($jsonMock, $this->changeStatus->execute());
    }

    /**
     * Testing of execute method when rule status is locked by WVTAV functionality,
     * status changed and return listing html code on ajax
     */
    public function testExecuteStatusIsLockedOnAjax()
    {
        $ruleId = 1;
        $isAjax = true;
        $type = Type::PRODUCT_BLOCK_TYPE;
        $isRuleStatusLockedByWvtavFunctionality = true;
        $listingHtml = 'html listing content';

        $this->requestMock->expects($this->exactly(4))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $ruleId],
                    ['type', null, $type],
                    ['isAjax', null, $isAjax]
                ]
            );

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->ruleStatusManagerMock->expects($this->once())
            ->method('isRuleStatusLockedByWvtavFunctionality')
            ->with($ruleId)
            ->willReturn($isRuleStatusLockedByWvtavFunctionality);
        $this->ruleStatusManagerMock->expects($this->never())
            ->method('switchRuleStatus')
            ->with($ruleId);

        $this->messageManagerMock->expects($this->once())
            ->method('addNoticeMessage')
            ->with(__('You need to enable "Who Viewed This Also Viewed" functionality in the settings first'));

        $listingMock = $this->getMockBuilder(Listing::class)
            ->setMethods(['render'])
            ->disableOriginalConstructor()
            ->getMock();
        $listingMock->expects($this->once())
            ->method('render')
            ->with($type)
            ->willReturn($listingHtml);
        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('container_listing_renderer')
            ->willReturn($listingMock);
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $jsonMock = $this->getMockBuilder(Json::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $jsonMock->expects($this->once())
            ->method('setData')
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);

        $this->assertSame($jsonMock, $this->changeStatus->execute());
    }
}
