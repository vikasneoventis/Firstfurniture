<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-08T14:54:37+00:00
 * File:          app/code/Xtento/TrackingImport/Block/Adminhtml/Manual.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Block\Adminhtml;

class Manual extends \Magento\Backend\Block\Template
{
    protected $importResult = false;

    /**
     * @var \Xtento\TrackingImport\Model\System\Config\Source\Import\Profile
     */
    protected $profileSource;

    /**
     * @var \Xtento\TrackingImport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Framework\View\Element\Html\Date
     */
    protected $dateElement;

    /**
     * @var \Xtento\TrackingImport\Model\ImportFactory
     */
    protected $importFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface
     */
    protected $dateTimeFormatter;

    /**
     * Manual constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Xtento\TrackingImport\Model\System\Config\Source\Import\Profile $profileSource
     * @param \Xtento\TrackingImport\Helper\Entity $entityHelper
     * @param \Magento\Framework\View\Element\Html\Date $dateElement
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter
     * @param \Xtento\TrackingImport\Model\ImportFactory $importFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Xtento\TrackingImport\Model\System\Config\Source\Import\Profile $profileSource,
        \Xtento\TrackingImport\Helper\Entity $entityHelper,
        \Magento\Framework\View\Element\Html\Date $dateElement,
        \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter,
        \Xtento\TrackingImport\Model\ImportFactory $importFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileSource = $profileSource;
        $this->entityHelper = $entityHelper;
        $this->systemStore = $systemStore;
        $this->dateElement = $dateElement;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->importFactory = $importFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Xtento_TrackingImport::manual_import.phtml');
    }

    public function getJs($filename)
    {
        $url = $this->_assetRepo->createAsset(
            'Xtento_TrackingImport::js/' . $filename,
            ['_secure' => $this->getRequest()->isSecure()]
        )->getUrl();
        return $url;
    }

    public function getProfileSelectorHtml()
    {
        $html = '<select class="select" name="profile_id" id="profile_id" style="width: 320px;">';
        $html .= '<option value="">' . __('--- Select Profile---') . '</option>';
        $enabledProfiles = $this->profileSource->toOptionArray();
        $profilesByGroup = [];
        foreach ($enabledProfiles as $profile) {
            $profilesByGroup[$profile['entity']][] = $profile;
        }
        foreach ($profilesByGroup as $entity => $profiles) {
            $html .= '<optgroup label="' . __(
                    '%1 Import',
                    $this->entityHelper->getEntityName($entity)
                ) . '">';
            foreach ($profiles as $profile) {
                $html .= '<option value="' . $profile['value'] . '" entity="' . $entity . '">' . $profile['label'] . ' (' . __(
                        'ID: %1',
                        $profile['value']
                    ) . ')</option>';
            }
            $html .= '</optgroup>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getSession()
    {
        return $this->_backendSession;
    }

    public function getImportResult()
    {
        if (!$this->importResult) {
            $this->importResult = $this->getSession()->getData('xtento_trackingimport_debug_log');
            $this->getSession()->setData('xtento_trackingimport_debug_log', false);
        }
        if (empty($this->importResult)) {
            return false;
        }
        return $this->importResult;
    }

    protected function _toHtml()
    {
        $messagesBlock = <<<EOT
<div id="messages">
    <div class="messages">
        <div class="message message-warning warning" id="warning-msg" style="display:none">
            <div id="warning-msg-text"></div>
        </div>
        <div class="message message-success success" id="success-msg" style="display:none">
            <div id="success-msg-text"></div>
        </div>
    </div>
</div>
EOT;
        return $messagesBlock . parent::_toHtml();
    }
}
