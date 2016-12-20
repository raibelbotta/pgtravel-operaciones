$(document).ready(function() {
    $('input:radio[name="offer_form[clientType]"]').on('ifClicked', function() {
        var value = $(this).val();
        if (value === 'direct') {
            $('.block-clienttype.block-clienttype-direct').show();
            $('.block-clienttype:not(.block-clienttype-direct)').hide();
        } else if (value === 'registered') {
            $('.block-clienttype.block-clienttype-registered').show();
            $('.block-clienttype:not(.block-clienttype-registered)').hide();
            if ($('input#offer_form_directClientFullName').data('tooltipster-ns')) {
                $('input#offer_form_directClientFullName').tooltipster('hide');
            }
        }
    });

    $('#offer_form_client').on('change', function() {
        $('#offer_form_notificationContact').empty();
        var id = $(this).val();
        $.getJSON(url_getclientcontact + '?client=' + id, function(json) {
            $('#offer_form_notificationContact').append($('<option value=""></option>'));
            $.each(json.elements, function(i, e) {
                $('#offer_form_notificationContact').append($('<option value="' + e.id + '">' + e.text + '</option>'));
            });
        });
    });

    $('#offer_form_services').find('.datepicker').datetimepicker({
        format: 'DD/MM/YYYY HH:mm'
    });

    //collection stuff
    +(function() {
        $('body').on('click', '.btn-add-item', function(event) {
            var $btn = $(this);
            if ($btn.is('a')) {
                event.preventDefault();
            }

            var $container = $($btn.data('collection')),
                index = $container.data('index'),
                prototype = $container.data('prototype')
                $counter = $container.data('counter') ? $($container.data('counter')) : null;

            $item = $(prototype.replace(/__name__/g, index));
            $container.data('index', index + 1);
            if ($counter) {
                $counter.val(parseInt($counter.val(),10) + 1);
            }
            $container.append($item);

            $item.find('.datepicker').datetimepicker({
                format: 'DD/MM/YYYY HH:mm'
            });

            $item.find('input:text:first').focus();

            $('body').animate({
                scrollTop: $item.offset().top
            }, 500);
        });

        $('body').on('click', '.btn-remove-item', function(event) {
            var $btn = $(this);
            if ($btn.is('a')) {
                event.preventDefault();
            }

            $btn.closest('.item').fadeOut(function() {
                var $container = $(this).closest('.collection'),
                    $counter = $container.data('counter') ? $($container.data('counter')) : null;
                $(this).remove();
                if ($counter) {
                    $counter.val($counter.val() - 1);
                }
            });
        });
    }());

    //validation stuff
    +(function() {
        $('#reservation').validate({
            errorPlacement: function(error, element) {
                if (element.is(':hidden')) {
                    element = element.closest(':visible');
                }
                if (!element.data('tooltipster-ns')) {
                    element.tooltipster({
                        trigger: 'custom',
                        onlyOne: false,
                        position: 'bottom-left',
                        positionTracker: true
                    });
                }
                element.tooltipster('update', $(error).text());
                element.tooltipster('show');
            },
            ignore: '',
            messages: {
                'servicesCounter': {
                    min: 'Add a service at least'
                }
            },
            rules: {
                'servicesCounter': {
                    min: 1
                },
                'offer_form[directClientFullName]': {
                    required: {
                        depends: function() {
                            return $('#offer_form_clientType_1').prop('checked') === true;
                        }
                    }
                },
                'offer_form[client]': {
                    required: {
                        depends: function() {
                            return $('#offer_form_clientType_0').prop('checked') === true;
                        }
                    }
                }
            },
            success: function (label, element) {
                var $element = $(element);
                if ($element.is(':hidden')) {
                    $element = $element.closest(':visible');
                }
                $element.tooltipster('hide');
            }
        });
    }());

    +(function() {
        $('body').on('click', '.btn-search-service', function() {
            var $item = $(this).closest('.item');

            $('#searchServiceModal').data('item', $item).modal({
                backdrop: 'static'
            });
            $('#searchServiceModal .modal-body').empty().append($('<p>Cargando datos...</p>')).load(url_searchservice);
        });
    }());
});