<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Block\Adminhtml\Feeds;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_collectionFactory = null;
    protected $_coreHelper = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Wyomind\DataFeedManager\Model\ResourceModel\Feeds\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Wyomind\DataFeedManager\Model\ResourceModel\Feeds\CollectionFactory $collectionFactory,
        \Wyomind\Core\Helper\Data $coreHelper,
        array $data = []
    ) {
    
        $this->_collectionFactory = $collectionFactory;
        $this->_coreHelper = $coreHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('datafeedmanagerGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', ['header' => __('Id'), 'width' => '50px', 'index' => 'id']);
        $this->addColumn('name', ['header' => __('Filename'), 'index' => 'name']);
        $this->addColumn(
            'type',
            ['header' => __('Type'), 'index' => 'type',
            'renderer' => 'Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Renderer\Type']
        );
        $this->addColumn('path', ['header' => __('Path'), 'index' => 'path']);

        $this->addColumn(
            'link',
            [
            'header' => __('Link'),
            'align' => 'left',
            'index' => 'link',
            "filter" => false,
            "sortable" => false,
            'renderer' => 'Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Renderer\Link'
                ]
        );

        $this->addColumn(
            'updated_at',
            [
            'header' => __('Update'),
            'index' => 'updated_at',
            'type' => 'datetime'
                ]
        );

        $this->addColumn(
            'store_id',
            [
            'header' => __('Store'),
            'index' => 'store_id',
            'type' => 'store',
                ]
        );

        $this->addColumn(
            'status',
            [
            'header' => __('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => [
                1 => __('Enabled'),
                0 => __('Disabled'),
            ],
                ]
        );

        $this->addColumn(
            'feed_status',
            [
            'header' => __('Status'),
            'align' => 'left',
            'renderer' => 'Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Renderer\Status',
            "filter" => false,
            "sortable" => false,
                ]
        );

        $this->addColumn(
            'action',
            [
            'header' => __('Action'),
            'type' => 'action',
            'getter' => 'getId',
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'header_css_class' => 'col-action',
            'column_css_class' => 'col-action',
            'renderer' => 'Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Renderer\Action',
                ]
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return "";
    }
}
