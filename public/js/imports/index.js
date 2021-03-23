$(document).ready(function () {    
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#btn_import').click(function (e) {
        e.preventDefault();
        let date_from = ''
        var import_ = true
        let date_to = ''
        let site_type = []
        date_from = $('#date_from').val();
        date_to = $('#date_to').val();
        // ajax-import-ec-cube
        if(!_validatedate(date_from)){
            $('.error-date').css('display', 'block');
            $('.error-text-date').text('無効な日付です。再入力してください。');
            return false;
        }else if(!_validatedate(date_to)){
            $('.error-date').css('display', 'block');
            $('.error-text-date').text('無効な日付です。再入力してください。');
            return false;
        }
        $('.error-date').css('display', 'none');
        if(date_from === '' || date_to === '') // nếu không nhập đầy đủ thông tin
        {
            ModalError('取込期間(受注日)を選択してください。', '', 'OK')
            return false
        }
        // kiểm tra nếu ngày bắt đầu lớn hơn ngày kết thúc
        if(date_from > date_to)
        {
            ModalError('日時自は日時至以前で選択してください。', '', 'OK')
            return false
        }
        // kiểm tra có check loại web site nào ko?
        $.each($('input[name="site_type"]:checked'), function () {
            site_type.push($(this).val())
        })
        if(site_type.length === 0)
        {
            ModalError('取込ECサイトを選択してください。', '', 'OK')
            return false
        }
        $('.modal-confirm-add-user').remove();
        let  modal = `
            <div class="modal fade modal-confirm-add-user" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">この条件で取り込みます。よろしいでしょうか？</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="import_confirm" data-dismiss="modal">`+'OK'+`</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">`+'キャンセル'+`</button>
                    </div>
                    </div>
                </div>
            </div>`
        $('body').append(modal); // thêm class model error
        $('.modal-confirm-add-user').modal()
//        }
    });

    //Import master
    $(document).on('click', '#btn_import_master', function(){
        $('.modal-confirm-add-user').remove();
        let  modal = `
            <div class="modal fade modal-confirm-add-user" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">マスタを取込みます。よろしでしょうか？</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="import_master_confirm" data-dismiss="modal">`+'OK'+`</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">`+'キャンセル'+`</button>
                    </div>
                    </div>
                </div>
            </div>`
        $('body').append(modal); // thêm class model error
        $('.modal-confirm-add-user').modal()
    });

    $(document).on('click', '#import_master_confirm', function(){
        loading()
        $.ajax({
            method: 'post',
            url: url_master_import,
            success: function(res){
                ModalSuccess(res.message, 'Ok')
                endLoading()
            }
        })
    });
    //End Import master
    $(document).on('click', '#import_confirm', function () {
        let data = {
            'date_from': $('#date_from').val(),
            'date_to': $('#date_to').val()
        }

        let site_type = []
        $.each($('input[name="site_type"]:checked'), function () {
            site_type.push($(this).val())
        })
        data['site_type'] = site_type

        loading()
        $.ajax({
            method: 'POST',
            url: 'import/ajax-import-ec-cube',
            data: data,
            success: function (res) {
                if(res.status === true)
                {
                    let message = "指定された取込期間(受注日)で取込ました。";
                    if(res.message != ''){
                        message = res.message;
                    }
                    ModalSuccess(message, 'OK')
                    endLoading()
                }else {
                    let message = "指定された取込期間(受注日)で取込に失敗しました。";
                    if(res.message != ''){
                        message = res.message;
                    }
                    ModalSuccess(message, 'OK')
                    endLoading()
                }
            }
        })
    })
    // click import lại những hóa đơn bị lỗi
    $(document).on('click', '.re-import', function () {
        var error_id = parseInt($(this).parent().find('.error-id').val())
        var import_id = parseInt($(this).parent().find('.import-id').val())
        loading()
        $.ajax({
            method: 'POST',
            url: 'import/ajax-re-import',
            data: {
                error_id: error_id,
                import_id: import_id
            },
            success: function (res) {
                if(res.status === true)
                {
                    let message = "指定された取込期間(受注日)で取込ました。";
                    if(res.message != ''){
                        message = res.message;
                    }
                    ModalSuccess(message, 'OK')
                }else {
                    let message = "指定された取込期間(受注日)で取込に失敗しました。";
                    if(res.message != ''){
                        message = res.message;
                    }
                    ModalSuccess(message, 'OK')
                }
                endLoading()
            }
        })
    })
});