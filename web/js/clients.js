App = typeof App !== 'undefined' ? App : {};
App.Clients = typeof App.Clients !== 'undefined' ? App.Clients : {};

+(App.Clients.Index = function($) {
    var init = function() {
        var $datatable = $('#datatable-clients');

        $datatable.dataTable({
            'order': [[ 0, 'asc' ]],
            'columnDefs': [
                {
                    orderable: false,
                    sortable: false,
                    targets: [1]
                },
                {
                    name: 'fullname',
                    targets: [0]
                }
            ],
            "dom": "lfrtip",
            "processing": true,
            "serverSide": true,
            "ajax": {
                'method': 'POST',
                'url': Routing.generate('app_clients_getdata')
            }
        });

        $datatable.on('draw.dt', function() {
            $(this).find('a.btn-delete').on('click', function(event) {
                event.preventDefault();

                var url = $(this).attr('href'),
                    $a = $(this);

                swal({
                    title: Translator.trans('Confirm remove'),
                    text: Translator.trans('The record will be removed. Are you sure you want to continue?'),
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d9534f'
                }, function(isConfirmed) {
                    if (isConfirmed) {
                        $a.closest('td').text(Translator.trans('Removing...')).closest('tr').addClass('row-removing');
                        $.ajax(url, {
                            dataType: 'json',
                            method: 'post',
                            success: function(json) {
                                $datatable.find('tr.row-removing').remove();
                                swal({
                                    title: Translator.trans('Notification'),
                                    text: Translator.trans('Client has been removed successfuly')
                                });
                                $datatable.dataTable().api().draw(false);
                            }
                        });
                    }
                });
            });
        });
    }

    return {
        init: function() {
            init();
        }
    };
}(jQuery));
