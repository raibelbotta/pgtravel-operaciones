App = typeof App !== 'undefined' ? App : {};
App.Places = typeof App.Places !== 'undefined' ? App.Places : {};

App.Places.Index = function($) {
    var init = function() {
        var $datatable = $('#datatable-x');

        $datatable.dataTable({
            'columns': [
                {
                    'name': 'name',
                    'title': Translator.trans('Name')
                },
                {
                    'name': 'postalAddress',
                    'title': Translator.trans('Postal address')
                },
                {
                    'name': 'province',
                    'title': Translator.trans('Province')
                },
                {
                    'title': Translator.trans('Actions'),
                    'orderable': false,
                    'sortable': false,
                    'width': '80px'
                }
            ],
            'serverSide': true,
            'processing': true,
            'ajax': {
                'method': 'POST',
                'url': Routing.generate('app_places_getdata')
            },
            'oSearch': {
                'sSearch': $datatable.data('search')
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
}(jQuery);
