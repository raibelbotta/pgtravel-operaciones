App = typeof App !== 'undefined' ? App : {};
App.Bookings = typeof App.Bookings !== 'undefined' ? App.Bookings : {};

+(App.Bookings.Form = function($) {
    "use strcit";
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

        $('#offer_form_client').select2({
            width: '100%'
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

        App.Forms.initTelephoneControl($('#offer_form_directClientMobilePhone'));

        $('body').on('click', '.btn-search-service', function() {
            var $item = $(this).closest('.item');

            $('#searchServiceModal').remove();
            var $m = $('<div id="searchServiceModal" class="modal fade in"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">' + Translator.trans('Search service') + '</h4></div><div class="modal-body"></div><div class="modal-footer"><button type="button" data-dismiss="modal" class="btn btn-default">' + Translator.trans('Close') + '</button></div></div></div></div>').appendTo($('body'));
            $m.data('item', $item).modal({
                backdrop: 'static'
            });
            $m.find('.modal-body').empty().append($('<p>' + Translator.trans('Loading data...') + '</p>')).load(Routing.generate('app_offers_searchservice'), initSearchBox);
        });

        $('body').on('change', '.item.item-service select[name$="[model]"]', function() {
            var $item = $(this).closest('.item'),
                option = this.options[this.selectedIndex];

            var options = $(option).data('options') ? $(option).data('options').split(' ') : [];
            $.each(options, function(i) {
                var className = options[i];
                if (className.substr(0, 1) === '-') {
                    $item.find('.visible-' + className.substr(1)).hide();
                } else {
                    $item.find('.visible-' + (className.substr(0, 1) == '+' ? className.substr(1) : className)).show();
                }
            });

            $item.find('input[name$="[cost]"]').trigger('change');
        });

        $('.item.item-service select[name$="[model]"]').trigger('change');

        +(function() {
            var getFloat = function(val) {
                return isNaN(parseFloat(val)) ? 0 : parseFloat(val);
            };

            var updateExpense = function(item) {
                var $item = $(item),
                    $nights = $item.find('input[name$="[nights]"]'),
                    $pax = $item.find('input[name$="[pax]"]'),
                    $price = $item.find('input[name$="[cost]"]'),
                    $total = $item.find('input[name$="[totalPrice]"]'),
                    nights = $nights.is(':visible') ? getFloat($nights.val()) : 0,
                    pax = $pax.is(':visible') ? getFloat($pax.val()) : 0,
                    total = nights * getFloat($price.val()) + pax * getFloat($price.val());

                $total.val(total.toFixed(2));
            }

            var updateTotalExpenses = function() {
                var total = new Number(0);
                $('.item-service input[name$="[totalPrice]"]').each(function() {
                    total += getFloat($(this).val());
                });

                $('#offer_form_totalExpenses').val(total.toFixed(2)).trigger('change');
            }

            $('#offer_form_services').on('change', 'input[name$="[nights]"], input[name$="[pax]"], input[name$="[cost]"]', function() {
                updateExpense($(this).closest('.item-service'));
            });

            //Todos los totales de item-service
            $('#offer_form_services').on('change', '.item-service input[name$="[totalPrice]"]', function() {
                updateTotalExpenses();
            });

            //Inputs de un cargo administrativo
            $('.item-administrative-charge').on('change', 'input', function() {
                if (!$(this).is('[name$="[pax]"], [name$="[multiplier]"], [name$="[price]"]')) {
                    return;
                }

                var $item = $(this).closest('.item'),
                    total = getFloat($item.find('input[name$="[multiplier]"]').val())
                        * getFloat($item.find('input[name$="[pax]"]').val())
                        * getFloat($item.find('input[name$="[price]"]').val());

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
        }());
    }

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

        var initPlaces = function(item) {
            $(item).find('select[name$="[origin]"], select[name$="[destination]"]').select2({
                minimunInputLength: 1,
                width: '100%',
                ajax: {
                    url: Routing.generate('app_offers_getplaces'),
                    dataType: 'json',
                    delay: 250,
                    method: 'GET',
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page
                        }
                    },
                    processResults: function (json) {
                        var data = json.data;
                        data.unshift({id: 0, text: ''});
                        return {
                            results: data
                        };
                    }
                }
            });
        }

        var initSupplier = function(item) {
            $(item).find('select[name$="[supplier]"]').select2({
                width: '100%'
            });
        }

        $('.item.item-service').each(function() {
            initDatepickers(this);
            initPlaces(this);
            initSupplier(this);
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
                $counter.val(parseInt($counter.val(), 10) + 1);
            }
            $container.append($item);

            if ($item.is('.item-service')) {
                if ($container.find('.item-service').length > 1) {
                    var $prev = $item.prev();
                    for (var i = 0; i < 2; i++) {
                        if ($prev.find('.datepicker:eq(' + i + ')').val() !== '') {
                            $item.find('.datepicker:eq(' + i + ')').val($prev.find('.datepicker:eq(' + i + ')').val());
                        }
                    }
                }
                $item.find('select[name$="[model]"]').trigger('change');
                initDatepickers($item);
                initPlaces($item);
                initSupplier($item);
            }


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
            'errorPlacement': function(error, element) {
                if (element.is(':hidden')) {
                    element = element.closest(':visible');
                }
                if (!element.data('tooltipster-ns')) {
                    element.tooltipster({
                        'trigger': 'custom',
                        'onlyOne': false,
                        'position': 'bottom-left',
                        'positionTracker': true
                    });
                }
                element.tooltipster('update', $(error).text());
                element.tooltipster('show');
            },
            'ignore': ':hidden:not(input[name="servicesCounter"])',
            'messages': {
                'servicesCounter': {
                    'min': Translator.trans('Add a service at least')
                }
            },
            'rules': {
                'servicesCounter': {
                    'min': 1
                },
                'offer_form[directClientFullName]': {
                    'required': {
                        'depends': function() {
                            return $('#offer_form_clientType_1').prop('checked') === true;
                        }
                    }
                },
                'offer_form[client]': {
                    'required': {
                        'depends': function() {
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

    var initSearchBox = function() {
        var $searchBox = $('#searchServiceModal');

        $searchBox.on('preDraw.dt', 'table', function() {
            $(this).parent().block({
                'message': Translator.trans('Loading services...')
            });
        }).on('draw.dt', 'table', function() {
            $(this).parent().unblock();
        });

        var utils = {
            initDatepickers: function($date0, $date1, callback) {
                var options = {
                    format: $date0.data('format') ? $date0.data('format') : 'DD/MM/YYYY',
                    showClear: true,
                    showTodayButton: true
                };

                $date0.datetimepicker(options);
                $date1.datetimepicker($.extend({}, options, {
                    useCurrent: false
                }));
                $date0.on('dp.change', function(e) {
                    $date1.data("DateTimePicker").minDate(e.date);
                    callback();
                });
                $date1.on("dp.change", function(e) {
                    $date0.data("DateTimePicker").maxDate(e.date);
                    callback();
                });
            },
            translateResults: function($item, values) {
                var gControl = function(fname) {
                    return $item.find('[name$="[' + fname + ']"]');
                };

                gControl('model').val(values.model).trigger('change');

                $.each(values, function(name, value) {
                    gControl(name).val(value);
                });
            }
        }


        var initHotelControls = function() {
            //Este está hecho con un datatable dinámico (serverSide: true)
            var $tab = $searchBox.find('#tab-hotel'),
                $table = $tab.find('table.table-results');

            $table.dataTable({
                serverSide: true,
                ajax: {
                    url: Routing.generate('app_offers_gethotelprices'),
                    data: function(baseData) {
                        return $.extend({}, baseData, {
                            'filter': {
                                'from': $tab.find('input:text.datepicker:first').val(),
                                'to': $tab.find('input:text.datepicker:last').val(),
                                'pax': $tab.find('select[name$="[pax]"]').val(),
                                'plan': $tab.find('select[name$="[plan]"]').val(),
                                'quantity': $tab.find('select[name$="[quantity]"]').val()
                            }
                        });
                    },
                    method: 'POST'
                },
                aoColumns: [
                    {name: 'hotel', title: Translator.trans('Hotel')},
                    {name: 'room', title: Translator.trans('Room')},
                    {name: 'seasson', title: Translator.trans('Season')},
                    {name: 'plan', title: Translator.trans('Plan')},
                    {name: 'pax', title: Translator.trans('Pax')},
                    {name: 'price', title: Translator.trans('Price')},
                    {name: 'total', title: Translator.trans('Total')},
                    {sortable: false, searchable: false, width: '40px'}
                ]
            });

            var updateResults = function() {
                $table.dataTable().api().draw();
            }

            $table.on('click', 'button.btn-select-service', function() {
                var data = $(this).data('service');

                $searchBox.modal('hide');

                var $item = $searchBox.data('item');

                utils.translateResults($item, {
                    'model': 'hotel',
                    'startAt': $tab.find('input.datepicker:first').val() + ' 12:00',
                    'endAt': $tab.find('input.datepicker:last').val() + ' 16:00',
                    'name': data.serviceName,
                    'facilityName': data.hotel,
                    'nights': data.nights,
                    'supplier': data.supplier.id,
                    'pax': data.pax,
                    'supplierNotes': data.plan,
                    'cost': data.cost,
                    'totalPrice': data.totalPrice
                });

                $item.find('select[name$="[supplier]"]').trigger('change.select2');
                $item.find('input[name$="[totalPrice]"]').trigger('change');
            });

            $tab.find('form .date:first').on('dp.change', function(e) {
                var from = e.date ? e.date.clone() : null,
                    to = $tab.find('form input.datepicker:last').val() ? moment($tab.find('form input.datepicker:last').val(), 'DD/MM/YYYY') : null;

                if (!from || !to) {
                    $tab.find('form input[name=nights]').val('');
                } else {
                    var nights = to.startOf('day').diff(from.startOf('day'), 'days');
                    $tab.find('form input[name=nights]').val(nights);
                }
            });
            $tab.find('form .date:last').on('dp.change', function(e) {
                var from = $tab.find('form input.datepicker:first').val() ? moment($tab.find('form input.datepicker:first').val(), 'DD/MM/YYYY') : null,
                    to = e.date ? e.date.clone() : null;

                if (!from || !to) {
                    $tab.find('form input[name=nights]').val('');
                } else {
                    var nights = to.startOf('day').diff(from.startOf('day'), 'days');
                    $tab.find('form input[name=nights]').val(nights);
                }
            });
            $tab.find('input[name=nights]').on('change', function() {
                var from = $tab.find('form input.datepicker:first').val() ? moment($tab.find('form input.datepicker:first').val(), 'DD/MM/YYYY') : null,
                    nights = $(this).val();

                if ('' === nights) {
                    $tab.find('form input.datepicker:last').val('');
                } else {
                    from.add(nights, 'days');
                    $tab.find('form input.datepicker:last').val(from.format('DD/MM/YYYY'));
                }
                updateResults();
            });

            utils.initDatepickers($tab.find('input.datepicker:first').parent(), $tab.find('input.datepicker:last').parent(), updateResults);

            $tab.find('select').on('change', function() {
                updateResults();
            });

            $searchBox.find('a[role=tab][aria-controls=tab-hotel]').on('shown.bs.tab', function() {
                updateResults();
            });
        }

        var initPrivateHouseControls = function() {
            $tab = $searchBox.find('#tab-private-house');

            var updateResults = function() {
                var $dv = $tab.find('.table-responsive'),
                    data = {
                        'from': $tab.find('input:text.datepicker:first').val(),
                        'to': $tab.find('input:text.datepicker:last').val(),
                        'quantity': $tab.find('select[name="quantity"]').val(),
                        'address': $tab.find('input[name="address"]').val(),
                        'plan': $tab.find('select[name="plan"]').val(),
                        'province': $tab.find('select[name=province]').val()
                    };
                $dv.empty().append(Translator.trans('Loading services...')).load(Routing.generate('app_offers_getprivatehouseprices'), data, function() {
                    $dv.find('table').DataTable();
                });
            }

            $tab.find('select[name=province]').on('change', function() {
                updateResults();
            });
            $tab.find('form .date:first').on('dp.change', function(e) {
                var from = e.date ? e.date.clone() : null,
                    to = $tab.find('form input.datepicker:last').val() ? moment($tab.find('form input.datepicker:last').val(), 'DD/MM/YYYY') : null;

                if (!from || !to) {
                    $tab.find('form input[name=nights]').val('');
                } else {
                    var nights = to.startOf('day').diff(from.startOf('day'), 'days');
                    $tab.find('form input[name=nights]').val(nights);
                }
            });
            $tab.find('form .date:last').on('dp.change', function(e) {
                var from = $tab.find('form input.datepicker:first').val() ? moment($tab.find('form input.datepicker:first').val(), 'DD/MM/YYYY') : null,
                    to = e.date ? e.date.clone() : null;

                if (!from || !to) {
                    $tab.find('form input[name=nights]').val('');
                } else {
                    var nights = to.startOf('day').diff(from.startOf('day'), 'days');
                    $tab.find('form input[name=nights]').val(nights);
                }
            });
            $tab.find('input[name=nights]').on('change', function() {
                var from = $tab.find('form input.datepicker:first').val() ? moment($tab.find('form input.datepicker:first').val(), 'DD/MM/YYYY') : null,
                    nights = $(this).val();

                if ('' === nights) {
                    $tab.find('form input.datepicker:last').val('');
                } else {
                    from.add(nights, 'days');
                    $tab.find('form input.datepicker:last').val(from.format('DD/MM/YYYY'));
                }
                updateResults();
            });

            utils.initDatepickers($tab.find('input.datepicker:first').parent(), $tab.find('input.datepicker:last').parent(), updateResults);

            $tab.find('form#filter-private-house').find('input:not(.datepicker, [name=nights]), select').on('change', function() {
                updateResults();
            });

            $searchBox.find('a[role=tab][aria-controls=tab-private-house]').on('shown.bs.tab', function() {
                updateResults();
            });

            $tab.on('click', 'button.btn-select-service', function() {
                var data = $(this).data('service'),
                    $item = $searchBox.data('item');

                utils.translateResults($item, {
                    'model': 'private-house',
                    'startAt': $tab.find('input.datepicker:first').val() + ' 12:00',
                    'endAt': $tab.find('input.datepicker:last').val() + ' 16:00',
                    'name': data.service,
                    'supplier': data.supplier.id,
                    'facilityName': data.supplier.name,
                    'nights': data.nights,
                    'pax': 1,
                    'cost': data.cost,
                    'totalPrice': data.totalPrice,
                    'supplierNotes': data.supplierNotes
                });

                $item.find('[name$="[supplier]"]').trigger('change.select2');
                $item.find('[name$="[totalPrice]"]').trigger('change');

                $searchBox.modal('hide');
            });
        }

        var initCarRentalControls = function() {
            var $tab = $searchBox.find('#tab-car-rental'),
                updateResults = function() {
                    var $dv = $tab.find('.table-responsive'),
                        data = {
                            'from': $tab.find('input:text.datepicker:first').val(),
                            'to': $tab.find('input:text.datepicker:last').val(),
                            'quantity': $tab.find('select[name="quantity"]').val(),
                            'cartype': $tab.find('select[name="cartype"]').val()
                        };
                    $dv.empty().append(Translator.trans('Loading services...')).load(Routing.generate('app_offers_getcarrentalprices'), data, function() {
                        $dv.find('table').DataTable();
                    });
                }

            utils.initDatepickers($tab.find('input.datepicker:first').parent(), $tab.find('input.datepicker:last').parent(), updateResults);

            $tab.find('form#filter-car-rental').find('input:not(.datepicker), select').on('change', function() {
                updateResults();
            });

            $searchBox.find('a[role=tab][aria-controls=tab-car-rental]').on('shown.bs.tab', function() {
                updateResults();
            });

            $tab.on('click', 'button.btn-select-service', function() {
                var data = $(this).data('service'),
                    $item = $searchBox.data('item');

                utils.translateResults($item, {
                    'model': 'car-rental',
                    'startAt': $tab.find('input.datepicker:first').val() + ' 08:00',
                    'endAt': $tab.find('input.datepicker:last').val() + ' 08:00',
                    'name': data.name,
                    'supplier': data.supplier.id,
                    'rentCar': data.carType,
                    'cost': data.cost,
                    'totalPrice': data.totalPrice
                });

                $item.find('[name$="[supplier]"]').trigger('change.select2');
                $item.find('[name$="[totalPrice]"]').trigger('change');

                $searchBox.modal('hide');
            });
        }

        var initTransportControls = function() {
            var $tab = $searchBox.find('#tab-transport'),
                initTable = function() {
                    $tab.find('.table-results').dataTable({
                        serverSide: true,
                        aoColumns: [
                            {name: 'service', 'title': Translator.trans('Service')},
                            {name: 'supplier', 'title': Translator.trans('Supplier')},
                            {name: 'price', 'title': Translator.trans('Price')},
                            {name: 'total', 'title': Translator.trans('Total')},
                            {'sortable': false, searchable: false, width: '40px'}
                        ],
                        ajax: {
                            url: Routing.generate('app_offers_gettransportprices'),
                            method: 'POST',
                            data: function(baseData) {
                                return $.extend({}, baseData, {
                                    filter: {
                                        'from': $tab.find('input:text.datepicker:first').val(),
                                        'to': $tab.find('input:text.datepicker:last').val(),
                                        'quantity': $tab.find('select[name="quantity"]').val(),
                                        'addhalfday': $tab.find('input:checkbox').prop('checked') == true ? 1 : 0
                                    }
                                });
                            }
                        }
                    });
                },
                updateResults = function() {
                    if ($tab.find('.table-results tbody').length > 0) {
                        $tab.find('table.table-results').dataTable().api().draw();
                    } else {
                        initTable();
                    }
                }

            $tab.find('form input:checkbox').iCheck({
                checkboxClass: 'icheckbox_flat-green'
            });
            $tab.find('form input:checkbox').on('ifChanged', function() {
                updateResults();
            });

            utils.initDatepickers($tab.find('input.datepicker:first').parent(), $tab.find('input.datepicker:last').parent(), updateResults);

            $tab.find('form#filter-transport').find('input:not(.datepicker), select').on('change', function() {
                updateResults();
            });

            $searchBox.find('a[role=tab][aria-controls=tab-transport]').on('shown.bs.tab', function() {
                updateResults();
            });

            $tab.on('click', 'button.btn-select-service', function() {
                var data = $(this).data('service'),
                    $item = $searchBox.data('item');

                utils.translateResults($item, {
                    'model': 'transport',
                    'startAt': $tab.find('input.datepicker:first').val() + ' 08:00',
                    'endAt': $tab.find('input.datepicker:last').val() + ' 08:00',
                    'name': data.name,
                    'supplier': data.supplier.id,
                    'cost': data.cost,
                    'totalPrice': data.totalPrice
                });

                $item.find('[name$="[supplier]"]').trigger('change.select2');
                $item.find('[name$="[totalPrice]"]').trigger('change');

                $searchBox.modal('hide');
            });
        }

        var initGeneralControls = function() {
            $searchBox.find('.tab-content .tab-pane:not(#tab-hotel, #tab-private-house, #tab-car-rental, #tab-transport)').each(function() {
                var $tab = $(this),
                    model = $tab.attr('id').replace(/^tab\-/, ''),
                    updateResults = function() {
                        var data = {
                            'model': model,
                            'from': $tab.find('input.datepicker:first').val(),
                            'to': $tab.find('input.datepicker:last').val(),
                            'quantity': $tab.find('select[name=quantity]').val()
                        }

                        $tab.find('.table-responsive.results').empty().text(Translator.trans('Loading services...')).load(Routing.generate('app_offers_getgeneralserviceprices'), data, function() {
                            $tab.find('.table-responsive.results table').DataTable({});
                        });
                    }

                utils.initDatepickers($tab.find('.date:has(.datepicker):first'), $tab.find('.date:has(.datepicker):last'), updateResults);

                $tab.find('form').find('select, input:not(.datepicker)').on('change', function() {
                    updateResults();
                });

                $tab.on('click', 'button.btn-select-service', function() {
                    var data = $(this).data('service'),
                        $item = $searchBox.data('item');

                    $searchBox.modal('hide');

                    utils.translateResults($item, {
                        'model': model,
                        'startAt': $tab.find('input.datepicker:first').val(),
                        'endAt': $tab.find('input.datepicker:last').val(),
                        'name': data.serviceName,
                        'supplier': data.supplier.id,
                        'pax': data.pax,
                        'cost': data.cost,
                        'totalPrice': data.totalPrice
                    });

                    $item.find('[name$="[supplier]"]').trigger('change.select2');
                    $item.find('[name$="[totalPrice]"]').trigger('change');
                });

                $searchBox.find('a[role=tab][aria-controls=tab-' + model + ']').on('show.bs.tab', function() {
                    updateResults();
                });
            });
        }

        initHotelControls();
        initPrivateHouseControls();
        initCarRentalControls();
        initTransportControls();
        initGeneralControls();

        +(function() {
            var model = $searchBox.data('item').find('select[name$="[model]"]').val();
            $searchBox.find('.nav.nav-tabs a[href="#tab-' + model + '"]').tab('show');
        }());
    }

    return {
        init: function() {
            init();
            initCollections();
            initValidation();
        }
    }
}(jQuery));
