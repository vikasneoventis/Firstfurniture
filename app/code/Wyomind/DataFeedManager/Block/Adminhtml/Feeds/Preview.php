<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Block\Adminhtml\Feeds;

class Preview extends \Magento\Backend\Block\Template
{

    protected $_dfmModel = null;
    protected $_coreHelper = null;
    public $fileType = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Wyomind\DataFeedManager\Model\Feeds $dfmModel,
        \Wyomind\Core\Helper\Data $coreHelper,
        array $data = []
    ) {
        $this->_dfmModel = $dfmModel;
        $this->_coreHelper = $coreHelper;
        parent::__construct($context, $data);
    }

    public function getContent()
    {

        $request = $this->getRequest();
        $id = $request->getParam('id');

        $model = $this->_dfmModel;
        $model->limit = $this->_coreHelper->getDefaultConfig('datafeedmanager/system/preview');
        $model->setDisplay(true);

        $model->load($id);

        try {
            $content = $model->generateFile($request);
            $this->fileType = $model->getType() == 1 ? "xml" : "other";
            return $content;
        } catch (\Exception $e) {
            return __('Unable to generate the data feed : ' . $e->getMessage());
        }
    }
}
