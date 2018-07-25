App = typeof App !== 'undefined' ? App : {};

+(App.PrivateHousePrices = function($) {
    "use strict";

    var initControls = function() {
        $('a.link-edit-notes').on('click', function(event) {
            event.preventDefault();

            var input = $('input:text[data-params="' + $(this).data('params') + '"]'),
                modal = $(this).closest('tr').find('.modal'),
                url = $(this).attr('href');

            modal.find('.modal-body').empty();
            modal.find('.modal-footer button.btn-primary').hide();

            if (($.trim(input.val()) === '') || !$.isNumeric(input.val())) {
                swal({
                    title: 'Notes without price',
                    text: 'You must set a price for this record before write notes',
                    type: 'warning'
                });

                return;
            }

            modal.data('url', url);
            modal.modal();
        });

        $('.modal-notes').on('shown.bs.modal', function() {
            var modal = $(this);

            modal.find('.modal-body')
                .load($(this).data('url'), function() {
                    modal.find('.modal-footer button.btn-primary').fadeIn();
                })
                .block({
                    message: Translator.trans('Loading data...')
                });
        });

        $('button.btn-submit-note').on('click', function() {
            $(this).attr('disabled', 'disabled');
            $(this).closest('form').submit();
        });

        $('.modal-notes form').each(function() {
            var form = $(this);
            $(this).ajaxForm({
                target: form.find('.modal-body'),
                success: function() {
                    form.find('button.btn-primary').removeAttr('disabled');
                }
            });
        });
    };

    return {
        init: function() {
            initControls();
        }
    }
}(jQuery));
