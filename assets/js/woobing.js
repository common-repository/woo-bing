;(function($) {
    var WOOBING = {
        init: function() {

            $('.woobing').on( 'click', '.woobing-more-field', this.addMore );
            $('.woobing').on( 'click', '.woobing-remove-more', this.removeMore );
            $('.woobing .woobing-form-product').on( 'submit',  this.formValidation );
            $('.woobing').on( 'click', '.woobing-product-field-addMore', this.addMoreGroup );
            $('.woobing').on( 'click', '.woobing-product-field-remove', this.removeMoreGroup );
            $('.woobing-delete-product').on('click', this.deleteProduct);
            $('.woobing').on( 'change', '.product_id', this.changeProduct );
            $('.woobing').on( 'change', '.woobing-all-product-checkbox-feed', this.allProductHide );
            $('#woobing-feed-metabox-wrap').on('change', '.woobing-all-product-checkbox', this.feedDefault );
            $('.woobing').on( 'change', '.woobing-target-country-drop', this.targetCountry );
            this.chosen();
            this.allProductHide();
            this.datePicker();
            this.feedDefault();
        },

        targetCountry: function(e) {
            e.preventDefault();
            
            var self = $(this),
                val  = self.val(),
                country = jQuery.parseJSON( woobing_var.country_details );
            
            if ( val == '-1' ) {
                $('.woobing-content-language').val('-1').closest('.woobing-hidden').hide();
                $('.woobing-currency-field').val( ' ' ).closest('.woobing-hidden').hide();
                return;
            }
            
            $.each( country, function( key, value ) {

                if ( key == val ) {
                    var language_code = value.language_code,
                        currency = value.currency_code;
                    
                    $('.woobing-content-language').val( language_code ).closest('.woobing-hidden').show();
                    $('.woobing-currency-field').val( currency ).closest('.woobing-hidden').show();
                }
            });

            
        },

        feedDefault: function() {
            var check = $('.woobing-all-product-checkbox');
            $.each( check, function( key, val ) {
                if ( $(val).prop('checked') ) {
                    $(val).closest('.woobing-form-field').siblings('.woobing-form-field').hide();
                } else {
                   $(val).closest('.woobing-form-field').siblings('.woobing-form-field').show();
                }
            });
        },

        datePicker: function() {
            $('.woobing-date-picker').datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true,
                numberOfMonths: 1,
            });
        },
        allProductHide: function() {
            if ( $('.woobing-all-product-checkbox-feed').prop('checked') ) {
                $('.woobing-product-chosen-field-wrap').hide();
            } else {
                $('.woobing-product-chosen-field-wrap').show();
            }
        },
        chosen: function() {

            $('.woobing .woobing-chosen').chosen({ width: '300px' });

        },

        changeProduct: function() {
            var self = $(this),
                data = {
                    product_id : self.val(),
                    action : 'change_product',
                    _wpnonce: woobing_var.nonce
                };

            $.post( woobing_var.ajaxurl, data, function( res ) {
                if ( res.success ) {
                    window.location.href = res.data.url;
                }
            });
        },

        deleteProduct: function(e) {
            e.preventDefault();
            if ( !confirm( 'Are you sure?' ) ) {
                return;
            }
            var self = $(this),
                data = {
                    merchant_product_id : self.data('merchant_product_id'),
                    merchant_id : self.data('merchant_id'),
                    post_id: self.data('post_id'),
                    action : 'delete_product',
                    _wpnonce: woobing_var.nonce
                };

            $.post( woobing_var.ajaxurl, data, function( res ) {
                if ( res.success ) {
                    alert( res.data.success_msg );
                    location.reload()
                } else {
                    window.location.href = res.data.url;
                }
            });
        },

        removeMoreGroup: function(e) {
            e.preventDefault();
            var self = $(this),
                wrap = self.closest('.woobing-form-field').parent('.woobing-form-field'),
                remove_wrap = wrap.find( '.woobing-product-field-child-wrap' );
            if ( remove_wrap.length > 1 ) {
                remove_wrap.last().remove();
            }
        },

        addMoreGroup: function(e) {
            e.preventDefault();
            var self = $(this),
                wrap = self.closest('.woobing-form-field').parent('.woobing-form-field'),
                length = wrap.find('.woobing-product-field-child-wrap').length,
                current_lenght = length - 1;
                clone_wrap = wrap.find( '.woobing-product-field-child-wrap' ).last(),
                clone = clone_wrap.clone(),
                all_name = clone.find('input, textare, select');

            $.each( all_name, function( index, value ) {
                var this_val = $(value),
                    name = this_val.attr('name'),
                    new_name = name.replace('['+current_lenght+']', '['+length+']');
                this_val.attr('name', new_name );
            });

            clone_wrap.after(clone);
        },

        formValidation: function(e) {
            e.preventDefault();
            var form = $(this),
                submited_data = form.serialize().replace(/[^&]+=&/g, '').replace(/&[^&]+=$/g, '');
            validate = woobing.formValidator( form );
            if( ! validate ) {
                return false;
            }
            var spinner = form.find('.woobing-spinner-wrap');
            spinner.addClass('woobing-spinner');
            $.post( woobing_var.ajaxurl, submited_data, function(res) {
                spinner.removeClass('woobing-spinner');

                if ( res.success ) {
                    $('.woobing-submit-notification')
                        .addClass('updated')
                        .removeClass('error')
                        .html('<div class="woobing-success"><strong>'+res.data.success_msg+'</strong></div>');

                    $('body,html').animate({scrollTop: 10 }, 'slow');
                } else {
                    if ( res.data.authentication_fail ) {
                        location.reload();
                    } else {
                        $('.woobing-submit-notification').addClass('updated error')
                            .html(
                                '<div class="woobing-error-code">Error code : '+res.data.error_code+'</div>'+
                                '<div class="error-message">Error message : '+res.data.error_msg+'</div>'
                            );
                        $('body,html').animate({scrollTop: 10 }, 'slow');
                    }
                }
            });

            return false;
        },

        formValidator: function( form ) {
            var required = form.find('[data-woobing_validation="1"]'),
                validate = true,
                scroll_selector = [];

            form.find('.woobing-notification').remove();

            $.each( required, function( key, field ) {
                var self = $(field),
                    field_wrap = self.closest('.woobing-form-field'),
                    has_dependency = self.data('woobing_dependency');

                if ( has_dependency ) {
                    var dependency_handelar = form.find('[data-'+has_dependency+']'),
                        dependency_handelar_val = dependency_handelar.data(has_dependency);

                    if( dependency_handelar_val == 'checked' ) {
                        if ( !$(dependency_handelar).is(':checked') ) {
                            return;
                        }
                    }
                }

                if ( self.is('select') && self.data('woobing_required') && self.val() === '-1' ) {
                    validate = false;
                    field_wrap.find('.woobing-notification').remove();
                    field_wrap.append('<div class="woobing-notification">'+self.data('woobing_required_error_msg')+'</div>');
                    scroll_selector.push(self);
                }

                if( self.data('woobing_required') && ( self.val() === '' || self.val() === null ) ) {
                    validate = false;
                    field_wrap.find('.woobing-notification').remove();
                    field_wrap.append('<div class="woobing-notification">'+self.data('woobing_required_error_msg')+'</div>');
                    scroll_selector.push(self);
                }

                if ( validate && self.data('woobing_email') ) {
                    validate = woobingGeneral.validateEmail( self.val() );
                    if ( validate === false ) {
                        field_wrap.find('.woobing-notification').remove();
                        field_wrap.append('<div class="woobing-notification">'+self.data('woobing_email_error_msg')+'</div>');
                        scroll_selector.push(self);
                    }
                }
            });

            if( ! validate ) {
                $('body,html').animate({scrollTop: scroll_selector[0].offset().top - 100});
            }

            return validate;
        },

        removeMore: function(e) {
            e.preventDefault();
            var self = $(this);
            self.closest('.woobing-form-field').remove();
        },

        addMore: function(e) {
            e.preventDefault();
            var self = $(this),
                self_wrap = self.closest('.woobing-form-field'),
                name = self_wrap.find( 'input, select, textare' ).data('field_name'),
                clone = self_wrap.clone(),
                remove_icon = '<i class="woobing-remove-more">-</i>',
                remove_icon_length = $(clone).find('.woobing-remove-more').length,
                append_field = ( remove_icon_length === 0 ) ? $(clone).find('.woobing-more-field').after(remove_icon) : '',

                all_name = clone.find('input, textare, select').attr( 'name', name );

            self_wrap.after(clone);
        },
    }
    
    WOOBING.init();

})(jQuery);