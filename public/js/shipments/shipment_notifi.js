var arr_checkbox = [];
$(document).ready(function() {      
    $( ".datepicker" ).datepicker({
        dateFormat: 'yy/mm/dd'
    })
    let d = new Date(getDateNow());
    d.setDate(d.getDate() - 1);
    let date_from = getD(d);
    let date_to = getDateNow();
    searchShipmentNotification(1, date_from, date_to, null)

    $(document).on('click', '.btn-import', function(){
        loading();
        var data = new FormData();
        var file_data = $('#getFile').prop('files')[0];
        data.append('result_file', file_data);
        $.ajax({
            cache: false,
            contentType: false,
            processData: false,
            method: "POST",
            url: url_import_ship,
            data: data,
            success: function(res){
                var message = '';
                if(res.status == true){
                    if(res.list_error.length <= 0){
                        message += res.success;
                    }else {
                        var str_error = ``;
                        for(var i = 0; i < res.list_error.length; i++){
                            str_error += res.list_error[i]+'、';
                        }
                        str_error = str_error.substring(0, (str_error.length - 1));
                        message += '下記の行目だけは取込めません。再確認してください。行目：'+str_error;
                    }
                    ModalSuccessNoReload(message, 'Ok')
                }else {
                    ModalSuccessNoReload(res.message, 'Ok')
                }
                endLoading();
            }
        })
    })
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

$("#getFile").change(function() {
    filename = this.files[0].name
    $('.file-name').val(filename)
});

$("input:checkbox[name=checkbox_stage]:checked").each(function(){
    arr_checkbox.push($(this).val());
});

//click search result
$('.btn-search').on('click', function(){
    $('.alert').css('display', 'none')
    var radio = $('input[name=radio_date]:checked', '#form_shipment').val();
    var date_from = $('.date-from').val();
    var date_to = $('.date-to').val();
    searchShipmentNotification(radio, date_from, date_to, true)
});

function searchShipmentNotification(radio, date_from, date_to, load_index = null){
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
    // Kiểm tra giá trị null khi nhấn tìm kiếm
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
                $('.search-result').css('display', 'block');//url                    
                let url = `order/search-order?`;
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
                table_site_type =
                `<thead>
                    <th width="5%">番号</th>
                    <th width="27%">ECサイト別</th>
                    <th width="11%">受注処理中</th>
                    <th width="11%">要確認件数</th>
                    <th width="11%">保留中件数</th>
                    <th width="35%">出荷通知データダウンロード</th>
                </thead>
                <tbody>`;
                for(var i = 0; i < res.data_2.length; i++){
                    if(res.data_2[i].id != 0){
                        table_site_type += ` 
                        <tr>
                            <td style="text-align: center;">
                                <input type="hidden" class="st-id" value="`+ res.data_2[i].id +`">
                                <input type="hidden" class="st-name" value="`+ res.data_2[i].name +`">`
                                + (i+1) +
                            `</td>
                            <td>`+ res.data_2[i].name +`</td>
                            <td>
                                <input type="hidden" class="s_o3_p3" value="`+ res.data_2[i].status_o3_p3 +`" />
                                <a href="`+url+`&status_support=3&flag_confirm=3&site_type=`+res.data_2[i].id+`" target="_blank">`+ res.data_2[i].status_o3_p3 +`件</a>
                            </td>
                            <td>
                                <input type="hidden" class="s_o4_p3" value="`+ res.data_2[i].status_o4_p3 +`">                                
                                <a href="`+url+`&status_support=4&flag_confirm=3&site_type=`+res.data_2[i].id+`" target="_blank">`+ res.data_2[i].status_o4_p3 +`件</a>
                            </td>
                            <td>
                                <input type="hidden" class="s_o5_p3" value="`+ res.data_2[i].status_o5_p3 +`">
                                <a href="`+url+`&status_support=5&flag_confirm=3&site_type=`+res.data_2[i].id+`" target="_blank">`+ res.data_2[i].status_o5_p3 +`件</a>
                            </td>
                            <td>
                                <span style="margin-right: 15px;">
                                    <select style="width: 60%" class="stage_select">
                                        <option value="0"></option>
                                        <option `+ ((res.data_2[i].id == 1) ? 'style="display: none;"' : '') + ` value="1">出荷通知データ</option>
                                        <option value="2">送り状番号ファイル</option>
                                        <option value="3">発注明細・梱包指示書</option>
                                        <option value="33">PDF発注明細・梱包指示書</option>
                                    </select>
                                </span>            
                                <span style="float: right;"><a class="btn-download" style="width: 40%">ダウンロード</a></span>       
                            </td>
                        </tr>`;
                    }else {
                        table_site_type += ` 
                        <tr>
                            <td style="text-align: center;">
                                <input type="hidden" class="st-id" value="`+ res.data_2[i].id +`">
                                <input type="hidden" class="st-name" value="`+ res.data_2[i].name +`">`
                                + 9 +
                            `</td>
                            <td>その他</td>
                            <td>
                                <input type="hidden" class="s_o3_p3" value="`+ res.data_2[i].orther_1 +`" />
                                <a href="`+url+`&status_support=3&flag_confirm=3&site_type=`+res.data_2[i].id+`" target="_blank">`+ res.data_2[i].orther_1 +`件</a>
                            </td>
                            <td>
                                <input type="hidden" class="s_o4_p3" value="`+ res.data_2[i].orther_2 +`">                                
                                <a href="`+url+`&status_support=4&flag_confirm=3&site_type=`+res.data_2[i].id+`" target="_blank">`+ res.data_2[i].orther_2 +`件</a>
                            </td>
                            <td>
                                <input type="hidden" class="s_o5_p3" value="`+ res.data_2[i].orther_3 +`">
                                <a href="`+url+`&status_support=5&flag_confirm=3&site_type=`+res.data_2[i].id+`" target="_blank">`+ res.data_2[i].orther_3 +`件</a>
                            </td>
                            <td>
                                <span style="margin-right: 15px;">
                                    <select style="width: 60%" class="stage_select">
                                        <option value="0"></option>
                                        <option value="2">送り状番号ファイル</option>
                                        <option value="3">発注明細・梱包指示書</option>
                                        <option value="33">PDF発注明細・梱包指示書</option>
                                    </select>
                                </span>            
                                <span style="float: right;"><a class="btn-download" style="width: 40%">ダウンロード</a></span>       
                            </td>
                        </tr>`;
                    }
                }
                table_site_type += 
                `</tbody>`;
                $(".table-site-type").html(table_site_type);
                endLoading()
            }
        })
    }
}

//export file
$(".table-site-type").on("click", ".btn-download", function(e) {
    var amazon = [4,5,6,7,8];//Website amazon
    var currentRow = $(this).closest("tr");
    var check_select = currentRow.find("td .stage_select").val();
    var select_text = currentRow.find("td .stage_select option:selected").text();
    if(check_select == 0){//Không chọn dropdown download        
        ModalError('出力内容を選択してから再度ダウンロードボタンを押してください。', '', 'OK')
        return false;
    }else {
        // Lấy giá trị checkbok của 操作
        var stage1 = 0;//0: không check bỏ chọn loại cần xác nhận
        var stage2 = 0;//0: không check bỏ chọn loại đang bảo lưu
        var stage3 = 0;//0: không check đổi trạng thái đặt hàng từ B3 sang B4
        var a = [];
        $.each($("input[name='checkbox_stage']:checked"), function(){
            var val = $(this).val();
            a.push(val)
            if(val == "1"){
                stage1 = 1;
            }else if (val == "2"){
                stage2 = 2;
            }
            else{
                stage3 = 3;
            }
        });
        // End lấy giá trị checkbok của 操作
        var radio = $('input[name=radio_date]:checked', '#form_shipment').val();
        var date_from = $('.date-from').val();
        var date_to = $('.date-to').val();
        var site_type_id = currentRow.find("td .st-id").val();
        var status_o3_p3 = parseInt(currentRow.find('td .s_o3_p3').val());//A3-B3
        var status_o4_p3 = parseInt(currentRow.find('td .s_o4_p3').val());//A4-B3
        var status_o5_p3 = parseInt(currentRow.find('td .s_o5_p3').val());//A5-B3
        var param = {};
        if((status_o3_p3 + status_o4_p3 + status_o5_p3) > 0){
            if(stage1 == 1 && stage2 == 2 && status_o3_p3 == 0){//có check bỏ chọn loại đang bảo lưu và cần xác nhận
                ModalError('データはありません。'+select_text+'が出力出来ません。', '', 'OK')
                return false;
            }
            else if(stage1 == 1 && stage2 == 0 && status_o5_p3 == 0 && status_o3_p3 == 0){//chỉ check bỏ chọn loại cần xác nhận
                ModalError('データはありません。'+select_text+'が出力出来ません。', '', 'OK')
                return false;
            }
            else if(stage1 == 0 && stage2 == 2 && status_o4_p3 == 0 && status_o3_p3 == 0){//chỉ check bỏ chọn loại đang bảo lưu
                ModalError('データはありません。'+select_text+'が出力出来ません。', '', 'OK')
                return false;
            }else {  
                if(stage3 == 3){
                    if(check_select == 1){
                        if(amazon.includes(parseInt(site_type_id))){//Loại website amazon
                            param = {
                                'site_type': site_type_id,
                                'range': radio,
                                'date_from': date_from,
                                'date_to': date_to,
                                'stage1': ((stage1 == 0) ? null : stage1),
                                'stage2': ((stage2 == 0) ? null : stage2),
                                'stage3': ((stage3 == 0) ? null : stage3),
                                'website': 'amazon',
                                'sel_download': 1,
                                'url_get_list_supplier_by_site_type': url_get_list_supplier_by_site_type,
                                'export_notification_amazon': export_notification_amazon
                            };
                        }else if(parseInt(site_type_id) == 2){
                            let date_ = getDateNow().split('/');
                            param = {
                                'site_type': site_type_id,
                                'range': radio,
                                'date_from': date_from,
                                'date_to': date_to,
                                'stage1': ((stage1 == 0) ? null : stage1),
                                'stage2': ((stage2 == 0) ? null : stage2),
                                'stage3': ((stage3 == 0) ? null : stage3),
                                'website': 'rakuten',
                                'file_name': 'rakuten'+ date_[0] + date_[1] + date_[2],
                                'sel_download': 1
                            };
                        }else if(parseInt(site_type_id) == 3){
                            let date_ = getDateNow().split('/');
                            param = {                        
                                'site_type': site_type_id,
                                'range': radio,
                                'date_from': date_from,
                                'date_to': date_to,
                                'stage1': (stage1 == 0) ? null : stage1,
                                'stage2': (stage2 == 0) ? null : stage2,
                                'stage3': (stage3 == 0) ? null : stage3,
                                'website': 'yahoo',
                                'file_name': 'odstats_order',
                                'sel_download': 1,
                                'screen': 7
                            }
                        }
                    }else if (check_select == 2){                  
                        param = {
                            'site_type': site_type_id,
                            'range': radio,
                            'date_from': date_from,
                            'date_to': date_to,
                            'stage1': (stage1 == 0) ? null : stage1,
                            'stage2': (stage2 == 0) ? null : stage2,
                            'stage3': (stage3 == 0) ? null : stage3,
                            'sel_download': 2,
                            'url_export_shipment_bill': url_export_shipment_bill
                        }
                    }else {                     
                        param = {
                            'site_type': site_type_id,
                            'range': radio,
                            'date_from': date_from,
                            'date_to': date_to,
                            'stage1': (stage1 == 0) ? null : stage1,
                            'stage2': (stage2 == 0) ? null : stage2,
                            'stage3': (stage3 == 0) ? null : stage3,
                            'sel_download': check_select,
                            'url_get_list_supplier_by_site_type': url_get_list_supplier_by_site_type,
                            'url_export_purchase': url_export_purchase
                        }
                    }
                    ModalConfirmReturn(select_text+'を出力します。よろしいでしょうか？', 'キャンセル', 'Ok', url_export_shipment_notification, param, 7);
                }else {
                    loading()
                    if(check_select == 1){//Xuất thông báo gửi hàng
                        if(amazon.includes(parseInt(site_type_id))){//Loại website amazon
                            param = {
                                'site_type': site_type_id,
                                'range': radio,
                                'date_from': date_from,
                                'date_to': date_to,
                                'stage1': (stage1 == 0) ? null : stage1,
                                'stage2': (stage2 == 0) ? null : stage2,
                            }
                            $.ajax({
                                type: 'post',
                                url: url_get_list_supplier_by_site_type, //GET route 
                                data:  param,
                                success: function (result) {
                                    if(result.status == true){
                                        let list_details = [];
                                        result.data.forEach(function (element) {
                                            list_details.push(element.detail_id)
                                        })
                                        window.location = export_notification_amazon+'?list_details='+list_details+'&screen=7&site_type='+site_type_id
                                    }else {
                                        ModalError('警報', '', 'OK')
                                    }
                                    endLoading()
                                }
                            });
                        }else if(parseInt(site_type_id) == 2){//Loại website rakuten
                            let date_ = getDateNow().split('/');
                            let file_name = 'rakuten' + date_[0] + date_[1] + date_[2];
                            window.location = url_export_shipment_notification+'?site_type='+site_type_id+'&range='+radio+'&date_from='+date_from+'&date_to='
                            +date_to+'&stage1='+((stage1 == 0) ? null : stage1)+'&stage2='+((stage2 == 0) ? null : stage2)+'&stage3='+((stage3 == 0) ? null : stage3)
                            +'&website=rakuten&file_name='+file_name+'&screen=7';
                            endLoading()
                        }else if(parseInt(site_type_id) == 3) {//Loại website yahoo
                            let file_name = 'odstats_order';
                            window.location = url_export_shipment_notification+'?site_type='+site_type_id+'&range='+radio+'&date_from='+date_from+'&date_to='
                            +date_to+'&stage1='+((stage1 == 0) ? null : stage1)+'&stage2='+((stage2 == 0) ? null : stage2)+'&stage3='+((stage3 == 0) ? null : stage3)
                            +'&website=yahoo&file_name='+file_name+'&screen=7';
                            endLoading()
                        }
                    }else if(check_select == 2) {//Xuất file mã bill gửi hàng
                        endLoading()
                        let date_ = getDateNow().split('/');
                        window.location = url_export_shipment_bill+'?site_type='+site_type_id+'&range='+radio+'&date_from='+date_from+
                                        '&date_to='+date_to+'&stage1='+((stage1 == 0) ? null : stage1)+'&stage2='+((stage2 == 0) ? null : stage2)+
                                        '&file_name=shipment_code_'+date_[0]+date_[1]+date_[2];
                    }else {//Xuất giấy chỉ dẫn đóng gói, chi tiết đặt hàng                   
                        param = {
                            'site_type': site_type_id,
                            'range': radio,
                            'date_from': date_from,
                            'date_to': date_to,
                            'stage1': (stage1 == 0) ? null : stage1,
                            'stage2': (stage2 == 0) ? null : stage2,
                        }
                        $.ajax({
                            type: 'post',
                            url: url_get_list_supplier_by_site_type, //GET route 
                            data:  param,
                            success: function (result) {
                                if(result.status == true){
                                    let arr_sup = [];
                                    result.data.forEach(function (element_su) {
                                        arr_sup.push(element_su.supplied_id)
                                    })
                                    let data_download = [];
                                    let supplied_list = [... new Set (arr_sup)];
                                    // add những order_details có chung nhà cung cấp thành 1
                                    supplied_list.forEach(function (element_su) {
                                        let detail_su =[];
                                        let name_su = '';
                                        result.data.forEach(function (element, index) {
                                            if(element.supplied_id === element_su)
                                            {
                                                name_su = element.supplied
                                                detail_su.push(element.detail_id)
                                            }
                                        })
                                        let arr = {
                                            'supplied': name_su,
                                            'details': detail_su
                                        }
                                        data_download.push(arr)
                                    })
                                    if(check_select == 33){
                                        doExport(data_download, 0, null, 3, 7, url_export_purchase, null)
                                    }else {
                                        $.each(data_download, function( key, value ) {  
                                            param = {
                                                'arr_detail': value.details,
                                                'sel_download': check_select,
                                                'screen': 7,
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
                                                    link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
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
                                    ModalError('警報', '', 'OK')
                                    endLoading()
                                }
                            }
                        });     
                    }
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