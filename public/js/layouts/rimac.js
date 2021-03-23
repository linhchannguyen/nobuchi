$(document).ready(function () {
    $('.top').hide();
    var DEBUG = true; // True set cho hiện thị consolog, False không cho hiện thị 
    if(!DEBUG){
        if(!window.console) window.console = {};
        var methods = ["log", "debug", "warn", "info"];
        for(var i=0;i<methods.length;i++){
            console[methods[i]] = function(){};
        }
    }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        statusCode:{
            405: function () {
                window.location.reload()
            }
        }
    })
    $(".date-jp" ).datepicker({ 
        dateFormat: 'yy/mm/dd',
        timeFormat:  "hh:mm:ss"
    }); // date picker
    
    let d = new Date(getDateNow());
    d.setDate(d.getDate() - 1);
    $(".date-from").val(getD(d))
    $(".date-to").val(getDateNow())
    
    //Dropdow logout
	$(document).on('click', '.header-right', function(){
		document.getElementById("myDropdown").classList.toggle("show");
	})
	$(document).click(function(e) {
        if (!$(e.target).is('.header-right *')) {
		    $('.dropdown-content').removeClass("show");
        }
    });
    //End dropdow logout
    /**
     * number_format
     */
    number_format = function (number, decimals, dec_point, thousands_sep) {
        if(isNaN(number))
        {
            number = 0
        }
        number = parseFloat(number)
        number = number.toFixed(decimals);

        var nstr = number.toString();
        nstr += '';
        x = nstr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? dec_point + x[1] : '';
        var rgx = /(\d+)(\d{3})/;

        while (rgx.test(x1))
            x1 = x1.replace(rgx, '$1' + thousands_sep + '$2');

        return x1 + x2;
    }
    /**
     * number input 
     */
    $(document).on('blur', '.number-input', function (){
        let value = number_format(parseFloat($(this).val().replace(/\,/g,'')),0,'.',',')
        $(this).val(value)
    })
    /**
     * check input number
     */
    $(document).on('keydown keyup', '.number-input', function (e){
        var regExp = /[0-9\-\.\,]/;
        var value = e.key;
        var val = $(this).val()
        if(e.which === 67 ||e.which === 65 || e.which === 88 || e.which === 86)
        {
            $(this).val(val.replace(value,''))
        }
        if (!regExp.test(value)
          && e.which != 8   // backspace
          && e.which != 46  // delete
          && e.which != 9  // tab
          && e.which != 17  // ctrl
          && e.which != 67  // c
          && e.which != 65  // a
          && e.which != 86 //v
          && e.which != 88  // x
          && (e.which < 37  // arrow keys
            || e.which > 40)) {
              e.preventDefault();
              return false;
        }
    })
    /**
     * check input number
     */
    $(document).on('keydown keyup', '.number', function (e){
        var regExp = /[0-9\-\.\,]/;
        var value = e.key;
        
        var val = $(this).val()
        if(e.which === 67 ||e.which === 65 || e.which === 88 || e.which === 86)
        {
            $(this).val(val.replace(value,''))
        }
        if (!regExp.test(value)
          && e.which != 8   // backspace
          && e.which != 46  // delete
          && e.which != 9  // tab
          && e.which != 17  // ctrl
          && e.which != 67  // c
          && e.which != 65  // a
          && e.which != 86 //v
          && e.which != 88  // x
          && (e.which < 37  // arrow keys
            || e.which > 40)) {
              e.preventDefault();
              return false;
        }
    })
    /**
     * fax input
     */
    $(document).on('keydown keyup', '.fax-input', function (e){
        $('.fax-input').attr('minlength', 10)
        $('.fax-input').attr('maxlength', 25)
        var regExp = /[0-9\-\+]/;
        var value = e.key;
        var val = $(this).val()
        if(e.which === 67 ||e.which === 65 || e.which === 86 || e.which === 88)
        {
            $(this).val(val.replace(value,''))
        }
        if (!regExp.test(value)
          && e.which != 8   // backspace
          && e.which != 46  // delete
          && e.which != 9  // tab
          && e.which != 17  // ctrl
          && e.which != 67  // ctrl
          && e.which != 65  // ctrl
          && e.which != 86  // ctrl
          && e.which != 88  // ctrl
          && (e.which < 37  // arrow keys
            || e.which > 40)) {
              e.preventDefault();
              return false;
        }
        // if($(this).val().length === 3 || $(this).val().length === 7)
        // {
        //     if( e.which != 8   // backspace
        //         && e.which != 46 ) // delete)
        //     {
        //         $(this).val(val+'-')
        //     }
        // }
    })
    
    /**
     * zip input
     */
    
    $(document).on('keydown keyup', '.zip-input', function (e){
        var regExp = /[0-9\-]/;
        var value = e.key;
        var val = $(this).val()
        if(e.which === 67 ||e.which === 65 || e.which === 88 || e.which === 86)
        {
            $(this).val(val.replace(value,''))
        }
        if (!regExp.test(value)
          && e.which != 8   // backspace
          && e.which != 46  // delete
          && e.which != 9  // tab
          && e.which != 17  // ctrl
          && e.which != 67  // c
          && e.which != 65  // a
          && e.which != 86 //v
          && e.which != 88  // x
          && (e.which < 37  // arrow keys
            || e.which > 40)) {
              e.preventDefault();
              return false;
        }
        if($(this).val().length === 3)
        {
            if( e.which != 8   // backspace
                && e.which != 46 ) // delete)
            {
                $(this).val(val+'-')
            }
        }
    })
    /**
     * fax phone
     */
    $('.tel-input').attr('minlength', 10)
    $('.tel-input').attr('maxlength', 25)
    
    $(document).on('keydown keyup', '.tel-input', function (e){
        $('.tel-input').attr('minlength', 10)
        $('.tel-input').attr('maxlength', 25)
        var regExp = /[0-9\-\+]/;
        var value = e.key;
        var val = $(this).val()
        if(e.which === 67 ||e.which === 65 || e.which === 86 || e.which === 88)
        {
            $(this).val(val.replace(value,''))
        }
        if (!regExp.test(value)
          && e.which != 8   // backspace
          && e.which != 46  // delete
          && e.which != 9  // tab
          && e.which != 17  // ctrl
          && e.which != 67  // ctrl
          && e.which != 65  // ctrl
          && e.which != 86  // ctrl
          && e.which != 88  // ctrl
          && (e.which < 37  // arrow keys
            || e.which > 40)) {
              e.preventDefault();
              return false;
        }
        // if($(this).val().length === 3 || $(this).val().length === 7)
        // {
        //     if( e.which != 8   // backspace
        //         && e.which != 46 ) // delete)
        //     {
        //         $(this).val(val+'-')
        //     }
        // }
    })
    /**
     * input số dương tel-input
     */
    
    $(document).on('keydown keyup', '.money-plus', function (e) {
        var regExp = /[0-9\,\.]/;
        var value = e.key;
        var val = $(this).val()
        if(e.which === 67 ||e.which === 65 || e.which === 88 || e.which === 86)
        {
            $(this).val(val.replace(value,''))
        }
        if (!regExp.test(value)
          && e.which != 8   // backspace
          && e.which != 46  // delete
          && e.which != 9  // tab
          && e.which != 17  // ctrl
          && e.which != 67  // c
          && e.which != 65  // a
          && e.which != 86 //v
          && e.which != 88  // x
          && (e.which < 37  // arrow keys
            || e.which > 40)) {
              e.preventDefault();
              return false;
        }
    });
    /**
     * modal error
     * parameter {
     *  message: Thông báo lỗi,
     * btnClose: button đóng của modal
     * }
     */
    ModalError = function (message, title_message, btnclose) {
        $('#test').remove(); // xóa class modal nếu có
        let modal = ''
        modal = `
        <div id="modal_error" class="modal fade modal-error" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">警報</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="text-align:center">
                <b class="title-message-body" style="font-size=16px;">`+title_message+`</b>
                <br>
                <b style="font-size=16px;">`+message+`</b>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">`+btnclose+`</button>
            </div>
            </div>
        </div>
        </div>`
        $('body').append(modal); // thêm class model error
        $('.modal-error').modal({backdrop: 'static', keyboard: false})
    }
    $(document).on('hide.bs.modal','#modal_error', function () {
        $('#modal_error').remove()
        $('.modal-backdrop').remove()
    });
    
    /**
     * ModalConfirm
     * modal xác nhận người dùng
     * @param (message: Thông báo xác nhận, 
     * btnClose: button không đồng ý,
     * btnOk: Button đồng ý,
     * url: đường link để request,
     * data: dữ liệu gửi lên server
     * )
     * @author Dat
     */
    var url = '';
    var data = {};
    var method = 'GET';
    var url_reload = ''
    ModalConfirm = (message = '', btnClose = 'キャンセル', btnOk = 'Ok', url_modal = null, data_modal = {}, method_modal = 'GET', url_reload_modal = '') => {
        $('.modal-confirm').remove(); // xóa class modal nếu có
        let modal = ''
        if(url_modal != null)
        {
            url = url_modal
        }
        url_reload = url_reload_modal
        data =  data_modal
        method = method_modal
        modal = `
        <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header">            
                <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">警報</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="text-align:center">
                <b style="font-size=16px;">`+message+`</b>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">`+btnClose+`</button>
                <button type="button" class="btn btn-primary" id="save_confirm_modal" data-dismiss="modal">`+btnOk+`</button>
            </div>
            </div>
        </div>
        </div>`
        $('body').append(modal); // thêm class model error
        $('.modal-confirm').modal()
    }
    $(document).on('click', '#save_confirm_modal', () => {
        loading()
        $.ajax({
            "url": url,
            "data": data,
            "method": method,
            success: function (res) {
                if(res.status === true)
                {
                    if(url_reload !== '')
                    {
                        window.location.href = url_reload
                    } else
                    {
                        let url_copy = url.split('/')
                        if(url_copy[(url_copy.length -1)] == 'ajax-copy-order'){
                            ModalSuccessNoReload('選択中の注文をコピーしました。', 'Ok')
                            $("#btn_search").click()
                            // window.location.href = window.location.origin + '/order/edit-order/'+res.order_copy;                
                        }else if (url_copy[(url_copy.length -1)] == 'ajax-delete-order'){
                            ModalSuccessNoReload('選択中の商品を削除しました。', 'Ok')
                            $("#btn_search").click()
                        }else {
                            location.reload();
                        }
                    }
                }
                if(res.status === false)
                {
                    ModalError(res.message, '', 'OK')
                }
                endLoading()
            }
        })
    })

    loading = function(){
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
    }

    endLoading = function(){
        $('.loading-full-page').remove()
    }

    /**
     * ModalSuccess
     * modal xác nhận sau khi progress success
     * @param (message: Thông báo xác nhận, 
     * btnOk: Button đồng ý,
     * )
     * @author chan_nl
     */
    ModalSuccess = (message = '', btnOk = 'Ok') => {
        $('.modal-success').remove(); // xóa class modal nếu có
        let modal = ''
        modal = `
        <div class="modal fade modal-success" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">通知</h5>
                <button type="button" class="close" id="btn_close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="text-align:center">
                <b style="font-size=16px;">`+message+`</b>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn_ok" data-dismiss="modal">`+btnOk+`</button>
            </div>
            </div>
        </div>
        </div>`
        $('body').append(modal); // thêm class model error
        $('.modal-success').modal()
    }
    $(document).on('click', '#btn_ok', () => {                         
        endLoading()
        window.location.reload()
    })
    $(document).on('click', '#btn_close', () => {                         
        endLoading()
        window.location.reload()
    })

    /**
     * ModalSuccess
     * modal xác nhận sau khi progress success
     * @param (message: Thông báo xác nhận, 
     * btnOk: Button đồng ý,
     * )
     * @author chan_nl
     */
    ModalSuccessNoReload = (message = '', btnOk = 'Ok') => {
        $('.modal-success-no-reload').remove(); // xóa class modal nếu có
        let modal = ''
        modal = `
        <div class="modal fade modal-success-no-reload" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">通知</h5>
                <button type="button" class="close" id="btn_close_no_reload" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="text-align:center">
                <b style="font-size=16px;">`+message+`</b>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn_ok_no_reload" data-dismiss="modal">`+btnOk+`</button>
            </div>
            </div>
        </div>
        </div>`
        $('body').append(modal); // thêm class model error
        $('.modal-success-no-reload').modal()
    }

    /**
     * Confirm screen [5]
     * Description:
     * - nếu có truyền url là nhấn nút 発注送信
     * - nếu không truyền url là nhấn nút 発注書一括送信
     * @author: chan_nl
     * Updated: 2019/11/22
     * Created: 2019/11/22
     */
    var url_ = '';
    var param_ = {};
    var date_ = getDateNow().split('/');
    var confirm_screen_ = 0;
    ModalConfirmReturn = (message = '', btnClose = 'キャンセル', btnOk = 'Ok', url = '', param = {}, confirm_screen = 0) => {  
        $('.modal-confirm-return').remove(); // xóa class modal nếu có
        url_ = url;
        param_ = param;
        confirm_screen_ = confirm_screen;
        let modal = '';
        modal = `
        <div class="modal fade modal-confirm-return" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="text-align:center">
                <b style="font-size=16px;">`+message+`</b>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">`+btnClose+`</button>
                <button type="button" class="btn btn-primary" id="save_confirm_purchases_modal" data-dismiss="modal">`+btnOk+`</button>
            </div>
            </div>
        </div>
        </div>`
        $('body').append(modal); // thêm class model error
        $('.modal-confirm-return').modal()
    }    
    $(document).on('click', '#save_confirm_purchases_modal', () => {
        loading()
        if(confirm_screen_ == 5){//Xác nhận màn hình 5
            $.ajax({
                cache: false,
                url: url_, //GET route 
                data: param_, //your parameters data here processData: true,
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
                    let file_name = param_.supplied + date_[0] + date_[1] + date_[2];
                    let link = '';
                    if(param_.sel_download == 11 || param_.sel_download == 22 || param_.sel_download == 33){
                        link = '<a id='+randomId+' href='+url+' download='+file_name+'.pdf>link</a>';
                    }else {
                        link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
                    }
                    $('body').append(link)
                    $(`#${randomId}`)[0].click()
                    $(`#${randomId}`).remove()
                    if(status === 'success')
                    {
                        searchPurchases(param_.range, param_.date_from, param_.date_to, true)
                        if(param_.stage4 == 4){
                            ModalSuccessNoReload('FAXを送信しました。', 'Ok')
                        }
                    }
                    endLoading()
                },
                error: function (ajaxContext) {
                  
                  endLoading()
                }
            });
        }else if (confirm_screen_ == 6){//Xác nhận màn hình 6
            if(param_.sel_download == 1){
                if(param_.yamato.includes(parseInt(param_.delivery_method))){
                    $.ajax({
                        cache: false,
                        url: url_, //GET route 
                        data:  param_,
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
                            let file_name = '';
                            if(parseInt(param_.delivery_method) == 2){
                                file_name = 'yamato_express_';
                            }else if(parseInt(param_.delivery_method) == 3){
                                file_name = 'yamato_nekoposu_';
                            }else if(parseInt(param_.delivery_method) == 4){
                                file_name = 'yamato_compact_';
                            }else if(parseInt(param_.delivery_method) == 5){
                                file_name = 'yubin_express_';
                            }else if(parseInt(param_.delivery_method) == 6){
                                file_name = 'yubin_packet_';
                            }
                            file_name = file_name+ date_[0] + date_[1] + date_[2]
                            let link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
                            $('body').append(link)
                            $(`#${randomId}`)[0].click()
                            $(`#${randomId}`).remove()
                            if(status === 'success')
                            {
                                searchShipment(param_.range, param_.date_from, param_.date_to, true)
                            }
                            endLoading()
                        },
                        error: function (ajaxContext) {
                            
                            endLoading()
                        }
                    });
                }else {
                    let date_ = getDateNow().split('/');
                    var flag = 1;//Xử lý bất đồng bộ
                    var param_url = param_.url_export_sagawa_shipment;
                    var file_name = 'sagawa_hiden_'+date_[0]+date_[1]+date_[2];
                    if(param_.delivery_method == 9 || param_.delivery_method == '9'){
                        param_url = param_.url_send_shipment_II;
                    }
                    if(flag == 1){
                        flag++;
                        window.location = param_url+'?delivery_method='+param_.delivery_method+'&range='+
                        param_.range+'&date_from='+param_.date_from+'&date_to='+param_.date_to+'&stage1='+
                        ((param_.stage1 == 0) ? null : param_.stage1)+'&stage2='+((param_.stage2 == 0) ? null : param_.stage2)+'&stage3=3&screen=6'+
                        '&file_name='+file_name;
                        if(flag > 1){
                            searchShipment(param_.range, param_.date_from, param_.date_to, true)
                        }
                    }
                    if(flag > 1){
                        searchShipment(param_.range, param_.date_from, param_.date_to, true)
                    }
                }
            }else if(param_.sel_download == 2) {    
                let date_ = getDateNow().split('/');
                var flag = 1;//Xử lý bất đồng bộ
                if(flag == 1){
                    window.location = param_.url_export_shipment_bill+'?delivery_method='+param_.delivery_method+'&range='+
                    param_.range+'&date_from='+param_.date_from+'&date_to='+param_.date_to+'&stage1='+
                    ((param_.stage1 == 0) ? null : param_.stage1)+'&stage2='+((param_.stage2 == 0) ? null : param_.stage2)+'&stage3=3&file_name=shipment_code_'+date_[0]+date_[1]+date_[2];
                    flag++;
                    if(flag > 1){
                        searchShipment(param_.range, param_.date_from, param_.date_to, true)
                    }
                }
                if(flag > 1){
                    searchShipment(param_.range, param_.date_from, param_.date_to, true)
                }
            }else {
                param_get_list_supplier_by_delivery_method = {
                    'delivery_method': param_.delivery_method,
                    'range': param_.range,
                    'date_from': param_.date_from,
                    'date_to': param_.date_to,
                    'stage1': (param_.stage1 == 0) ? null : param_.stage1,
                    'stage2': (param_.stage2 == 0) ? null : param_.stage2,
                }
                $.ajax({
                    type: 'post',
                    url: param_.url_get_list_supplier_by_delivery_method, //GET route 
                    data:  param_get_list_supplier_by_delivery_method,
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
                            if(param_.sel_download == 33){
                                doExport(data_download, 0, param_.stage3, 3, 6, param_.url_export_purchase, param_)
                            }else {
                                $.each(data_download, function( key, value ) {  
                                    param_export_purchase = {
                                        'arr_detail': value.details,
                                        'stage3': param_.stage3,
                                        'screen': 6,
                                        'sel_download': param_.sel_download,
                                        'supplier_name': value.supplied,
                                        'pdf': null
                                    }
                                    $.ajax({
                                        cache: false,
                                        url: param_.url_export_purchase, //GET route 
                                        data:  param_export_purchase,
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
                                            if(param_.sel_download == 33){
                                                link = '<a id='+randomId+' href='+url+' download='+file_name+'.pdf>link</a>';
                                            }else {
                                                link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
                                            }
                                            $('body').append(link)
                                            $(`#${randomId}`)[0].click()
                                            $(`#${randomId}`).remove()
                                            endLoading()
                                            if(status === 'success')
                                            {
                                                searchShipment(param_.range, param_.date_from, param_.date_to, true)
                                            }
                                        },
                                        error: function (ajaxContext) {
                                            
                                            endLoading()
                                        }
                                    });
                                })                                
                            }
                        }else {
                            ModalError('Server error', '', 'OK')
                        }
                        if(param_.sel_download != 33){
                            endLoading()
                        }
                    }
                });
            }
        }else if (confirm_screen_ == 7){//Xác nhận màn hình 7
            if(param_.sel_download == 1){
                if(param_.website == 'amazon'){//Loại website amazon
                    param_get_list_detail = {
                        'site_type': param_.site_type,
                        'range': param_.range,
                        'date_from': param_.date_from,
                        'date_to': param_.date_to,
                        'stage1': (param_.stage1 == 0) ? null : param_.stage1,
                        'stage2': (param_.stage2 == 0) ? null : param_.stage2,
                    }
                    $.ajax({
                        type: 'post',
                        url: param_.url_get_list_supplier_by_site_type, //GET route 
                        data:  param_get_list_detail,
                        success: function (result) {
                            if(result.status == true){
                                let list_details = [];
                                result.data.forEach(function (element) {
                                    list_details.push(element.detail_id)
                                })
                                var flag = 1;//Xử lý bất đồng bộ
                                if(flag == 1){
                                    flag++;
                                    window.location = param_.export_notification_amazon+'?list_details='+list_details+'&stage3='+param_.stage3+'&screen=7&site_type='+param_.site_type
                                    if(flag > 1){
                                        searchShipmentNotification(param_.range, param_.date_from, param_.date_to, true)
                                    }
                                }
                                if(flag > 1){
                                    searchShipmentNotification(param_.range, param_.date_from, param_.date_to, true)
                                }
                            }else {
                                searchShipmentNotification(param_.range, param_.date_from, param_.date_to, true)
                                ModalError('警報', '', 'OK')
                            }
                            endLoading()
                        }
                    });
                }else if(param_.website == 'rakuten'){//Loại website rakuten
                    var flag = 1;//Xử lý bất đồng bộ
                    if(flag == 1){
                        window.location = url_+'?site_type='+param_.site_type+'&range='+param_.range+'&date_from='+param_.date_from+'&date_to='
                        +param_.date_to+'&stage1='+((param_.stage1 == 0) ? null : param_.stage1)+'&stage2='+((param_.stage2 == 0) ? null : param_.stage2)
                        +'&stage3='+((param_.stage3 == 0) ? null : param_.stage3)+'&website='+param_.website+'&file_name='+param_.file_name+'&screen=7';
                        flag++;
                        if(flag > 1){
                            flag++;
                            searchShipmentNotification(param_.range, param_.date_from, param_.date_to, true)
                        }
                    }
                    if(flag > 1){
                        searchShipmentNotification(param_.range, param_.date_from, param_.date_to, true)
                    }
                }else {//Loại website yahoo
                    var flag = 1;//Xử lý bất đồng bộ
                    if(flag == 1){
                        window.location = url_+'?site_type='+param_.site_type+'&range='+param_.range+'&date_from='+param_.date_from+'&date_to='
                        +param_.date_to+'&stage1='+((param_.stage1 == 0) ? null : param_.stage1)+'&stage2='+((param_.stage2 == 0) ? null : param_.stage2)
                        +'&stage3='+((param_.stage3 == 0) ? null : param_.stage3)+'&website='+param_.website+'&file_name='+param_.file_name+'&screen=7';
                        flag++;
                        if(flag > 1){
                            flag++;
                            searchShipmentNotification(param_.range, param_.date_from, param_.date_to, true)
                        }
                    }
                    if(flag > 1){
                        searchShipmentNotification(param_.range, param_.date_from, param_.date_to, true)
                    }
                }
            }else if(param_.sel_download == 2){
                let date_ = getDateNow().split('/');
                var flag = 1;//Xử lý bất đồng bộ
                if(flag == 1){
                    window.location = param_.url_export_shipment_bill+'?site_type='+param_.site_type+'&range='+
                    param_.range+'&date_from='+param_.date_from+'&date_to='+param_.date_to+'&stage1='+
                    ((param_.stage1 == 0) ? null : param_.stage1)+'&stage2='+((param_.stage2 == 0) ? null : param_.stage2)+'&stage3=3&file_name=shipment_code_'+date_[0]+date_[1]+date_[2];
                    flag++;
                    if(flag > 1){
                        searchShipmentNotification(param_.range, param_.date_from, param_.date_to, true)
                    }
                }
                if(flag > 1){
                    searchShipmentNotification(param_.range, param_.date_from, param_.date_to, true)
                }
            }else {
                $.ajax({
                    type: 'post',
                    url: param_.url_get_list_supplier_by_site_type, //GET route 
                    data:  param_,
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
                            if(param_.sel_download == 33){
                                doExport(data_download, 0, param_.stage3, 3, 7, param_.url_export_purchase, param_)
                            }else {
                                $.each(data_download, function( key, value ) {
                                    var param_export_purchasse = {
                                        'arr_detail': value.details,
                                        'stage3': param_.stage3,
                                        'screen': 7,
                                        'sel_download': param_.sel_download,
                                        'supplier_name': value.supplied,
                                        'pdf': null
                                    }
                                    $.ajax({
                                        cache: false,
                                        url: url_export_purchase, //GET route 
                                        data:  param_export_purchasse,
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
                                            if(param_.sel_download == 33){
                                                link = '<a id='+randomId+' href='+url+' download='+file_name+'.pdf>link</a>';
                                            }else {
                                                link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
                                            }
                                            $('body').append(link)
                                            $(`#${randomId}`)[0].click()
                                            $(`#${randomId}`).remove()
                                            endLoading()                                        
                                            if(status === 'success')
                                            {
                                                searchShipmentNotification(param_.range, param_.date_from, param_.date_to, true)
                                            }
                                        },
                                        error: function (ajaxContext) {
                                            
                                            endLoading()
                                        }
                                    });
                                })
                            }
                        }else {
                            ModalError('Server error', '', 'OK')
                        }
                        if(param_.sel_download != 33){
                            endLoading()
                        }
                    }
                });    
            }
        }else if (confirm_screen_ == 9){//Xác nhận màn hình 9
            // Cập nhật thông tin order detail
            $.ajax({
                headers: { 'X-CSRF-TOKEN': token_header},
                type: "POST",
                url: url_,
                data: {
                    data: param_
                },
                success: function(res){
                    if(res.status === true)
                    {
                        ModalSuccess(res.message, 'Ok')
                    }else {                    
                        ModalError(res.message, '保存出来ていません。', 'OK')
                    }
                    endLoading()
                }
            })
        }else if (confirm_screen_ == 22){//Xác nhận màn hình 22
            if(param_.sel_download == 1){                
                $.ajax({
                    cache: false,
                    url: url_, //GET route 
                    data:  param_,
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
                        let file_name = param_.supplied + date_[0] + date_[1] + date_[2];
                        let link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
                        $('body').append(link)
                        $(`#${randomId}`)[0].click()
                        $(`#${randomId}`).remove()
                        $('.loading-full-page').remove()                               
                        if(status === 'success')
                        {                        
                            window.location.reload()
                        }
                    },
                    error: function (ajaxContext) {
                        endLoading()
                    }
                });
            }else if (param_.sel_download == 3){
                $.ajax({
                    headers: { 'X-CSRF-TOKEN': token_header},
                    type: "POST",
                    url: url_,
                    data: {
                        data: param_
                    },
                    success: function(res){
                        if(res.status === true)
                        {
                            ModalSuccess(res.message, 'Ok')                            
                            setTimeout(function(){
                                window.location.reload()
                            }
                            , 3000);
                        }else {                    
                            let message = '';
                            
                            if(res.message === undefined)
                            {
                                message = 'ログイン中のユーザ名はこの画面にアクセス権限を持っていません。'
                                ModalError(message, '保存出来ていません。', 'OK')
                            }
                            else 
                            {
                                ModalError(res.message, '保存出来ていません。', 'OK')
                            }
                        }
                        endLoading()
                    }
                })
            }else {
                // Cập nhật thông tin order detail
                $.ajax({
                    headers: { 'X-CSRF-TOKEN': token_header},
                    type: "POST",
                    url: url_,
                    data: {
                        data: param_
                    },
                    success: function(res){
                        if(res.status === true)
                        {
                            ModalSuccess(res.message, 'Ok')
                        }else {                    
                            let message = '';
                            
                            if(res.message === undefined)
                            {
                                message = 'ログイン中のユーザ名はこの画面にアクセス権限を持っていません。'
                                ModalError(message, '保存出来ていません。', 'OK')
                            }
                            else 
                            {
                                ModalError(res.message, '保存出来ていません。', 'OK')
                            }
                        }
                        endLoading()
                    }
                })
            }
        }
    })
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

function getDateNow(){
    var d = new Date();
    var month = d.getMonth()+1;
    var day = d.getDate();
    var output = d.getFullYear() + '/' + 
    (month<10 ? '0' : '') + month + '/' +
    (day<10 ? '0' : '') + day;
    return output;
}

function getStringDateNow(){
    var d = new Date();
    var month = d.getMonth()+1;
    var day = d.getDate();
    var output = d.getFullYear() + 
    (month<10 ? '0' : '') + month +
    (day<10 ? '0' : '') + day;
    return output;
}

function _validatedate(date)
{
    var dateformat = /^\d{4}[\/](0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])$/;
    // Match the date format through regular expression
    if(date.match(dateformat))
    {
        //Test which seperator is used '/' or '-'
        var opera1 = date.split('/');
        var opera2 = date.split('-');
        lopera1 = opera1.length;
        lopera2 = opera2.length;
        // Extract the string into month, date and year
        if (lopera1>1)
        {
            var pdate = date.split('/');
        }
        else if (lopera2>1)
        {
            var pdate = date.split('-');
        }
        var yy = parseInt(pdate[0]);
        var mm  = parseInt(pdate[1]);
        var dd = parseInt(pdate[2]);
        // Create list of days of a month [assume there is no leap year by default]
        var ListofDays = [31,28,31,30,31,30,31,31,30,31,30,31];
        if (mm==1 || mm>2)
        {
            if (dd>ListofDays[mm-1])
            {
                return false;
            }
        }
        if (mm==2)
        {
            var lyear = false;
            if ( (!(yy % 4) && yy % 100) || !(yy % 400)) 
            {
                lyear = true;
            }
            if ((lyear==false) && (dd>=29))
            {
                return false;
            }
            if ((lyear==true) && (dd>29))
            {
                return false;
            }
        }
        return true;
    }
    else
    {
        return false;
    }
}

function doExport(arr, index, stage, sel_download, screen, url_export, param_export){
    if(index < arr.length){
        var param_ = (param_export != null) ? param_export : null;
        setTimeout(function(){
            var supplier_name = '';
            if(screen == 6 || screen == 7 || screen == 9){
                arr[index].supplied = arr[index].supplied+"";
                supplier_name = arr[index].supplied.replace(" ", "");
            }else if(screen == 10){
                arr[index].name_supplied = arr[index].name_supplied+"";
                supplier_name = arr[index].name_supplied.replace(" ", "");
            }else {
                arr[index].supplier_name = arr[index].supplier_name+"";
                supplier_name = arr[index].supplier_name.replace(" ", "");
            }
        
            param = {
                'arr_detail': (screen == 6 || screen == 7 || screen == 9 || screen == 10) ? arr[index].details : arr[index].arr_detail,
                'stage3': stage,
                'sel_download': sel_download,
                'screen': screen,
                'supplier_name': supplier_name,
                'pdf': true
            }
            $.ajax({
                cache: false,
                url: url_export,
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
                    let file_name = supplier_name + date_[0] + date_[1] + date_[2];
                    let link = '<a id='+randomId+' href='+url+' download='+file_name+'.pdf>link</a>';
                    $('body').append(link)
                    $(`#${randomId}`)[0].click()
                    $(`#${randomId}`).remove()
                    index++;
                    doExport(arr, index, stage, sel_download, screen, url_export, param_);
                    if(index == arr.length){
                        if(stage != null){
                            if(screen == 6){
                                searchShipment(param_.range, param_.date_from, param_.date_to, true)
                            }else if(screen == 7){
                                searchShipmentNotification(param_.range, param_.date_from, param_.date_to, true)
                            }
                        }else {
                            endLoading()
                        }
                    }
                },
                error: function(){
                    endLoading()
                }
            })
        }, 500)
    }
}
function doExportPayable(arr, index, year, month, payment_term, check_select, url_export){
    if(index < arr.length){
        setTimeout(function(){
            var supplier_name = '';
            arr[index].supplied = arr[index].supplied+"";
            supplier_name = arr[index].supplied.replace(" ", "");
            param = {
                'arr_detail': arr[index].details,
                'pay_year': year,
                'pay_month': month,
                'payment_term': payment_term,//kỳ hạn thống kê
                'supplier_name': supplier_name,
                'pdf': true
            }
            $.ajax({
                cache: false,
                url: url_export, //GET route 
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
                    let file_name = supplier_name + date_[0] + date_[1] + date_[2];
                    let link = '';
                    if(check_select == 44){
                        link = '<a id='+randomId+' href='+url+' download='+file_name+'.pdf>link</a>';
                    }else {
                        link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
                    }
                    $('body').append(link)
                    $(`#${randomId}`)[0].click()
                    $(`#${randomId}`).remove()
                    index++;
                    doExportPayable(arr, index, year, month, payment_term, check_select, url_export)
                    if(index == arr.length){
                        endLoading()
                    }
                }
            });
        }, 100)
    }
}

//Custom datatable
function setDataTable(table){
    $(table).DataTable({
        "sDom": '<"top"p><"clear">t',
        "pageLength": 50,
        "searching": false,
        "ordering": false,
        "Sort": false,
        "bDestroy": true,
        "language": {
            "sZeroRecords":  "検索条件に該当するデータはありません。",
            "sInfo":         "",//Đang xem _START_ đến _END_ trong tổng số _TOTAL_ mục
            "sInfoEmpty":    "",//Đang xem 0 đến 0 trong tổng số 0 mục
            "sInfoFiltered": "",
            "paginate": {
                "previous": "‹",
                "next":"›"
            }
        },
    });
};

//Validate password
function ValidatePassword(inputText)
{
    var passformat = /^[0-9a-zA-Z]{8,16}$/;
    if(inputText.match(passformat)){
        return true;
    }else {
        return false;
    }
}

//Validate username
function ValidateUsername(inputText)
{
    var passformat = /^[0-9a-zA-Z_-]{4,20}$/;
    if(inputText.match(passformat)){
        return true;
    }else {
        return false;
    }
}

//Validate ship code
function ValidateShipcode(inputText)
{
    var shipcode = /^[a-zA-Z0-9_-]{1,}$/;
    if(inputText.match(shipcode)){
        return true;
    }else {
        return false;
    }
}

//Check empty object
function isEmptyObject(obj) {
    for(var prop in obj) {
      if(obj.hasOwnProperty(prop)) {
        return false;
      }
    }
    return JSON.stringify(obj) === JSON.stringify({});
}