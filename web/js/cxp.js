App = typeof App !== 'undefined' ? App : {};
App.PayableAccounts = typeof App.PayableAccounts !== 'undefined' ? App.PayableAccounts : {};

App.PayableAccounts.Index = function() {
    var init = function() {
        var $table = $('#datatable-x');

        $table.on('click', '.btn-change-state', function(event) {
            event.preventDefault();

            var $modal = $('.modal#modal-change');

            $modal.find('.modal-content').empty().load($(this).attr('href'), function() {
                $modal.find('form').ajaxForm({
                    beforeSubmit: function(){
                        $modal.find('button[type=submit]').text(Translator.trans('Saving...')).attr('disabled', 'disabled');
                    },
                    dataType: 'json',
                    success: function(json) {
                        $modal.modal('hide');
                        $table.dataTable().api().draw(true);
                    }
                });

                var $container = $modal.find('.collection');
                $container.data('index', $container.find('.item').length);

                $modal.find('.btn-add-item').on('click', function() {
                    var index = $container.data('index'),
                        prototype = $container.data('prototype');

                    $container.append($(prototype.replace(/__name__/g, index)));
                    $container.data('index', index + 1);
                });
            });

            $modal.modal();
        });

        $table.on('click', '.btn-view', function(event) {
            event.preventDefault();

            var $modal = $('.modal#modal-view');

            $modal.modal();
            $modal.find('.modal-content').empty().load($(this).attr('href'));
        });

        $('.modal#modal-change button.btn-primary').on('click', function() {
            $(this).attr('disabled', 'disabled').text(Translator.trans('Saving...'))
            $.ajax($('.modal#modal-change').data('record-url'), {
                data: {
                    notes: $('.modal#modal-change textarea').val()
                },
                dataType: 'json',
                method: 'POST',
                success: function(json) {
                    $table.dataTable().api().draw(false);
                    swal({
                        title: Translator.trans('Success'),
                        text: Translator.trans('Operation done successfuly.')
                    });
                    $('.modal#modal-change').modal('hide');
                }
            })
        });

        $table.dataTable({
            order: [[3, 'asc']],
            aoColumns: [
                {name: 'client'},
                {name: 'name'},
                {name: 'service'},
                {name: 'startAt', searchable: false},
                {name: 'endAt', searchable: false},
                {name: 'supplier'},
                {name: 'date', searchable: false},
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
                url: Routing.generate('app_cxpagar_getdata'),
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
    }

    return {
        init: function() {
            init();
        }
    }
}();
