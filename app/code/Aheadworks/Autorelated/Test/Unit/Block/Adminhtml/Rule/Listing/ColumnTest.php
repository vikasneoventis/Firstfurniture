<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Test\Unit\Block\Adminhtml\Rule\Listing;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column;
use Magento\Framework\View\LayoutInterface;
use Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer;

/**
 * Test for \Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column
 */
class ColumnTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Column
     */
    private $column;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->column = $objectManager->getObject(
            Column::class,
            ['layout' => $this->layoutMock]
        );
    }

    /**
     * Testing of getColumnRenderer method
     *
     * @param string $class
     * @param string $type
     * @dataProvider getColumnRendererDataProvider
     */
    public function testGetColumnRenderer($class, $type)
    {
        $this->column->setRenderer($class);
        $this->column->setType($type);

        $class = $class ? : Renderer\Text::class;
        $rendererMock = $this->getMockBuilder($class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->willReturn($rendererMock);

        $this->assertEquals($rendererMock, $this->column->getColumnRenderer());
    }

    /**
     * @return array
     */
    public function getColumnRendererDataProvider()
    {
        return [
            [Renderer\Text::class, ''],
            [Renderer\Actions::class, ''],
            ['', 'text']
        ];
    }

    /**
     * Testing of getClassType method
     *
     * @param string $type
     * @param string $expected
     * @dataProvider getClassTypeDataProvider
     */
    public function testGetClassType($type, $expected)
    {
        $class = new \ReflectionClass($this->column);
        $method = $class->getMethod('getClassType');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->column, $type));
    }

    /**
     * @return array
     */
    public function getClassTypeDataProvider()
    {
        return [
            ['text', Renderer\Text::class],
            ['actions', Renderer\Actions::class],
            ['test', Renderer\Text::class],
        ];
    }

    /**
     * Testing of getCssClass method
     *
     * @param string $columnName
     * @dataProvider getCssClassDataProvider
     */
    public function testGetCssClass($columnName)
    {
        $expected = 'col-' . $columnName;
        $this->column->setColumnName($columnName);
        $this->assertEquals($expected, $this->column->getCssClass());
    }

    /**
     * @return array
     */
    public function getCssClassDataProvider()
    {
        return [
            ['column_1'],
            ['column_2']
        ];
    }
}
