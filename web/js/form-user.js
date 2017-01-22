App = typeof App !== 'undefined' ? App : {};
App.Users = typeof App.Users !== 'undefined' ? App.Users : {};
App.Users.Form = function() {
    var init = function() {
        $('input[type=tel]').intlTelInput({
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
            preferredCountries: ['cu'],
            utilsScript: url_utilScript
        });
        
        +(function() {
            $.validator.addMethod('strongpassword', function(value, element) {
                return this.optional(element) || (/[A-Z]/.test(value) && /[a-z]/.test(value) && /[0-9]/.test(value) && value.length > 7);
            }, Translator.trans('Password strong is too low (8 characters minimun and contains at least 1 character from groups [A-Z], [a-z] and [0-9])'));
            $('#user').validate({
                rules: {
                    '{{ form.plainPassword.first.vars.full_name }}': 'strongpassword',
                    '{{ form.plainPassword.second.vars.full_name }}': {
                        equalTo: '#{{ form.plainPassword.first.vars.id }}'
                    }
                },
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
        }());
    }

    return {
        init: function() {
            init();
        }
    }
}();