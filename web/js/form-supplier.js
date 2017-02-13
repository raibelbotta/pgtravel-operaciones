App = typeof App !== 'undefined' ? App : {};
App.Suppliers = typeof App.Suppliers !== 'undefined' ? App.Suppliers : {};

App.Suppliers.Form = function() {
    var initControls = function() {
        App.Forms.initTelephoneControl($('form#supplier input[type=tel]'));

        $('form#supplier .collection-employees').on('item-added.app', function(event, data) {
            App.Forms.initTelephoneControl($(data.item).find('input[type=tel]'));
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
}();
