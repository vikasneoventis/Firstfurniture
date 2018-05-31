
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(["jquery","Magento_Ui/js/modal/modal",
    "Magento_Ui/js/modal/confirm"], function ($, modal, confirm) {
    'use strict';

    return {
        test: function (url) {
            $.ajax({
                url: url,
                data: {
                    ftp_host: $('#ftp_host').val(),
                    ftp_port: $('#ftp_port').val(),
                    ftp_login: $('#ftp_login').val(),
                    ftp_password: $('#ftp_password').val(),
                    ftp_dir: $('#ftp_dir').val(),
                    ftp_active: $('#ftp_active').val(),
                    use_sftp: $('#use_sftp').val(),
                },
                type: 'POST',
                showLoader: true,
                success: function (data) {
                    confirm({
                        title: "Ftp Connection",
                        content: data
                    });
                }
            });
        }
    }
});