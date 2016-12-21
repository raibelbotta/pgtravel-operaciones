$(document).ready(function() {
    $('#reservation_form_services').find('.datepicker').datetimepicker({
        format: 'DD/MM/YYYY HH:mm'
    });

    //collection stuff
    +(function() {
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

            $item.find('.datepicker').datetimepicker({
                format: 'DD/MM/YYYY HH:mm'
            });

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
            ignore: '',
            messages: {
                'servicesCounter': {
                    min: 'Add a service at least'
                }
            },
            rules: {
                'servicesCounter': {
                    min: 1
                },
                'reservation_form[directClientFullName]': {
                    required: {
                        depends: function() {
                            return $('#reservation_form_clientType_1').prop('checked') === true;
                        }
                    }
                },
                'reservation_form[client]': {
                    required: {
                        depends: function() {
                            return $('#reservation_form_clientType_0').prop('checked') === true;
                        }
                    }
                }
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
        $('#btnRecalc').on('click', function() {
            var sum = new Number(0);
            $('.item-service').each(function() {
                sum += parseFloat($(this).find('input[name*="[supplierPrice]"]').val());
            });

            var sum2 = new Number(0);
            $('.item-administrative-charge').each(function() {
                sum2 += parseFloat($(this).find('input[name*="[price]"]').val());
            });

            $('#reservation_form_clientCharge').val((new Number(sum * 0.3 + sum + sum2)).toFixed(2));
        });
    }(jQuery));

    +(function($) {
        $('body').on('click', 'button.btn-calc-ads', function() {
            var $item = $(this).closest('.item'),
                $pax = $item.find('input[name*="[factor]"]'),
                $base = $item.find('input[name*="[base]"]'),
                $price = $item.find('input[name*="[price]"]'),
                price = (new Number(parseFloat($pax.val() ? $pax.val() : 0) * parseFloat($base.val() ? $base.val() : 0))).toFixed(2);

            $price.val(price);
        });
    }(jQuery));
});