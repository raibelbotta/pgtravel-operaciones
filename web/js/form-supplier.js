App = typeof App !== 'undefined' ? App : {};
App.Suppliers = typeof App.Suppliers !== 'undefined' ? App.Suppliers : {};

+(App.Suppliers.Form = function($) {
    var initControls = function() {
        App.Forms.initTelephoneControl($('form#supplier input[type=tel]'));

        $('form#supplier .collection-employees').on('item-added.app', function(event, data) {
            App.Forms.initTelephoneControl($(data.item).find('input[type=tel]'));
        });

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
        }
    }
}(jQuery));
