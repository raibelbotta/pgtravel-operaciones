App = typeof App !== 'undefined' ? App : {};
App.Bookings = typeof App.Bookings !== 'undefined' ? App.Bookings : {};

+(App.Bookings.Index = function($) {
    var $table = $('#datatable-offers');

    var init = function() {
        var clickCancel = function(event) {
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
                            $table.dataTable().api().draw(false);
                        }
                    });
                }
            });
        }

        var clickUncancel = function(event) {
            event.preventDefault();

            var $a = $(this), url = $a.attr('href');

            $a.closest('td').text(Translator.trans('Updating...')).closest('tr').addClass('row-removing');
            $.ajax(url, {
                'dataType': 'json',
                'method': 'POST',
                'success': function(json) {
                    $table.dataTable().api().draw(false);
                }
            });
        }

        var clickDelete = function(event) {
            event.preventDefault();

            var url = $(this).attr('href'),
                $a = $(this);

            swal({
                title: Translator.trans('Confirm removal'),
                text: Translator.trans('The record will be removed. Are you sure you want to continue?'),
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d9534f'
            }, function(isConfirmed) {
                if (isConfirmed) {
                    if (confirm('Are you sure you want to continue with removal?')) {
                        $a.closest('td').text(Translator.trans('Removing...')).closest('tr').addClass('row-removing');
                        $.ajax(url, {
                            dataType: 'json',
                            method: 'post',
                            success: function(json) {
                                $table.find('tr.row-removing').remove();
                                $table.dataTable().api().draw(false);
                            }
                        });
                    }
                }
            });
        }

        var clickPromote = function(event) {
            event.preventDefault();

            var url = $(this).attr('href'),
                $a = $(this);

            swal({
                title: Translator.trans('Confirm move'),
                text: Translator.trans('The record will be moved to the official operation. Are you sure you want to continue?'),
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#337ab7'
            }, function(isConfirmed) {
                if (isConfirmed) {
                    $a.closest('td').text(Translator.trans('Moving...')).closest('tr').addClass('row-promoting');
                    $.ajax(url, {
                        dataType: 'json',
                        method: 'post',
                        success: function(json) {
                            $table.find('tr.row-promoting').remove();
                            $table.dataTable().api().draw(false);
                        }
                    });
                }
            });
        }

        var clickUnpromote = function(event) {
            event.preventDefault();

            var url = $(this).attr('href'),
                $a = $(this);

            swal({
                title: Translator.trans('Confirm move'),
                text: Translator.trans('The record will be pulled off from the operation. Are you sure you want to continue?'),
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#337ab7'
            }, function(isConfirmed) {
                if (isConfirmed) {
                    $a.closest('td').text(Translator.trans('Moving...')).closest('tr').addClass('row-promoting');
                    $.ajax(url, {
                        'dataType': 'json',
                        'method': 'POST',
                        'success': function(json) {
                            $table.find('tr.row-promoting').remove();
                            $table.dataTable().api().draw(false);
                        }
                    });
                }
            });
        }

        $table.on('click', 'a.btn-promote', clickPromote);
        $table.on('click', 'a.btn-unpromote', clickUnpromote);
        $table.on('click', 'a.btn-delete', clickDelete);
        $table.on('click', 'a.btn-cancel', clickCancel);
        $table.on('click', 'a.btn-uncancel', clickUncancel);

        $table.dataTable({
            order: [[ 4, 'asc' ]],
            columnDefs: [
                {
                    'searchable': false,
                    'sortable': false,
                    'targets': [5],
                    'width': '158px',
                    'title': Translator.trans('Actions')
                },
                {
                    'name': 'version',
                    'searchable': false,
                    'sortable': false,
                    'targets': [0],
                    'title': 'V',
                    'width': '20px'
                },
                {
                    'name': "state",
                    'searchable': false,
                    'targets': [1],
                    'title': Translator.trans('State'),
                    'width': '35px'
                },
                {
                    'name': 'name',
                    'targets': [2],
                    'title': Translator.trans('Name')
                },
                {
                    'name': 'client',
                    'targets': [3],
                    'title': Translator.trans('Client')
                },
                {
                    'name': 'startAt',
                    'searchable': false,
                    'targets': [4],
                    'title': Translator.trans('Date')
                }
            ],
            processing: true,
            serverSide: true,
            ajax: {
                method: 'post',
                url: Routing.generate('app_offers_getdata'),
                data: function(baseData) {
                    var filter = [];
                    $.each($('form#filter').serializeArray(), function(i, e) {
                        filter[e['name']] = e['value'];
                    });

                    return $.extend(true, baseData, filter);
                }
            }
        });
    }

    var initFilter = function() {
        $('form#filter input[name="booking_list_filter_form[startAt][left_date]"]').parent().datetimepicker({
            format: 'DD/MM/YYYY',
            showClear: true
        });
        $('form#filter input[name="booking_list_filter_form[startAt][right_date]"]').parent().datetimepicker({
            format: 'D/MM/YYYY',
            useCurrent: false,
            showClear: true
        });
        $('form#filter input[name="booking_list_filter_form[startAt][left_date]"]').parent().on('dp.change', function(e) {
            $('form#filter input[name="booking_list_filter_form[startAt][right_date]"]').parent().data("DateTimePicker").minDate(e.date);
            $table.DataTable().draw();
        });
        $('form#filter input[name="booking_list_filter_form[startAt][right_date]"]').parent().on("dp.change", function(e) {
            $('form#filter input[name="booking_list_filter_form[startAt][left_date]"]').parent().data("DateTimePicker").maxDate(e.date);
            $table.DataTable().draw();
        });

        $('form#filter select').on('change', function() {
            $table.DataTable().draw();
        });
    }

    return {
        init: function() {
            init();
            initFilter();
        }
    }
}(jQuery));
