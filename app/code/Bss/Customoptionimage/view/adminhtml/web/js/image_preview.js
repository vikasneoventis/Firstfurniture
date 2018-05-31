/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Customoptionimage
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'Magento_Catalog/js/form/element/checkbox',
    'Magento_Ui/js/form/element/single-checkbox'
], function (checkbox,singleCheckbox) {
    'use strict';

    return singleCheckbox.extend({
        defaults: {
            bss_preview: '',
            src: '',
            bss_span: '',
            bss_span_class: ''
        },
        initConfig: function () {
            this._super();
            var key1 = this.dataScope.split('.')[3];
            var key2 = this.dataScope.split('.')[5];
            this.bss_preview = 'bss_preview_' + key1 + '_' + key2;
            this.bss_span = 'bss_span_' + key1 + '_' + key2;
            return this;
        },
        del: function () {
            this.setSrc();
            
            if (document.getElementById(this.bss_span).className != 'bss-checkbox-null') {
                if (document.getElementById(this.bss_span).className == 'bss-checkbox-del') {
                    this.onCheckedChanged(true);
                    this.onExtendedValueChanged(true);
                    document.getElementById(this.uid).checked = true;
                    document.getElementById(this.bss_span).className = 'bss-checkbox-undo';
                } else {
                    this.reset();
                    document.getElementById(this.bss_span).className = 'bss-checkbox-del';
                    document.getElementById(this.uid).checked = false;
                    document.getElementById(this.bss_preview).src = this.src;
                }
            }
        },
        setSrc: function () {
            if (this.src.length == 0) {
                this.src = this.getPreview();
            }
        }
    });
});
