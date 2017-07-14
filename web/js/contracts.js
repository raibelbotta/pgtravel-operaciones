App = typeof App !== 'undefined' ? App : {};
App.Contracts = typeof App.Contracts !== 'undefined' ? App.Contracts : {};

+(App.Contracts.Index = function($) {
    var $datatable = $('#datatable-x');

    var initTable = function() {
        $datatable.dataTable({
            order: [[ 1, 'asc' ]],
            columns: [
                {
                    sortable: false,
                    searchable: false
                },
                {name: 'name'},
                {name: 'model'},
                {name: 'supplier'},
                {name: 'signedAt'},
                {name: 'startAt'},
                {name: 'endAt'},
                {
                    sortable: false,
                    searchable: false,
                    width: '80px'
                }
            ],
            serverSide: true,
            processing: true,
            ajax: {
                method: 'POST',
                url: Routing.generate('app_contracts_getdata'),
                data: function(data) {
                    return $.extend(true, data, {
                        filter: {
                            type: $('select#filter_type').val()
                        }
                    });
                }
            }
        });

        $datatable.on('draw.dt', function() {
            $(this).find('input').iCheck({
                'checkboxClass': 'icheckbox_flat-green'
            });
        });

        $datatable.on('click', 'a.btn-delete', function(event) {
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
    };

    var initFilter = function() {
        $('#filter select').on('change', function() {
            $datatable.DataTable().draw(true);
        });
    }

    return {
        init: function() {
            initTable();
            initFilter();
        }
    }
}(jQuery));
