App = typeof App !== 'undefined' ? App : {};
App.Main = function() {
    var initTooltipster = function() {
        $('[title]:not(.sidebar-footer *)').tooltipster({theme: 'tooltipster-shadow'});
        $('body').on('draw.dt', 'table', function() {
            $(this).find('[title]').tooltipster({theme: 'tooltipster-shadow'});
        });
    }

    var initiCheck = function() {
        // iCheck
        if ($("input.flat")[0]) {
            $('input.flat').iCheck({
                checkboxClass: 'icheckbox_flat-green',
                radioClass: 'iradio_flat-green'
            });
        };
    }

    var initPasswordModal = function() {
        $('#modalPassword button[type=button]:last').hide().on('click', function() {
            $('#modalPassword form').submit();
        });

        $.validator.addMethod('validpassword', function(value, element) {
            return this.optional(element) || (/[A-Z]/.test(value) && /[0-9]/.test(value) && /[a-z]/.test(value) && value.length > 7);
        }, Translator.trans('Password strength is too low (include letters, capital letters and digits)'));

        $('#linkChangePassword').on('click', function(event) {
            event.preventDefault();

            $('#modalPassword').modal().on('hide.bs.modal', function(){
                $(this).find('form').remove();
            });
            $('#modalPassword .modal-body').empty().load(Routing.generate('app_user_changepassword'), function() {
                $('#modalPassword form').validate({
                    messages: {
                        'form[current_password]': {
                            remote: Translator.trans('Wrong password')
                        }
                    },
                    rules: {
                        'form[current_password]': {
                            remote: {
                                url: Routing.generate('app_user_checkpassword'),
                                type: 'post',
                                data: {
                                    password: function() {
                                        return $('#form_current_password').val();
                                    }
                                }
                            }
                        },
                        'form[plainPassword][first]': 'validpassword',
                        'form[plainPassword][second]': {
                            equalTo: '#form_plainPassword_first'
                        }
                    },
                    errorPlacement: function(error, element) {
                        if (!element.data('tooltipster-ns')) {
                            element.tooltipster({
                                trigger: 'custom',
                                onlyOne: false,
                                position: 'bottom-left',
                                positionTracker: true
                            });
                        }
                        element.tooltipster('update', $(error).text());
                        element.tooltipster('show');
                    },
                    success: function (label, element) {
                        $(element).tooltipster('hide');
                    },
                    submitHandler: function() {
                        $('#modalPassword form').ajaxSubmit({
                            dataType: 'json',
                            beforeSubmit: function() {
                                $('#modalPassword button[type=button]:last').hide();
                            },
                            success: function(json) {
                                if (json.result == 'success') {
                                    $('#modalPassword .modal-body').empty().append($('<div/>').addClass('alert alert-success').text(Translator.trans('Password changed successfully!')));
                                } else {
                                    $('#modalPassword .modal-body').empty().append($('<div/>').addClass('alert alert-warning').text(Translator.trans('Password coold not be changed.')));
                                }
                                $('#modalPassword button[type=button]:first').text('Close');
                            }
                        });
                    }
                })
                $('#modalPassword button[type=button]').show();
            });
        });
    }

    return {
        init: function() {
            initTooltipster();
            initiCheck();
            initPasswordModal();
        }
    }
}();

App.Forms = function() {
    var initCollectionControls = function() {
        var clickRemoveItem = function(event) {
            event.preventDefault();

            $(this).closest('.item').fadeOut(function() {
                var $container = $(this).closest('.collection');
                $(this).remove();
                $container.trigger('item-removed.app');
            });
        }

        var clickAddItem = function(event) {
            var $container = $(this).parent().parent().find('.collection:first'),
                prototype = $container.data('prototype'),
                index = $container.data('index');

            if ($container.length == 0 || typeof prototype == 'undefined' || typeof index == 'undefined') {
                return;
            }

            var $item = $(prototype.replace(/__name__/g, index));

             $container.data('index', index + 1);
             $container.append($item);

             $($item.find('input:text:visible, select:visible, textarea:visible')[0]).focus();

             $container.trigger('item-added.app', {
                index: index,
                item: $item
             });
        }

        $('body').on('click', '.btn-add-item', clickAddItem);
        $('body').on('click', '.btn-remove-item', clickRemoveItem);

        $('.collection').each(function() {
            var $container = $(this);
            $container.data('index', $container.find('>.item').length);
        });
    }

    var initTelephoneControl = function(control, options) {
        $(control).intlTelInput($.extend({}, {
            allowExtensions: true,
            autoFormat: false,
            autoHideDialCode: true,
            autoPlaceholder: false,
            defaultCountry: 'auto',
            geoIpLookup: function(callback) {
                $.get('http://ipinfo.io', function() {}, "jsonp").always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : '';
                    callback(countryCode);
                });
            },
            nationalMode: false,
            numberType: 'MOBILE',
            preferredCountries: ['us', 'ca', 'cu'],
            utilsScript: phone_util_script_url
        }, options));
    }

    return {
        init: function() {
            initCollectionControls();
        },
        initTelephoneControl: initTelephoneControl
    }
}();
