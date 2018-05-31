define(
    [
        'Magento_Ui/js/form/form',
        'Amasty_Checkout/js/action/update-delivery',
        'Amasty_Checkout/js/model/delivery',
        'Amasty_Checkout/js/view/checkout/datepicker'
    ],
    function (
        Component,
        updateAction,
        deliveryService
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amasty_Checkout/checkout/delivery_date',
                listens: {
                    'update': 'update'
                }
            },

            isLoading: deliveryService.isLoading,

            update: function () {
                this.source.set('params.invalid', false);
                this.source.trigger('amcheckoutDelivery.data.validate');

                if (!this.source.get('params.invalid')) {
                    var data = this.source.get('amcheckoutDelivery');

                    updateAction(data);
                }
            }
        });
    }
);
