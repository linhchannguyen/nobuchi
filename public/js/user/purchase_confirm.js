$(document).ready(function(){
    $('.no-allowed').prop('disabled', true)
    $( ".datepicker" ).datepicker({
        dateFormat: 'yy/mm/dd'
    });
    //ẩn tool tip
    $('.tt-price-edit').hide()
    $('.tt-deliv-date').hide()
    $('.tt-bill-number').hide()
    $('.tt-bill-number-empty').hide()
})

$('.check_flag_p_status').on('click', function(){
    checked = $(this).find('.flag_p_status').prop('checked');
    if(checked == true){
        $(this).find('.flag_p_status').prop('checked', false);
    }else {
        $(this).find('.flag_p_status').prop('checked', true);
    }
});
$('.flag_p_status').on('click', function(){
    checked = $(this).prop('checked');
    if(checked == true){
        $(this).prop('checked', false);
    }else {
        $(this).prop('checked', true);
    }
});

//Cập nhật trạng thái B1, B2, B3 -> B4
$('.btn-update').on('click', function(){
    var table = $(".table-result tbody");
    var arr_purchase_id = [];
    var arr_purchase_code = [];
    var check = false;
    var arr_shipment_update = [];
    table.find('tr').each(function (key, val) {
        var o_detail_id = $(this).find('td .o-detail-id').val();
        var ship_id = $(this).find('td .ship-id').val();
        var p_id = $(this).find('td .p-id').val();
        var p_code = $(this).find('td .p-code').val();
        var o_code = $(this).find('td .o-code').val();
        var p_status = $(this).find('td .p-status').val();
        var checked = $(this).find('td input:checkbox[name=check_one]:checked').val();
        if(checked == 'on') {
            if(p_status == 1 || p_status == 2 || p_status == 3){
                arr_shipment_update.push({
                    'order_detail_id': o_detail_id,
                    'ship_id': ship_id,
                });
                let p_update = {
                    'p_code': p_code,
                    'o_code': o_code
                }
                arr_purchase_id.push(p_id);
                arr_purchase_code.push(p_update);
            }
            if (p_status == 4 || p_status == 5) {
                check = true;
            }
        }
    })
    if(check){
        ModalError('対応状況が出荷済又はキャンセルである物は情報更新出来ませんためチェック外してから再度出荷済にするボタンをクリックしてください。', '保存出来ていません。', 'OK');
        return false;
    }else {
        if(arr_purchase_id.length <= 0) {
            ModalError('商品にチェック入れてから出荷済にするボタンをクリックしてください。', '保存出来ていません。', 'OK')
            return false;
        }else {
            var message = '入力内容を保存します。よろしいですか？';
            for(var i = 0; i < data_old.length; i++){//Chạy mảng group order_code
                if(data_old[i].length > 1){//Nếu order có nhiều sản phẩm cùng 1 người nhận thì kiểm tra các trường hợp của logic đổi mã ship
                    var arr_detail_id_check = [];
                    var arr_ship_check = [];
                    var arr_ship_uncheck = [];
                    for(var j = 0; j < data_old[i].length; j++){
                        for(var k = 0; k < arr_shipment_update.length; k++){
                            if(arr_shipment_update[k].order_detail_id == data_old[i][j].order_detail_id){
                                arr_ship_check.push({
                                    order_detail_id: arr_shipment_update[k].order_detail_id,
                                    ship_id: arr_shipment_update[k].ship_id
                                });
                                arr_detail_id_check.push(arr_shipment_update[k].order_detail_id);
                            }
                        }
                        if(arr_detail_id_check.includes(data_old[i][j].order_detail_id+"") == false){
                            arr_ship_uncheck.push(data_old[i][j]);
                        }
                    }
                    if(arr_ship_uncheck.length > 0){
                        for(var i = 0; i < arr_ship_check.length; i++){
                            for(var j = 0; j < arr_ship_uncheck.length; j++){
                                if(arr_ship_check[i].ship_id == arr_ship_uncheck[j].ship_id){
                                    var message = '同梱中の商品も出荷済になります。よろしいですか？';
                                }
                            }
                        }
                    }
                }
            }
            let param = {
                'arr_purchase': arr_purchase_id,
                'arr_purchase_code': arr_purchase_code,
                'sel_download': 3
            }
            ModalConfirmReturn(message, 'キャンセル', 'Ok', url_update_status_purchase, param, 22)
        }
    }
});

//Xuất giấy đặt hàng, giấy hướng dẫn đóng gói, chi tiết đặt hàng
$('.btn-print').on('click', function(){
    var table = $(".table-result tbody");
    var arr_order_detail = [];
    table.find('tr').each(function (key, val) {
        var o_detail_id = $(this).find('td .o-detail-id').val();
        var p_id = $(this).find('td .p-id').val();
        var checked = $(this).find('td input:checkbox[name=check_one]:checked').val();
        if(checked == 'on'){
            let detail_id = {
                'o_detail_id': o_detail_id,
            };
            arr_order_detail.push(detail_id);
        }
    })
    if(arr_order_detail.length <= 0) {
        ModalError('商品にチェック入れてからチェックした発注書をダウンロードボタンをクリックしてください。', '', 'OK')
        $('.title-message-body').remove()
        $('.modal-body>br').remove()
        return false;
    }else {
        let param = {
            'arr_detail': arr_order_detail,
            'supplied': supplied,
            'screen': 22,
            'sel_download': 1
        }
        ModalConfirmReturn('発注一覧表＋発注明細・梱包指示書を出力します。よろしいでしょうか？', 'キャンセル', 'Ok', url_export_purchase, param, 22)
    }
})

function findDuplicates(arr) {
    var object = {};
    var result = [];
    arr.forEach(function (item) {
        if(!object[item])
            object[item] = 0;
        object[item] += 1;
    })
    for (var prop in object) {
        if(object[prop] >= 2) {
            result.push(prop);
        }
    }
    return result;

}
//Xác nhận đặt hàng dùng cho NCC
$('.btn-save').on('click', function(){
    var updated_at = [];//mảng dùng để kiểm tra 2 màng hình cùng thao tác update dữ liệu
    if(length_table > 0) {
        var table = $(".table-result tbody");
        var arr_checked = [];
        var arr_shipment_update = [];
        var check = false;
        table.find('tr').each(function (key, val) {
            var pStatus = $(this).find('td .p-status').val();
            var $tds = $(this).find('td');
            var o_detail_id = $(this).find('td .o-detail-id').val();
            var update_at = $(this).find('td .o-updated-at').val();
            var p_id = $(this).find('td .p-id').val();
            var p_code = $(this).find('td .p-code').val();
            var o_code = $(this).find('td .o-code').val();
            var p_date = $(this).find('td .p-date').val();
            var od_tax = $(this).find('td .od-tax').val();
            var deliv_date = $(this).find('td .deliv-date').val();
            var total_price = $tds.eq(9).text();
            var price_edit = $(this).find('td .price-edit').val();
            var o_price_edit = $(this).find('td .old-price-edit').val();
            var ship_id = $(this).find('td .ship-id').val();
            var bill_number = $(this).find('td .bill-number').val();
            var o_bill_number = $(this).find('td .old-bill-number').val();
            var o_deliv_date = $(this).find('td .old-deliv-date').val();
            var checked = $(this).find('td input:checkbox[name=check_one]:checked').val();
            if (checked == 'on') {
                if (pStatus == 1 || pStatus == 2 || pStatus == 3) {
                    arr_shipment_update.push({
                        'order_detail_id': o_detail_id,
                        'shipment_code': bill_number,
                        'ship_id': ship_id,
                        'order_code': o_code,
                        'shipment_date': deliv_date,
                        'p_id': p_id,
                        'total_price': total_price,
                        'price_edit': price_edit,
                        'od_tax': od_tax,
                        'o_price_edit': o_price_edit,
                        'o_bill_number': o_bill_number,
                        'o_deliv_date': o_deliv_date,
                        'p_code': p_code,
                        'p_date': p_date,
                    });
                    // set updated at
                    updated_at.push({
                        'detail_id': o_detail_id,
                        'updated_at': update_at
                    })
                    arr_checked.push(o_detail_id+'|'+p_id+'|'+deliv_date+'|'+total_price+'|'+price_edit+'|'
                                    +od_tax+'|'+o_price_edit+'|'+ship_id+'|'+bill_number+'|'+o_bill_number+'|'
                                    +o_deliv_date+'|'+p_code+'|'+p_date+'|'+o_code);
                }
                if (pStatus == 4 || pStatus == 5) {
                    check = true;
                }

            }
        });

        if(check){
            ModalError('対応状況が出荷済又はキャンセルである物は情報更新出来ませんためチェック外してから再度入力内容を保存ボタンをクリックしてください。', '保存出来ていません。', 'OK');
            return false;
        }else {
            if(arr_checked.length <= 0) {
                ModalError('商品にチェック入れてから再度入力内容を保存ボタンをクリックしてください。', '保存出来ていません。', 'OK')
                return false;
            } else {
                // Kiểm tra ngày giao hàng nhỏ hơn ngày đặt hàng hoặc tiền mua hàng + tiền đính chính <= 0 thì return false
                if(!checkDelivDate()){
                    return false;
                }
                // Logic update màn hình [22]:
                // - Nếu mã ship chỉ có 1 (1 sản phẩm - 1 người nhận) => Khi bấm update thì lưu nội dung bình thường (giá trị trên màn hình như thế nào thì lưu như thế đó)
    
                // - Nếu mã ship có nhiều hơn 1 (nhiều sản phẩm - 1 mã ship)
                // + Không đổi mã ship, đổi ngày giao hàng nhưng có ngày giao hàng khác nhau => Không cho lưu
                // + Không đổi mã ship, đổi ngày giao hàng nhưng không có ngày giao hàng khác nhau => Update ngày giao hàng của ship đó
                // + Đổi mã ship:
                //     . số lượng ship mới < số lượng ship cũ => tạo ship mới
                //     . số lượng ship mới = số lượng ship cũ => chỉ update ship/ngày giao hàng/tiền đính chính
                // + Nếu không nhập gì mà bấm lưu thì lưu bình thường, giá trị trên màn hình không thay đổi
                // 2 mảng dùng để lưu các sản phẩm cần tách mã ship khi ngày giao hàng thay đổi hoặc mã ship thay đổi
                var arr_update_ship = [];//Mảng update ship khi 1 sản phẩm có 1 người nhận
                var arr_update_ships = [];//Mảng update shup khi nhiều sản phẩm có 1 người nhận
                var arr_remove_ship = [];//Mảng ship remove khi tất cả sản phẩm cùng 1 người nhận bị đổi thành mã ship khác
                var arr_ship_no_check = [];//Mảng ship cũ không check
                var arr_detail_id_check = [];
                for(var i = 0; i < data_old.length; i++){//Chạy mảng group order_code
                    if(data_old[i].length > 1){//Nếu order có nhiều sản phẩm thì kiểm tra các trường hợp của logic đổi mã ship
                        var arr_shipcode_change = [];
                        var arr_shipcode_unchange = [];
                        var arr_ship_change = [];
                        var arr_ship_unchange = [];
                        var data_old_temp = data_old[i];
                        for(var j = 0; j < data_old[i].length; j++){
                            for(var k = 0; k < arr_shipment_update.length; k++){//Chạy mảng ship được check trên màn hình để kiểm tra có mã ship nào cần tạo mới không
                                if(data_old[i][j].order_detail_id == arr_shipment_update[k].order_detail_id){
                                    arr_detail_id_check.push(arr_shipment_update[k].order_detail_id)
                                    if(data_old[i][j].shipment_code == arr_shipment_update[k].shipment_code){//Không đổi mã ship
                                        arr_shipcode_unchange.push(arr_shipment_update[k]);
                                        arr_ship_unchange.push(arr_shipment_update[k].shipment_code);
                                    }else {//Đổi mã ship
                                        data_old_temp[j].shipment_code = arr_shipment_update[k].shipment_code;
                                        arr_shipcode_change.push(arr_shipment_update[k]);
                                        arr_ship_change.push(arr_shipment_update[k].shipment_code);
                                    }
                                }
                            }
                            if(arr_detail_id_check.includes(data_old[i][j].order_detail_id+"") == false){
                                arr_ship_no_check.push(data_old[i][j]);
                            }
                        }
                        var arr_duplicate_ship_change = findDuplicates(arr_ship_change);//Mảng mã ship có từ 2 sản phẩm trở lên
                        var arr_duplicate_ship_unchange = findDuplicates(arr_ship_unchange);//Mảng mã ship có từ 2 sản phẩm trở lên
                        //Kiểm tra các sản phẩm trùng mã ship mà có ngày giao hàng khác nhau thì báo lỗi
                        for(var j = 0; j < arr_shipcode_change.length; j++){//Chạy mảng ship thay đổi
                            if(arr_duplicate_ship_change.includes(arr_shipcode_change[j].shipment_code+"")){//Nếu ship tồn tại trong mảng có từ 2 sản phẩm trở lên
                                arr_update_ships.push(arr_shipcode_change[j])
                                for(var k = j+1; k < arr_shipcode_change.length; k++){//Chạy từ phần tư i+1 để check ngày giao hàng của cũng ship có khác nhau thì báo lỗi
                                    if(arr_shipcode_change[j].shipment_code == arr_shipcode_change[k].shipment_code
                                        && arr_shipcode_change[j].shipment_date != arr_shipcode_change[k].shipment_date){
                                        ModalError('同梱する商品の納品日が一致しないため保存できません。同じ送り状番号の商品は納品日を同じ日付で入力してください。', '保存出来ていません。', 'OK')
                                        return false;
                                    }
                                }
                            }else {//Kiểm tra nếu có gộp ship thì báo lỗi
                                arr_update_ship.push(arr_shipcode_change[j]);
                            }
                            for(var l = 0; l < data_old_temp.length; l++){
                                if(arr_shipcode_change[j].shipment_code == data_old_temp[l].shipment_code
                                    && arr_shipcode_change[j].ship_id != data_old_temp[l].ship_id){
                                    ModalError('お届け先の異なる商品に、同じ送り状番号は保存できません。別の送り状番号を登録してください。', '保存出来ていません。', 'OK')
                                    return false;
                                }
                            }
                        }
                        //Kiểm tra các sản phẩm trùng mã ship mà có ngày giao hàng khác nhau thì báo lỗi
                        for(var j = 0; j < arr_shipcode_unchange.length; j++){//Chạy mảng ship không thay đổi
                            if(arr_duplicate_ship_unchange.includes(arr_shipcode_unchange[j].shipment_code+"")){//Nếu ship tồn tại trong mảng có từ 2 sản phẩm trở lên
                                for(var k = j+1; k < arr_shipcode_unchange.length; k++){//Chạy từ phần tư i+1 để check ngày giao hàng của cũng ship có khác nhau thì báo lỗi
                                    if(arr_shipcode_unchange[j].shipment_code == arr_shipcode_unchange[k].shipment_code
                                        && arr_shipcode_unchange[j].shipment_date != arr_shipcode_unchange[k].shipment_date){
                                        ModalError('同梱する商品の納品日が一致しないため保存できません。同じ送り状番号の商品は納品日を同じ日付で入力してください。', '保存出来ていません。', 'OK')
                                        return false;
                                    }
                                }
                            }
                        }
                        if(arr_shipcode_change.length == data_old[i].length){//Kiểm tra nếu không còn mã ship cũ thì remove luôn ship cũ
                            arr_remove_ship.push(data_old[i][0].ship_id)
                        }
                    }
                }
                // Cập nhật thông tin order detail
                data_update = {
                    'check_update': updated_at,
                    'arr_checked': arr_checked,
                    'one_on_one': arr_update_ship,
                    'one_on_many': arr_update_ships,
                    'list_remove_ship': arr_remove_ship
                }
                ModalConfirmReturn('入力した内容を保存します。よろしいでしょうか？', 'キャンセル', 'Ok', url_update_order_detail, data_update, 22)
            }
        }
    }else {
        ModalError('データはありません。保存出来ません。検索条件を再度確認してください。', '', 'OK')//không tìm thấy dữ liệu. vui lòng kiểm tra lại điều kiện tìm kiếm
    }
})

$(document).on('blur keydown keyup', '.bill-number', function (e) {
    if (e.which == 32){
        return false
    }    
});

// Kiểm tra xem có ngày giao hàng lớn hơn ngày đặt hàng
function checkDelivDate(){
    var flag = false;// kiểm tra xem có ngày giao hàng lớn hơn ngày đặt hàng hoặc tiền mua hàng + tiền đính chính <= 0 thì return false
    var table = $(".table-result tbody");
    table.find('tr').each(function (key, val) {
        var $tds = $(this).find('td'),
        o_date = $tds.eq(1).text(),   
        deliv_date = $(this).find('td .deliv-date').val();
        checked = $(this).find('td input:checkbox[name=check_one]:checked').val();
        var total_price = parseFloat($tds.eq(9).text().replace(/,/g, ''));
        var price_edit = parseFloat($(this).find('td .price-edit').val().replace(/,/g, ''));
        var bill_number = $(this).find('td .bill-number').val();
        if(checked == 'on'){
            if(bill_number == '' || bill_number.trim().length == 0){
                flag = true;
                $(this).find('td .tt-bill-number-empty').addClass("tooltip-text")
                $(this).find('td .tooltip-text').show();
                $(this).find('td .tooltip-text').delay(5000).fadeOut();
            }
            if(price_edit < 0 && price_edit * (-1) >= total_price){//Kiểm tra nhập tiền đính chính sao cho update tiền mua hàng phải > 0
                flag = true;
                $(this).find('td .tt-price-edit').addClass("tooltip-text")
                $(this).find('td .tooltip-text').show();
                $(this).find('td .tooltip-text').delay(5000).fadeOut();
            }
            if(o_date > deliv_date){// Show thông báo nếu ngày giao hàng < ngày đặt hàng
                flag = true;
                $(this).find('td .tt-deliv-date').addClass("tooltip-text")
                $(this).find('td .tooltip-text').show();
                $(this).find('td .tooltip-text').delay(5000).fadeOut();
            }
        }
    });
    if(flag == true){
        setTimeout(function(){
            table.find('tr').each(function () {
                $(this).find('td:eq(2) span').removeClass("tooltip-text")
                $(this).find('td:eq(4) span').removeClass("tooltip-text")
                $(this).find('td:eq(10) span').removeClass("tooltip-text")
            })
        }
        , 5500);
        return false;
    }
    return true;
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

// Check all checkbox
$("#check_all").click(function(){
    var value_check = $(this).prop('checked') 
    var table = $(".table-result tbody");
    table.find('tr').each(function (key, val) {
        $(this).find('td .check_one').prop('checked', value_check);
    });
});


$(".datepicker").each(function() {    
    $(this).datepicker('setDate', new Date($(this).val()));
});