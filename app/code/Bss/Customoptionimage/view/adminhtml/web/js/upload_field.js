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
    'Magento_Ui/js/form/element/file-uploader',
    'Magento_Ui/js/form/element/media',
    'jquery'
], function (upload,media,$) {
    'use strict';

    return upload.extend({
        defaults: {
            bss_upl_preview: '',
            bss_upl_span: '',
            bss_option_so: '',
            bss_value_so: '',
            tempImg: ''
        },
        initConfig: function () {
            this._super();
            this.bss_upl_preview = 'bss_upl_preview_' + this.uid;
            this.bss_upl_span = 'bss_upl_span_' + this.uid;
            this.bss_option_so = this.dataScope.split('.')[3];
            this.bss_value_so = this.dataScope.split('.')[5];
            this.uploadFieldId = 'Bss_Customoptionimage_' + this.bss_option_so + '_' + this.bss_value_so;
            return this;
        },
        del: function () {
            document.getElementById(this.bss_upl_span).className = 'bss-checkbox-null';
            document.getElementById(this.uid).value = "";
            this.tempImg = '';
        },
        clickUpload: function () {
            document.getElementById(this.uid).click();
        },
        readURL: function () {
            var formData = new FormData(),
                baseUrl = this.baseUrl,
                upFieldId = this.uploadFieldId,
                $widget = this;
            formData.append('temporary_image', $('#' + this.uid)[0].files[0]);
            formData.append('option_sortorder', this.bss_option_so);
            formData.append('value_sortorder', this.bss_value_so);
            if (document.getElementById(this.uid).files && document.getElementById(this.uid).files[0]) {
                var file = document.getElementById(this.uid).files[0];
                var extension = file.name.substring(file.name.lastIndexOf('.'));
                var validFileType = ".jpg , .png , .bmp";
                if (validFileType.indexOf(extension.toLowerCase()) < 0) {
                    alert("Please select valid file type. The supported file types are .jpg , .png , .bmp");
                    return false;
                }
            }
            $.ajax({
                url : baseUrl,
                type : 'POST',
                data : formData,
                processData: false,
                contentType: false,
                success : function (data) {
                    $('#' + upFieldId).val(data);
                    $widget.storageImage(data);
                }
            });
        },
        storageImage: function (data) {
            $('#' + this.bss_upl_preview).attr('src', data);
            $('#' + this.bss_upl_span).attr('class', 'bss-checkbox-del');
            this.tempImg = data;
        },
        getClass: function () {
            if (this.tempImg == '') {
                return 'bss-checkbox-null';
            } else {
                return 'bss-checkbox-del'
            }
        }
    });
});
