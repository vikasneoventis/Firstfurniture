<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Test\Unit\Block\Adminhtml\Rule;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing;
use Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\ColumnFactory;
use Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column;
use Magento\Framework\ObjectManagerInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form;

/**
 * Test for \Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing
 */
class ListingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Listing
     */
    private $listing;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var ColumnFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $columnFactoryMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->columnFactoryMock = $this->getMockBuilder(ColumnFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->setMethods(['isAjax'])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock
            ]
        );
        $this->listing = $objectManager->getObject(
            Listing::class,
            [
                'context' => $contextMock,
                'objectManager' => $this->objectManagerMock,
                'columnFactory' => $this->columnFactoryMock
            ]
        );
    }

    /**
     * Testing of getListingColumns method
     */
    public function testGetListingColumns()
    {
        $columns = [
            'column1' => ['header' => 'column1'],
            'column2' => ['header' => 'column2'],
        ];
        $this->listing->setColumns($columns);

        $columnMock = $this->getMockBuilder(Column::class)
            ->setMethods(['setData', 'setColumnName', 'setPrimaryFieldName', 'setTypeFieldName'])
            ->disableOriginalConstructor()
            ->getMock();
        $columnMock->expects($this->exactly(2))
            ->method('setData')
            ->willReturnSelf();
        $columnMock->expects($this->exactly(2))
            ->method('setColumnName')
            ->willReturnSelf();
        $columnMock->expects($this->exactly(2))
            ->method('setPrimaryFieldName')
            ->willReturnSelf();
        $columnMock->expects($this->exactly(2))
            ->method('setTypeFieldName')
            ->willReturnSelf();

        $this->columnFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($columnMock);

        $this->assertTrue(is_array($this->listing->getListingColumns()));
    }

    /**
     * Testing of getListingColumns method, if exception occur
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Incorrect the column data format
     */
    public function testGetListingColumnsException()
    {
        $columns = [
            'column1' => 'header'
        ];
        $this->listing->setColumns($columns);
        $this->listing->getListingColumns();
    }

    /**
     * Testing of isAjax method
     *
     * @param bool $isAjax
     * @param bool $expected
     * @dataProvider isAjaxDataProvider
     */
    public function testIsAjax($isAjax, $expected)
    {
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn($isAjax);

        $this->assertEquals($expected, $this->listing->isAjax());
    }

    /**
     * @return array
     */
    public function isAjaxDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    /**
     * Testing of toHtml method, method getCollectionClassName() does not return a collection class
     */
    public function testToHtml()
    {
        $expected = '';
        $collectionClassName = Form::class;
        $collectionClassMock = $this->getMockBuilder($collectionClassName)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->listing->setCollectionClassName($collectionClassName);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($collectionClassName)
            ->willReturn($collectionClassMock);

        $this->assertEquals($expected, $this->listing->toHtml());
    }
}
