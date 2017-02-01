App = typeof App !== 'undefined' ? App : {};
App.Suppliers = typeof App.Suppliers !== 'undefined' ? App.Suppliers : {};

App.Suppliers.Index = function() {
    var init = function() {
        var $datatable = $('#datatable-suppliers');

        $datatable.dataTable({
            'columnDefs': [
                {
                    'orderable': false,
                    'sortable': false,
                    'targets': [2],
                    'title': Translator.trans('Actions'),
                    'width': '80px'
                },
                {
                    'name': 'name',
                    'targets': [0],
                    'title': Translator.trans('Name')
                },
                {
                    'name': 'fixedPhone',
                    'targets': [1],
                    'title': Translator.trans('Fixed phone'),
                    'searchable': false,
                    'sortable': false
                }
            ],
            serverSide: true,
            processing: true,
            ajax: {
                method: 'post',
                url: Routing.generate('app_suppliers_getdata')
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
    }
}();
