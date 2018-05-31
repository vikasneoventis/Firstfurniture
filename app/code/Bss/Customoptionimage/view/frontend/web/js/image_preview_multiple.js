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
    'jquery',
    'underscore',
    'mage/template',
    'priceUtils',
    'jquery/ui'
], function ($, _, mageTemplate, utils) {
    'use strict';

    $.widget('bss.bss_preview_multiple', {
        _create: function () {
            var url = [],
                $widget = this;
            url = $widget.updateImage($widget);
            $widget.updateEventListener($widget, url);
        },
        updateEventListener: function ($widget, url) {
            var viewType = this.options.viewType,
                width = this.options.imageWidth,
                height = this.options.imageHeight;

            if (this.options.viewType == 1) {
                $('.Bss_image_multiselect img').css('width', width + 'px')
                .css('height', height + 'px').css('border', 'solid 2px #ddd');
                $('.Bss_image_multiselect').fadeIn();
            }

            $widget.element.find('.product-custom-option.multiselect').change(function () {
                var values = [];
                var html = '';
                if (viewType == 0) {
                    $(this).each(function () {
                        values.push($(this).val());
                    });
                    $.each(values[0],function (index, vl) {
                        if (url[vl].length > 0) {
                            html += '<img alt="" src="' +
                            url[vl] +
                            '" title="' +
                            $("option[value=" + vl + "]").html() +
                            '" style="height: ' +
                            height +
                            'px; width: ' +
                            width +
                            'px; border: solid 1px #ddd;" />';
                        }
                    });
                    if (html.length > 0) {
                        $widget.element.find('.Bss_image_multiselect').html(html);
                        $widget.element.find('.Bss_image_multiselect').fadeIn();
                    } else {
                        $widget.element.find('.Bss_image_multiselect').fadeOut();
                    }
                } else if (viewType == 1) {
                    $(this).each(function () {
                        values.push($(this).val());
                    });
                    $('.Bss_image_multiselect img').css('border', 'solid 2px #ddd');
                    $.each(values[0],function (index, vl) {
                        $('#image_preview_' + vl).css('border', 'solid 2px #d33');
                    });
                }
            });
        },
        updateImage: function ($widget) {
            var result = [];
            $.each($widget.options.imageUrls, function (index, image) {
                result[image.id] = image.url;
            });
            return result;
        }
    });
    return $.bss.bss_preview_multiple;
});
