App = typeof App !== 'undefined' ? App : {};
App.Bookings = typeof App.Bookings !== 'undefined' ? App.Bookings : {};

App.Bookings.Form = function() {
    var initCollections = function() {
        var updateNights = function($item, trigger) {
            var $nights = $item.find('input[name$="[nights]"]'),
                $dates = $item.find('input.datepicker'),
                now = $.now();

            if ($nights.data('ajax')) {
                $nights.data('ajax').abort();
            }

            if ($($dates[0]).val() == '') {
                return;
            }

            if ($(trigger).is('.datepicker')) {
                if ($($dates[1]).val() == '') {
                    $nights.val('');
                    return;
                }

                var xhr = $.ajax(Routing.generate('app_offers_getnights') + '?id=' + now, {
                    data: {
                        from: $($dates[0]).val(),
                        to: $($dates[1]).val()
                    },
                    dataType: 'json',
                    method: 'POST',
                    success: function(json) {
                        $nights.val(json.nights);
                        $nights.removeData('ajax');
                    }
                });
            } else {
                if ($(trigger).val() == '') {
                    $($dates[1]).val('');
                    return;
                }

                var xhr = $.ajax(Routing.generate('app_offers_getnights') + '?id=' + now, {
                    data: {
                        from: $($dates[0]).val(),
                        nights: $nights.val()
                    },
                    dataType: 'json',
                    method: 'POST',
                    success: function(json) {
                        $($dates[1]).val(json.to);
                        $nights.removeData('ajax');
                    }
                });
            }

            $nights.data('ajax', xhr);
        }

        var initDatepickers = function(item) {
            var dps = $(item).find('input.datepicker'), options = {
                format: 'DD/MM/YYYY HH:mm',
                showClear: true,
                showTodayButton: true
            };
            $(dps[0]).parent().datetimepicker(options);
            $(dps[1]).parent().datetimepicker($.extend({}, options, {
                useCurrent: false
            }));
            $(dps[0]).parent().on('dp.change', function(e) {
                $(dps[1]).parent().data("DateTimePicker").minDate(e.date);
                updateNights($(item), dps[0]);
            });
            $(dps[1]).parent().on("dp.change", function(e) {
                $(dps[0]).parent().data("DateTimePicker").maxDate(e.date);
                updateNights($(item), dps[1]);
            });
        }

        $('.item.item-service').each(function() {
            initDatepickers(this);
        });

        $('body').on('change', '.item-service input[name$="[nights]"]', function() {
            updateNights($(this).closest('.item'), this);
        });

        $('body').on('click', '.btn-add-item', function(event) {
            var $btn = $(this);
            if ($btn.is('a')) {
                event.preventDefault();
            }

            var $container = $($btn.data('collection')),
                index = $container.data('index'),
                prototype = $container.data('prototype')
                $counter = $container.data('counter') ? $($container.data('counter')) : null;

            $item = $(prototype.replace(/__name__/g, index));
            $container.data('index', index + 1);
            if ($counter) {
                $counter.val(parseInt($counter.val(),10) + 1);
            }
            $container.append($item);

            initDatepickers($item);

            $item.find('input:text:first').focus();

            //hide error if there is
            if ($container.closest('div.x_panel').find('h2').data('tooltipster-ns')) {
                $container.closest('div.x_panel').find('h2').tooltipster('hide');
            }

            $('body').animate({
                scrollTop: $item.offset().top
            }, 500);
        });

        $('body').on('click', '.btn-remove-item', function(event) {
            var $btn = $(this), $item = $btn.closest('.item');

            if ($btn.is('a')) {
                event.preventDefault();
            }

            swal({
                title: Translator.trans('Confirm operation'),
                text: Translator.trans('The service will be removed. Are you sure you want to continue?'),
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d9534f'
            }, function(isConfirmed) {
                if (isConfirmed) {
                    $item.fadeOut(function() {
                        var $container = $(this).closest('.collection'),
                            $counter = $container.data('counter') ? $($container.data('counter')) : null;
                        $(this).remove();
                        if ($counter) {
                            $counter.val($counter.val() - 1);
                        }
                    });
                }
            });
        });
    }

    var initValidation = function() {
        $('#reservation').validate({
            errorPlacement: function(error, element) {
                if (element.is(':hidden')) {
                    element = element.closest(':visible');
                }
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
            ignore: ':hidden:not(input[name="servicesCounter"])',
            messages: {
                'servicesCounter': {
                    min: Translator.trans('Add a service at least')
                }
            },
            rules: {
                'servicesCounter': {
                    min: 1
                },
                'offer_form[directClientFullName]': {
                    required: {
                        depends: function() {
                            return $('#offer_form_clientType_1').prop('checked') === true;
                        }
                    }
                },
                'offer_form[client]': {
                    required: {
                        depends: function() {
                            return $('#offer_form_clientType_0').prop('checked') === true;
                        }
                    }
                },
                'offer_form[percentApplied][plus]': 'number'
            },
            success: function (label, element) {
                var $element = $(element);
                if ($element.is(':hidden')) {
                    $element = $element.closest(':visible');
                }
                $element.tooltipster('hide');
            }
        });
    }

    var init = function() {
        $('input:radio[name="offer_form[clientType]"]').on('ifClicked', function() {
            var value = $(this).val();
            if (value === 'direct') {
                $('.block-clienttype.block-clienttype-direct').show();
                $('.block-clienttype:not(.block-clienttype-direct)').find('input:text, select').each(function() {
                    if ($(this).data('tooltipster-ns')) {
                        $(this).tooltipster('hide');
                    }
                });
                $('.block-clienttype:not(.block-clienttype-direct)').hide();
            } else if (value === 'registered') {
                $('.block-clienttype.block-clienttype-registered').show();
                $('.block-clienttype:not(.block-clienttype-registered)').find('input:text, select').each(function() {
                    if ($(this).data('tooltipster-ns')) {
                        $(this).tooltipster('hide');
                    }
                });
                $('.block-clienttype:not(.block-clienttype-registered)').hide();
            }
        });

        $('#offer_form_client').on('change', function() {
            $('#offer_form_notificationContact').empty();
            var id = $(this).val();
            $.getJSON(Routing.generate('app_offers_getclientcontacts') + '?client=' + id, function(json) {
                $('#offer_form_notificationContact').append($('<option value=""></option>'));
                $.each(json.elements, function(i, e) {
                    $('#offer_form_notificationContact').append($('<option value="' + e.id + '">' + e.text + '</option>'));
                });
            });
        });

        $('#offer_form_directClientMobilePhone').intlTelInput({
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
            preferredCountries: ['ca', 'us', 'gb'],
            utilsScript: phone_util_script_url
        });

        $('body').on('click', '.btn-search-service', function() {
            var $item = $(this).closest('.item');

            $('#searchServiceModal').data('item', $item).modal({
                backdrop: 'static'
            });
            $('#searchServiceModal .modal-body').empty().append($('<p>' + Translator.trans('Loading data...') + '</p>')).load(Routing.generate('app_offers_searchservice'));
        });

        $('body').on('change', '.item.item-service select[name$="[model]"]', function() {
            var $item = $(this).closest('.item'),
                option = this.options[this.selectedIndex],
                $nightsControl = $item.find('input[name$="[nights]"]');

            if (parseInt($(option).data('has-nights')) === 1) {
                $nightsControl.parent().show();
            } else {
                $nightsControl.parent().hide();
            }
        });

        $('.item.item-service select[name$="[model]"]').trigger('change');

        +(function($) {
            var getFloat = function(val) {
                return isNaN(parseFloat(val)) ? 0 : parseFloat(val);
            };

            var updateTotalExpenses = function() {
                var total = new Number(0);
                $('.item-service input[name$="[supplierPrice]"]').each(function() {
                    total += getFloat($(this).val());
                });

                $('#offer_form_totalExpenses').val(total.toFixed(2)).trigger('change');
            }

            //Todos los totales de item-service
            $('#offer_form_services').on('change', '.item-service input[name$="[supplierPrice]"]', function() {
                updateTotalExpenses();
            });

            //Inputs de un cargo administrativo
            $('.item-administrative-charge').on('change', 'input', function() {
                if (!$(this).is('[name$="[pax]"], [name$="[nights]"], [name$="[price]"]')) {
                    return;
                }

                var total = new Number();
                $(this).closest('.item').find('[name$="[pax]"], [name$="[nights]"]').each(function() {
                    total += getFloat($(this).val()) * getFloat($(this).closest('.item').find('input[name$="[price]"]').val());
                });

                $(this).closest('.item').find('input[name$="[total]"]').val(total.toFixed(2)).trigger('change');
            });

            var updateTotalCharges = function() {
                var $total = $('#offer_form_totalCharges'),
                    $items = $('.item-administrative-charge input[name$="[total]"]'),
                    total = new Number(0);

                $items.each(function() {
                    total += getFloat($(this).val());
                });

                $total.val(total.toFixed(2)).trigger('change');
            }

            //Totales de cargos administrativos
            $('.item-administrative-charge').on('change', 'input[name$="[total]"]', function() {
                updateTotalCharges();
            });

            //Las line charges
            $('#offer_form_totalExpenses, #offer_form_totalCharges, #offer_form_percentApplied_percent, #offer_form_percentApplied_plus').on('change', function() {
                var $controls = $('#offer_form_totalExpenses, #offer_form_totalCharges, #offer_form_percentApplied_percent, #offer_form_percentApplied_plus');

                var sum = getFloat($('#offer_form_totalExpenses').val()),
                    sum2 = getFloat($('#offer_form_totalCharges').val()),
                    $plus = $('#offer_form_percentApplied_percent'), charge;

                if ($plus.val() === 'plus') {
                    charge = new Number(sum + sum2 + getFloat($('#offer_form_percentApplied_plus').val()));
                } else {
                    charge = new Number(sum * ($plus.val() / 100) + sum + sum2);
                }

                $('#offer_form_clientCharge').val(charge.toFixed(2));
            });

            $('#offer_form_totalExpenses').trigger('change');

            $('#offer_form_percentApplied_percent').on('change', function() {
                if ($(this).val() !== 'plus') {
                    $('#offer_form_percentApplied_plus').val(0);
                }
            });

            updateTotalExpenses();
            updateTotalCharges();
        }(jQuery));
    }

    return {
        init: function() {
            init();
            initCollections();
            initValidation();
        }
    }
}();
