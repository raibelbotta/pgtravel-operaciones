App = typeof App !== 'undefined' ? App : {};
App.Suppliers = typeof App.Suppliers !== 'undefined' ? App.Suppliers : {};

+(App.Suppliers.Form = function($) {
    "use strict";

    var initEmployeeCollection = function() {
        var $container = $('#supplier_form_employees');

        $container.data('index', $container.find('> .item').length);

        $('#btnAddEmployee').on('click', function(event) {
            event.preventDefault();

            var index = $container.data('index'),
                prototype = $container.data('prototype'),
                $item = $(prototype.replace(/form_employees___name___/g, 'form_employees_' + index + '_').replace(/employees\]\[__name__\]/g, 'employees][' + index + ']'));

            $container.append($item);

            App.Forms.initTelephoneControl($item.find('input[type=tel]'));
            $item.find('.collection-emails').data('index', 0);

            $container.data('index', index + 1);
        });

        $('.collection-emails').each(function() {
            $(this).data('index', $(this).find('.item').length);
        });

        $container.on('click', '.btn-add-email', function(event) {
            event.preventDefault();

            var $container = $(this).closest('.row').find('.collection-emails'),
                index = $container.data('index'),
                prototype = $container.data('prototype'),
                $item = $(prototype.replace(/__name__/g, index));

            $container.append($item);

            $container.data('index', index + 1);
        });

        $container.on('click', '.btn-remove-item', function(event) {
            event.preventDefault();

            console.log($(this).parents());
            $(this).closest('.item').fadeOut(function() {
                $(this).remove();
            });
        });
    }

    var initControls = function() {
        App.Forms.initTelephoneControl($('form#supplier input[type=tel]'));

        $('form#supplier select[name$="[place]"]').select2({
            width: '100%',
            minimunInputLength: 1,
            ajax: {
                url: Routing.generate('app_suppliers_getplaces'),
                dataType: 'json',
                delay: 250,
                method: 'GET',
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

    var initValidator = function() {
        $('#supplier').validate({
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
            }
        });
    }

    return {
        init: function() {
            initControls();
            initValidator();
            initEmployeeCollection();
        }
    }
}(jQuery));
