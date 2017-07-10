App = typeof App !== 'undefined' ? App : {};
App.CarRentalPrices = typeof App.CarRentalPrices !== 'undefined' ? App.CarRentalPrices : {};

+(App.CarRentalPrices.Form = function($) {
    "use strict";
    var init = function() {
        $('body').on('change', 'input.updatable-ajax', function() {
            var $input = $(this);

            if ($input.data('saving')) {
                $input.data('saving').xhr.abort();
            }

            var id = Date.now(),
                paramsStr = $input.data('params'),
                lines = paramsStr.split('|'),
                params = {
                    inputId: id,
                    value: $input.val()
                };
            $.each(lines, function(i, line) {
                var pair = line.split('=');
                params[pair[0]] = pair[1];
            });

            $input.css({borderColor: 'red'}).attr({'ajax-id': id});

            var obj = {
                xhr: $.ajax(Routing.generate('app_contracts_setcarrentalprice'), {
                    data: params,
                    dataType: 'json',
                    method: 'POST',
                    success: function(json) {
                        $('input.updatable-ajax[ajax-id=' + json.inputId + ']').val(json.value).css({borderColor: ''}).removeAttr('ajax-id').removeData('saving');
                    }
                })
            };

            $input.data('saving', obj);
        });
    }

    return {
        init: function() {
            init();
        }
    }
}(jQuery));
