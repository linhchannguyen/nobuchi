$(document).ready(function() {
    $( ".datepicker" ).datepicker({
        dateFormat: 'yy/mm/dd'
    })

    let d = new Date(getDateNow());
    d.setDate(d.getDate() - 1);
    date_from = getD(d);
    date_to = getDateNow();
    searchPurchases(1, date_from, date_to, null)
})

$('.check_flag_search').on('click', function(){
    $(this).find('.flag_search').prop('checked', true);
});

$('.check_flag_stage').on('click', function(){
    checked = $(this).find('.flag_stage').prop('checked');
    if(checked == true){
        $(this).find('.flag_stage').prop('checked', false);
    }else {
        $(this).find('.flag_stage').prop('checked', true);
    }
});

$('.flag_stage').on('click', function(){
    checked = $(this).prop('checked');
    if(checked == true){
        $(this).prop('checked', false);
    }else {
        $(this).prop('checked', true);
    }
});
//click search result
$('.btn-search').on('click', function(){
    var radio = $('input[name=radio_date]:checked', '#form_purchase').val();
    var date_from = $('.date-from').val();
    var date_to = $('.date-to').val();
    searchPurchases(radio, date_from, date_to, true)
});

function searchPurchases(radio, date_from, date_to, load_index = null){
    if(!_validatedate(date_from)){
        $('.error-date').css('display', 'block');
        $('.error-text-date').text('無効な日付です。再入力してください。');
        return false;
    }else if(!_validatedate(date_to)){
        $('.error-date').css('display', 'block');
        $('.error-text-date').text('無効な日付です。再入力してください。');
        return false;
    }
    // Kiểm tra giá trị null khi nhấn tìm kiếm
    if(date_from == '' && date_to == ''){
        $('.error-date').css('display', 'block');
        $('.error-text-date').text('検索日時を選択してください。');
        return false;
    }
    else if(date_from == ''){
        $('.error-date').css('display', 'block');
        $('.error-text-date').text('日時自を選択してください。');
        return false;
    }else if(date_to == ''){
        $('.error-date').css('display', 'block');
        $('.error-text-date').text('日時至を選択してください。');
        return false;
    }else {
        $('.error-date').css('display', 'none');
    }
    if(date_from > date_to){
        $('.error-date').css('display', 'block');
        $('.error-text-date').text('日時至は日時自以降で選択してください。');
        return false;
    // end Kiểm tra giá trị null khi nhấn tìm kiếm
    }else {
        if(load_index == true){
            loading()
        }
        $('.error-date').css('display', 'none');
        $.ajax({
            headers: { 'X-CSRF-TOKEN': token_header },
            type: "POST",
            url: url_search,
            data: {
                range: radio,
                date_from: $('.date-from').val(),
                date_to: $('.date-to').val()
            },
            success: function(res){
                $('.search-result').css('display', 'block');
                //url
                var url = `order/search-order?`;
                if(radio == 0){//Ngày tạo order
                    url += `date_created_from=`+date_from+`&date_created_to=`+date_to;
                }
                if(radio == 1){//Ngày import
                    url += `date_import_from=`+date_from+`&date_import_to=`+date_to;
                }
                if(radio == 2){//Ngày đặt order
                    url += `date_purchased_from=`+date_from+`&date_purchased_to=`+date_to;
                }
                if(radio == 3){//Ngày xuất hàng
                    url += `delivery_date_from=`+date_from+`&delivery_date_to=`+date_to;
                }
                if(radio == 4){//Ngày dự định xuất hàng
                    url += `ship_schedule_from=`+date_from+`&ship_schedule_to=`+date_to;
                }
                if(radio == 5){//Ngày dự định nhận hàng
                    url += `recive_schedule_from=`+date_from+`&recive_schedule_to=`+date_to;
                }
                table_result =
                `<thead>
                    <th width="5%">番号</th>
                    <th width="27%">集荷先</th>
                    <th width="11%">受注処理中</th>
                    <th width="11%">要確認件数</th>
                    <th width="11%">保留中件数</th>
                    <th width="35%">データダウンロード</th>
                </thead>
                <tbody>`;
                let j = 1;
                for(let i = 0; i < res.data_2.length; i++){
                    if(res.data_2[i].status_o3_p1 > 0 || res.data_2[i].status_o4_p1 > 0 || res.data_2[i].status_o5_p1 > 0){
                        table_result += `
                        <tr>
                            <td style="text-align: center;">
                            <input type="hidden" name="supplier-id" class="supplier-id" value="`+ res.data_2[i].id +`">
                            <input type="hidden" name="supplier-name" class="supplier-name" value="`+ res.data_2[i].name +`">
                            `+ (j) +`</td>
                            <td>`+ res.data_2[i].name +`</td>
                            <td>
                                <input type="hidden" class="s_o3_p1" value="`+ res.data_2[i].status_o3_p1 +`" />
                                <a href="`+url+`&status_support=3&flag_confirm=1&supplied_id=`+res.data_2[i].id+`" target="_blank">`+ res.data_2[i].status_o3_p1 +`件</a>
                            </td>
                            <td>
                                <input type="hidden" class="s_o4_p1" value="`+ res.data_2[i].status_o4_p1 +`">
                                <a href="`+url+`&status_support=4&flag_confirm=1&supplied_id=`+res.data_2[i].id+`" target="_blank">`+res.data_2[i].status_o4_p1 +`件</a>
                            </td>
                            <td>
                                <input type="hidden" class="s_o5_p1" value="`+ res.data_2[i].status_o5_p1 +`">
                                <a href="`+url+`&status_support=5&flag_confirm=1&supplied_id=`+res.data_2[i].id+`" target="_blank">`+ res.data_2[i].status_o5_p1 +`件</a>
                            </td>
                            <td>
                                <span style="margin-right: 15px;">
                                    <select style="width: 250px;" class="stage_select">
                                        <option value="0"></option>
                                        <option value="1">発注一覧表＋発注明細・梱包指示書</option>
                                        <option value="11">PDF発注一覧表＋発注明細・梱包指示書</option>
                                        <option value="2">発注一覧表</option>
                                        <option value="22">PDF発注一覧表</option>
                                        <option value="3">発注明細・梱包指示書</option>
                                        <option value="33">PDF発注明細・梱包指示書</option>
                                    </select>
                                </span>
                                <span style="float: right;"><a class="btn-download">ダウンロード</a></span>
                            </td>
                        </tr>`;
                        j++
                    }
                }                
                if(j == 1){
                    table_result += `
                        <tr>
                            <td class="text-center" colspan="6" style="color: red;">検索条件に該当するデータがありません。</td>
                        </tr>`;
                    $('.dataTables_paginate').css('display', 'none')
                    $('.top').hide()
                }
                table_result +=
                `</tbody>`;
                $(".table-result").html(table_result);
                if(j > 1){
                    setDataTable('.table-result');
                    if(j > 51){
                        $('.top').show()
                    }else {
                        $('.top').hide()
                    }
                }
                endLoading()
            }
        })
    }
}

$(".table-result").on("click", ".btn-download", function(e) {
    var currentRow = $(this).closest("tr");
    var check_select = currentRow.find("td .stage_select").val();
    var select_text = currentRow.find("td .stage_select option:selected").text();
    if(check_select == 0){//Không chọn dropdown download
        ModalError('出力内容を選択してから再度ダウンロードボタンを押してください。', '', 'OK')
        return false;
    }else {
        //Lấy giá trị checkbok của 操作
        var stage1 = 0;//0: không check bỏ chọn loại cần xác nhận. 1: có check
        var stage2 = 0;//0: không check bỏ chọn loại đang bảo lưu. 1: có check
        var stage3 = 0;//0: không check update trang thai order => 4 (đang xl đóng gói)
        var stage4 = 0;//0: không check tự động gửi fax trong lúc download
        $.each($("input[name='checkbox_stage']:checked"), function(){
            var val = $(this).val();
            if(val == "1"){
                stage1 = 1;
            }
            else if(val == "2"){
                stage2 = 2;
            }
            else if(val == "3"){
                stage3 = 3;
            }
            else if(val == "4"){
                stage4 = 4;
            }
        });
        // End lấy giá trị checkbok của 操作
        var radio = $('input[name=radio_date]:checked', '#form_purchase').val();
        var date_from = $('.date-from').val();
        var date_to = $('.date-to').val();
        var supplier_id = currentRow.find("td .supplier-id").val();//Id nhà cung cấp
        var supplied = currentRow.find("td .supplier-name").val();//Tên nhà cung cấp
        var status_o3_p1 = parseInt(currentRow.find('td .s_o3_p1').val());//A3-B1
        var status_o4_p1 = parseInt(currentRow.find('td .s_o4_p1').val());//A4-B1
        var status_o5_p1 = parseInt(currentRow.find('td .s_o5_p1').val());//A5-B1
        var param = {};
        if((status_o3_p1 + status_o4_p1 + status_o5_p1) > 0){
            if(stage1 == 1 && stage2 == 2 && status_o3_p1 == 0){//có check bỏ chọn loại đang bảo lưu và cần xác nhận
                ModalError('データはありません。'+select_text+'が出力出来ません。', '', 'OK')
                return false;
            }
            else if(stage1 == 1 && stage2 == 0 && status_o5_p1 == 0 && status_o3_p1 == 0){//chỉ check bỏ chọn loại cần xác nhận
                ModalError('データはありません。'+select_text+'が出力出来ません。', '', 'OK')
                return false;
            }
            else if(stage1 == 0 && stage2 == 2 && status_o4_p1 == 0 && status_o3_p1 == 0){//chỉ check bỏ chọn loại đang bảo lưu
                ModalError('データはありません。'+select_text+'が出力出来ません。', '', 'OK')
                return false;
            }else {
                param = {
                    'supplier_id': supplier_id,
                    'supplied': supplied,
                    'supplier_name': supplied,
                    'range': radio,
                    'date_from': date_from,
                    'date_to': date_to,
                    'stage1': (stage1 == 0) ? null : stage1,
                    'stage2': (stage2 == 0) ? null : stage2,
                    'stage3': (stage3 == 0) ? null : stage3,
                    'stage4': (stage4 == 0) ? null : stage4,
                    'sel_download': check_select,
                    'screen': 5,
                    'pdf': (check_select == 11 || check_select == 22 || check_select == 33) ? true : null
                }
                if(stage3 == 3){//Nếu có chọn update tình trạng hỗ trợ thì hiển thị thông báo xác nhận
                    ModalConfirmReturn(supplied+message_confirm_f+select_text+message_confirm_l, 'キャンセル', 'Ok', url_export_one, param, 5);
                }else {
                    loading()
                    $.ajax({
                        cache: false,
                        url: url_export_one, //GET route
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
                            let file_name = supplied + date_[0] + date_[1] + date_[2];
                            let link = '';
                            if(check_select == 11 || check_select == 22 || check_select == 33){
                                link = '<a id='+randomId+' href='+url+' download='+file_name+'.pdf>link</a>';
                            }else {
                                link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
                            }
                            $('body').append(link)
                            $(`#${randomId}`)[0].click()
                            $(`#${randomId}`).remove()
                            if(status === 'success' && stage4 == 4)
                            {
                                ModalSuccessNoReload('FAXを送信しました。', 'Ok')
                            }
                            endLoading()
                        },
                        error: function (ajaxContext) {
                            endLoading()
                        }
                    });
                }
            }
        }else {
            ModalError('データはありません。'+select_text+'が出力出来ません。', '', 'OK')
            return false;
        }
    }
});

//click button search date today
$('.btn-search-today').on('click', function(){
    $(".date-from").val(getDateNow())
    $(".date-to").val(getDateNow())
});

//click button search date from - date to
$('.btn-search-from-to').on('click', function(){
    let d = new Date(getDateNow());
    d.setDate(d.getDate() - 1);
    $(".date-from").val(getD(d))
    $(".date-to").val(getDateNow())
});

//function get yesterday
function getD(d){
    var month = d.getMonth()+1;
    var day = d.getDate();
    var output = d.getFullYear() + '/' +
    (month<10 ? '0' : '') + month + '/' +
    (day<10 ? '0' : '') + day;
    return output;
}

//function get date now
function getDateNow(){
    var d = new Date();
    var month = d.getMonth()+1;
    var day = d.getDate();
    var output = d.getFullYear() + '/' +
    (month<10 ? '0' : '') + month + '/' +
    (day<10 ? '0' : '') + day;
    return output;
}