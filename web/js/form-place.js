App = typeof App !== 'undefined' ? App : {};
App.Places = typeof App.Places !== 'undefined' ? App.Places : {};

+(App.Places.Form = function($) {
    var initValidator = function() {
        $('form#place').validate({
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
            initValidator();
        }
    }
}(jQuery));
