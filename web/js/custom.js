App = {
    Main: function() {
        var initTooltipster = function() {
            $('[title]:not(.sidebar-footer *)').tooltipster({theme: 'tooltipster-shadow'});
            $('body').on('draw.dt', 'table', function() {
                $(this).find('[title]').tooltipster({theme: 'tooltipster-shadow'});
            });
        }

        var initiCheck = function() {
            // iCheck
            if ($("input.flat")[0]) {
                $('input.flat').iCheck({
                    checkboxClass: 'icheckbox_flat-green',
                    radioClass: 'iradio_flat-green'
                });
            };
        }

        var initPasswordModal = function() {
            $('#modalPassword button[type=button]:last').hide().on('click', function() {
                $('#modalPassword form').submit();
            });
            
            $.validator.addMethod('validpassword', function(value, element) {
                return this.optional(element) || (/[A-Z]/.test(value) && /[0-9]/.test(value) && /[a-z]/.test(value) && value.length > 7);
            }, 'Password strength is too low (include letters, capital letters and digits)');

            $('#linkChangePassword').on('click', function(event) {
                event.preventDefault();

                $('#modalPassword').modal().on('hide.bs.modal', function(){
                    $(this).find('form').remove();
                });
                $('#modalPassword .modal-body').empty().load(Routing.generate('app_user_changepassword'), function() {
                    $('#modalPassword form').validate({
                        messages: {
                            'form[current_password]': {
                                remote: 'Wrong password'
                            }
                        },
                        rules: {
                            'form[current_password]': {
                                remote: {
                                    url: Routing.generate('app_user_checkpassword'),
                                    type: 'post',
                                    data: {
                                        password: function() {
                                            return $('#form_current_password').val();
                                        }
                                    }
                                }
                            },
                            'form[plainPassword][first]': 'validpassword',
                            'form[plainPassword][second]': {
                                equalTo: '#form_plainPassword_first'
                            }
                        },
                        errorPlacement: function(error, element) {
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
                        success: function (label, element) {
                            $(element).tooltipster('hide');
                        },
                        submitHandler: function() {
                            $('#modalPassword form').ajaxSubmit({
                                dataType: 'json',
                                beforeSubmit: function() {
                                    $('#modalPassword button[type=button]:last').hide();
                                },
                                success: function(json) {
                                    if (json.result == 'success') {
                                        $('#modalPassword .modal-body').empty().append($('<div/>').addClass('alert alert-success').text('Password changed successfully!'));
                                    } else {
                                        $('#modalPassword .modal-body').empty().append($('<div/>').addClass('alert alert-warning').text('Password coold not be changed.'));
                                    }
                                    $('#modalPassword button[type=button]:first').text('Close');
                                }
                            });
                        }
                    })
                    $('#modalPassword button[type=button]').show();
                });
            });
        }

        return {
            init: function() {
                initTooltipster();
                initiCheck();
                initPasswordModal();
            }
        }
    }(),

    Forms: function() {
        var initCollectionControls = function() {
            var clickRemoveItem = function(event) {
                event.preventDefault();

                $(this).closest('.item').fadeOut(function() {
                    var $container = $(this).closest('.collection');
                    $(this).remove();
                    $container.trigger('item-removed.app');
                });
            }

            var clickAddItem = function(event) {
                var $container = $(this).parent().parent().find('.collection:first'),
                    prototype = $container.data('prototype'),
                    index = $container.data('index');

                if ($container.length == 0 || typeof prototype == 'undefined' || typeof index == 'undefined') {
                    return;
                }

                var $item = $(prototype.replace(/__name__/g, index));

                 $container.data('index', index + 1);
                 $container.append($item);

                 $($item.find('input:text:visible, select:visible, textarea:visible')[0]).focus();

                 $container.trigger('item-added.app', {
                    index: index,
                    item: $item
                 });
            }

            $('body').on('click', '.btn-add-item', clickAddItem);
            $('body').on('click', '.btn-remove-item', clickRemoveItem);

            $('.collection').each(function() {
                var $container = $(this);
                $container.data('index', $container.find('>.item').length);
            });
        }

        return {
            init: function() {
                initCollectionControls();
            }
        }
    }() 
};

/**
 * Resize function without multiple trigger
 * 
 * Usage:
 * $(window).smartresize(function(){  
 *     // code here
 * });
 */
(function($,sr){
    // debouncing function from John Hann
    // http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/
    var debounce = function (func, threshold, execAsap) {
      var timeout;

        return function debounced () {
            var obj = this, args = arguments;
            function delayed () {
                if (!execAsap)
                    func.apply(obj, args); 
                timeout = null; 
            }

            if (timeout)
                clearTimeout(timeout);
            else if (execAsap)
                func.apply(obj, args);

            timeout = setTimeout(delayed, threshold || 100); 
        };
    };

    // smartresize 
    jQuery.fn[sr] = function(fn){  return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr); };

})(jQuery,'smartresize');
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var CURRENT_URL = window.location.href.split('#')[0].split('?')[0],
    $BODY = $('body'),
    $MENU_TOGGLE = $('#menu_toggle'),
    $SIDEBAR_MENU = $('#sidebar-menu'),
    $SIDEBAR_FOOTER = $('.sidebar-footer'),
    $LEFT_COL = $('.left_col'),
    $RIGHT_COL = $('.right_col'),
    $NAV_MENU = $('.nav_menu'),
    $FOOTER = $('footer');

// Sidebar
$(document).ready(function() {
    // TODO: This is some kind of easy fix, maybe we can improve this
    var setContentHeight = function () {
        // reset height
        $RIGHT_COL.css('min-height', $(window).height());

        var bodyHeight = $BODY.outerHeight(),
            footerHeight = $BODY.hasClass('footer_fixed') ? -10 : $FOOTER.height(),
            leftColHeight = $LEFT_COL.eq(1).height() + $SIDEBAR_FOOTER.height(),
            contentHeight = bodyHeight < leftColHeight ? leftColHeight : bodyHeight;

        // normalize content
        contentHeight -= $NAV_MENU.height() + footerHeight;

        $RIGHT_COL.css('min-height', contentHeight);
    };

    $SIDEBAR_MENU.find('a').on('click', function(ev) {
        var $li = $(this).parent();

        if ($li.is('.active')) {
            $li.removeClass('active active-sm');
            $('ul:first', $li).slideUp(function() {
                setContentHeight();
            });
        } else {
            // prevent closing menu if we are on child menu
            if (!$li.parent().is('.child_menu')) {
                $SIDEBAR_MENU.find('li').removeClass('active active-sm');
                $SIDEBAR_MENU.find('li ul').slideUp();
            }
            
            $li.addClass('active');

            $('ul:first', $li).slideDown(function() {
                setContentHeight();
            });
        }
    });

    // toggle small or large menu
    $MENU_TOGGLE.on('click', function() {
        if ($BODY.hasClass('nav-md')) {
            $SIDEBAR_MENU.find('li.active ul').hide();
            $SIDEBAR_MENU.find('li.active').addClass('active-sm').removeClass('active');
        } else {
            $SIDEBAR_MENU.find('li.active-sm ul').show();
            $SIDEBAR_MENU.find('li.active-sm').addClass('active').removeClass('active-sm');
        }

        $BODY.toggleClass('nav-md nav-sm');

        setContentHeight();
    });

    // check active menu
    $SIDEBAR_MENU.find('a[href*="' + CURRENT_URL + '"]:first').parent('li').addClass('current-page');

    $SIDEBAR_MENU.find('a').filter(function () {
        return this.href !== '' && CURRENT_URL.search(this.href) !== -1;
    }).parent('li').addClass('current-page').parents('ul').slideDown(function() {
        setContentHeight();
    }).parent().addClass('active');

    // recompute content when resizing
    $(window).smartresize(function(){  
        setContentHeight();
    });

    setContentHeight();

    // fixed sidebar
    if ($.fn.mCustomScrollbar) {
        $('.menu_fixed').mCustomScrollbar({
            autoHideScrollbar: true,
            theme: 'minimal',
            mouseWheel:{ preventDefault: true }
        });
    }
});
// /Sidebar

// Panel toolbox
$(document).ready(function() {
    $('body').on('click', '.collapse-link', function() {
        var $BOX_PANEL = $(this).closest('.x_panel'),
            $ICON = $(this).find('i'),
            $BOX_CONTENT = $BOX_PANEL.find('.x_content');
        
        // fix for some div with hardcoded fix class
        if ($BOX_PANEL.css('style')) {
            $BOX_CONTENT.slideToggle(200, function(){
                $BOX_PANEL.removeAttr('style');
            });
        } else {
            $BOX_CONTENT.slideToggle(200); 
            $BOX_PANEL.css('height', 'auto');  
        }

        $ICON.toggleClass('fa-chevron-up fa-chevron-down');
    });

    $('.close-link').click(function () {
        var $BOX_PANEL = $(this).closest('.x_panel');

        $BOX_PANEL.remove();
    });
});
// /Panel toolbox

// Tooltip
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    });
});
// /Tooltip

// Progressbar
if ($(".progress .progress-bar")[0]) {
    $('.progress .progress-bar').progressbar();
}
// /Progressbar

// Switchery
$(document).ready(function() {
    if ($(".js-switch")[0]) {
        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        elems.forEach(function (html) {
            var switchery = new Switchery(html, {
                color: '#26B99A'
            });
        });
    }
});
// /Switchery

// Table
$('table input').on('ifChecked', function () {
    checkState = '';
    $(this).parent().parent().parent().addClass('selected');
    countChecked();
});
$('table input').on('ifUnchecked', function () {
    checkState = '';
    $(this).parent().parent().parent().removeClass('selected');
    countChecked();
});

var checkState = '';

$('.bulk_action input').on('ifChecked', function () {
    checkState = '';
    $(this).parent().parent().parent().addClass('selected');
    countChecked();
});
$('.bulk_action input').on('ifUnchecked', function () {
    checkState = '';
    $(this).parent().parent().parent().removeClass('selected');
    countChecked();
});
$('.bulk_action input#check-all').on('ifChecked', function () {
    checkState = 'all';
    countChecked();
});
$('.bulk_action input#check-all').on('ifUnchecked', function () {
    checkState = 'none';
    countChecked();
});

function countChecked() {
    if (checkState === 'all') {
        $(".bulk_action input[name='table_records']").iCheck('check');
    }
    if (checkState === 'none') {
        $(".bulk_action input[name='table_records']").iCheck('uncheck');
    }

    var checkCount = $(".bulk_action input[name='table_records']:checked").length;

    if (checkCount) {
        $('.column-title').hide();
        $('.bulk-actions').show();
        $('.action-cnt').html(checkCount + ' Records Selected');
    } else {
        $('.column-title').show();
        $('.bulk-actions').hide();
    }
}

// Accordion
$(document).ready(function() {
    $(".expand").on("click", function () {
        $(this).next().slideToggle(200);
        $expand = $(this).find(">:first-child");

        if ($expand.text() == "+") {
            $expand.text("-");
        } else {
            $expand.text("+");
        }
    });
});

// NProgress
if (typeof NProgress != 'undefined') {
    $(document).ready(function () {
        NProgress.start();
    });

    $(window).load(function () {
        NProgress.done();
    });
}
