App = typeof App !== 'undefined' ? App : {};
App.Clients = typeof App.Clients !== 'undefined' ? App.Clients : {};
App.Clients.Form = function() {
    var init = function() {
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

                App.Forms.initTelephoneControl($item.find('input[type=tel]'));
            });

            $('body').on('click', '.btn-delete-item', function(event) {
                event.preventDefault();

                $(this).closest('.item').fadeOut(function() {
                    $(this).remove();
                    $('input:hidden[name="contactCounter"]').val($container.find('.item').length);
                });
            });
        }());

        App.Forms.initTelephoneControl($('input[type=tel]'));

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
                    min: Translator.trans('Client has no contact person')
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
    }

    return {
        init: function() {
            init();
        }
    }
}();
