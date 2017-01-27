App=typeof App!=="undefined"?App:{};App.Main=function(){var c=function(){$("[title]:not(.sidebar-footer *)").tooltipster({theme:"tooltipster-shadow"});$("body").on("draw.dt","table",function(){$(this).find("[title]").tooltipster({theme:"tooltipster-shadow"})})};var b=function(){if($("input.flat")[0]){$("input.flat").iCheck({checkboxClass:"icheckbox_flat-green",radioClass:"iradio_flat-green"})}};var a=function(){$("#modalPassword button[type=button]:last").hide().on("click",function(){$("#modalPassword form").submit()});$.validator.addMethod("validpassword",function(e,d){return this.optional(d)||(/[A-Z]/.test(e)&&/[0-9]/.test(e)&&/[a-z]/.test(e)&&e.length>7)},Translator.trans("Password strength is too low (include letters, capital letters and digits)"));$("#linkChangePassword").on("click",function(d){d.preventDefault();$("#modalPassword").modal().on("hide.bs.modal",function(){$(this).find("form").remove()});$("#modalPassword .modal-body").empty().load(Routing.generate("app_user_changepassword"),function(){$("#modalPassword form").validate({messages:{"form[current_password]":{remote:Translator.trans("Wrong password")}},rules:{"form[current_password]":{remote:{url:Routing.generate("app_user_checkpassword"),type:"post",data:{password:function(){return $("#form_current_password").val()}}}},"form[plainPassword][first]":"validpassword","form[plainPassword][second]":{equalTo:"#form_plainPassword_first"}},errorPlacement:function(e,f){if(!f.data("tooltipster-ns")){f.tooltipster({trigger:"custom",onlyOne:false,position:"bottom-left",positionTracker:true})}f.tooltipster("update",$(e).text());f.tooltipster("show")},success:function(e,f){$(f).tooltipster("hide")},submitHandler:function(){$("#modalPassword form").ajaxSubmit({dataType:"json",beforeSubmit:function(){$("#modalPassword button[type=button]:last").hide()},success:function(e){if(e.result=="success"){$("#modalPassword .modal-body").empty().append($("<div/>").addClass("alert alert-success").text(Translator.trans("Password changed successfully!")))}else{$("#modalPassword .modal-body").empty().append($("<div/>").addClass("alert alert-warning").text(Translator.trans("Password coold not be changed.")))}$("#modalPassword button[type=button]:first").text("Close")}})}});$("#modalPassword button[type=button]").show()})})};return{init:function(){c();b();a()}}}();App.Forms=function(){var a=function(){var b=function(d){d.preventDefault();$(this).closest(".item").fadeOut(function(){var e=$(this).closest(".collection");$(this).remove();e.trigger("item-removed.app")})};var c=function(g){var h=$(this).parent().parent().find(".collection:first"),f=h.data("prototype"),e=h.data("index");if(h.length==0||typeof f=="undefined"||typeof e=="undefined"){return}var d=$(f.replace(/__name__/g,e));h.data("index",e+1);h.append(d);$(d.find("input:text:visible, select:visible, textarea:visible")[0]).focus();h.trigger("item-added.app",{index:e,item:d})};$("body").on("click",".btn-add-item",c);$("body").on("click",".btn-remove-item",b);$(".collection").each(function(){var d=$(this);d.data("index",d.find(">.item").length)})};return{init:function(){a()}}}();
App=typeof App!=="undefined"?App:{};App.Bookings=typeof App.Bookings!=="undefined"?App.Bookings:{};App.Bookings.Index=function(){var a=function(){var b=$("#datatable-offers");b.on("click","a.btn-cancel",function(d){d.preventDefault();var e=$(this),c=e.attr("href");swal({title:Translator.trans("Confirm operation"),text:Translator.trans("The record will be cancelled. Are you sure you want to continue?"),type:"warning",showCancelButton:true,confirmButtonColor:"#d9534f"},function(f){if(f){e.closest("td").text(Translator.trans("Updating...")).closest("tr").addClass("row-removing");$.ajax(c,{dataType:"json",method:"post",success:function(g){b.dataTable().api().draw(false)}})}})});b.dataTable({order:[[4,"asc"]],columnDefs:[{searchable:false,sortable:false,targets:[5],width:"135px"},{name:"version",searchable:false,sortable:false,targets:[0],title:"V",width:"20px"},{name:"state",searchable:false,targets:[1],title:Translator.trans("State"),width:"35px"},{name:"name",targets:[2],title:Translator.trans("Name")},{name:"client",targets:[3],title:Translator.trans("Client")},{name:"startAt",searchable:false,targets:[4],title:Translator.trans("Date")}],processing:true,serverSide:true,ajax:{method:"post",url:Routing.generate("app_offers_getdata"),data:function(c){return $.extend(true,c,{filter:{state:$('form#filter select[name$="[state]"]').val(),cancelled:$('form#filter select[name$="[cancelled]"]').val(),fromDate:$('form#filter input:text[name$="[fromDate]"]').val(),toDate:$('form#filter input:text[name$="[toDate]"]').val()}})}}});+(function(c){c('form#filter input[name$="[fromDate]"]').parent().datetimepicker({format:"DD/MM/YYYY"});c('form#filter input[name$="[toDate]"]').parent().datetimepicker({format:"D/MM/YYYY",useCurrent:false});c('form#filter input[name$="[fromDate]"]').parent().on("dp.change",function(d){c('form#filter input[name$="[toDate]"]').parent().data("DateTimePicker").minDate(d.date);b.DataTable().draw(false)});c('form#filter input[name$="[toDate]"]').parent().on("dp.change",function(d){c('form#filter input[name$="[fromDate]"]').parent().data("DateTimePicker").maxDate(d.date);b.DataTable().draw(false)});c("form#filter select").on("change",function(){b.DataTable().draw(false)})}(jQuery));$("body").on("click","a.btn-delete",function(d){d.preventDefault();var c=$(this).attr("href"),e=$(this);swal({title:Translator.trans("Confirm remove"),text:Translator.trans("The record will be removed. Are you sure you want to continue?"),type:"warning",showCancelButton:true,confirmButtonColor:"#d9534f"},function(f){if(f){e.closest("td").text(Translator.trans("Removing...")).closest("tr").addClass("row-removing");$.ajax(c,{dataType:"json",method:"post",success:function(g){b.find("tr.row-removing").remove();b.dataTable().api().draw(false)}})}})});$("body").on("click","a.btn-promote",function(d){d.preventDefault();var c=$(this).attr("href"),e=$(this);swal({title:Translator.trans("Confirm move"),text:Translator.trans("The record will be moved to the official operation. Are you sure you want to continue?"),type:"warning",showCancelButton:true,confirmButtonColor:"#337ab7"},function(f){if(f){e.closest("td").text(Translator.trans("Moving...")).closest("tr").addClass("row-promoting");$.ajax(c,{dataType:"json",method:"post",success:function(g){b.find("tr.row-promoting").remove();b.dataTable().api().draw(false)}})}})})};return{init:function(){a()}}}();
App=typeof App!=="undefined"?App:{};App.Clients=typeof App.Clients!=="undefined"?App.Clients:{};App.Clients.Index=function(){var a=function(){var b=$("#datatable-clients");b.dataTable({order:[[0,"asc"]],columnDefs:[{orderable:false,sortable:false,targets:[1]},{name:"fullname",targets:[0]}],dom:"lfrtip",processing:true,serverSide:true,ajax:{method:"post",url:Routing.generate("app_clients_getdata")}});b.on("draw.dt",function(){$(this).find("a.btn-delete").on("click",function(d){d.preventDefault();var c=$(this).attr("href"),e=$(this);swal({title:Translator.trans("Confirm remove"),text:Translator.trans("The record will be removed. Are you sure you want to continue?"),type:"warning",showCancelButton:true,confirmButtonColor:"#d9534f"},function(f){if(f){e.closest("td").text(Translator.trans("Removing...")).closest("tr").addClass("row-removing");$.ajax(c,{dataType:"json",method:"post",success:function(g){b.find("tr.row-removing").remove();swal({title:Translator.trans("Notification"),text:Translator.trans("Client has been removed successfuly")});b.dataTable().api().draw(false)}})}})})})};return{init:function(){a()}}}();
App=typeof App!=="undefined"?App:{};App.Contracts=typeof App.Contracts!=="undefined"?App.Contracts:{};App.Contracts.Index=function(){var a=function(){var b=$("#datatable-x");b.dataTable({order:[[1,"asc"]],aoColumns:[{sortable:false,searchable:false},{name:"name"},{name:"model"},{name:"supplier"},{name:"signedAt"},{name:"startAt"},{name:"endAt"},{sortable:false,searchable:false,width:80}],serverSide:true,processing:true,ajax:{method:"post",url:Routing.generate("app_contracts_getdata")}});b.on("draw.dt",function(){$(this).find("input").iCheck({checkboxClass:"icheckbox_flat-green"});$(this).find("a.btn-delete").on("click",function(d){d.preventDefault();var c=$(this).attr("href"),e=$(this);swal({title:Translator.trans("Confirm remove"),text:Translator.trans("The record will be removed. Are you sure you want to continue?"),type:"warning",showCancelButton:true,confirmButtonColor:"#d9534f"},function(f){if(f){e.closest("td").text(Translator.trans("Removing...")).closest("tr").addClass("row-removing");$.ajax(c,{dataType:"json",method:"post",success:function(g){b.find("tr.row-removing").remove();b.dataTable().api().draw(false)}})}})})})};return{init:function(){a()}}}();
(function(c,b){var a=function(g,d,e){var h;return function f(){var k=this,j=arguments;function i(){if(!e){g.apply(k,j)}h=null}if(h){clearTimeout(h)}else{if(e){g.apply(k,j)}}h=setTimeout(i,d||100)}};jQuery.fn[b]=function(d){return d?this.bind("resize",a(d)):this.trigger(b)}})(jQuery,"smartresize");var CURRENT_URL=window.location.href.split("#")[0].split("?")[0],$BODY=$("body"),$MENU_TOGGLE=$("#menu_toggle"),$SIDEBAR_MENU=$("#sidebar-menu"),$SIDEBAR_FOOTER=$(".sidebar-footer"),$LEFT_COL=$(".left_col"),$RIGHT_COL=$(".right_col"),$NAV_MENU=$(".nav_menu"),$FOOTER=$("footer");$(document).ready(function(){var a=function(){$RIGHT_COL.css("min-height",$(window).height());var d=$BODY.outerHeight(),b=$BODY.hasClass("footer_fixed")?-10:$FOOTER.height(),e=$LEFT_COL.eq(1).height()+$SIDEBAR_FOOTER.height(),c=d<e?e:d;c-=$NAV_MENU.height()+b;$RIGHT_COL.css("min-height",c)};$SIDEBAR_MENU.find("a").on("click",function(b){var c=$(this).parent();if(c.is(".active")){c.removeClass("active active-sm");$("ul:first",c).slideUp(function(){a()})}else{if(!c.parent().is(".child_menu")){$SIDEBAR_MENU.find("li").removeClass("active active-sm");$SIDEBAR_MENU.find("li ul").slideUp()}c.addClass("active");$("ul:first",c).slideDown(function(){a()})}});$MENU_TOGGLE.on("click",function(){if($BODY.hasClass("nav-md")){$SIDEBAR_MENU.find("li.active ul").hide();$SIDEBAR_MENU.find("li.active").addClass("active-sm").removeClass("active")}else{$SIDEBAR_MENU.find("li.active-sm ul").show();$SIDEBAR_MENU.find("li.active-sm").addClass("active").removeClass("active-sm")}$BODY.toggleClass("nav-md nav-sm");a()});$SIDEBAR_MENU.find('a[href*="'+CURRENT_URL+'"]:first').parent("li").addClass("current-page");$SIDEBAR_MENU.find("a").filter(function(){return this.href!==""&&CURRENT_URL.search(this.href)!==-1}).parent("li").addClass("current-page").parents("ul").slideDown(function(){a()}).parent().addClass("active");$(window).smartresize(function(){a()});a();if($.fn.mCustomScrollbar){$(".menu_fixed").mCustomScrollbar({autoHideScrollbar:true,theme:"minimal",mouseWheel:{preventDefault:true}})}});$(document).ready(function(){$("body").on("click",".collapse-link",function(){var a=$(this).closest(".x_panel"),b=$(this).find("i"),c=a.find(".x_content");if(a.css("style")){c.slideToggle(200,function(){a.removeAttr("style")})}else{c.slideToggle(200);a.css("height","auto")}b.toggleClass("fa-chevron-up fa-chevron-down")});$(".close-link").click(function(){var a=$(this).closest(".x_panel");a.remove()})});$(document).ready(function(){$('[data-toggle="tooltip"]').tooltip({container:"body"})});if($(".progress .progress-bar")[0]){$(".progress .progress-bar").progressbar()}$(document).ready(function(){if($(".js-switch")[0]){var a=Array.prototype.slice.call(document.querySelectorAll(".js-switch"));a.forEach(function(b){var c=new Switchery(b,{color:"#26B99A"})})}});$("table input").on("ifChecked",function(){checkState="";$(this).parent().parent().parent().addClass("selected");countChecked()});$("table input").on("ifUnchecked",function(){checkState="";$(this).parent().parent().parent().removeClass("selected");countChecked()});var checkState="";$(".bulk_action input").on("ifChecked",function(){checkState="";$(this).parent().parent().parent().addClass("selected");countChecked()});$(".bulk_action input").on("ifUnchecked",function(){checkState="";$(this).parent().parent().parent().removeClass("selected");countChecked()});$(".bulk_action input#check-all").on("ifChecked",function(){checkState="all";countChecked()});$(".bulk_action input#check-all").on("ifUnchecked",function(){checkState="none";countChecked()});function countChecked(){if(checkState==="all"){$(".bulk_action input[name='table_records']").iCheck("check")}if(checkState==="none"){$(".bulk_action input[name='table_records']").iCheck("uncheck")}var a=$(".bulk_action input[name='table_records']:checked").length;if(a){$(".column-title").hide();$(".bulk-actions").show();$(".action-cnt").html(a+" Records Selected")}else{$(".column-title").show();$(".bulk-actions").hide()}}$(document).ready(function(){$(".expand").on("click",function(){$(this).next().slideToggle(200);$expand=$(this).find(">:first-child");if($expand.text()=="+"){$expand.text("-")}else{$expand.text("+")}})});if(typeof NProgress!="undefined"){$(document).ready(function(){NProgress.start()});$(window).load(function(){NProgress.done()})};
App=typeof App!=="undefined"?App:{};App.ReceivableAccounts=typeof App.ReceivableAccounts!=="undefined"?App.ReceivableAccounts:{};App.ReceivableAccounts.Index=function(){var a=function(){var b=$("#datatable-x");b.on("click",".btn-change-state",function(d){d.preventDefault();var c=$(".modal#modal-change");c.data("process",$(this));$(this).attr("disabled","disabled");c.modal();c.find(".modal-content").empty().append($('<div class="modal-body"><p>'+Translator.trans("Loading data...")+"</p></div>")).load($(this).attr("href"),function(){c.data("process").removeAttr("disabled");c.find("form").ajaxForm({dataType:"json",success:function(f){c.modal("hide");$(c.data("process")).parent().text(Translator.trans("Updating..."));b.dataTable().api().draw(false)}});var e=c.find(".collection");e.data("index",e.find(".item").length);c.find(".btn-add-item").on("click",function(){var h=c.find(".collection"),g=h.data("prototype"),f=h.data("index");$item=$(g.replace(/__name__/g,f)).appendTo(h);h.data("index",f+1)})})});b.on("click",".btn-view",function(e){e.preventDefault();var c=$(".modal#modal-view"),d=$(this).data("cancel-url");c.modal();c.find(".modal-content").empty().load($(this).attr("href"),function(){c.find("a.btn-danger").attr("href",d)})});b.dataTable({order:[[2,"desc"]],aoColumns:[{name:"client"},{name:"name"},{name:"startAt",searchable:false},{name:"endAt",searchable:false},{name:"price",searchable:false},{name:"date"},{name:"notes"},{sortable:false,searchable:false}],serverSide:true,processing:true,ajax:{method:"post",url:Routing.generate("app_cxcobrar_getdata"),data:function(c){return $.extend({},c,{filter:{state:$('form#filter select[name$="[state]"]').val()}})}}});$("form#filter select").on("change",function(){b.dataTable().api().draw(true)})};return{init:function(){a()}}}();
App=typeof App!=="undefined"?App:{};App.PayableAccounts=typeof App.PayableAccounts!=="undefined"?App.PayableAccounts:{};App.PayableAccounts.Index=function(){var a=function(){var b=$("#datatable-x");b.on("click",".btn-change-state",function(d){d.preventDefault();var c=$(".modal#modal-change");c.find(".modal-content").empty().load($(this).attr("href"),function(){c.find("form").ajaxForm({beforeSubmit:function(){c.find("button[type=submit]").text(Translator.trans("Saving...")).attr("disabled","disabled")},dataType:"json",success:function(f){c.modal("hide");b.dataTable().api().draw(true)}});var e=c.find(".collection");e.data("index",e.find(".item").length);c.find(".btn-add-item").on("click",function(){var g=e.data("index"),f=e.data("prototype");e.append($(f.replace(/__name__/g,g)));e.data("index",g+1)})});c.modal()});b.on("click",".btn-view",function(d){d.preventDefault();var c=$(".modal#modal-view");c.modal();c.find(".modal-content").empty().load($(this).attr("href"))});$(".modal#modal-change button.btn-primary").on("click",function(){$(this).attr("disabled","disabled").text(Translator.trans("Saving..."));$.ajax($(".modal#modal-change").data("record-url"),{data:{notes:$(".modal#modal-change textarea").val()},dataType:"json",method:"POST",success:function(c){b.dataTable().api().draw(false);swal({title:Translator.trans("Success"),text:Translator.trans("Operation done successfuly.")});$(".modal#modal-change").modal("hide")}})});b.dataTable({order:[[3,"asc"]],aoColumns:[{name:"client"},{name:"name"},{name:"service"},{name:"startAt",searchable:false},{name:"endAt",searchable:false},{name:"supplier"},{name:"date",searchable:false},{name:"notes"},{sortable:false,searchable:false}],serverSide:true,processing:true,ajax:{method:"post",url:Routing.generate("app_cxpagar_getdata"),data:function(c){return $.extend({},c,{filter:{state:$('form#filter select[name$="[state]"]').val()}})}}});$("form#filter select").on("change",function(){b.dataTable().api().draw(true)})};return{init:function(){a()}}}();
App=typeof App!=="undefined"?App:{};App.Bookings=typeof App.Bookings!=="undefined"?App.Bookings:{};App.Bookings.Form=function(){var b=function(){var d=function(f,h){var i=f.find('input[name$="[nights]"]'),j=f.find("input.datepicker"),g=$.now();if(i.data("ajax")){i.data("ajax").abort()}if($(j[0]).val()==""){return}if($(h).is(".datepicker")){if($(j[1]).val()==""){i.val("");return}var k=$.ajax(Routing.generate("app_offers_getnights")+"?id="+g,{data:{from:$(j[0]).val(),to:$(j[1]).val()},dataType:"json",method:"POST",success:function(l){i.val(l.nights);i.removeData("ajax")}})}else{if($(h).val()==""){$(j[1]).val("");return}var k=$.ajax(Routing.generate("app_offers_getnights")+"?id="+g,{data:{from:$(j[0]).val(),nights:i.val()},dataType:"json",method:"POST",success:function(l){$(j[1]).val(l.to);i.removeData("ajax")}})}i.data("ajax",k)};var e=function(h){var g=$(h).find("input.datepicker"),f={format:"DD/MM/YYYY HH:mm",showClear:true,showTodayButton:true};$(g[0]).parent().datetimepicker(f);$(g[1]).parent().datetimepicker($.extend({},f,{useCurrent:false}));$(g[0]).parent().on("dp.change",function(i){$(g[1]).parent().data("DateTimePicker").minDate(i.date);d($(h),g[0])});$(g[1]).parent().on("dp.change",function(i){$(g[0]).parent().data("DateTimePicker").maxDate(i.date);d($(h),g[1])})};$(".item.item-service").each(function(){e(this)});$("body").on("change",'.item-service input[name$="[nights]"]',function(){d($(this).closest(".item"),this)});$("body").on("click",".btn-add-item",function(l){var k=$(this);if(k.is("a")){l.preventDefault()}var m=$(k.data("collection")),h=m.data("index"),g=m.data("prototype");$counter=m.data("counter")?$(m.data("counter")):null;$item=$(g.replace(/__name__/g,h));m.data("index",h+1);if($counter){$counter.val(parseInt($counter.val(),10)+1)}m.append($item);if($item.is(".item-service")){if(m.find(".item-service").length>1){var f=$item.prev();for(var j=0;j<2;j++){if(f.find(".datepicker:eq("+j+")").val()!==""){$item.find(".datepicker:eq("+j+")").val(f.find(".datepicker:eq("+j+")").val())}}}$item.find('select[name$="[model]"]').trigger("change");e($item)}$item.find("input:text:first").focus();if(m.closest("div.x_panel").find("h2").data("tooltipster-ns")){m.closest("div.x_panel").find("h2").tooltipster("hide")}$("body").animate({scrollTop:$item.offset().top},500)});$("body").on("click",".btn-remove-item",function(h){var g=$(this),f=g.closest(".item");if(g.is("a")){h.preventDefault()}swal({title:Translator.trans("Confirm operation"),text:Translator.trans("The service will be removed. Are you sure you want to continue?"),type:"warning",showCancelButton:true,confirmButtonColor:"#d9534f"},function(i){if(i){f.fadeOut(function(){var k=$(this).closest(".collection"),j=k.data("counter")?$(k.data("counter")):null;$(this).remove();if(j){j.val(j.val()-1)}})}})})};var a=function(){$("#reservation").validate({errorPlacement:function(d,e){if(e.is(":hidden")){e=e.closest(":visible")}if(!e.data("tooltipster-ns")){e.tooltipster({trigger:"custom",onlyOne:false,position:"bottom-left",positionTracker:true})}e.tooltipster("update",$(d).text());e.tooltipster("show")},ignore:':hidden:not(input[name="servicesCounter"])',messages:{servicesCounter:{min:Translator.trans("Add a service at least")}},rules:{servicesCounter:{min:1},"offer_form[directClientFullName]":{required:{depends:function(){return $("#offer_form_clientType_1").prop("checked")===true}}},"offer_form[client]":{required:{depends:function(){return $("#offer_form_clientType_0").prop("checked")===true}}},"offer_form[percentApplied][plus]":"number"},success:function(e,f){var d=$(f);if(d.is(":hidden")){d=d.closest(":visible")}d.tooltipster("hide")}})};var c=function(){$('input:radio[name="offer_form[clientType]"]').on("ifClicked",function(){var d=$(this).val();if(d==="direct"){$(".block-clienttype.block-clienttype-direct").show();$(".block-clienttype:not(.block-clienttype-direct)").find("input:text, select").each(function(){if($(this).data("tooltipster-ns")){$(this).tooltipster("hide")}});$(".block-clienttype:not(.block-clienttype-direct)").hide()}else{if(d==="registered"){$(".block-clienttype.block-clienttype-registered").show();$(".block-clienttype:not(.block-clienttype-registered)").find("input:text, select").each(function(){if($(this).data("tooltipster-ns")){$(this).tooltipster("hide")}});$(".block-clienttype:not(.block-clienttype-registered)").hide()}}});$("#offer_form_client").on("change",function(){$("#offer_form_notificationContact").empty();var d=$(this).val();$.getJSON(Routing.generate("app_offers_getclientcontacts")+"?client="+d,function(e){$("#offer_form_notificationContact").append($('<option value=""></option>'));$.each(e.elements,function(f,g){$("#offer_form_notificationContact").append($('<option value="'+g.id+'">'+g.text+"</option>"))})})});$("#offer_form_directClientMobilePhone").intlTelInput({allowExtensions:true,autoFormat:false,autoHideDialCode:true,autoPlaceholder:false,defaultCountry:"auto",geoIpLookup:function(d){$.get("http://ipinfo.io",function(){},"jsonp").always(function(f){var e=(f&&f.country)?f.country:"";d(e)})},nationalMode:false,numberType:"MOBILE",preferredCountries:["ca","us","gb"],utilsScript:phone_util_script_url});$("body").on("click",".btn-search-service",function(){var d=$(this).closest(".item");$("#searchServiceModal").data("item",d).modal({backdrop:"static"});$("#searchServiceModal .modal-body").empty().append($("<p>"+Translator.trans("Loading data...")+"</p>")).load(Routing.generate("app_offers_searchservice"))});$("body").on("change",'.item.item-service select[name$="[model]"]',function(){var d=$(this).closest(".item"),f=this.options[this.selectedIndex],e=d.find('input[name$="[nights]"]');if(parseInt($(f).data("has-nights"))===1){e.parent().show()}else{e.parent().hide()}});$('.item.item-service select[name$="[model]"]').trigger("change");+(function(g){var e=function(h){return isNaN(parseFloat(h))?0:parseFloat(h)};var d=function(){var h=new Number(0);g('.item-service input[name$="[supplierPrice]"]').each(function(){h+=e(g(this).val())});g("#offer_form_totalExpenses").val(h.toFixed(2)).trigger("change")};g("#offer_form_services").on("change",'.item-service input[name$="[supplierPrice]"]',function(){d()});g(".item-administrative-charge").on("change","input",function(){if(!g(this).is('[name$="[pax]"], [name$="[nights]"], [name$="[price]"]')){return}var h=new Number();g(this).closest(".item").find('[name$="[pax]"], [name$="[nights]"]').each(function(){h+=e(g(this).val())*e(g(this).closest(".item").find('input[name$="[price]"]').val())});g(this).closest(".item").find('input[name$="[total]"]').val(h.toFixed(2)).trigger("change")});var f=function(){var h=g("#offer_form_totalCharges"),j=g('.item-administrative-charge input[name$="[total]"]'),i=new Number(0);j.each(function(){i+=e(g(this).val())});h.val(i.toFixed(2)).trigger("change")};g(".item-administrative-charge").on("change",'input[name$="[total]"]',function(){f()});g("#offer_form_totalExpenses, #offer_form_totalCharges, #offer_form_percentApplied_percent, #offer_form_percentApplied_plus").on("change",function(){var h=g("#offer_form_totalExpenses, #offer_form_totalCharges, #offer_form_percentApplied_percent, #offer_form_percentApplied_plus");var k=e(g("#offer_form_totalExpenses").val()),i=e(g("#offer_form_totalCharges").val()),l=g("#offer_form_percentApplied_percent"),j;if(l.val()==="plus"){j=new Number(k+i+e(g("#offer_form_percentApplied_plus").val()))}else{j=new Number(k*(l.val()/100)+k+i)}g("#offer_form_clientCharge").val(j.toFixed(2))});g("#offer_form_totalExpenses").trigger("change");g("#offer_form_percentApplied_percent").on("change",function(){if(g(this).val()!=="plus"){g("#offer_form_percentApplied_plus").val(0)}});d();f()}(jQuery))};return{init:function(){c();b();a()}}}();
App=typeof App!=="undefined"?App:{};App.Clients=typeof App.Clients!=="undefined"?App.Clients:{};App.Clients.Form=function(){var a=function(){+(function(){$(".collection").each(function(){var c=$(this),b=c.find("> .item").length;c.data("index",b)});$("body").on("click",".btn-add-item",function(e){e.preventDefault();var f=$(this).parent().parent().find(".collection"),d=f.data("prototype"),c=f.data("index"),b=$(d.replace(/__name__/g,c));f.append(b);b.find("input:text:first").focus();f.data("index",c+1);$('input:hidden[name="contactCounter"]').val($("#client_form_contacts").find(".item").length);b.find("input[type=tel]").intlTelInput({allowExtensions:true,autoFormat:false,autoHideDialCode:true,autoPlaceholder:false,defaultCountry:"auto",geoIpLookup:function(g){$.get("http://ipinfo.io",function(){},"jsonp").always(function(i){var h=(i&&i.country)?i.country:"";g(h)})},nationalMode:false,numberType:"MOBILE",preferredCountries:["cu"],utilsScript:url_utilScript})});$("body").on("click",".btn-delete-item",function(b){b.preventDefault();$(this).closest(".item").fadeOut(function(){$(this).remove();$('input:hidden[name="contactCounter"]').val($container.find(".item").length)})})}());$("input[type=tel]").intlTelInput({allowExtensions:true,autoFormat:false,autoHideDialCode:true,autoPlaceholder:false,defaultCountry:"auto",geoIpLookup:function(b){$.get("http://ipinfo.io",function(){},"jsonp").always(function(d){var c=(d&&d.country)?d.country:"";b(c)})},nationalMode:false,numberType:"MOBILE",preferredCountries:["cu"],utilsScript:url_utilScript});$("form#client").validate({errorPlacement:function(b,c){if(c.is(":hidden")){c=c.closest(":visible")}if(!c.data("tooltipster-ns")){c.tooltipster({trigger:"custom",onlyOne:false,position:"bottom-left",positionTracker:true})}c.tooltipster("update",$(b).text());c.tooltipster("show")},messages:{contactCounter:{min:Translator.trans("Client has no contact person")}},rules:{contactCounter:{min:1}},ignore:":hidden:not(input[name=contactCounter])",success:function(b,c){if($(c).is(":hidden")){c=$(c).closest(":visible")}$(c).tooltipster("hide")}})};return{init:function(){a()}}}();
App=typeof App!=="undefined"?App:{};App.Contracts=typeof App.Contracts!=="undefined"?App.Contracts:{};App.Contracts.Form=function(){var a=function(){+(function(b){b("#contract_form_model").on("change",function(){var c=b(this).val();b(".visible-condition").each(function(){if(b(this).hasClass("visible-condition-"+c)){b(this).show()}else{b(this).hide();b(this).find(".collection:first").data("index",0).empty()}})});b("#contract_form_signedAt").parent().datetimepicker({format:"DD/MM/YYYY"});b("#contract_form_startAt").parent().datetimepicker({format:"DD/MM/YYYY HH:mm"});b("#contract_form_endAt").parent().datetimepicker({useCurrent:false,format:"DD/MM/YYYY HH:MM"});b("#contract_form_startAt").parent().on("dp.change",function(c){b("#contract_form_endAt").parent().data("DateTimePicker").minDate(c.date)});b("#contract_form_endAt").parent().on("dp.change",function(c){b("#contract_form_startAt").parent().data("DateTimePicker").maxDate(c.date)});b(".item-top-service").each(function(){var c=b(this).find(".datetimepicker");b(c[0]).datetimepicker({format:"DD/MM/YYYY HH:mm"});b(c[1]).datetimepicker({format:"DD/MM/YYYY HH:mm",useCurrent:false});b(c[0]).on("dp.change",function(d){b(c[1]).data("DateTimePicker").minDate(d.date)});b(c[1]).on("dp.change",function(d){b(c[0]).data("DateTimePicker").maxDate(d.date)})})}(jQuery));+(function(){$("#contract").validate({errorPlacement:function(b,c){if(!c.data("tooltipster-ns")){c.tooltipster({trigger:"custom",onlyOne:false,position:"bottom-left",positionTracker:true})}c.tooltipster("update",$(b).text());c.tooltipster("show")},rules:{"contract_form[startAt]":{required:{depends:function(){return""!==$("#contract_form_endAt").val()}}},"contract_form[endAt]":{required:{depends:function(){return""!==$("#contract_form_startAt").val()}}}},success:function(b,c){$(c).tooltipster("hide")}})}());+(function(){var b=function(f){f.preventDefault();$(this).closest(".item").remove()};var e=function(f){if(typeof f==="undefined"){f=$("body")}$(f).find(".collection").each(function(){var g=$(this);g.data("index",g.find(">.item").length)})};var d=function(j){var k=$(this).parent().parent().find(".collection:first"),i=k.data("prototype"),h=k.data("index");if(k.hasClass("collection-facilities")){var g=$(i.replace(/facilities___name__/g,"facilities_"+h).replace(/facilities\]\[__name__/g,"facilities]["+h))}else{var g=$(i.replace(/__name__/g,h))}k.data("index",h+1);k.append(g);if(g.find(".collection").length>0){e(g)}if(g.hasClass("item-top-service")){var f=g.find(".datetimepicker");$(f[0]).datetimepicker({format:"DD/MM/YYYY HH:mm"});$(f[1]).datetimepicker({format:"DD/MM/YYYY HH:mm",useCurrent:false});$(f[0]).on("dp.change",function(l){$(f[1]).data("DateTimePicker").minDate(l.date)});$(f[1]).on("dp.change",function(l){$(f[0]).data("DateTimePicker").maxDate(l.date)})}if(g.hasClass("item-facility-season")){var f=g.find(".datetimepicker");$(f[0]).on("dp.change",function(l){$(f[1]).data("DateTimePicker").minDate(l.date)}).datetimepicker({format:"DD/MM/YYYY"});$(f[1]).on("dp.change",function(l){$(f[0]).data("DateTimePicker").maxDate(l.date)}).datetimepicker({format:"DD/MM/YYYY",useCurrent:false})}$(g.find("input:text:visible, select:visible")[0]).focus()};var c=function(i){var g=$(this).closest(".item"),j=g.closest(".collection"),h=j.data("index");$newItem=g.clone();g.closest(".collection").append($newItem);$newItem.find("[id][name]").each(function(){var l=$(this);if(/facilities_\d+_/.test(l.attr("id"))){var m=l.attr("id"),k=m.replace(/facilities_\d+/,"facilities_"+h);l.attr("id",k);$('label[for="'+m+'"]').attr("for",k);l.attr("name",l.attr("name").replace(/facilities\]\[\d+/,"facilities]["+h))}});var f=$newItem.find(".collection.collection-seasons");f.data("prototype",f.data("prototype").replace(/facilities_\d+/g,"facilities_"+h).replace(/facilities\]\[\d+/g,"facilities]["+h));console.log(f.data("prototype"));j.data("index",h+1);if($newItem.find(".collection").length>0){e($newItem)}$newItem.find(".input-daterange").each(function(){var k=$(this);k.datepicker({inputs:k.find("input:text").toArray()});k.find("input:text:first").on("change",function(){$(this).closest(".item").find("input:text:last").datepicker("setDate",$(this).val())})})};$("body").on("click",".btn-add-item",d);$("body").on("click",".btn-remove",b);$("body").on("click",".btn-clone-item",c);$(".btn-remove").on("click",b);e();$(".item-top-service").each(function(){var f=$(this).find(".datetimepicker");$(f[0]).datetimepicker({format:"DD/MM/YYYY HH:mm"});$(f[1]).datetimepicker({format:"DD/MM/YYYY HH:mm",useCurrent:false});$(f[0]).on("dp.change",function(g){$(f[1]).data("DateTimePicker").minDate(g.date)});$(f[1]).on("dp.change",function(g){$(f[0]).data("DateTimePicker").maxDate(g.date)})});$(".item-facility-season").each(function(){var f=$(this).find(".datetimepicker");$(f[0]).datetimepicker({format:"DD/MM/YYYY"});$(f[1]).datetimepicker({format:"DD/MM/YYYY",useCurrent:false});$(f[0]).on("dp.change",function(g){$(f[1]).data("DateTimePicker").minDate(g.date)});$(f[1]).on("dp.change",function(g){$(f[0]).data("DateTimePicker").maxDate(g.date)})})}())};return{init:function(){a()}}}();
App=typeof App!=="undefined"?App:{};App.Profile=typeof App.Profile!=="undefined"?App.Profile:{};App.Profile.Form=function(){var a=function(){$("input[type=tel]").intlTelInput({allowExtensions:true,autoFormat:false,autoHideDialCode:true,autoPlaceholder:false,defaultCountry:"auto",geoIpLookup:function(b){$.get("http://ipinfo.io",function(){},"jsonp").always(function(d){var c=(d&&d.country)?d.country:"";b(c)})},nationalMode:false,numberType:"MOBILE",preferredCountries:["cu"],utilsScript:util_url});+(function(){$.validator.addMethod("strongpassword",function(c,b){return this.optional(b)||(/[A-Z]/.test(c)&&/[a-z]/.test(c)&&/[0-9]/.test(c)&&c.length>7)},Translator.trans("Password strong is too low (8 characters minimun and contains at least 1 character from groups [A-Z], [a-z] and [0-9])"));$("form#profile").validate({errorPlacement:function(b,c){if(!c.data("tooltipster-ns")){c.tooltipster({trigger:"custom",onlyOne:false,position:"bottom-left",positionTracker:true})}c.tooltipster("update",$(b).text());c.tooltipster("show")},success:function(b,c){$(c).tooltipster("hide")}})});+(function(b){b(".fileupload").on("change","input:file",function(c,d){if(typeof d!=="undefined"&&d.indexOf("clear")!==-1&&b(this).closest(".fileupload").find('input:checkbox[name="fos_user_profile_form[imageFile][delete]"]').length>0){b(this).closest(".fileupload").find('input:checkbox[name="fos_user_profile_form[imageFile][delete]"]').prop("checked",true)}})}(jQuery))};return{init:function(){a()}}}();
App=typeof App!=="undefined"?App:{};App.Suppliers=typeof App.Suppliers!=="undefined"?App.Suppliers:{};App.Suppliers.Form=function(){var b=function(){$("form#supplier input[type=tel]").intlTelInput({allowExtensions:true,autoFormat:false,autoHideDialCode:true,autoPlaceholder:false,defaultCountry:"auto",geoIpLookup:function(c){$.get("http://ipinfo.io",function(){},"jsonp").always(function(e){var d=(e&&e.country)?e.country:"";c(d)})},nationalMode:false,numberType:"MOBILE",preferredCountries:["cu"],utilsScript:url_utilScript});$("form#supplier .collection-employees").on("item-added.app",function(c,d){$(d.item).find("input[type=tel]").intlTelInput({allowExtensions:true,autoFormat:false,autoHideDialCode:true,autoPlaceholder:false,defaultCountry:"auto",geoIpLookup:function(e){$.get("http://ipinfo.io",function(){},"jsonp").always(function(g){var f=(g&&g.country)?g.country:"";e(f)})},nationalMode:false,numberType:"MOBILE",preferredCountries:["cu"],utilsScript:url_utilScript})});$("#supplier_form_employees").on("item-added.app",function(){$('input:hidden[name="employeeCounter"]').val($("#supplier_form_employees").find(".item").length)});$("#supplier_form_employees").on("item-removed.app",function(){$('input:hidden[name="employeeCounter"]').val($("#supplier_form_employees").find(".item").length)})};var a=function(){$("#supplier").validate({errorPlacement:function(c,d){if(d.is(":hidden")){d=d.closest(":visible")}if(!d.data("tooltipster-ns")){d.tooltipster({trigger:"custom",onlyOne:false,position:"bottom-left",positionTracker:true})}d.tooltipster("update",$(c).text());d.tooltipster("show")},ignore:':hidden:not(input:hidden[name="employeeCounter"])',messages:{employeeCounter:{min:"No employees"}},rules:{employeeCounter:{min:1}},success:function(c,d){if($(d).is(":hidden")){d=$(d).closest(":visible")}$(d).tooltipster("hide")}})};return{init:function(){b();a()}}}();
App=typeof App!=="undefined"?App:{};App.Users=typeof App.Users!=="undefined"?App.Users:{};App.Users.Form=function(){var a=function(){$("input[type=tel]").intlTelInput({allowExtensions:true,autoFormat:false,autoHideDialCode:true,autoPlaceholder:false,defaultCountry:"auto",geoIpLookup:function(b){$.get("http://ipinfo.io",function(){},"jsonp").always(function(d){var c=(d&&d.country)?d.country:"";b(c)})},nationalMode:false,numberType:"MOBILE",preferredCountries:["cu"],utilsScript:url_utilScript});+(function(){$.validator.addMethod("strongpassword",function(c,b){return this.optional(b)||(/[A-Z]/.test(c)&&/[a-z]/.test(c)&&/[0-9]/.test(c)&&c.length>7)},Translator.trans("Password strong is too low (8 characters minimun and contains at least 1 character from groups [A-Z], [a-z] and [0-9])"));$("#user").validate({rules:{"{{ form.plainPassword.first.vars.full_name }}":"strongpassword","{{ form.plainPassword.second.vars.full_name }}":{equalTo:"#{{ form.plainPassword.first.vars.id }}"}},errorPlacement:function(b,c){if(!c.data("tooltipster-ns")){c.tooltipster({trigger:"custom",onlyOne:false,position:"bottom-left",positionTracker:true})}c.tooltipster("update",$(b).text());c.tooltipster("show")},success:function(b,c){$(c).tooltipster("hide")}})}())};return{init:function(){a()}}}();
App=typeof App!=="undefined"?App:{};App.Notifications=typeof App.Notifications!=="undefined"?App.Notifications:{};App.Notifications.Index=function(){var a=function(){var b=$("#datatable-x");b.on("click",".btn-change-state",function(c){c.preventDefault();$(this).attr("disabled","disabled");$("#modalConfirm").data("process",$(this));$("#modalConfirm .modal-content").load($(this).attr("href"),function(){$("#modalConfirm").modal();b.find(".btn[disabled]").removeAttr("disabled");$("#modalConfirm form").ajaxForm({beforeSuccess:function(){$("#modalConfirm button.btn-primary").attr("disabled","disabled")},success:function(d){$("#modalConfirm").modal("hide");$($("#modalConfirm").data("process")).parent().text(Translator.trans("Updating..."));$("#modalConfirm").removeData("process");b.dataTable().api().draw(true)}})})});b.dataTable({order:[[5,"asc"]],aoColumns:[{name:"name"},{name:"operator"},{name:"client"},{name:"supplier"},{name:"service"},{name:"startAt",searchable:false},{name:"endAt",searchable:false},{name:"reference"},{sortable:false,searchable:false}],processing:true,serverSide:true,ajax:{method:"post",url:Routing.generate("app_notifications_getdata"),data:function(c){return $.extend({},c,{filter:{state:$('form#filter select[name$="[state]"]').val()}})}}});$('form#filter select[name$="[state]"]').on("change",function(){b.dataTable().api().draw(true)})};return{init:function(){a()}}}();
App=typeof App!=="undefined"?App:{};App.Suppliers=typeof App.Suppliers!=="undefined"?App.Suppliers:{};App.Suppliers.Index=function(){var a=function(){var b=$("#datatable-suppliers");b.dataTable({columnDefs:[{orderable:false,sortable:false,targets:[1]},{name:"name",targets:[0]}],serverSide:true,processing:true,ajax:{method:"post",url:Routing.generate("app_suppliers_getdata")}});b.on("draw.dt",function(){$(this).find("input").iCheck({checkboxClass:"icheckbox_flat-green"});$(this).find("a.btn-delete").on("click",function(d){d.preventDefault();var c=$(this).attr("href"),e=$(this);swal({title:Translator.trans("Confirm remove"),text:Translator.trans("The record will be removed. Are you sure you want to continue?"),type:"warning",showCancelButton:true,confirmButtonColor:"#d9534f"},function(f){if(f){e.closest("td").text(Translator.trans("Removing...")).closest("tr").addClass("row-removing");$.ajax(c,{dataType:"json",method:"post",success:function(g){b.find("tr.row-removing").remove();b.dataTable().api().draw(false)}})}})})})};return{init:function(){a()}}}();
App=typeof App!=="undefined"?App:{};App.Users=typeof App.Users!=="undefined"?App.Users:{};App.Users.Index=function(){var a=function(){var b=$("#datatable-users");b.dataTable({order:[[1,"asc"]],columnDefs:[{orderable:false,targets:[0,3]}],processing:true});b.on("draw.dt",function(){$(this).find("input").iCheck({checkboxClass:"icheckbox_flat-green"})});$(".btn-delete").on("click",function(d){d.preventDefault();var c=$(this).attr("href");swal({title:Translator.trans("Confirm remove"),text:Translator.trans("The record will be removed. Are you sure you want to continue?"),type:"warning",showCancelButton:true,confirmButtonColor:"#d9534f"},function(e){if(e){location.href=c}})})};return{init:function(){a()}}}();