App = typeof App !== 'undefined' ? App : {};
App.HotelPrices = typeof App.HotelPrices !== 'undefined' ? App.HotelPrices : {};

+(App.HotelPrices.Form = function($) {
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
                var pair = line.split(':');
                params[pair[0]] = pair[1];
            });

            $input.css({borderColor: 'red'}).attr({'ajax-id': id});

            var obj = {
                xhr: $.ajax(Routing.generate('app_contracts_sethotelprice'), {
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

    var initControls = function() {
        $('body').on('click', 'a.copy-prices', function(event) {
            event.preventDefault();

            var block = $(this).closest('table');
            var fromSeason = $(this).data('from');
            var re = new RegExp('season:' + $(this).data('to') + '\\|');

            block.find('input:text').filter(function() {
                return $(this).data('params') && re.test($(this).data('params'));
            }).each(function() {
                var destination = $(this),
                    fromId = destination.data('params').replace(/season:(\d+)\|/, 'season:' + fromSeason + '|');
                destination.val(block.find('input[data-params="' + fromId + '"]').val()).trigger('change');
            });

        });
    }

    return {
        init: function() {
            init();
            initControls();
        }
    }
}(jQuery));
