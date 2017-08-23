App = typeof App !== 'undefined' ? App : {};
App.Notifications = typeof App.Notifications !== 'undefined' ? App.Notifications : {};

+(App.Notifications.Index = function($) {
    var $table = $('#datatable-x');

    var init = function() {
        $table.on('click', '.btn-change-state', function(event) {
            event.preventDefault();

            $(this).attr('disabled', 'disabled');
            $('#modalConfirm').data('process', $(this));

            $('#modalConfirm .modal-content').load($(this).attr('href'), function() {
                $('#modalConfirm').modal();
                $table.find('.btn[disabled]').removeAttr('disabled');

                $('#modalConfirm form').ajaxForm({
                    beforeSuccess: function() {
                        $('#modalConfirm button.btn-primary').attr('disabled', 'disabled');
                    },
                    success: function(json) {
                        $('#modalConfirm').modal('hide');

                        $($('#modalConfirm').data('process')).parent().text(Translator.trans('Updating...'));
                        $('#modalConfirm').removeData('process');

                        $table.dataTable().api().draw(true);
                    }
                });
            });
        });

        $table.dataTable({
            order: [[5, 'asc']],
            aoColumns: [
                {name: 'name'},
                {name: 'operator'},
                {name: 'client'},
                {name: 'supplier'},
                {name: 'service'},
                {name: 'startAt', searchable: false},
                {name: 'endAt', searchable: false},
                {name: 'reference'},
                {sortable: false, searchable: false}
            ],
            processing: true,
            serverSide: true,
            ajax: {
                method: 'post',
                url: Routing.generate('app_notifications_getdata'),
                data: function(params) {
                    var filter = [];
                    $.each($('form#filter').serializeArray(), function(i, e) {
                        filter[e['name']] = e['value'];
                    });

                    return $.extend({}, params, filter);
                }
            }
        });
    }

    var initFilter = function() {
        $('form#filter select').on('change', function() {
            $table.dataTable().api().draw();
        });
    }

    return {
        init: function() {
            init();
            initFilter();
        }
    }
}(jQuery));
