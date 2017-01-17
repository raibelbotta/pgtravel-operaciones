$(document).ready(function() {
    var $table = $('#datatable-offers');

    $table.on('click', 'a.btn-cancel', function(event) {
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
    });

    $table.dataTable({
        'order': [[ 4, 'asc' ]],
        'columnDefs': [
            {
                searchable: false,
                sortable: false,
                targets: [5],
                width: '135px'
            },
            {
                name: "version",
                searchable: false,
                targets: [0],
                title: 'V'
            },
            {
                name: "state",
                searchable: false,
                targets: [1],
                title: Translator.trans('State')
            },
            {
                name: 'name',
                targets: [2],
                title: Translator.trans('Name')
            },
            {
                name: 'client',
                targets: [3],
                title: Translator.trans('Client')
            },
            {
                name: 'startAt',
                searchable: false,
                targets: [4],
                title: Translator.trans('Date')
            }
        ],
        processing: true,
        serverSide: true,
        ajax: {
            "method": 'post',
            "url": Routing.generate('app_offers_getdata'),
            "data": function(baseData) {
                return $.extend(true, baseData, {
                    "filter": {
                        "state": $('form#filter select[name$="[state]"]').val(),
                        "cancelled": $('form#filter select[name$="[cancelled]"]').val(),
                        "fromDate": $('form#filter input:text[name$="[fromDate]"]').val(),
                        "toDate": $('form#filter input:text[name$="[toDate]"]').val()
                    }
                });
            }
        }
    });

    +(function($) {
        $('form#filter input[name$="[fromDate]"]').parent().datetimepicker({
            format: 'DD/MM/YYYY'
        });
        $('form#filter input[name$="[toDate]"]').parent().datetimepicker({
            format: 'D/MM/YYYY',
            useCurrent: false
        });
        $('form#filter input[name$="[fromDate]"]').parent().on('dp.change', function(e) {
            $('form#filter input[name$="[toDate]"]').parent().data("DateTimePicker").minDate(e.date);
            $table.DataTable().draw(false);
        });
        $('form#filter input[name$="[toDate]"]').parent().on("dp.change", function(e) {
            $('form#filter input[name$="[fromDate]"]').parent().data("DateTimePicker").maxDate(e.date);
            $table.DataTable().draw(false);
        });

        $('form#filter select').on('change', function() {
            $table.DataTable().draw(false);
        });

    }(jQuery));

    $('body').on('click', 'a.btn-delete', function(event) {
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
                        $table.find('tr.row-removing').remove();
                        $table.dataTable().api().draw(false);
                    }
                });
            }
        });
    });

    $('body').on('click', 'a.btn-promote', function(event) {
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
    });
});
