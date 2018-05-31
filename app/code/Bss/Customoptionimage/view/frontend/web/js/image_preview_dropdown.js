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

    $.widget('bss.bss_preview', {
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

            $('.Bss_image_preview img').css('width', width + 'px')
            .css('height', height + 'px').css('border', 'solid 2px #ddd');
            if (this.options.viewType == 1) {
                $('.Bss_image_preview').fadeIn();
            }

            $widget.element.find('select.product-custom-option:not(.multiselect)').change(function () {
                var optionid = $(this).attr('id').split('_')[1];
                if (viewType == 0) {
                    var element = $widget.element.find('.Bss_image_preview img');
                    if (typeof url[$(this).val()] == 'string' && url[$(this).val()].length > 0) {
                        element.attr('src', url[$(this).val()]);
                        element.attr('title', $("option[value=" + $(this).val() + "]").html());
                        $widget.element.find('.Bss_image_preview').fadeIn();
                    } else {
                        $widget.element.find('.Bss_image_preview').fadeOut();
                    }
                } else if (viewType == 1) {
                    if (typeof url[$(this).val()] == 'string') {
                        var element = $widget.element.find('#image_preview_' + $(this).val());
                        $widget.element.find('.Bss_image_preview img').css('border','solid 2px #ddd');
                        element.css('border','solid 2px #d33');
                    } else {
                        $widget.element.find('.Bss_image_preview img').css('border','solid 2px #ddd');
                    }
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
    return $.bss.bss_preview;
});
