$(document).ready(function() {
    var $table = $('#datatable-x');

    $table.on('click', '.btn-change-state', function(event) {
        event.preventDefault();

        var $modal = $('.modal#modal-change');
        $modal.data('process', $(this));
        $(this).attr('disabled', 'disabled');

        $modal.modal();
        $modal.find('.modal-content').empty().append($('<div class="modal-body"><p>' + Translator.trans('Loading data...') + '</p></div>')).load($(this).attr('href'), function() {
            $modal.data('process').removeAttr('disabled');
            $modal.find('form').ajaxForm({
                dataType: 'json',
                success: function(json) {
                    $modal.modal('hide');
                    $($modal.data('process')).parent().text(Translator.trans('Updating...'));
                    $table.dataTable().api().draw(false);
                }
            });

            var $container = $modal.find('.collection');
            $container.data('index', $container.find('.item').length);

            $modal.find('.btn-add-item').on('click', function() {
                var $container = $modal.find('.collection'),
                    prototype = $container.data('prototype'),
                    index = $container.data('index');

                $item = $(prototype.replace(/__name__/g, index)).appendTo($container);
                $container.data('index', index + 1);
            });
        });
    });

    $table.on('click', '.btn-view', function(event) {
        event.preventDefault();

        var $modal = $('.modal#modal-view'),
            cancelUrl = $(this).data('cancel-url');

        $modal.modal();
        $modal.find('.modal-content').empty().load($(this).attr('href'), function() {
            $modal.find('a.btn-danger').attr('href', cancelUrl);
        });
    });

    $table.dataTable({
        order: [[2, 'desc']],
        aoColumns: [
            {name: 'client'},
            {name: 'name'},
            {name: 'startAt', searchable: false},
            {name: 'endAt', searchable: false},
            {name: 'price', searchable: false},
            {name: 'date'},
            {name: 'notes'},
            {
                sortable: false,
                searchable: false
            }
        ],
        serverSide: true,
        processing: true,
        ajax: {
            method: 'post',
            url: Routing.generate('app_cxcobrar_getdata'),
            data: function(params) {
                return $.extend({}, params, {
                    filter: {
                        state: $('form#filter select[name$="[state]"]').val()
                    }
                });
            }
        }
    });

    $('form#filter select').on('change', function() {
        $table.dataTable().api().draw(true);
    }); 
});

