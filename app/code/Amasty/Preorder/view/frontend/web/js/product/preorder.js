define([
    "jquery",
    "jquery/ui",
    'Magento_Catalog/js/catalog-add-to-cart'
], function($) {
    'use strict';

    $.widget('mage.amastyPreorder', {
       options: {
           addToCartButton: $("#product-addtocart-button span"),
           availabilityElement: $("#product-addtocart-button").parents(".product-info-main").first().find('.stock'),
           preOrderNote: '',
           addToCartLabel: ''
       },

        _original: {
            availabilityText: '',
            addToCartLabel: ''
        },

        _enabled: false,

        _create: function() {
            this._saveOriginal();
        },

        _saveOriginal: function(){
            if (this.options.availabilityElement){
                this._original.availabilityText = this.options.availabilityElement.text();
            }

            if (this.options.addToCartButton){
                this._original.addToCartLabelText = this.options.addToCartButton.text();
            }
        },

        _changeLabels: function() {
            if (this.options.availabilityElement){
              this.options.availabilityElement.html(this.options.preOrderNote);
            }
            this.options.addToCartButton.html(this.options.addToCartLabel);
        },

        enable: function() {
            /*if(this._enabled) {
                return;
            }*/
            this._enabled = true;
            this._changeLabels();
        },

        disable: function() {
            /*if(!this._enabled) {
                return;
            }*/
            this._enabled = false;
            if (this.options.availabilityElement){
                this.options.availabilityElement.text(this._original.availabilityText);
            }
            this.options.addToCartButton.text(this._original.addToCartLabelText);
        }
    });

    return $.mage.amastyPreorder;
});
