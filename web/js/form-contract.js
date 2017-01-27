App = typeof App !== 'undefined' ? App : {};
App.Contracts = typeof App.Contracts !== 'undefined' ? App.Contracts : {};
App.Contracts.Form = function(){
    var init = function() {
        +(function($) {
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
            });

            $('#contract_form_signedAt').parent().datetimepicker({
                format: 'DD/MM/YYYY'
            });
            $('#contract_form_startAt').parent().datetimepicker({
                format: 'DD/MM/YYYY HH:mm'
            });
            $('#contract_form_endAt').parent().datetimepicker({
                useCurrent: false,
                format: 'DD/MM/YYYY HH:MM'
            });
            $('#contract_form_startAt').parent().on('dp.change', function(event) {
                 $('#contract_form_endAt').parent().data('DateTimePicker').minDate(event.date);
            });
            $('#contract_form_endAt').parent().on('dp.change', function(event) {
                $('#contract_form_startAt').parent().data('DateTimePicker').maxDate(event.date);
            });

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

        $('#contract').validate({
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
            rules: {
                'contract_form[startAt]': {
                    required: {
                        depends: function() {
                            return '' !== $('#contract_form_endAt').val();
                        }
                    }
                },
                'contract_form[endAt]': {
                    required: {
                        depends: function() {
                            return '' !== $('#contract_form_startAt').val();
                        }
                    }
                }
            },
            success: function (label, element) {
                $(element).tooltipster('hide');
            }
        });

        +(function() {
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

            var clickAddItem = function(event) {
                var $container = $(this).parent().parent().find('.collection:first'),
                    prototype = $container.data('prototype'),
                    index = $container.data('index');

                if ($container.hasClass('collection-facilities')) {
                    var $item = $(prototype.replace(/facilities___name__/g, 'facilities_' + index).replace(/facilities\]\[__name__/g, 'facilities][' + index));
                } else {
                    var $item = $(prototype.replace(/__name__/g, index));
                }

                 $container.data('index', index + 1);
                 $container.append($item);

                 if ($item.find('.collection').length > 0) {
                    updateContainerIndexes($item);
                 }

                 if ($item.hasClass('item-top-service')) {
                    var $controls = $item.find('.datetimepicker');
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
                 }

                 if ($item.hasClass('item-facility-season')) {
                    var $controls = $item.find('.datetimepicker');
                    $($controls[0]).on('dp.change', function(e) {
                        $($controls[1]).data('DateTimePicker').minDate(e.date);
                    }).datetimepicker({
                        format: 'DD/MM/YYYY'
                    });
                    $($controls[1]).on("dp.change", function(event) {
                        $($controls[0]).data("DateTimePicker").maxDate(event.date);
                    }).datetimepicker({
                        format: 'DD/MM/YYYY',
                        useCurrent: false
                    });
                 }

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

            $('.btn-remove').on('click', clickRemoveItem);

            updateContainerIndexes();

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
            $('.item-facility-season').each(function() {
                var $controls = $(this).find('.datetimepicker');
                $($controls[0]).datetimepicker({
                    format: 'DD/MM/YYYY'
                });
                $($controls[1]).datetimepicker({
                    format: 'DD/MM/YYYY',
                    useCurrent: false
                });
                $($controls[0]).on('dp.change', function(event) {
                    $($controls[1]).data('DateTimePicker').minDate(event.date);
                });
                $($controls[1]).on("dp.change", function(event) {
                    $($controls[0]).data("DateTimePicker").maxDate(event.date);
                });
            });
        }());
    }

    return {
        init: function() {
            init();
        }
    }
}();
