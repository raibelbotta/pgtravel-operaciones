App = typeof App !== 'undefined' ? App : {};
App.Users = typeof App.Users !== 'undefined' ? App.Users : {};
App.Users.Index = function() {
    var init = function() {
        var $datatable = $('#datatable-users');

        $datatable.dataTable({
            'order': [[ 1, 'asc' ]],
            'columnDefs': [
                {
                    orderable: false,
                    targets: [0, 3]
                }
            ],
            'processing': true
        });

        $datatable.on('draw.dt', function() {
            $(this).find('input').iCheck({
                checkboxClass: 'icheckbox_flat-green'
            });
        });

        $('.btn-delete').on('click', function(event) {
            event.preventDefault();

            var url = $(this).attr('href');

            swal({
                title: Translator.trans('Confirm remove'),
                text: Translator.trans('The record will be removed. Are you sure you want to continue?'),
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d9534f'
            }, function(isConfirmed) {
                if (isConfirmed) {
                    location.href = url;
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