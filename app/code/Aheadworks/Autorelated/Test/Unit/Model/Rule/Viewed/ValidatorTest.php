<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Test\Unit\Model\Rule\Viewed;

use Aheadworks\Autorelated\Model\Rule\Viewed\Validator;

use Aheadworks\Autorelated\Model\Rule\CurrentPageObject;
use Aheadworks\Autorelated\Model\Source\Type as SourceType;
use Aheadworks\Autorelated\Model\Rule;
use Aheadworks\Autorelated\Model\Rule\Viewed\Product as ViewedProduct;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Autorelated\Model\Rule\Viewed\Validator
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var CurrentPageObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currentPageObjectMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->currentPageObjectMock = $this->getMockBuilder(CurrentPageObject::class)
            ->setMethods(['getCurrentProductIdForBlock', 'getCurrentCategoryIdForBlock'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = $objectManager->getObject(
            Validator::class,
            [
                'currentPageObject' => $this->currentPageObjectMock
            ]
        );
    }

    /**
     * Testing of canShowOnCategoryPage method on return true
     */
    public function testCanShowOnCategoryPageTrue()
    {
        $currentCategoryId = 1;
        $type = SourceType::CATEGORY_BLOCK_TYPE;
        $ruleMock = $this->getMockBuilder(Rule::class)
            ->setMethods(['getCategoryIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $ruleMock->expects($this->once())
            ->method('getCategoryIds')
            ->willReturn('');

        $this->currentPageObjectMock->expects($this->once())
            ->method('getCurrentCategoryIdForBlock')
            ->with($ruleMock, $type)
            ->willReturn($currentCategoryId);

        $class = new \ReflectionClass($this->validator);
        $method = $class->getMethod('canShowOnCategoryPage');
        $method->setAccessible(true);

        $this->assertTrue($method->invokeArgs($this->validator, [$ruleMock, $type]));
    }

    /**
     * Testing of canShowOnCategoryPage method on return false
     */
    public function testCanShowOnCategoryPageFalse()
    {
        $currentCategoryId = 1;
        $type = SourceType::CATEGORY_BLOCK_TYPE;
        $ruleMock = $this->getMockBuilder(Rule::class)
            ->setMethods(['getCategoryIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $ruleMock->expects($this->exactly(2))
            ->method('getCategoryIds')
            ->willReturn('2,3,4');

        $this->currentPageObjectMock->expects($this->once())
            ->method('getCurrentCategoryIdForBlock')
            ->with($ruleMock, $type)
            ->willReturn($currentCategoryId);

        $class = new \ReflectionClass($this->validator);
        $method = $class->getMethod('canShowOnCategoryPage');
        $method->setAccessible(true);

        $this->assertFalse($method->invokeArgs($this->validator, [$ruleMock, $type]));
    }

    /**
     * Testing of canShowOnProductAndCartPage method on return true
     */
    public function testCanShowOnProductAndCartPageTrue()
    {
        $currentProductId = 1;
        $type = SourceType::PRODUCT_BLOCK_TYPE;
        $viewedMatchingProductIds = [4, 2, $currentProductId];

        $this->currentPageObjectMock->expects($this->once())
            ->method('getCurrentProductIdForBlock')
            ->willReturn($currentProductId);

        $viewedProductMock = $this->getMockBuilder(ViewedProduct::class)
            ->setMethods(['getMatchingProductIds', 'getConditions'])
            ->disableOriginalConstructor()
            ->getMock();
        $viewedProductMock->expects($this->once())
            ->method('getConditions')
            ->willReturn([]);
        $viewedProductMock->expects($this->once())
            ->method('getMatchingProductIds')
            ->willReturn($viewedMatchingProductIds);

        $ruleMock = $this->getMockBuilder(Rule::class)
            ->setMethods(['getViewedProductRule'])
            ->disableOriginalConstructor()
            ->getMock();
        $ruleMock->expects($this->exactly(2))
            ->method('getViewedProductRule')
            ->willReturn($viewedProductMock);

        $class = new \ReflectionClass($this->validator);
        $method = $class->getMethod('canShowOnProductAndCartPage');
        $method->setAccessible(true);

        $this->assertTrue($method->invokeArgs($this->validator, [$ruleMock, $type]));
    }

    /**
     * Testing of canShowOnProductAndCartPage method on return false
     */
    public function testCanShowOnProductAndCartPageFalse()
    {
        $currentProductId = 1;
        $type = SourceType::PRODUCT_BLOCK_TYPE;
        $viewedMatchingProductIds = [4, 2, 5];

        $this->currentPageObjectMock->expects($this->once())
            ->method('getCurrentProductIdForBlock')
            ->willReturn($currentProductId);

        $viewedProductMock = $this->getMockBuilder(ViewedProduct::class)
            ->setMethods(['getMatchingProductIds', 'getConditions'])
            ->disableOriginalConstructor()
            ->getMock();
        $viewedProductMock->expects($this->once())
            ->method('getConditions')
            ->willReturn([]);
        $viewedProductMock->expects($this->once())
            ->method('getMatchingProductIds')
            ->willReturn($viewedMatchingProductIds);

        $ruleMock = $this->getMockBuilder(Rule::class)
            ->setMethods(['getViewedProductRule'])
            ->disableOriginalConstructor()
            ->getMock();
        $ruleMock->expects($this->exactly(2))
            ->method('getViewedProductRule')
            ->willReturn($viewedProductMock);

        $class = new \ReflectionClass($this->validator);
        $method = $class->getMethod('canShowOnProductAndCartPage');
        $method->setAccessible(true);

        $this->assertFalse($method->invokeArgs($this->validator, [$ruleMock, $type]));
    }
}
