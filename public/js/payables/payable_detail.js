var _this_total = 0;//tiền mua hàng ở td click

$(document).ready(function(){
    selectYear();      
    $( ".datepicker" ).datepicker({
        dateFormat: 'yy/mm/dd'
    });

    $(".table-fee-l").on("click", ".price-edit", function() {
        var currentRow=$(this).closest("tr");          
        _this_total = currentRow.find("td:eq(8)").text();
    });

    //ẩn tool tip
    $('.tt-price-edit').hide()
    $('.tt-deliv-date').hide()

    // Check all checkbox
    $("#check_all").click(function(){
        $('input:checkbox').prop('checked', this.checked);
    });
})

// Tìm kiếm order theo điều kiện: order_id, year, month, supplier_id
$('.btn-search-direct').on('click', function(){
    $("<input />").attr("type", "hidden")
        .attr("name", "order_id")
        .attr("value", $('.id-order').val())
        .appendTo(".form-search");
    $("<input />").attr("type", "hidden")
        .attr("name", "purchase_id")
        .attr("value", $('.id-purchase').val())
        .appendTo(".form-search");
    $('.form-search').submit();
})

// Download file
$('.btn-search-download').on('click', function(){
    var year_ = year_detail;//$('.date-payable').val()
    var month_ = month_detail;//$('.month-payable').val()            
    var payment_term = {//kỳ hạn thống kê
        year: year_,
        month: month_
    }
    var check_select = $(".sel_download").val();
    if(check_select == 0){//Không chọn dropdown download        
        ModalError('出力内容を選択してから再度ダウンロードボタンを押してください。', '', 'OK')
        return false;
    }else {
        var table = $(".table-fee-l tbody");
        var arr_checked = [];
        table.find('tr').each(function (key, val) {
            var o_detail_id = $(this).find('td .o-detail-id').val();
            var supplied_id = $(this).find('td .supplied-id').val();
            var supplied = $(this).find('td .supplied-name').val();
            var checked = $(this).find('td input:checkbox[name=check_one]:checked').val();
            if(checked == 'on'){
                let arr_supplier = {
                    'supplied_id': supplied_id,
                    'o_detail_id': o_detail_id,
                    'supplied': supplied
                };
                arr_checked.push(arr_supplier);
            }
        });        
        if(arr_checked.length <= 0) {
            ModalError('商品にチェック入れてから再度ダウンロードボタンをクリックしてください。', '', 'OK')
            $('.title-message-body').remove()
            $('.modal-body>br').remove()
            return false;
        }else {
            loading()
            if(check_select != 3 && check_select != 44){
                let sel_download = 0;
                if(check_select == 1){
                    sel_download = 2;
                }else if(check_select == 2){
                    sel_download = 3;
                }else {
                    sel_download = check_select;
                }
                let arr_sup = [];
                arr_checked.forEach(function (element_su) {
                    arr_sup.push(element_su.supplied_id)
                })
                let data_download = [];
                let supplied_list = [... new Set (arr_sup)];
                // add những order_details có chung nhà cung cấp thành 1
                supplied_list.forEach(function (element_su) {
                    let detail_su =[];
                    let name_su = '';
                    arr_checked.forEach(function (element, index) {
                        if(element.supplied_id === element_su)
                        {
                            name_su = element.supplied
                            detail_su.push(element.o_detail_id)
                        }
                    })
                    let arr = {
                        'supplied': name_su,
                        'details': detail_su
                    }
                    data_download.push(arr)
                })
                if((sel_download == 22 || sel_download == 33)){
                    doExport(data_download, 0, null, sel_download, 9, url_export_purchase, null)
                }else {
                    $.each(data_download, function( key, value ) {  
                        param = {
                            'arr_detail': value.details,
                            'screen': 9,
                            'sel_download': sel_download,
                            'supplier_name': value.supplied,
                            'pdf': null
                        }
                        $.ajax({
                            cache: false,
                            url: url_export_purchase, //GET route 
                            data:  param,
                            contentType: 'application/json; charset=utf-8',
                            dataType: 'binary',
                            xhrFields: {
                                responseType: 'blob',
                            },
                            success: function (result, status, xhr) {
                                let responseType = xhr.getResponseHeader('content-type') || 'application/octet-binary'
                                let blob = new Blob([result], { type: responseType });
                                let url = URL.createObjectURL(blob);
                                let randomId = `download-${Math.floor(Math.random()*1000000)}`;
                                let date_ = getDateNow().split('/');
                                let file_name = value.supplied + date_[0] + date_[1] + date_[2];
                                let link = '';
                                if(sel_download == 22 || sel_download == 33){
                                    link = '<a id='+randomId+' href='+url+' download='+file_name+'.pdf>link</a>';
                                }else {
                                    link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
                                }
                                $('body').append(link)
                                $(`#${randomId}`)[0].click()
                                $(`#${randomId}`).remove()
                                endLoading()
                            },
                            error: function (ajaxContext) {
                                endLoading()
                            }
                        });
                    })
                }
            }else {
                let arr_sup = [];
                arr_checked.forEach(function (element_su) {
                    arr_sup.push(element_su.supplied_id)
                })
                let data_download = [];
                let supplied_list = [... new Set (arr_sup)];
                // add những order_details có chung nhà cung cấp thành 1
                supplied_list.forEach(function (element_su) {
                    let detail_su =[];
                    let name_su = '';
                    arr_checked.forEach(function (element, index) {
                        if(element.supplied_id === element_su)
                        {
                            name_su = element.supplied
                            detail_su.push(element.o_detail_id)
                        }
                    })
                    let arr = {
                        'supplied': name_su,
                        'details': detail_su
                    }
                    data_download.push(arr)
                })
                if(year_ == '0' || month_ == '0'){
                    ModalError('ダウンロードに失敗しました。年度、もしくは月度が選択されていません。', '', 'OK')
                    endLoading()
                    return false;
                }
                if(check_select == 44){
                    doExportPayable(data_download, 0, year_, month_, payment_term, check_select, url_order_payable)
                }else {
                    $.each(data_download, function( key, value ) {
                        param = {
                            'arr_detail': value.details,
                            'pay_year': year_,
                            'pay_month': month_,
                            'payment_term': payment_term,//kỳ hạn thống kê
                            'supplier_name': value.supplied,
                            'pdf': null
                        }
                        $.ajax({
                            cache: false,
                            url: url_order_payable, //GET route 
                            data:  param,
                            contentType: 'application/json; charset=utf-8',
                            dataType: 'binary',
                            xhrFields: {
                                responseType: 'blob',
                            },
                            success: function (result, status, xhr) {
                                let responseType = xhr.getResponseHeader('content-type') || 'application/octet-binary'
                                let blob = new Blob([result], { type: responseType });
                                let url = URL.createObjectURL(blob);
                                let randomId = `download-${Math.floor(Math.random()*1000000)}`;
                                let date_ = getDateNow().split('/');
                                let file_name = value.supplied + date_[0] + date_[1] + date_[2];
                                let link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
                                $('body').append(link)
                                $(`#${randomId}`)[0].click()
                                $(`#${randomId}`).remove()
                                endLoading()
                            },
                            error: function (ajaxContext) {
                                endLoading()
                            }
                        });
                    })
                }
            }            
        }
    }
});

// Kiểm tra xem có ngày giao hàng lớn hơn ngày đặt hàng
function checkDelivDate(){
    var flag = false;// kiểm tra xem có ngày giao hàng lớn hơn ngày đặt hàng hoặc tiền mua hàng + tiền đính chính <= 0 thì return false
    var table = $(".table-fee-l tbody");
    table.find('tr').each(function (key, val) {
        var $tds = $(this).find('td'),
        deliv_date = $(this).find('td .deliv-date').val();
        checked = $(this).find('td input:checkbox[name=check_one]:checked').val();
        var quantity = parseFloat($tds.eq(7).text().replace(/,/g, ''));
        var total_price = parseFloat($(this).find('td .o-cost-price').val());
        var price_edit = parseFloat($(this).find('td .price-edit').val().replace(/,/g, ''));
        if(checked == 'on'){
            if(price_edit < 0 && price_edit * (-1) >= (total_price * quantity)){//Kiểm tra nhập tiền đính chính sao cho update tiền mua hàng phải > 0
                flag = true;
                $(this).find('td .tt-price-edit').addClass("tooltip-text")
                $(this).find('td .tooltip-text').show();
                $(this).find('td .tooltip-text').delay(5000).fadeOut();
            }
            if(!_validatedate(deliv_date)){// Show thông báo nếu ngày giao hàng < ngày đặt hàng
                flag = true;
                $(this).find('td .tt-deliv-date').addClass("tooltip-text")
                $(this).parent().find('.tooltip-text').css({"top": "-25px", "width": "300px"});
                $(this).find('td .tooltip-text').show();
                $(this).find('td .tooltip-text').delay(5000).fadeOut();
            }
        }
    });
    if(flag == true){
        setTimeout(function(){
            table.find('tr').each(function () {
                $(this).find('td:eq(2) span').removeClass("tooltip-text")
                $(this).find('td:eq(9) span').removeClass("tooltip-text")
            })
        }
        , 5500);
        return false;
    }
    return true;
}

// Cập nhật thông tin chi tiết của order
$('.btn-search-save').click(function(e){
    var updated_at = [];//mảng dùng để kiểm tra 2 màng hình cùng thao tác update dữ liệu
    if(length_table > 0){
        var table = $(".table-fee-l tbody");
        var arr_checked = [];
        table.find('tr').each(function (key, val) {
            var $tds = $(this).find('td');
            var o_detail_id = $(this).find('td .o-detail-id').val();
            var o_cost_price = $(this).find('td .o-cost-price').val();
            var update_at = $(this).find('td .o-updated-at').val();
            var p_id = $(this).find('td .p-id').val();
            var od_tax = $(this).find('td .od-tax').val();
            var deliv_date = $(this).find('td .deliv-date').val();
            var old_deliv_date = $(this).find('td .old-deliv-date').val();
            var quantity = $tds.eq(7).text();
            var total_price = $tds.eq(8).text();
            var price_edit = $(this).find('td .price-edit').val();
            var o_price_edit = $(this).find('td .old-price-edit').val();
            var ship_id = $(this).find('td .ship-id').val();
            var o_code = $(this).find('td .o-code').val();
            var p_code = $(this).find('td .p-code').val();
            var checked = $(this).find('td input:checkbox[name=check_one]:checked').val();
            if(checked == 'on'){
                // set updated at
                updated_at.push({
                    'detail_id': o_detail_id,
                    'updated_at': update_at
                })
                arr_checked.push(o_detail_id+'|'+p_id+'|'+deliv_date+'|'+total_price+'|'+price_edit+'|'+od_tax+'|'+o_price_edit+'|'+ship_id+'|'+o_code+'|'+p_code+'|'+old_deliv_date+"|"+o_cost_price+"|"+quantity);
            }
        });

        if(arr_checked.length <= 0) {
            ModalError('発注書にチェック入れてから再度入力内容を保存ボタンをクリックしてください。', '保存出来ていません。', 'OK')
            return false;
        }else {        
            // Kiểm tra ngày giao hàng nhỏ hơn ngày đặt hàng hoặc tiền mua hàng + tiền đính chính <= 0 thì return false
            if(!checkDelivDate()){
                return false;
            }
            // Cập nhật thông tin order detail
            data_update = {
                'check_update': updated_at,
                'arr_checked': arr_checked
            }
            ModalConfirmReturn('入力した内容を保存します。よろしいでしょうか？', 'キャンセル', 'Ok', url_update_order_detail, data_update, 9)
        }        
    }else {        
        ModalError('データはありません。保存出来ません。検索条件を再度確認してください。', '保存出来ていません。', 'OK')//không tìm thấy dữ liệu. vui lòng kiểm tra lại điều kiện tìm kiếm
    }
});

//kiểm tra nếu sửa ngày giao hàng
$('.deliv-date').datepicker({
    dateFormat: 'yy/mm/dd',
    onSelect: function(dateText) {
        var $this = $(this).parent().parent().parent()
        var supplied_id = $this.find('td .supplied-id').val()
        var ship_id = $this.find('td .ship-id').val()
        var o_id = $this.find('td .o-id').val()
        var p_code = $this.find('td .p-code').val()
        var delivery_method = $this.find('td .delivery-method').val()
        var deliv_date = $this.find('td .deliv-date').val()
        var table = $(".table-fee-l tbody");
        checked = $this.find('td input:checkbox[name=check_one]:checked').val();
        table.find('tr').each(function (key, val) {
            let supplied_id_ = $(this).find('td .supplied-id').val()
            let ship_id_ = $(this).find('td .ship-id').val()
            let o_id_ = $(this).find('td .o-id').val()
            let p_code_ = $(this).find('td .p-code').val()
            let delivery_method_ = $(this).find('td .delivery-method').val()
            //Kiểm tra sp nào cùng shipment thì update cùng ngày giao hàng
            if(supplied_id_ == supplied_id && ship_id_ == ship_id && o_id_ == o_id && delivery_method_ == delivery_method){
                $(this).find('td .deliv-date').val(deliv_date)
                if(checked == 'on'){
                    $(this).find('td .check_one').prop('checked', true);
                }
            }
        });
    }
});

//Kiểm tra nếu cùng shipment thì checked
$(".table-fee-l").on('click', '.check_one', function(e) {
    var value_check = $(this).prop('checked') 
    var $this = $(this).parent().parent()
    var supplied_id = $this.find('td .supplied-id').val()
    var ship_id = $this.find('td .ship-id').val()
    var o_id = $this.find('td .o-id').val()
    var p_code = $this.find('td .p-code').val()
    var delivery_method = $this.find('td .delivery-method').val()
    var table = $(".table-fee-l tbody");
    table.find('tr').each(function (key, val) {
        let supplied_id_ = $(this).find('td .supplied-id').val()
        let ship_id_ = $(this).find('td .ship-id').val()
        let o_id_ = $(this).find('td .o-id').val()
        let p_code_ = $(this).find('td .p-code').val()
        let delivery_method_ = $(this).find('td .delivery-method').val()
        var deliv_date = $this.find('td .deliv-date').val()
        var old_deliv_date = $this.find('td .old-deliv-date').val()
        //Kiểm tra sp nào cùng shipment thì check vào sp đó
        if(supplied_id_ == supplied_id && ship_id_ == ship_id && o_id_ == o_id && delivery_method_ == delivery_method){
            if(deliv_date != formatDate_(old_deliv_date)){
                $(this).find('td .check_one').prop('checked', value_check);
            }
        }
    });
})

//function formatNumber
function formatNumber(num){
    return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}

$(document).on('blur', '.price-edit', function (){
    let value = number_format(parseFloat($(this).val().replace(/\,/g,'')), 0, '.', ',')
    $(this).val(value)
})

// function format date
function formatDate_(date){
    var d = new Date(date);
    var month = d.getMonth()+1;
    var day = d.getDate();
    var output = d.getFullYear() + '/' + 
    (month<10 ? '0' : '') + month + '/' +
    (day<10 ? '0' : '') + day;
    return output;
}

//function get year now
function getYearNow(){
    var d = new Date();
    var year = d.getFullYear();
    return year;
}


//function get year now
function getMonthNow(){
    var d = new Date();
    var month = d.getMonth()+1;
    return month;
}

//function select year
function selectYear(){
    var year = getYearNow();
    $('.date-payable').append($('<option>', { 
        value: 0,
        text : null,
        selected: (0 == year_detail) ? true  : false
    }));
    for(var i = 10; i >= 0; i--){
        $('.date-payable').append($('<option>', { 
            value: year-i,
            text : year-i,
            selected: (year-i == year_detail) ? true  : false
        }));
    }
    for(var i = 1; i <= 10; i++){
        $('.date-payable').append($('<option>', { 
            value: year+i,
            text : year+i,
            selected: (year+i == year_detail) ? true  : false
        }));
    }
    $('.month-payable').append($('<option>', { 
        value: 0,
        text : null,
        selected: (0 == month_detail) ? true  : false
    }));
    for(var i = 1; i <= 12; i++){
        $('.month-payable').append($('<option>', { 
            value: i,
            text : i,
            selected: (i == month_detail) ? true  : false
        }));
    }
}

$(".datepicker").each(function() {    
    $(this).datepicker('setDate', new Date($(this).val()));
});