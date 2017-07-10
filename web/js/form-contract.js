App = typeof App !== 'undefined' ? App : {};
App.Contracts = typeof App.Contracts !== 'undefined' ? App.Contracts : {};

+(App.Contracts.Form = function($){

    var initValidator = function() {
        $('#contract').validate({
            'errorPlacement': function(error, element) {
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
            rules: {
                'contract_form[startAt]': {
                    'required': {
                        'depends': function() {
                            return '' !== $('#contract_form_endAt').val();
                        }
                    }
                },
                'contract_form[endAt]': {
                    'required': {
                        'depends': function() {
                            return '' !== $('#contract_form_startAt').val();
                        }
                    }
                }
            },
            success: function (label, element) {
                $(element).tooltipster('hide');
            }
        });
    }

    var initControls = function() {
        $('#contract_form_model').on('change', function() {
            var model = $(this).val();
            $('.visible-condition').each(function() {
                if ($(this).hasClass('visible-condition-' + model)) {
                    $(this).show();
                } else {
                    $(this).hide();
                    $(this).find('.collection:first').data('index', 0).empty();
                }
            });
            $('.hiddeable-condition').each(function() {
                if ($(this).hasClass('hiddeable-condition-' + model)) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
            $('.visible-condition-only').each(function() {
                if ($(this).hasClass('visible-condition-only-' + model)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $('#contract_form_signedAt').parent().datetimepicker({
            format: 'DD/MM/YYYY'
        });
        $('#contract_form_startAt').parent().datetimepicker({
            format: 'DD/MM/YYYY'
        });
        $('#contract_form_endAt').parent().datetimepicker({
            useCurrent: false,
            format: 'DD/MM/YYYY'
        });
        $('#contract_form_startAt').parent().on('dp.change', function(event) {
             $('#contract_form_endAt').parent().data('DateTimePicker').minDate(event.date);
        });
        $('#contract_form_endAt').parent().on('dp.change', function(event) {
            $('#contract_form_startAt').parent().data('DateTimePicker').maxDate(event.date);
        });
    }

    var init = function() {
        +(function($) {
            $('.item-top-service').each(function() {
                var $controls = $(this).find('.datetimepicker');
                $($controls[0]).datetimepicker({
                    format: 'DD/MM/YYYY HH:mm'
                });
                $($controls[1]).datetimepicker({
                    format: 'DD/MM/YYYY HH:mm',
                    useCurrent: false
                });
                $($controls[0]).on("dp.change", function(event) {
                    $($controls[1]).data("DateTimePicker").minDate(event.date);
                });
                $($controls[1]).on("dp.change", function(event) {
                    $($controls[0]).data("DateTimePicker").maxDate(event.date);
                });
            });
        }(jQuery));

        +(function() {
            var initServiceDates = function($item) {
                $item.find('.date:has(.datepicker):first').datetimepicker({
                    format: 'DD/MM/YYYY'
                });
                $item.find('.date:has(.datepicker):last').datetimepicker({
                    format: 'DD/MM/YYYY',
                    useCurrent: false
                });
                $item.find('.date:has(.datepicker):first').on("dp.change", function(event) {
                    $item.find('.date:has(.datepicker):last').data("DateTimePicker").minDate(event.date);
                });
                $item.find('.date:has(.datepicker):last').on("dp.change", function(event) {
                    $item.find('.date:has(.datepicker):first').data("DateTimePicker").maxDate(event.date);
                });
            }

            var clickRemoveItem = function(event) {
                event.preventDefault();

                $(this).closest('.item').remove();
            }

            var updateContainerIndexes = function(object) {
                if (typeof object === 'undefined') {
                    object = $('body');
                }
                $(object).find('.collection').each(function() {
                    var $container = $(this);
                    $container.data('index', $container.find('>.item').length);
                });
            }

            var clickAddItem = function() {
                var $container = $(this).data('collection') ? $($(this).data('collection')) : $(this).closest(':has(.collection)').find('.collection:first'),
                    prototype = $container.data('prototype'),
                    index = $container.data('index');

                if ($container.hasClass('collection-facilities')) {
                    var $item = $(prototype
                        .replace(/facilities___name__/g, 'facilities_' + index)
                        .replace(/facilities\]\[__name__/g, 'facilities][' + index));
                } else if ($container.hasClass('collection-car-rental-seassons')) {
                    var $item = $(prototype
                        .replace(/carRentalSeassons___name__/g, 'carRentalSeassons_' + index)
                        .replace(/carRentalSeassons\]\[__name__/g, 'carRentalSeassons][' + index));
                } else {
                    var $item = $(prototype.replace(/__name__/g, index));
                }

                $container.data('index', index + 1);
                $container.append($item);

                if ($item.find('.collection').length > 0) {
                    updateContainerIndexes($item);
                }

                initServiceDates($item);

                $($item.find('input:text:visible, select:visible')[0]).focus();
            }

            var clickCloneItem = function(event) {
                var $item = $(this).closest('.item'),
                    $container = $item.closest('.collection'),
                    index = $container.data('index');

                $newItem = $item.clone();
                $item.closest('.collection').append($newItem);

                $newItem.find('[id][name]').each(function() {
                    var $e = $(this);
                    if (/facilities_\d+_/.test($e.attr('id'))) {
                        var oldId = $e.attr('id'),
                            newId = oldId.replace(/facilities_\d+/, 'facilities_' + index);
                        $e.attr('id', newId);
                        $('label[for="' + oldId + '"]').attr('for', newId);
                        $e.attr('name', $e.attr('name').replace(/facilities\]\[\d+/, 'facilities][' + index));
                    }
                });

                var $cXd = $newItem.find('.collection.collection-seasons');
                $cXd.data('prototype', $cXd.data('prototype').replace(/facilities_\d+/g, 'facilities_' + index).replace(/facilities\]\[\d+/g, 'facilities][' + index));
                console.log($cXd.data('prototype'));

                $container.data('index', index + 1);

                if ($newItem.find('.collection').length > 0) {
                    updateContainerIndexes($newItem);
                }

                $newItem.find('.input-daterange').each(function() {
                    var $container = $(this);
                    $container.datepicker({
                        inputs: $container.find('input:text').toArray()
                    });
                    $container.find('input:text:first').on('change', function() {
                        $(this).closest('.item').find('input:text:last').datepicker('setDate', $(this).val());
                    });
                });
            }

            $('body').on('click', '.btn-add-item', clickAddItem);
            $('body').on('click', '.btn-remove', clickRemoveItem);
            $('body').on('click', '.btn-clone-item', clickCloneItem);

            $('body').on('click', '.btn-create-rest-seasons', function() {
                var $btnAddItem = $(this).parent().find('button.btn-add-item'),
                    $collection = $(this).closest(':has(.collection)').find('.collection:first'),
                    laps = [];
                $collection.find('.item').each(function() {
                    var $item = $(this);
                    laps.push({
                        'from': moment($item.find('input:text:first').val(), 'DD/MM/YYYY', true),
                        'to': moment($item.find('input:text:last').val(), 'DD/MM/YYYY', true)
                    });
                });

                var newLaps = [],
                    startAt = moment($('#contract_form_startAt').val(), 'DD/MM/YYYY', true),
                    endAt = moment($('#contract_form_endAt').val(), 'DD/MM/YYYY', true),
                    startedLap = null,
                    found;
                while (startAt.isBefore(endAt, 'day')) {
                    found = false;
                    $.each(laps, function(i) {
                        if (startAt.isSameOrAfter(laps[i].from, 'day') && startAt.isSameOrBefore(laps[i].to, 'day')) {
                            if (null !== startedLap) {
                                newLaps.push({
                                    from: startedLap.clone(),
                                    to: startAt.clone().subtract(1, 'days')
                                });
                                startedLap = null;
                            }
                            startAt = laps[i].to.clone();
                            startAt.add(1, 'days');
                            found = true;
                            return false;
                        }
                    });
                    if (!found) {
                        if (null === startedLap) {
                            startedLap = startAt.clone();
                        }
                        startAt.add(1, 'days');
                    }
                }
                if (null !== startedLap) {
                    newLaps.push({
                        from: startedLap.clone(),
                        to: startAt.clone()
                    });
                }

                if (newLaps.length > 0) {
                    for (var i = 0; i < newLaps.length; i++) {
                        $btnAddItem.trigger('click');
                    }
                    $items = $collection.find('.item');
                    for (var i = $items.length - newLaps.length; i < $items.length; i++) {
                        var $item = $($items[i]);
                        $item.find('input:text:first').val(newLaps[i - ($items.length - newLaps.length)].from.format('DD/MM/YYYY'));
                        $item.find('input:text:last').val(newLaps[i - ($items.length - newLaps.length)].to.format('DD/MM/YYYY'));
                    }
                }
            });

            updateContainerIndexes();

            $('.item-top-service, .item-facility-season, .item-private-house-service, .item-car-rental-seasson-date').each(function() {
                initServiceDates($(this));
            });
        }());
    }

    return {
        init: function() {
            initValidator();
            initControls();
            init();
        }
    }
}(jQuery));
