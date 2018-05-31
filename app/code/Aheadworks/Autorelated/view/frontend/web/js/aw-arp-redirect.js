/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

/**
 * Initialization widget for redirect
 *
 * @method click()
 */
define([
    'jquery',
    'jquery/ui'
], function($) {
    "use strict";

    $.widget('mage.awArpRedirect', {
        /**
         * Initialize widget
         */
        _create: function() {
            this.element.on('click', $.proxy(this.click, this));
        },

        /**
         * Redirect to url with rule id and uenc param
         */
        click: function()
        {
            var ruleId = this.element.attr('data-aw-arp-rule-id'),
                encodeUrl = this.element.attr('data-aw-arp-encode-url'),
                location = this.element.attr('href'),
                form = $('<form></form>'),
                field1 = $('<input></input>'),
                field2 = $('<input></input>');

            form.attr("method", "post");
            form.attr("action", location);
            form.hide();

            field1.attr('type', 'hidden');
            field1.attr('name', 'awarp_rule');
            field1.attr('value', ruleId);
            form.append(field1);

            field2.attr('type', 'hidden');
            field2.attr('name', 'uenc');
            field2.attr('value', encodeUrl);
            form.append(field2);

            $(document.body).append(form);

            $(form).submit();

            event.preventDefault();
        },
    });

    return $.mage.awArpRedirect;
});
