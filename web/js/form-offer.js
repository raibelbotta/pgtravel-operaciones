$(document).ready(function() {
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
        $.getJSON(url_getclientcontact + '?client=' + id, function(json) {
            $('#offer_form_notificationContact').append($('<option value=""></option>'));
            $.each(json.elements, function(i, e) {
                $('#offer_form_notificationContact').append($('<option value="' + e.id + '">' + e.text + '</option>'));
            });
        });
    });

    /*
    $('#offer_form_services').find('.datepicker').datetimepicker({
        format: 'DD/MM/YYYY HH:mm'
    });
    */

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

    //collection stuff
    +(function() {
        var linkDatepickers = function(item) {
            var dps = $(item).find('input.datepicker');
            $(dps[0]).parent().datetimepicker({
                format: 'DD/MM/YYYY HH:mm'
            });
            $(dps[1]).parent().datetimepicker({
                format: 'D/MM/YYYY HH:mm',
                useCurrent: false
            });
            $(dps[0]).parent().on('dp.change', function(e) {
                $(dps[1]).parent().data("DateTimePicker").minDate(e.date);
            });
            $(dps[1]).parent().on("dp.change", function(e) {
                $(dps[0]).parent().data("DateTimePicker").maxDate(e.date);
            });
        }

        $('.item.item-service').each(function() {
            linkDatepickers(this);
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

            linkDatepickers($item);

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
            var $btn = $(this);

            if ($btn.is('a')) {
                event.preventDefault();
            }

            $btn.closest('.item').fadeOut(function() {
                var $container = $(this).closest('.collection'),
                    $counter = $container.data('counter') ? $($container.data('counter')) : null;
                $(this).remove();
                if ($counter) {
                    $counter.val($counter.val() - 1);
                }
            });
        });
    }());

    //validation stuff
    +(function() {
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
                    min: 'Add a service at least'
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
    }());

    +(function() {
        $('body').on('click', '.btn-search-service', function() {
            var $item = $(this).closest('.item');

            $('#searchServiceModal').data('item', $item).modal({
                backdrop: 'static'
            });
            $('#searchServiceModal .modal-body').empty().append($('<p>Cargando datos...</p>')).load(url_searchservice);
        });
    }());

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
});