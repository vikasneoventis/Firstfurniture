<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml\Rule;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Backend\Block\Template\Context;
use Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\ColumnFactory;

/**
 * Class Listing
 *
 * @method string getPrimaryFieldName()
 * @method string getTypeFieldName()
 * @method string getCollectionClassName
 * @method array getColumns()
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml\Rule
 */
class Listing extends \Magento\Framework\View\Element\Template
{
    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Autorelated::listing/listing.phtml';

    /**
     * @var array
     */
    private $columns;

    /**
     * @var null
     */
    private $collection;

    /**
     * @var mixed
     */
    private $type;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ColumnFactory
     */
    private $columnFactory;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param ColumnFactory $columnFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        ColumnFactory $columnFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->objectManager = $objectManager;
        $this->columnFactory = $columnFactory;
    }

    /**
     * Render grid for type
     *
     * @param mixed $type
     * @return string
     */
    public function render($type)
    {
        $this->setType($type);
        return $this->toHtml();
    }

    /**
     * Set listing type for rendering
     *
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Return listing type for rendering
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return collection object
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Return columns
     *
     * @return array
     * @throws \Exception
     */
    public function getListingColumns()
    {
        if (!$this->columns) {
            foreach ($this->getColumns() as $columnName => $columnData) {
                if (!is_array($columnData)) {
                    throw new \Exception(__('Incorrect the column data format'));
                }

                $this->columns[$columnName] = $this->columnFactory->create()
                    ->setData($columnData)
                    ->setColumnName($columnName)
                    ->setPrimaryFieldName($this->getPrimaryFieldName())
                    ->setTypeFieldName($this->getTypeFieldName());
            }
        }

        return $this->columns;
    }

    /**
     * Is Ajax request
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->_request->isAjax();
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        $this->collection = $this->objectManager->create($this->getCollectionClassName());
        if (!$this->collection instanceof \Magento\Framework\Data\Collection) {
            return '';
        }
        $this->collection->addFieldToFilter($this->getTypeFieldName(), $this->getType());

        return parent::toHtml();
    }
}
