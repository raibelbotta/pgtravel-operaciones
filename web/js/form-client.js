$(document).ready(function() {
    +(function() {
        $('.collection').each(function() {
            var $container = $(this),
                index = $container.find('> .item').length;
            $container.data('index', index);
        });

        $('body').on('click', '.btn-add-item', function(event) {
            event.preventDefault();

            var $container = $(this).parent().parent().find('.collection'),
                prototype = $container.data('prototype'),
                index = $container.data('index'),
                $item = $(prototype.replace(/__name__/g, index));

            $container.append($item);

            $item.find('input:text:first').focus();

            $container.data('index', index + 1);
            $('input:hidden[name="contactCounter"]').val($('#client_form_contacts').find('.item').length);

            $item.find('input[type=tel]').intlTelInput({
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
        });

        $('body').on('click', '.btn-delete-item', function(event) {
            event.preventDefault();

            $(this).closest('.item').fadeOut(function() {
                $(this).remove();
                $('input:hidden[name="contactCounter"]').val($container.find('.item').length);
            });
        });
    }());

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

    $('form#client').validate({
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
        messages: {
            "contactCounter": {
                min: 'Client has no contact person'
            }
        },
        rules: {
            "contactCounter": {
                min: 1
            }
        },
        ignore: ':hidden:not(input[name=contactCounter])',
        success: function (label, element) {
            if ($(element).is(':hidden')) {
                element = $(element).closest(':visible');
            }
            $(element).tooltipster('hide');
        }
    });
});
