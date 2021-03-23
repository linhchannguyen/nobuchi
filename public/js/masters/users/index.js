$(document).ready(function () {
    $(document).on('click', '.supplied', function(){
        $('.top').hide();
        refreshModalSupplier()
    });
    var flag_CU = '';
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /**-------------------------------------------USERS-------------------------------------------*/
    if(count_checkbox == 0 || count_checkbox == 3){
        $('.flag_user_permission').prop('checked', true);
    }
    $('.check_flag_user_permission').on('click', function(){
        checked = $(this).find('.flag_user_permission').prop('checked');
        if(checked == true){
            $(this).find('.flag_user_permission').prop('checked', false);
        }else {
            $(this).find('.flag_user_permission').prop('checked', true);
        }
    });
    $('.flag_user_permission').on('click', function(){
        checked = $(this).prop('checked');
        if(checked == true){
            $(this).prop('checked', false);
        }else {
            $(this).prop('checked', true);
        }
    });

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

    //Click button add user show modal
    
    $(document).on('click', '#btn_add_user', function() {
        flag_CU = 'create';
        $('.modal-confirm-add-user').remove();
        let  modal = `<div class="modal fade modal-confirm-add-user" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 750px !important;">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle" style="color: black;">追加</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <div class="row">
                            <label style="position: relative; top: 7px;" class="col-md-2 control-label">ユーザー名:</label>
                            <div class="col-md-3">
                                <div class="tooltip-error">
                                    <input type="text" class="form-control user-name-add" value="" />
                                </div>
                            </div>
                            <div class="col-md-1"></div>
                            <label style="text-align: right; position: relative; top: 7px;" class="col-md-3 control-label">パスワード:</label>
                            <div class="col-md-3">
                                <div class="tooltip-error-left">
                                    <input type="password" class="form-control password-add" value="" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label style="position: relative; top: 7px;" class="col-md-2 control-label">権限:</label>
                            <div class="col-md-3">
                                <select class="form-control user-permission-add">                                
                                    <option value="1">運用者</option>
                                    <option value="2" selected>仕入先</option>
                                </select>
                            </div>
                            <div class="col-md-1"></div>
                            <label style="text-align: right; position: relative; top: 7px;" class="col-md-3 control-label">パスワード確認:</label>
                            <div class="col-md-3">
                                <div class="tooltip-error-left">
                                    <input type="password" class="form-control re-password-add" value="" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label style="position: relative; top: 7px;" class="col-md-2 control-label">仕入先名:</label>
                            <div class="col-md-3">
                                <input type="hidden" class="supplier-id-add"value="">
                                <div class="tooltip-error">
                                    <input type="text" class="form-control supplied supplier-name-add" data-toggle="modal" data-target="#modal_supplied" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">`+'キャンセル'+`</button>
                <button type="button" class="btn btn-primary" id="add_user_confirm">`+'保存'+`</button>
            </div>
            </div>
        </div>
        </div>`
        $('body').append(modal); // thêm class model error
        $('.modal-confirm-add-user').modal()
    });

    //Change permission
    $(document).on('change', '.user-permission-add', function() {
        if($(this).val() !== '2'){
            $('.supplier-name-add').parent().parent().parent().parent().css('display', 'none')
        }else {
            $('.supplier-name-add').parent().parent().parent().parent().css('display', 'block')
        }
    })

    //Confirm update user
    $(document).on('click', '#add_user_confirm', function(){
        $('.tooltip-error-left').find('.tooltip-text-left').remove()
        $('.tooltip-error').find('.tooltip-text').remove()
        let validate = true;
        let permission = $('.user-permission-add').val();
        let password = $('.password-add').val();
        let re_password = $('.re-password-add').val();
        let supplier_id = $('.supplier-id-add').val();
        let supplier_name = $('.supplier-name-add').val();
        let user_name = $('.user-name-add').val();
        //Validate username
        if(user_name == ''){
            let tooltip_error = '<span class="tooltip-text">必須です。入力してください。</span>'
            $('.user-name-add').parent().append(tooltip_error);
            $('.user-name-add').parent().find('.tooltip-text').css({"top": "-50px"});
            $('.user-name-add').parent().find('.tooltip-text').show();
            $('.user-name-add').parent().find('.tooltip-text').delay(3000).fadeOut();
            validate = false;
        }else {
            if(ValidateUsername(user_name) == false){
                if(user_name.length < 4 || user_name.length > 20){                    
                    let tooltip_error = '<span class="tooltip-text">ユーザー名はスペース無しで半角4～20文字で入力してください。</span>'
                    $('.user-name-add').parent().append(tooltip_error);
                    $('.user-name-add').parent().find('.tooltip-text').css({"top": "-70px"});
                    $('.user-name-add').parent().find('.tooltip-text').show();
                    $('.user-name-add').parent().find('.tooltip-text').delay(3000).fadeOut();
                }else {
                    let tooltip_error = '<span class="tooltip-text">ユーザー名はスペース無しで半角4～20文字で入力してください。</span>'
                    $('.user-name-add').parent().append(tooltip_error);
                    $('.user-name-add').parent().find('.tooltip-text').css({"top": "-50px"});
                    $('.user-name-add').parent().find('.tooltip-text').show();
                    $('.user-name-add').parent().find('.tooltip-text').delay(3000).fadeOut();
                    validate = false;
                }
            }
        }
        //Validate supplier
        if(permission == 2 && supplier_id == ''){
            let tooltip_error = '<span class="tooltip-text">必須です。入力してください。</span>'
            $('.supplier-name-add').parent().append(tooltip_error);
            $('.supplier-name-add').parent().find('.tooltip-text').css({"top": "-50px"});
            $('.supplier-name-add').parent().find('.tooltip-text').show();
            $('.supplier-name-add').parent().find('.tooltip-text').delay(3000).fadeOut();
            validate = false;
        }
        //Validate password
        if(password === ''){
            let tooltip_error = '<span class="tooltip-text-left">必須です。入力してください。</span>'
            $('.password-add').parent().append(tooltip_error);
            $('.password-add').parent().find('.tooltip-text-left').css({"top": "-25px", "left": "-145px", "width": "300px"});
            $('.password-add').parent().find('.tooltip-text-left').show();
            $('.password-add').parent().find('.tooltip-text-left').delay(3000).fadeOut();
            validate = false;
        }else {
            if(ValidatePassword(password) == false){
                if(password.length < 8 || password.length > 16){
                    let tooltip_error = '<span class="tooltip-text-left">パスワードは半角8～16文字で入力してください。</span>'
                    $('.password-add').parent().append(tooltip_error);
                    $('.password-add').parent().find('.tooltip-text-left').css({"top": "-25px", "left": "-200px", "width": "370px"});
                    $('.password-add').parent().find('.tooltip-text-left').show();
                    $('.password-add').parent().find('.tooltip-text-left').delay(3000).fadeOut();
                    validate = false;
                }else {
                    let tooltip_error = '<span class="tooltip-text-left">パスワードは特殊記号、半角カナ、全角文字、スペースを入力しないでください。</span>'
                    $('.password-add').parent().append(tooltip_error);
                    $('.password-add').parent().find('.tooltip-text-left').css({"top": "-50px", "left": "-200px", "width": "370px"});
                    $('.password-add').parent().find('.tooltip-text-left').show();
                    $('.password-add').parent().find('.tooltip-text-left').delay(3000).fadeOut();
                    validate = false;
                }
            }
        }
        if(password === '' && re_password !== ''){
            let tooltip_error = '<span class="tooltip-text-left">必須です。入力してください。</span>'
            $('.password-add').parent().append(tooltip_error);
            $('.password-add').parent().find('.tooltip-text-left').css({"top": "-25px", "left": "-145px", "width": "300px"});
            $('.password-add').parent().find('.tooltip-text-left').show();
            $('.password-add').parent().find('.tooltip-text-left').delay(3000).fadeOut();
            validate = false;
        }
        if(validate == true){
            if(password !== re_password){
                let tooltip_error = '<span class="tooltip-text-left">パスワードが一致しません。 再入力してください。</span>'
                $('.re-password-add').parent().append(tooltip_error);
                $('.re-password-add').parent().find('.tooltip-text-left').css({"top": "-25px", "left": "-245px", "width": "400px"});
                $('.re-password-add').parent().find('.tooltip-text-left').show();
                $('.re-password-add').parent().find('.tooltip-text-left').delay(3000).fadeOut();
                return false;
            }else {
                $('.modal-confirm-add-user').find('.close').click();
                if(permission != 2){
                    data = {
                        'user_name': user_name,
                        'password': password,
                        'user_type': permission
                    };
                }else {
                    data = {
                        'user_name': user_name,
                        'password': password,
                        'user_type': permission,
                        'supplier_id': supplier_id,
                        'supplier_name': supplier_name
                    };
                }
                loading();
                $.ajax({
                    type: "POST",
                    url: ajax_add_users,
                    data: data,
                    success: function (response) {
                        endLoading()
                        if(response.status === true)
                        {
                            ModalSuccess(response.message, 'Ok')
                            setTimeout(function() {location.reload()}, 2000)
                        } else
                        {
                            ModalError(response.message, '保存出来ていません。', 'OK')
                        }
                    }
                });
            }
        }
    });

    //Click button delete user show modal
    var user_name_del = '';
    var user_id_del = '';
    var user_type_del = '';
    $(document).on('click', '#btn_delete_user', function() {
        let current = $(this).parent().parent().parent().parent();
        user_id_del = current.find('.user-id').val();
        user_name_del = current.find('.user-name').val();
        user_type_del = current.find('.user-type').val();
        $('.modal-confirm-del-user').remove();
        let  modal = `<div class="modal fade modal-confirm-del-user" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle" style="color: black;">編集</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="text-align:center">
                <b style="font-size=16px;">`+user_name_del+`と言うユーザー名を削除しますか？</b>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">`+'キャンセル'+`</button>
                <button type="button" class="btn btn-primary" id="del_user_confirm" data-dismiss="modal">`+'保存'+`</button>
            </div>
            </div>
        </div>
        </div>`
        $('body').append(modal);
        $('.modal-confirm-del-user').modal()
    });

    //Confirm delete user
    $(document).on('click', '#del_user_confirm', function(){
        loading();
        $.ajax({
            type: "POST",
            url: ajax_delete_users,
            data: {
                'user_id': user_id_del,
                'user_name': user_name_del,
                'user_type': user_type_del,
            },
            success: function (response) {
                endLoading()
                if(response.status === true)
                {
                    ModalSuccess(response.message, 'Ok')
                    setTimeout(function() {location.reload()}, 2000)
                } else
                {
                    ModalError(response.message, '保存出来ていません。', 'OK')
                }
            }
        });
    });
    
    //click button upadte users show modal
    var user_name_update = '';
    var user_id_update = '';
    var user_type_update = '';
    var supplier_name_update = '';
    var supplier_id_update = '';
    $(document).on('click', '#btn_update_user', function() {
        flag_CU = 'update';
        let current = $(this).parent().parent().parent().parent();
        user_id_update = current.find('.user-id').val();
        user_name_update = current.find('.user-name').val();
        user_type_update = current.find('.user-type').val();
        //Reset lai gia tri supplier
        if(user_type_update == 1){
            supplier_id_update = '';
            supplier_name_update = '';
        }
        if(typeof current.find('.supplied-name').val() !== 'undefined'){
            supplier_name_update = current.find('.supplied-name').val();
            supplier_id_update = current.find('.supplied-id').val();
        }
        $('.modal-confirm-update-user').remove();
        let  modal = `<div class="modal fade modal-confirm-update-user" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 750px !important;">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle" style="color: black;">変更</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <div class="row">
                            <label style="position: relative; top: 7px;" class="col-md-2 control-label">ユーザー名:</label>
                            <div class="col-md-3">
                                <div class="tooltip-error">
                                    <span style="position: relative; top: 7px;">`+user_name_update+`</span>
                                </div>
                            </div>
                            <div class="col-md-1"></div>
                            <label style="text-align: right; position: relative; top: 7px;" class="col-md-3 control-label">パスワード:</label>
                            <div class="col-md-3">
                                <div class="tooltip-error-left">
                                    <input type="password" class="form-control password-update" value="" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label style="position: relative; top: 7px;" class="col-md-2 control-label">権限:</label>
                            <div class="col-md-3">`;
                                if(user_type_update == 0 && user_name_update == 'admin'){
                                    modal += `<span style="position: relative; top: 7px;">
                                    <input type="hidden"  class="user-permission-update" value="0"/>管理者
                                    </span>`;
                                }else {
                                    if(middleware_admin === '0'){
                                        modal += `
                                    <select class="form-control user-permission-update">
                                        <option value="1" `+((user_type_update == 1) ? 'selected' : '')+`>運用者</option>
                                        <option value="2" `+((user_type_update == 2) ? 'selected' : '')+`>仕入先</option>
                                    </select>`;
                                    }else {
                                        modal += `<span style="position: relative; top: 7px;">
                                        <input type="hidden"  class="user-permission-update" value="1"/>運用者
                                        </span>`;
                                    }
                                }
                                modal +=`
                            </div>
                            <div class="col-md-1"></div>
                            <label style="text-align: right; position: relative; top: 7px;" class="col-md-3 control-label">パスワード確認:</label>
                            <div class="col-md-3">
                                <div class="tooltip-error-left">
                                    <input type="password" class="form-control re-password-update" value="" />
                                </div>
                            </div>
                        </div>
                    </div>`;
                    if(user_type_update != 0 || user_name_update != 'admin'){
                        modal += `
                        <div class="form-group" `+((user_type_update != 2) ? "style='display: none'": "")+`>
                            <div class="row">
                                <label style="position: relative; top: 7px;" class="col-md-2 control-label">仕入先名:</label>
                                <div class="col-md-3">
                                    <input type="hidden" class="supplier-id-update"value="`+supplier_id_update+`">
                                    <div class="tooltip-error">
                                        <input type="text" class="form-control supplied supplier-name-update" data-toggle="modal" data-target="#modal_supplied" value="`+supplier_name_update+`">
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    }
                    modal += `
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">`+'キャンセル'+`</button>
                <button type="button" class="btn btn-primary" id="update_user_confirm">`+'保存'+`</button>
            </div>
            </div>
        </div>
        </div>`
        $('body').append(modal);
        $('.modal-confirm-update-user').modal()
    });

    //Change permission
    $(document).on('change', '.user-permission-update', function() {   
        if($(this).val() !== '2'){
            $('.supplier-name-update').parent().parent().parent().parent().css('display', 'none')
        }else {
            $('.supplier-name-update').parent().parent().parent().parent().css('display', 'block')
        }
    })

    //Confirm update user
    $(document).on('click', '#update_user_confirm', function(){
        $('.tooltip-error-left').find('.tooltip-text-left').remove()
        $('.tooltip-error').find('.tooltip-text').remove()
        let validate = true;
        let permission = $('.user-permission-update').val();
        let password = $('.password-update').val();
        let re_password = $('.re-password-update').val();
        let supplier_id = $('.supplier-id-update').val();
        let supplier_name = $('.supplier-name-update').val();
        let user_old = {};
        user_old.user_type = user_type_update;
        user_old.supplier_name = supplier_name_update;
        user_old.supplier_id = supplier_id_update;
        if(permission == 2 && supplier_id == ''){                            
            let tooltip_error = '<span class="tooltip-text">必須です。入力してください。</span>'
            $('.supplier-name-update').parent().append(tooltip_error);
            $('.supplier-name-update').parent().find('.tooltip-text').css({"top": "-50px"});
            $('.supplier-name-update').parent().find('.tooltip-text').show();
            $('.supplier-name-update').parent().find('.tooltip-text').delay(3000).fadeOut();
            validate = false;
        }
        if(password !== ''){
            if(ValidatePassword(password) == false){
                if(password.length < 8 || password.length > 16){
                    let tooltip_error = '<span class="tooltip-text-left">パスワードは半角8～16文字で入力してください。</span>'
                    $('.password-update').parent().append(tooltip_error);
                    $('.password-update').parent().find('.tooltip-text-left').css({"top": "-25px", "left": "-200px", "width": "370px"});
                    $('.password-update').parent().find('.tooltip-text-left').show();
                    $('.password-update').parent().find('.tooltip-text-left').delay(3000).fadeOut();
                    validate = false;
                }else {
                    let tooltip_error = '<span class="tooltip-text-left">パスワードは特殊記号、半角カナ、全角文字、スペースを入力しないでください。</span>'
                    $('.password-update').parent().append(tooltip_error);
                    $('.password-update').parent().find('.tooltip-text-left').css({"top": "-50px", "left": "-200px", "width": "370px"});
                    $('.password-update').parent().find('.tooltip-text-left').show();
                    $('.password-update').parent().find('.tooltip-text-left').delay(3000).fadeOut();
                    validate = false;
                }
            }
        }else if(password === '' && re_password !== ''){
            let tooltip_error = '<span class="tooltip-text-left">必須です。入力してください。</span>'
            $('.password-update').parent().append(tooltip_error);
            $('.password-update').parent().find('.tooltip-text-left').css({"top": "-25px", "left": "-145px", "width": "300px"});
            $('.password-update').parent().find('.tooltip-text-left').show();
            $('.password-update').parent().find('.tooltip-text-left').delay(3000).fadeOut();
            validate = false;
        }
        if(validate == true){
            if(password !== re_password){
                let tooltip_error = '<span class="tooltip-text-left">パスワードが一致しません。 再入力してください。</span>'
                $('.re-password-update').parent().append(tooltip_error);
                $('.re-password-update').parent().find('.tooltip-text-left').css({"top": "-25px", "left": "-245px", "width": "400px"});
                $('.re-password-update').parent().find('.tooltip-text-left').show();
                $('.re-password-update').parent().find('.tooltip-text-left').delay(3000).fadeOut();
                return false;
            }else {                
                if((user_type_update != 2 || permission == 1) && password == '' && supplier_name == ''){
                    let tooltip_error = '<span class="tooltip-text-left">必須です。入力してください。</span>'
                    $('.password-update').parent().append(tooltip_error);
                    $('.password-update').parent().find('.tooltip-text-left').css({"top": "-25px", "left": "-145px", "width": "300px"});
                    $('.password-update').parent().find('.tooltip-text-left').show();
                    $('.password-update').parent().find('.tooltip-text-left').delay(3000).fadeOut();
                    return false;
                }else if(user_type_update == 2 && permission == 2 && supplier_id == supplier_id_update && password == ''){
                    let tooltip_error = '<span class="tooltip-text-left">必須です。入力してください。</span>'
                    $('.password-update').parent().append(tooltip_error);
                    $('.password-update').parent().find('.tooltip-text-left').css({"top": "-25px", "left": "-145px", "width": "300px"});
                    $('.password-update').parent().find('.tooltip-text-left').show();
                    $('.password-update').parent().find('.tooltip-text-left').delay(3000).fadeOut();
                    return false;
                }else if(user_type_update == 0 && password == ''){
                    let tooltip_error = '<span class="tooltip-text-left">必須です。入力してください。</span>'
                    $('.password-update').parent().append(tooltip_error);
                    $('.password-update').parent().find('.tooltip-text-left').css({"top": "-25px", "left": "-145px", "width": "300px"});
                    $('.password-update').parent().find('.tooltip-text-left').show();
                    $('.password-update').parent().find('.tooltip-text-left').delay(3000).fadeOut();
                    return false;
                }else {
                    $('.modal-confirm-update-user').find('.close').click();
                    if(permission != 2){
                        data = {
                            'user_id': user_id_update,
                            'user_name': user_name_update,
                            'password': password,
                            'user_type': permission,
                            'user_old': user_old
                        };
                    }else {
                        data = {
                            'user_id': user_id_update,
                            'user_name': user_name_update,
                            'password': password,
                            'user_type': permission,
                            'supplier_id': supplier_id,
                            'supplier_name': supplier_name,
                            'user_old': user_old
                        };
                    }
                    loading();
                    $.ajax({
                        type: "POST",
                        url: ajax_update_users,
                        data: data,
                        success: function (response) {
                            endLoading()
                            if(response.status === true)
                            {
                                ModalSuccess(response.message, 'Ok')
                                setTimeout(function() {location.reload()}, 2000)
                            } else
                            {
                                ModalError(response.message, '保存出来ていません。', 'OK')
                            }
                        }
                    });
                }
            }
        }
    });

    //Click modal supplier
    $(document).on('click', '.supplier-name-update', function(){
        // $('.modal-confirm-update-user').modal('hide')
        $('#modal_supplied').css('overflow-y', 'auto')
        $('#modal_supplied').css({
            'overflow-y': 'auto',
            'z-index': '1051'
        })
    });
    $(document).on('click', '.supplier-name-add', function(){
        // $('.modal-confirm-add-user').modal('hide')
        $('#modal_supplied').css({
            'overflow-y': 'auto',
            'z-index': '1051'
        })
    });
    $(document).on('click', '.supplied-modal', function() {
        let info = {}
        info = $(this).prop('info')
        if(flag_CU == 'create'){
            $('.supplier-name-add').val(info.name)
            $('.supplier-id-add').val(info.supplie_id)
            $('.modal-confirm-add-user').modal('show')
        }else {
            $('.supplier-name-update').val(info.name)
            $('.supplier-id-update').val(info.supplie_id)
            $('.modal-confirm-update-user').modal('show')
        }
    })
    /**-------------------------------------------USERS-------------------------------------------*/

    /**-------------------------------------------PROCESS-HISTORY-------------------------------------------*/
    $(".history-date-from").val(getDateNow())
    $(".history-date-to").val(getDateNow())
    let param_request = {
        'date_from': getDateNow(),
        'date_to': getDateNow(),
        'permission0': 0,
        'permission1': 1,
        'permission2': 2,
    };
    getResultProcessHistory(param_request)
    setDataTable('.tb-process-history')
    $('.flag_h_permission').prop('checked', true);
    $('.check_flag_h_permission').on('click', function(){
        checked = $(this).find('.flag_h_permission').prop('checked');
        if(checked == true){
            $(this).find('.flag_h_permission').prop('checked', false);
        }else {
            $(this).find('.flag_h_permission').prop('checked', true);
        }
    });
    $('.flag_h_permission').on('click', function(){
        checked = $(this).prop('checked');
        if(checked == true){
            $(this).prop('checked', false);
        }else {
            $(this).prop('checked', true);
        }
    });
    $(document).on('click', '.btn-search-his-process', function(){
        loading();
        var permission0 = -1;//-1: bỏ chọn tìm kiếm quyền 管理者
        var permission1 = -1;//-1: bỏ chọn tìm kiếm quyền 運用者
        var permission2 = -1;//-1: bỏ chọn tìm kiếm quyền 仕入先
        $.each($("input[name='history-permission']:checked"), function(){
            var val = $(this).val();
            if(val == "0"){
                permission0 = 0;
            }
            else if(val == "1"){
                permission1 = 1;
            }
            else if(val == "2"){
                permission2 = 2;
            }
        });
        let search_user_name = $('.history-user-name').val()
        if(hasWhiteSpace($('.history-user-name').val()) !== true){
            $('.error-user-name').css('display', 'none');
            search_user_name = $('.history-user-name').val()
        }else {
            $('.error-user-name').css('display', 'block');
            $('.error-text-user-name').text('ユーザー名はスペースなしで半角文字のみを入力してください。');
            return false;
        }
        if(!_validatedate($(".history-date-from").val())){
            $('.error-date').css('display', 'block');
            $('.error-text-date').text('無効な日付です。再入力してください。');
            return false;
        }else {
            $('.error-date').css('display', 'none');
        }
        if(!_validatedate($(".history-date-to").val())){
            $('.error-date').css('display', 'block');
            $('.error-text-date').text('無効な日付です。再入力してください。');
            return false;
        }else {            
            $('.error-date').css('display', 'none');
        }
        $('.dataTables_paginate').css('display', 'none')
        var t = $('.tb-process-history').DataTable(); 
        t.clear().draw(false);
        let param_search_click = {
            'date_from': $(".history-date-from").val(),
            'date_to': $(".history-date-to").val(),
            'user_name': search_user_name,
            'permission0': permission0,
            'permission1': permission1,
            'permission2': permission2,
        }
        getResultProcessHistory(param_search_click);
    })
    function getResultProcessHistory(param){
        $.ajax({
            method: "POST",
            url: getListProcessHistory,
            data: {
                date_from: param.date_from,
                date_to: param.date_to,
                user_name: param.user_name,
                permission0: param.permission0,
                permission1: param.permission1,
                permission2: param.permission2
            },
            success: function (res) {
                if(res.status === true){
                    if(res.data.length > 0){
                        $('.dataTables_paginate').css('display', 'block')
                        var t = $('.tb-process-history').DataTable();
                        for(let i = 0; i < res.data.length; i++){
                            let permisson = res.data[i].process_permission;
                            t.row.add([
                                formatDate_(res.data[i].created_at),
                                res.data[i].process_user,
                                ((permisson == 0) ? '管理者' : (permisson == 1) ? '運用者' : '仕入先'),
                                res.data[i].process_screen,
                                res.data[i].process_description,
                            ]).draw(false);
                        }
                        endLoading()
                        if(res.data.length > 50){
                            $('.top').show();
                        }else{
                            $('.top').hide();
                        }
                    }else {
                        $('.dataTables_paginate').css('display', 'none')
                        var t = $('.tb-process-history').DataTable();
                        t.clear().draw(false);
                        endLoading()
                        $('.top').hide();
                    }
                    endLoading()
                }else {
                    $('.dataTables_paginate').css('display', 'none')
                    var t = $('.tb-process-history').DataTable();
                    t.clear().draw(false);
                    endLoading()
                    $('.top').hide();
                }
            }
        });
    }
    /**-------------------------------------------PROCESS-HISTORY-------------------------------------------*/

    // function format date
    function formatDate_(date){
        var d = new Date(date);
        var month = d.getMonth()+1;
        var day = d.getDate();
        var output = d.getFullYear() + '/' + 
        (month<10 ? '0' : '') + month + '/' +
        (day<10 ? '0' : '') + day + ' ' + d.getHours() + ':' + d.getMinutes();
        return output;
    }

    //Check space have into string
    function hasWhiteSpace(s) {
        return /\s/g.test(s);
    }
});