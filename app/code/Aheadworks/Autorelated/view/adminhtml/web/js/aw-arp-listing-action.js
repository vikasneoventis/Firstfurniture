/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

/**
 * Initialization widget for listing block
 *
 * @method click()
 * @method doAjax()
 */
define([
    "jquery"
], function($){
    'use strict';

    $.widget("mage.awArpListingAction", {
        options: {
            hideMsgTimeout: 5000,
            msgSelector: '.messages'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            this.element.on('click', $.proxy(this.click, this));
        },

        /**
         * Running Ajax request if necessary
         */
        click: function() {
            var confirmMsg = this.element.attr('data-confirmation');
            if (!confirmMsg || confirm(confirmMsg)) {
                this.doAjax();
            }
        },

        /**
         * Send an Ajax request and update the listing block
         */
        doAjax: function() {
            var listingSelector = '[data-type="' + this.element.attr('data-listing-type') + '"]',
                url = this.element.attr('data-url'),
                self = this,
                listing;

            $.ajax({
                url: url,
                data: {
                    form_key: FORM_KEY
                },
                showLoader: true
            }).done(function (response) {
                if (response.listing) {
                    listing = $(listingSelector);

                    if (listing.length > 0) {
                        listing.html(response.listing);
                        listing.find(self.options.msgSelector).delay(self.options.hideMsgTimeout).fadeOut();
                        listing.trigger('contentUpdated');
                    }
                }
            });
        }
    });

    return $.mage.awArpListingAction;
});
