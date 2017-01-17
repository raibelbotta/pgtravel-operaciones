$(document).ready(function() {
    $('table').on('click', 'a.btn-view', function(event) {
        event.preventDefault();

        var $modal = $('#modal-view');

        $modal.modal();
        $modal.find('a.btn-primary').attr('href', $(this).data('edit-url'));
        $modal.find('.modal-body').empty().append($('<p>' + Translator.trans('Loading data...') + '</p>')).load($(this).attr('href'));
    });

    var $datatable = $('#datatable-x');

    $datatable.dataTable({
        order: [[ 1, 'asc' ]],
        aoColumns: [
            {name: 'name'},
            {name: 'startAt'},
            {name: 'endAt'},
            {
                sortable: false,
                searchable: false
            }
        ],
        serverSide: true,
        processing: true,
        ajax: {
            method: 'post',
            url: Routing.generate('app_bookings_getdata')
        }
    });

    $datatable.on('draw.dt', function() {
        $(this).find('input').iCheck({
            checkboxClass: 'icheckbox_flat-green'
        });

        $(this).find('.btn-cancel').on('click', function(event) {
            event.preventDefault();

            var $a = $(this), url = $a.attr('href');

            swal({
                title: Translator.trans('Confirm operation'),
                text: Translator.trans('The record will be cancelled. Are you sure you want to continue?'),
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d9534f'
            }, function(isConfirmed) {
                if (isConfirmed) {
                    $a.closest('td').text(Translator.trans('Updating...')).closest('tr').addClass('row-removing');
                    $.ajax(url, {
                        dataType: 'json',
                        method: 'post',
                        success: function(json) {
                            $datatable.dataTable().api().draw(false);
                        }
                    });
                }
            });
        });

        $(this).find('a.btn-remove').on('click', function(event) {
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
});
