App = typeof App !== 'undefined' ? App : {};

+(App.PrivateHousePrices = function($) {
    "use strict";

    var initControls = function() {
        $('body').on('change', 'input.updatable-ajax', function() {
            var $input = $(this);

            if ($input.data('saving')) {
                $input.data('saving').xhr.abort();
            }

            var id = $.now(),
                paramsStr = $input.data('params'),
                lines = paramsStr.split('|'),
                params = {
                    inputId: id,
                    value: $input.val()
                };
            $.each(lines, function(i, line) {
                var pair = line.split(':');
                params[pair[0]] = pair[1];
            });

            $input.css({borderColor: 'red'}).attr({'ajax-id': id});

            var obj = {
                xhr: $.ajax(Routing.generate('app_contracts_setprivatehouseprice'), {
                    data: params,
                    dataType: 'json',
                    method: 'POST',
                    success: function(json) {
                        $('input:text.updatable-ajax[ajax-id=' + json.inputId + ']').val(json.value).css({borderColor: ''}).removeAttr('ajax-id').removeData('saving');
                    }
                })
            };

            $input.data('saving', obj);
        });
    }

    return {
        init: function() {
            initControls();
        }
    }
}(jQuery));
