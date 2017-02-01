App = typeof App !== 'undefined' ? App : {};
App.RentCarTypes = typeof App.RentCarTypes !== 'undefined' ? App.RentCarTypes : {};

App.RentCarTypes.Index = function() {
    var init = function() {
        var $datatable = $('#datatable-x');

        $datatable.dataTable({
            'columnDefs': [
                {
                    orderable: false,
                    sortable: false,
                    targets: [1]
                },
                {
                    name: 'name',
                    targets: [0]
                }
            ],
            processing: true
        });

        $datatable.find('a.btn-delete').on('click', function(event) {
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
                            location.href = location.href;
                        }
                    });
                }
            });
        });
    }

    return {
        init: function() {
            init();
        }
    }
}();
