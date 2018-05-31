/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(['jquery', "Magento_Ui/js/modal/confirm"], function ($, confirm) {
    'use strict';
    return {
        codeMirrorProductPattern: null,
        codeMirrorHeaderPattern: null,
        codeMirrorFooterPattern: null,
        current_type: "xml",
        current_value: 1,
        codeMirrorTxt: null,
        updateType: function (askConfirm) {
            if (askConfirm) {
                // si type selectionne = XML et precedent != XML => on passe de csv a xml
                if (this.current_type !== this.getType()) {

                    confirm({
                        title: "Data feed file type",
                        content: "Changing file type from/to xml will clear all your settings. Do you want to continue ?",
                        actions: {
                            confirm: function () {
                                var list1 = new Array("header", "product_pattern", "footer", "clean_data", "enclose_data");
                                var list2 = new Array("extra_header", "include_header", "extra_footer", "field_separator", "field_protector", "field_escape");
                                var list3 = new Array("header", "product_pattern", "footer", "extra_header", "extra_footer");

                                this.current_type = this.getType();
                                this.current_value = $("#type").val();


                                // empty all text field
                                list3.each(function (id) {
                                    $('#' + id).val("");
                                });

                                if (this.isXML()) {
                                    $("#fields").remove();
                                }


                                if (!this.isXML()) { // others
                                    list1.each(function (id) {
                                        $('#' + id).parent().parent().css({display: 'none'});
                                    });
                                    list2.each(function (id) {
                                        $('#' + id).parent().parent().css({display: 'block'});
                                    });
                                    this.displayTxtTemplate();
                                } else { // XML
                                    list1.each(function (id) {
                                        $('#' + id).parent().parent().css({display: 'block'});
                                    });
                                    list2.each(function (id) {
                                        $('#' + id).parent().parent().css({display: 'none'});
                                    });
                                }

                                this.codeMirrorProductPattern.setValue('');
                                this.codeMirrorHeaderPattern.setValue('');
                                this.codeMirrorFooterPattern.setValue('');
                                this.codeMirrorProductPattern.refresh();
                                this.codeMirrorHeaderPattern.refresh();
                                this.codeMirrorFooterPattern.refresh();

                            }.bind(this),
                            cancel: function () {
                                $('#type').val(this.current_value);
                                this.current_type = this.getType();
                            }.bind(this)
                        }
                    });
                } else {
                    this.current_value = $("#type").val();
                    this.current_type = this.getType();
                }
            } else {
                var list1 = new Array("header", "product_pattern", "footer", "clean_data", "enclose_data");
                var list2 = new Array("extra_header", "include_header", "extra_footer", "field_separator", "field_protector", "field_escape");

                this.current_type = this.getType();
                this.current_value = $("#type").val();

                if (this.isXML()) {
                    $("#fields").remove();
                }


                if (!this.isXML()) { // others
                    list1.each(function (id) {
                        $('#' + id).parent().parent().css({display: 'none'});
                    });
                    list2.each(function (id) {
                        $('#' + id).parent().parent().css({display: 'block'});
                    });
                    this.displayTxtTemplate();
                } else { // XML
                    list1.each(function (id) {
                        $('#' + id).parent().parent().css({display: 'block'});
                    });
                    list2.each(function (id) {
                        $('#' + id).parent().parent().css({display: 'none'});
                    });
                }
            }
        },
        getType: function () {
            if ($('#type').val() == 1) {
                return "xml";
            } else {
                return "txt";
            }
        },
        isXML: function (type) {
            if (typeof type == "undefined") {
                return $('#type').val() == 1;
            } else {
                return type == 1;
            }
        },
        displayTxtTemplate: function () {
            if ($("#fields").length == 0) {
                var content = "<div id='fields'>";
                content += "     Column name";
                content += "      <span style='margin-left:96px'>Pattern</span>";
                content += "<ul class='fields-list' id='fields-list'></ul>";
                content += "<button type='button' class='add-field' onclick='require([\"dfm_configuration\"], function (configuration) {configuration.addField(\"\",\"\",true); });'>Insert a new field</button>";
                content += "<div class='overlay-txtTemplate'>\n\
                            <div class='container-txtTemplate'> \n\
                            <textarea id='codemirror-txtTemplate'>&nbsp;</textarea>\n\
                            <button type='button' class='validate' onclick='require([\"dfm_configuration\"], function (configuration) {configuration.popup_validate(); });'>Validate</button>\n\
                            <button type='button' class='cancel' onclick='require([\"dfm_configuration\"], function (configuration) {configuration.popup_close(); });'>Cancel</button>\n\
                            </div>\n\
                            </div>";
                content += "</div>";
                $(content).insertAfter("#include_header");

                this.codeMirrorTxt = CodeMirror.fromTextArea(document.getElementById('codemirror-txtTemplate'), {
                    matchBrackets: true,
                    mode: "application/x-httpd-php",
                    indentUnit: 2,
                    indentWithTabs: false,
                    lineWrapping: true,
                    lineNumbers: false,
                    styleActiveLine: true
                });

                $("#fields-list").sortable({
                    revert: true,
                    axis: "y",
                    stop: function () {
                        this.fieldsToJson();
                    }.bind(this)
                });

                this.jsonToFields();
            }

        },
        addField: function (header, body, refresh) {
            var content = "<li class='txt-fields'>";
            content += "   <input class='txt-field  header-txt-field input-text ' type='text' value=\"" + header.replace(/"/g, "&quot;") + "\"/>";
            content += "   <input class='txt-field  body-txt-field input-text ' type='text' value=\"" + body.replace(/"/g, "&quot;") + "\"/>";
            content += "   <button class='txt-field remove-field'>\u2716</button>";
            content += "</li>";
            $("#fields-list").append(content);
            if (refresh) {
                this.fieldsToJson();
            }
        },
        removeField: function (elt) {
            $(elt).parents('li').remove();
            this.fieldsToJson();
        },
        fieldsToJson: function () {
            var data = new Object;
            data.header = new Array;
            var c = 0;
            $('INPUT.header-txt-field').each(function () {
                data.header[c] = $(this).val();
                c++;
            });
            data.body = new Array;
            c = 0;
            $('INPUT.body-txt-field').each(function () {
                data.body[c] = $(this).val();
                c++;
            });
            var pattern = '{"product":' + JSON.stringify(data.body) + "}";
            var header = '{"header":' + JSON.stringify(data.header) + "}";
            $("#product_pattern").val(pattern);
            $("#header").val(header);
            this.codeMirrorProductPattern.setValue(pattern);
            this.codeMirrorHeaderPattern.setValue(header);
            this.codeMirrorProductPattern.refresh();
            this.codeMirrorHeaderPattern.refresh();
        },
        jsonToFields: function () {
            var data = new Object;

            var header = [];
            if ($('#header').val() != '') {
                try {
                    header = $.parseJSON($('#header').val()).header;
                } catch (e) {
                    header = [];
                }
            }

            var body = [];
            if ($('#product_pattern').val() != '') {
                try {
                    body = $.parseJSON($('#product_pattern').val()).product;
                } catch (e) {
                    body = [];
                }
            }

            data.header = header;
            data.body = body;

            var i = 0;
            data.body.each(function () {
                this.addField(data.header[i], data.body[i], false);
                i++;
            }.bind(this));
        },
        popup_current: null,
        popup_close: function () {
            $(".overlay-txtTemplate").css({"display": "none"});
        },
        popup_open: function (content, field) {
            $(".overlay-txtTemplate").css({"display": "block"});
            this.codeMirrorTxt.refresh();
            this.codeMirrorTxt.setValue(content);
            this.popup_current = field;
            this.codeMirrorTxt.focus();
        },
        popup_validate: function () {
            $(this.popup_current).val(this.codeMirrorTxt.getValue());
            this.popup_current = null;
            this.popup_close();
            this.fieldsToJson();
        },
        generate: function () {
            confirm({
                title: "Generate data feed",
                content: "Generate a data feed can take a while. Are you sure you want to generate it now ?",
                actions: {
                    confirm: function () {
                        $('#generate_i').val('1');
                        $('#edit_form').submit();
                    }
                }
            });
        },
        delete: function () {
            confirm({
                title: "Delete data feed",
                content: "Are you sure you want to delete this feed ?",
                actions: {
                    confirm: function () {
                        $('#back_i').val('1');
                        $('#edit_form').submit();
                    }
                }
            });
        }

    };
});

