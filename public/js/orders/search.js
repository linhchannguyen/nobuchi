$(document).ready(function () {
    var params = getSearchParameters();
    if(!isEmptyObject(params)){
        $('#buttonExtend').find('.fa-caret-square-down').remove()
        $('#buttonExtend').append('<i class="fas fa-caret-square-up"></i>')
        $(".search-extend").show();
    }
    setDataTable('#table_reponses');
    if(count_data > 0){
        if(count_data > 50){
            $('.top').show();
        }else{
            $('.top').hide();
        }
    }else {
        $('.top').hide();
    }
    //Ẩn phân trang trước khi load modal
    $(document).on('click', '.search-sku', function(){
        $('.top').hide();
        refreshModalSku()
    });
    $(document).on('click', '.search-supplier', function(){
        $('.top').hide();
        refreshModalSupplier()
    });
    var searchClick = false;
    // select day
    //click button search date today
    $('#select_day').on('click', function(){
        $(".date-from").val(getDateNow())
        $(".date-to").val(getDateNow())
    });
    // select yesterday
    let d = new Date(getDateNow());
    d.setDate(d.getDate() - 1);
    $(".date-from").val(getD(d))
    $(".date-to").val(getDateNow())
    
    $("#select_yesterday").click(function () {
        let d = new Date(getDateNow());
        d.setDate(d.getDate() - 1);
        $(".date-from").val(getD(d))
        $(".date-to").val(getDateNow())
    })
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
    var extendSearch = false; // set show extend search
    var extendControl =  false; // set show extend controll
    $('#create_order').on('click', function(){
        window.open(url_create_order, '_blank');
    })
    $("#search_order").click(function () {
        let order_id = $("#order_id_input").val()
        if(order_id === '')
        {
            ModalError('受注IDを入力してください。 ', '', 'OK')
            return false;
        }
        window.open("edit-order/"+order_id, '_blank'); // new tab
    })
    $('#order_id_input').on('keydown keyup', function(e){
        $('#modal_error').remove()
        $('.modal-backdrop').remove()
        if($(this).val() != ''){
            if(e.which == 13){
                window.open("edit-order/"+$(this).val(), '_blank'); // new tab
                e.preventDefault();
            }
        }else {
            if(e.which == 13){
                ModalError('受注IDを入力してください。 ', '', 'OK')
                e.preventDefault();
            }
        }
    })
    // click show extend search 
    $("#buttonExtend").click(function (e) { 
        e.preventDefault();
        if(extendSearch === false)
        {
            $(this).find('.fa-caret-square-down').remove()
            $(this).append('<i class="fas fa-caret-square-up"></i>')
            extendSearch = true;
            $(".search-extend").show();
        } else
        {
            $(this).find('.fa-caret-square-up').remove()
            $(this).append('<i class="fas fa-caret-square-down"></i>')
            extendSearch = false
            $(".search-extend").hide();
        }
    });
    // click show extend controll
    $('.extend-control').click((e) =>{
        e.preventDefault();
        if(extendControl === false)
        {
            $('.extend-control').find('.fa-caret-square-down').remove()
            $('.extend-control').append('<i class="fas fa-caret-square-up"></i>')
            extendControl = true;
            $(".controll-extend").show();
        } else
        {
            $('.extend-control').find('.fa-caret-square-up').remove()
            $('.extend-control').append('<i class="fas fa-caret-square-down"></i>')
            extendControl = false
            $(".controll-extend").hide();
        }
    })
    /**
     * check all
     */
    $(document).on('change', '#check_all', () => {
        if($("input[name='check_all']:checked").val() !== 'checked') 
        {
            $("#table_reponses>tbody>tr").each((index, element) => {
                $(element).find('input:checkbox').prop( "checked", false )
            })
            return false
        }
        $("#table_reponses>tbody>tr").each((index, element) => {
            $(element).find('input:checkbox').prop( "checked", true )
        })

    })
    /**
     * o-watting-money click
     * @author Dat
     */
    $(document).on('click', '.status-o-watting-money', function () {
        let status = $(this).data('status')
        let value = $(this).data('value')
        if(value === 0)
        {
            setReposeTable()
            return false
        }
        let data = {} 
        if(searchClick === false)
        {
            data  = Object.assign({}, requests); // copy json
        }
        else
        {
            data = Object.assign({}, setData()); // copy json
        }
        data.status_support = status
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        
        $.ajax({
            url: 'ajax-search-conditions',
            method: 'GET',
            data: data,
            success: function (response) {
             setReposeTable(response.data)
             endLoading();
            }
        });
    })
    /**
     * o-proccess click
     * @author Dat
     */
    $(document).on('click', '.status-o-proccess', function () {
        let status = $(this).data('status')
        let value = $(this).data('value')
        if(value === 0)
        {
            setReposeTable()
            return false
        }
        let data = {} 
        if(searchClick === false)
        {
            data  = Object.assign({}, requests); // copy json
        }
        else
        {
            data = Object.assign({}, setData()); // copy json
        }
        data.status_support = status
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        $.ajax({
            url: 'ajax-search-conditions',
            method: 'GET',
            data: data,
            success: function (response) {
             setReposeTable(response.data)
             endLoading();
            }
        });
    })
    /**
     * o-proccess-purchase click
     * @author Dat
     */
    $(document).on('click', '.status-o-proccess-purchase', function () {
        let status = $(this).data('status')
        let value = $(this).data('value')
        if(value === 0)
        {
            setReposeTable()
            return false
        }
        let data = {} 
        if(searchClick === false)
        {
            data  = Object.assign({}, requests); // copy json
        }
        else
        {
            data = Object.assign({}, setData()); // copy json
        }
        data.status_support = status
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        $.ajax({
            url: 'ajax-search-conditions',
            method: 'GET',
            data: data,
            success: function (response) {
             setReposeTable(response.data)
             endLoading();
            }
        });
    })
    /**
     * o-proccess-wrap click
     * @author Dat
     */
    $(document).on('click', '.status-o-proccess-wrap', function () {
        let status = $(this).data('status')
        let value = $(this).data('value')
        if(value === 0)
        {
            setReposeTable()
            return false
        }
        let data = {} 
        if(searchClick === false)
        {
            data  = Object.assign({}, requests); // copy json
        }
        else
        {
            data = Object.assign({}, setData()); // copy json
        }
        data.status_support = status
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        $.ajax({
            url: 'ajax-search-conditions',
            method: 'GET',
            data: data,
            success: function (response) {
             setReposeTable(response.data)
             endLoading();
            }
        });
    })
    /**
     * o-proccess-ship click
     * @author Dat
     */
    $(document).on('click', '.status-o-proccess-ship', function () {
        let status = $(this).data('status')
        let value = $(this).data('value')
        if(value === 0)
        {
            setReposeTable()
            return false
        }
        let data = {} 
        if(searchClick === false)
        {
            data  = Object.assign({}, requests); // copy json
        }
        else
        {
            data = Object.assign({}, setData()); // copy json
        }
        data.status_support = status
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        $.ajax({
            url: 'ajax-search-conditions',
            method: 'GET',
            data: data,
            success: function (response) {
             setReposeTable(response.data)
             endLoading();
            }
        });
    })
    /**
     * o-ship-notified click
     * @author Dat
     */
    $(document).on('click', '.status-o-ship-notified', function () {
        let status = $(this).data('status')
        let value = $(this).data('value')
        if(value === 0)
        {
            setReposeTable()
            return false
        }
        let data = {} 
        if(searchClick === false)
        {
            data  = Object.assign({}, requests); // copy json
        }
        else
        {
            data = Object.assign({}, setData()); // copy json
        }
        data.status_support = status
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        $.ajax({
            url: 'ajax-search-conditions',
            method: 'GET',
            data: data,
            success: function (response) {
             setReposeTable(response.data)
             endLoading();
            }
        });
    })
    /**
     * o-del click
     * @author Dat
     */
    $(document).on('click', '.status-o-del', function () {
        let status = $(this).data('status')
        let value = $(this).data('value')
        if(value === 0)
        {
            setReposeTable()
            return false
        }
        let data = {} 
        if(searchClick === false)
        {
            data  = Object.assign({}, requests); // copy json
        }
        else
        {
            data = Object.assign({}, setData()); // copy json
        }
        data.status_support = status
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        $.ajax({
            url: 'ajax-search-conditions',
            method: 'GET',
            data: data,
            success: function (response) {
                setReposeTable(response.data)
                endLoading();
            }
        });
    })
    /**
     * o-confirm click
     * @author Dat
     */
    $(document).on('click', '.flag-o-confirm', function () {
        let flag_confirm = []
        flag_confirm.push($(this).data('flagconfirm'))// push flag_confirm to array 
        let value = $(this).data('value')
        if(value === 0)
        {
            setReposeTable()
            return false
        }
        let data = {} 
        if(searchClick === false)
        {
            data  = Object.assign({}, requests); // copy json
        }
        else
        {
            data = Object.assign({}, setData()); // copy json
        }
        data.flag_confirm = flag_confirm
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        $.ajax({
            url: 'ajax-search-conditions',
            method: 'GET',
            data: data,
            success: function (response) {
             setReposeTable(response.data)
             endLoading();
            }
        });
    })
    /**
     * o-save click
     * @author Dat
     */
    $(document).on('click', '.flag-o-save', function () {
        let flag_confirm = []
        flag_confirm.push($(this).data('flagconfirm'))// push flag_confirm to array 
        let value = $(this).data('value')
        if(value === 0)
        {
            setReposeTable()
            return false
        }
        let data = {} 
        if(searchClick === false)
        {
            data  = Object.assign({}, requests); // copy json
        }
        else
        {
            data = Object.assign({}, setData()); // copy json
        }
        data.flag_confirm = flag_confirm
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        $.ajax({
            url: 'ajax-search-conditions',
            method: 'GET',
            data: data,
            success: function (response) {
             setReposeTable(response.data)
             endLoading();
            }
        });
    })
    /***
     * function click total-order
     */
    $(document).on('click', '.total-order', function() {
        $("#btn_search").click()
    })
    /**
     * function click search btn
     * @author Dat
     * 2019/10/11
     */
    let order_status = [1, 2, 3, 4, 5, 6, 7];
    let purchase_status = [1, 2, 3, 4, 5];
    let delivery_way = [1, 2, 3, 4];
    let delivery_method = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    var flag_check_update = false;
    $("#btn_search").click(function () {
        refreshResult('#table_reponses')
        let data = setData();
        let type_date = ''
        let date_from = ''
        let date_to = ''
        let continue_search = false;
        date_from = $('#date_from').val();
        date_to = $('#date_to').val();
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
        type_date = $('input[name="type_date"]:checked').val()
        // kiểm tra xem có dữ liệu tìm kiếm hay không
        for (let value in data) {
            if(typeof(data[value]) === 'string')
            {
                if(data[value] !== '') 
                {
                    continue_search = true
                }
            }
            if (typeof(data[value]) === 'object')
            {
                if(data[value].length > 0)
                {
                    continue_search = true
                }
            }
            if(typeof(data[value]) ===  'number')
            {
                if(data[value] > 0)
                {
                    continue_search = true
                }
            }
        }
        // nếu không có điều kiện tìm kiếm sẽ bắt tìm kiếm theo ngày
        if(continue_search === false)
        {
            if(type_date === '')
            {
                return false
            }
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
        }
        // xet loading page
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        if(flag_check_update == false){
            $('body').append(loading); // loading fill page
        }

        $.ajax({
            url: 'ajax-search-conditions',
            method: 'GET',
            data: data,
            success: function (response) {
                if(response.status == "false"){
                    endLoading();
                    ModalError('日時至は日時自以降で選択してください。', '', 'Ok');
                }else {
                    setReposeTable(response.data)
                    if(order_status.includes(parseInt(dataConfirm.status_support))){
                        dataConfirm = [];
                        ModalSuccessNoReload('受注ステータスを更新しました。', 'Ok')
                    }else if(purchase_status.includes(parseInt(dataConfirm.flag_confirm))){
                        dataConfirm = [];
                        ModalSuccessNoReload('発注ステータスを更新しました。', 'Ok')
                    }else if(delivery_way.includes(parseInt(dataConfirm.delivery_way))){
                        dataConfirm = [];
                        ModalSuccessNoReload('納品方法を更新しました。', 'Ok')
                    }else if(delivery_method.includes(parseInt(dataConfirm.delivery_method))){
                        dataConfirm = [];
                        ModalSuccessNoReload('配送方法を更新しました。', 'Ok')
                    }
                    searchClick = true
                    endLoading(); // xóa loading page
                }
            }
        });
    });
    /**
     * function select category
     * @author Dat2019/10/14
     */
    $('.category-select').change(function () {
        let group = $(this).val()
        let data = {}
        data = {
            'group' : group
        }
        if(group != ''){
            $.ajax({
                'url': urlGetCategory,
                'method': 'GET',
                'data': data,
                'success': function (response) {
                    let options = '<option value=""></option>'
                    if(response.length > 0)
                    { 
                        response.forEach(element => {
                            options+=`
                                <option value="`+element.id+`">`+element.name+`</option>
                            `       
                        });
                        $(".product-select").html(options)
                    }else {
                        $(".product-select").html('')
                    }
                }
            })
        }else {            
            $(".product-select").html('')
        }
    })
    /**
     * click update create order, copy, delete order
     */
    $("#btn_control_order").click(function (){
        let value = ''
        let list_orders = []
        let list_products = []
        let list_details = []
        let data_update = {}
        let data = {}
        data = getDataCheckedTable()
        list_orders = data.list_orders
        list_products = data.list_products
        list_details = data.list_details
        value = $('#control_order').val()
        if(value === '')
        {
            ModalError('注文登録・削除項目にて内容を選択してから再度更新ボタン押してください。', '', 'OK')
            return false
        }
        if(value === 'create')
        {
            window.open('create', '_blank'); // mở cửa sổ tạo mới order
        }
        if(value === 'copy')
        {
            let message_confirm = "チェック入れている商品で受注伝票を新規作成します。よろしいでしょうか？"
            if(list_orders.length <= 0)
            {
                ModalError('商品にチェック入れてから再度更新ボタン押してください。', '', 'OK')
                return false
            }
            data_update = {
                'list_orders': list_orders,
                'list_products': list_products,
                'list_details': list_details,
            }
            ModalConfirm(message_confirm, 'キャンセル', 'OK', urlCopyOrder, data, 'POST')
        }
        if(value === 'delete')
        {
            let message_confirm = "選択中の商品を削除します。よろしいでしょうか？"
            if(list_orders.length <= 0)
            {
                ModalError('商品にチェック入れてから再度更新ボタン押してください。', '', 'OK')
                return false
            }
            data_update = {
                'list_orders': list_orders,
                'list_products': list_products
            }
            ModalConfirm(message_confirm, 'キャンセル', 'OK', urlDeleteOrder, data, 'POST')
        }
    })
    /**
     * click update btn_update_status_support_value
     */
    var dataConfirm = []
    var urlConfirm =''
    var methodConfirm = ''
    function confirmControl (url, method, data, message)
    {
        $('.modal-confirm').remove(); // xóa class modal nếu có
        let modal = `
        <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="save_confirm_control" data-dismiss="modal">OK</button>
            </div>
            </div>
        </div>
        </div>`
        $('body').append(modal); // thêm class model error
        $('.modal-confirm').modal()
        dataConfirm= data
        urlConfirm = url
        methodConfirm = method
    }
    $(document).on('click', '#save_confirm_control',function () {
        $.ajax({
            'url': urlConfirm,
            'method': methodConfirm,
            'data': dataConfirm,
            'success': function (response) {
                if(response.status === true)
                {
                    flag_check_update = true;
                    $("#btn_search").click()// call function search
                }else {
                    flag_check_update = false;
                    if(order_status.includes(parseInt(dataConfirm.status_support))){
                        ModalSuccessNoReload('受注ステータスの更新に失敗しました。', 'Ok')
                    }else if(purchase_status.includes(parseInt(dataConfirm.flag_confirm))){
                        ModalSuccessNoReload('発注ステータスの更新に失敗しました。', 'Ok')
                    }else if(delivery_way.includes(parseInt(dataConfirm.delivery_way))){
                        ModalSuccessNoReload('納品方法の更新に失敗しました。', 'Ok')
                    }else if(delivery_method.includes(parseInt(dataConfirm.delivery_method))){
                        ModalSuccessNoReload('配送方法の更新に失敗しました。', 'Ok')
                    }
                }
            }
        })
    })
    $("#btn_update_status_support_value").click(function () {
        let list_orders = []
        let list_purchase_codes = [];
        let list_products = []
        let data_update = {}
        let data = {}
        let status_support = ''
        status_support = $("#update_status_support_value").val()
        data = getDataCheckedTable()
        list_orders = data.list_orders
        list_products = data.list_products
        list_purchase_codes = data.list_purchase_codes
        data_update = {
            'list_orders': list_orders,
            'list_purchase_codes': list_purchase_codes,
            'list_products': list_products,
            'status_support': status_support
        }
        if(status_support === '')
        {
            ModalError('受注ステータス項目にて内容を選択してから再度更新ボタン押してください。', '', 'OK')
            return false
        }
        if(list_orders.length <= 0)
        {
            ModalError('商品にチェック入れてから再度更新ボタン押してください。', '', 'OK')
            return false
        }
        let message = ''
        switch (status_support) {
            case '1':
                message = 'チェック入れている商品で受注伝票の受注ステータスを新規注文に更新します。よろしいでしょうか？'
                break;
        
            case '2':
                message = 'チェック入れている商品で受注伝票の受注ステータスを入金待ちに更新します。よろしいでしょうか？'
                break;
        
            case '3':
                message = 'チェック入れている商品で受注伝票の受注ステータスを受注処理中に更新します。よろしいでしょうか？'
                break;

            case '4':
                message = 'チェック入れている商品で受注伝票の受注ステータスを要確認に更新します。よろしいでしょうか？'
                break;
            case '5':
                message = 'チェック入れている商品で受注伝票の受注ステータスを保留中に更新します。よろしいでしょうか？'
                break;
    
            case '6':
                message = 'チェック入れている商品で受注伝票の受注ステータスを完了に更新します。よろしいでしょうか？'
                break;
                                                        
            default:
                message = 'チェック入れている商品で受注伝票の受注ステータスをキャンセルに更新します。よろしいでしょうか？'
                break;
        }
        confirmControl(urlUpdateorder,'POST', data_update, message)
    })
    
    /**
     * click update btn_update_flag_value
     */
    $("#btn_update_flag_value").click(function () {
        let list_orders = []
        let list_purchase_codes = [];
        let list_products = []
        let data_update = {}
        let data = {}
        let flag_confirm = ''
        flag_confirm = $("#update_flag_value").val()
        if(flag_confirm === '')
        {
            ModalError('発注ステータス項目にて内容を選択してから再度更新ボタン押してください。', '', 'OK')
            return false
        }
        data = getDataCheckedTable()
        list_orders = data.list_orders
        list_products = data.list_products
        list_purchases = data.list_purchases
        list_purchase_codes = data.list_purchase_codes
        data_update = {
            'list_orders': list_orders,
            'list_purchase_codes': list_purchase_codes,
            'list_products': list_products,
            'list_purchases': list_purchases,
            'flag_confirm': flag_confirm
        }
        if(list_orders.length <= 0)
        {
            ModalError('商品にチェック入れてから再度更新ボタン押してください。', '', 'OK')
            return false
        }
        let message = ''
        switch (flag_confirm) {
            case '1':
                message = 'チェック入れている商品で受注伝票の発注ステータスを未処理に更新します。よろしいでしょうか？'
                break;        
            case '2':
                message = 'チェック入れている商品で受注伝票の発注ステータスを発注済、印刷済に更新します。よろしいでしょうか？'
                break;
            case '3':
                message = 'チェック入れている商品で受注伝票の発注ステータスを送り状作成済に更新します。よろしいでしょうか？'
                break;
            case '4':
                message = 'チェック入れている商品で受注伝票の発注ステータスを出荷通知済に更新します。よろしいでしょうか？'
                break;                                                        
            default:
                message = 'チェック入れている商品で受注伝票の発注ステータスをキャンセルに更新します。よろしいでしょうか？'
                break;
        }
        confirmControl(urlUpdateorder,'POST', data_update, message)
    })
    
    /**
     * click update btn_update_delivery_way
     */
    $("#btn_update_delivery_way").click(function () {
        let list_orders = []
        let list_purchase_codes = [];
        let list_products = []
        let data_update = {}
        let data = {}
        let delivery_way = ''
        delivery_way = $("#update_delivery_way_value").val()
        if(delivery_way === '')
        {
            ModalError('納品方法項目にて内容を選択してから再度更新ボタン押してください。', '', 'OK')
            return false
        }
        data =getDataCheckedTable()
        list_orders = data.list_orders
        list_products = data.list_products
        list_purchase_codes = data.list_purchase_codes
        data_update = {
            'list_orders': list_orders,
            'list_purchase_codes': list_purchase_codes,
            'list_products': list_products,
            'delivery_way': delivery_way
        }
        if(list_orders.length <= 0)
        {
            ModalError('商品にチェック入れてから再度更新ボタン押してください。', '', 'OK')
            return false
        }
        let message = ''
        switch (delivery_way) {
            case '1':
                message = 'チェック入れている商品で受注伝票の納品方法を直送に更新します。よろしいでしょうか？'
                break;
            case '2':
                message = 'チェック入れている商品で受注伝票の納品方法を引取に更新します。よろしいでしょうか？'
                break;
            case '3':
            message = 'チェック入れている商品で受注伝票の納品方法を配達に更新します。よろしいでしょうか？'
            break;
            default:
                message = 'チェック入れている商品で受注伝票の納品方法を仕入に更新します。よろしいでしょうか？'
                break;
        }
        confirmControl(urlUpdateorder,'POST', data_update, message)
    })
    
    /**
     * click update btn_update_delivery_method
     */
    $("#btn_update_delivery_method").click(function () {
        let list_orders = [];
        let list_purchase_codes = [];
        let list_shipments = [];
        let list_products = [];
        let data_update = {}
        let data = {}
        let delivery_method = ''
        delivery_method = $("#update_delivery_method_value").val()
        if(delivery_method === '')
        {
            ModalError('配送方法項目にて内容を選択してから再度更新ボタン押してください。', '', 'OK')
            return false
        }
        data = getDataCheckedTable()
        list_orders = data.list_orders
        list_products = data.list_products
        list_purchase_codes = data.list_purchase_codes
        list_shipments = data.list_shipments
        data_update = {
            'list_orders': list_orders,
            'list_purchase_codes': list_purchase_codes,
            'list_products': list_products,
            'list_shipments': list_shipments,
            'delivery_method': delivery_method
        }
        if(list_orders.length <= 0)
        {
            ModalError('商品にチェック入れてから再度更新ボタン押してください。', '', 'OK')
            return false
        }
        let message = ''
        switch (delivery_method) {
            case '1':
                message = 'チェック入れている商品で受注伝票の配送方法を佐川急便に更新します。よろしいでしょうか？'
                break;
            case '2':
                message = 'チェック入れている商品で受注伝票の配送方法をヤマト宅急便に更新します。よろしいでしょうか？'
                break;
            case '3':
            message = 'チェック入れている商品で受注伝票の配送方法をネコポスに更新します。よろしいでしょうか？'
                break;
            case '4':
                message = 'チェック入れている商品で受注伝票の配送方法をコンパクトに更新します。よろしいでしょうか？'
                break;
            case '5':
                message = 'チェック入れている商品で受注伝票の配送方法をゆうパックに更新します。よろしいでしょうか？'
                break;
            case '6':
            message = 'チェック入れている商品で受注伝票の配送方法をゆうパケットに更新します。よろしいでしょうか？'
                break;
            case '7':
            message = 'チェック入れている商品で受注伝票の配送方法を代引きに更新します。よろしいでしょうか？'
                break;
            case '9':
            message = 'チェック入れている商品で受注伝票の配送方法を佐川急便(秘伝II)に更新します。よろしいでしょうか？'
                break;
            default:
                message = 'チェック入れている商品で受注伝票の配送方法をその他に更新します。よろしいでしょうか？'
                break;
        }
        confirmControl(urlUpdateorder,'POST', data_update, message)
    })
    /**
     * click export data excel
     * @author Dat
     * 2019-11-01
     */
    $(document).on('click', '#btn_export_data', function () {
        let list_orders = []
        let data_export = ''
        data_export = $('#export_data').val()
        if(data_export === '')
        {
            ModalError('データ出力項目にて内容を選択してから再度ダウンロードボタン押してください。', '', 'OK')
            return false
        }
        let data = getDataCheckedTable()
        let details = []
        details = data.list_details
        list_orders = data.list_orders
        let order_list = ''
        let detail_list = ''
        if(list_orders.length <= 0)
        {
            ModalError('商品にチェック入れてから再度ダウンロードボタン押してください。', '', 'OK')
            return false
        }
        list_orders.forEach((element, index) => {
            if((list_orders.length-1) === index)
            {
                order_list += element
                detail_list+=details[index]
            }
            else
            {
                order_list += element +','
                detail_list+=details[index]+','
            }
        });
        switch (data_export) {
            case 'purchase':
                $('.modal-confirm').remove(); // xóa class modal nếu có
                let modal = `
                <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">選択された商品で発注一覧表を出力します。よろしいでしょうか？</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="button" class="btn btn-primary" id="purchase_confirm_ex" data-dismiss="modal">OK</button>
                    </div>
                    </div>
                </div>
                </div>`
                $('body').append(modal); // thêm class model error
                $('.modal-confirm').modal()
                $('#purchase_confirm_ex').click(function () {
                    loading()
                    let dataSuppDetails = []
                    let dataSupp = []
                    $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
                        dataSupp.push($(this).parent().parent().find('.supplied-id').data('supplied')) // mảng nhà cung cấp
                        dataSuppDetails.push({
                            sup:$(this).parent().parent().find('.supplied-id').data('supplied'), 
                            detail_id: $(this).parent().parent().find('.detail-id').data('detail'),
                            name_supplied: $(this).parent().parent().find('.supplied-id').data('suppliedname')
                        })
                    })
                    // xu ly duplicate supplied
                    let data_download = []
                    let supplied_list = [... new Set (dataSupp)]
                    // add những order_details có chung nhà cung cấp thành 1
                    supplied_list.forEach(function (element_su) {
                        let detail_su =[]
                        let supplier_name = '';
                        dataSuppDetails.forEach(function (element, index) {
                            if(element.sup === element_su)
                            {
                                supplier_name = element.name_supplied
                                detail_su.push(element.detail_id)
                            }
                        })
                        let arr = {
                            'arr_detail': detail_su,
                            'supplier_name': supplier_name
                        }
                        data_download.push(arr)
                    })
                    // down load file excel
                    var param = {};
                    $.each(data_download, function( key, value ) {  
                        param = {
                            'arr_detail': value.arr_detail,
                            'sel_download': 2,
                            'screen': 3
                        }
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
                                let file_name = value.supplier_name + date_[0] + date_[1] + date_[2];
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
                })
                break;
            case 'purchase_pdf':
                $('.modal-confirm-purchase-pdf').remove(); // xóa class modal nếu có
                let modal_purchase_pdf = `
                <div class="modal fade modal-confirm-purchase-pdf" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">選択された商品でPDF発注一覧表を出力します。よろしいでしょうか？</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="button" class="btn btn-primary" id="purchase_confirm_pdf" data-dismiss="modal">OK</button>
                    </div>
                    </div>
                </div>
                </div>`;
                $('body').append(modal_purchase_pdf);
                $('.modal-confirm-purchase-pdf').modal()
                $('#purchase_confirm_pdf').click(function () {
                    loading()
                    let dataSuppDetails = []
                    let dataSupp = []
                    $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
                        dataSupp.push($(this).parent().parent().find('.supplied-id').data('supplied')) // mảng nhà cung cấp
                        dataSuppDetails.push({
                            sup:$(this).parent().parent().find('.supplied-id').data('supplied'), 
                            detail_id: $(this).parent().parent().find('.detail-id').data('detail'),
                            name_supplied: $(this).parent().parent().find('.supplied-id').data('suppliedname')
                        })
                    })
                    // xu ly duplicate supplied
                    let data_download = []
                    let supplied_list = [... new Set (dataSupp)]
                    // add những order_details có chung nhà cung cấp thành 1
                    supplied_list.forEach(function (element_su) {
                        let detail_su =[]
                        let supplier_name = '';
                        dataSuppDetails.forEach(function (element, index) {
                            if(element.sup === element_su)
                            {
                                supplier_name = element.name_supplied
                                detail_su.push(element.detail_id)
                            }
                        })
                        let arr = {
                            'arr_detail': detail_su,
                            'supplier_name': supplier_name
                        }
                        data_download.push(arr)
                    })
                    doExport(data_download, 0, null, 2, 3, url_export_one, null)
                });
                break;
            case 'pack_intro':
                $('.modal-confirm').remove(); // xóa class modal nếu có
                let modal_pack_intro = `
                <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">選択された商品で発注明細・梱包指示書を出力します。よろしいでしょうか？</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="button" class="btn btn-primary" id="pack_intro_confirm_ex" data-dismiss="modal">OK</button>
                    </div>
                    </div>
                </div>
                </div>`
                $('body').append(modal_pack_intro); // thêm class model error
                $('.modal-confirm').modal()
                $('#pack_intro_confirm_ex').click(function (){
                    loading()
                    let dataSuppDetails2 = []
                    let dataSupp2 = []
                    $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
                        dataSupp2.push($(this).parent().parent().find('.supplied-id').data('supplied')) // mảng nhà cung cấp
                        dataSuppDetails2.push({
                            sup:$(this).parent().parent().find('.supplied-id').data('supplied'), 
                            detail_id: $(this).parent().parent().find('.detail-id').data('detail'),
                            name_supplied: $(this).parent().parent().find('.supplied-id').data('suppliedname')
                        })
                    })
                    // xu ly duplicate supplied
                    let data_download2 = []
                    let listSupplied = [... new Set (dataSupp2)]
                    // add những order_details có chung nhà cung cấp thành 1
                    listSupplied.forEach(function (element_su) {
                        let detail_su =[]
                        let details_suplied = []
                        detail_su['name_supplied']
                        dataSuppDetails2.forEach(function (element, index) {
                            if(element.sup === element_su)
                            {
                                details_suplied.push(element.detail_id)
                                detail_su['details'] = details_suplied
                                detail_su['name_supplied'] = element.name_supplied
                            }
                        })
                        data_download2.push(detail_su)
                    })
                    var param = {};
                    $.each(data_download2, function( key, value ) {    
                        param = {
                            'arr_detail': value.details,
                            'sel_download': 3,
                            'screen': 3
                        }
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
                                let file_name = value.name_supplied + date_[0] + date_[1] + date_[2];
                                let link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
                                $('body').append(link)
                                $(`#${randomId}`)[0].click()
                                $(`#${randomId}`).remove()
                                endLoading()
                            },
                            error: function (ajaxContext) {
                                toastr.error('Export error: '+ ajaxContext.responseText);
                                endLoading()
                            }
                        });
                    })
                })
                
                break;
            case 'pack_intro_pdf':
                $('.modal-confirm-package-pdf').remove(); // xóa class modal nếu có
                let modal_parkage_pdf = `
                <div class="modal fade modal-confirm-package-pdf" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">選択された商品でPDF発注明細・梱包指示書を出力します。よろしいでしょうか？</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="button" class="btn btn-primary" id="package_confirm_pdf" data-dismiss="modal">OK</button>
                    </div>
                    </div>
                </div>
                </div>`;
                $('body').append(modal_parkage_pdf);
                $('.modal-confirm-package-pdf').modal();
                $('#package_confirm_pdf').click(function () {
                    loading()
                    let dataSuppDetails2 = []
                    let dataSupp2 = []
                    $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
                        dataSupp2.push($(this).parent().parent().find('.supplied-id').data('supplied')) // mảng nhà cung cấp
                        dataSuppDetails2.push({
                            sup:$(this).parent().parent().find('.supplied-id').data('supplied'), 
                            detail_id: $(this).parent().parent().find('.detail-id').data('detail'),
                            name_supplied: $(this).parent().parent().find('.supplied-id').data('suppliedname')
                        })
                    })
                    // xu ly duplicate supplied
                    let data_download2 = []
                    let listSupplied = [... new Set (dataSupp2)]
                    // add những order_details có chung nhà cung cấp thành 1
                    listSupplied.forEach(function (element_su) {
                        let detail_su =[]
                        let details_suplied = []
                        detail_su['supplier_name']
                        dataSuppDetails2.forEach(function (element, index) {
                            if(element.sup === element_su)
                            {
                                details_suplied.push(element.detail_id)
                                detail_su['arr_detail'] = details_suplied
                                detail_su['supplier_name'] = element.name_supplied
                            }
                        })
                        data_download2.push(detail_su)
                    })
                    doExport(data_download2, 0, null, 3, 3, url_export_one, null)
                });
                break;
            case 'bill_sagawa':
                $('.modal-confirm').remove(); // xóa class modal nếu có
                let modal_bill_sagawa = `
                <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">佐川用送り状データのフォーマットで出力します。よろしいですか？</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="button" class="btn btn-primary" id="bill_sagawa_confirm_ex" data-dismiss="modal">OK</button>
                    </div>
                    </div>
                </div>
                </div>`
                $('body').append(modal_bill_sagawa); // thêm class model error
                $('.modal-confirm').modal()
                $('#bill_sagawa_confirm_ex').click(function () {
                    loading()
                    let list_details_bill_sagawa = ''
                    $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
                        list_details_bill_sagawa +=$(this).parent().parent().find('.detail-id').data('detail')+','
                    })
                    endLoading()
                    window.location = url_export_sagawa_shipment+"?list_details="+list_details_bill_sagawa+'&screen=3'
                })
                break;
            case 'bill_sagawa_II':
                $('.modal-confirm').remove(); // xóa class modal nếu có
                let modal_bill_sagawa_II = `
                <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">佐川急便(秘伝II)送り状発行データのフォーマットで出力します。よろしいですか？</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="button" class="btn btn-primary" id="bill_sagawa_II_confirm_ex" data-dismiss="modal">OK</button>
                    </div>
                    </div>
                </div>
                </div>`
                $('body').append(modal_bill_sagawa_II); // thêm class model error
                $('.modal-confirm').modal()
                $('#bill_sagawa_II_confirm_ex').click(function () {
                    loading()
                    let date_ = getDateNow().split('/');
                    let list_details_bill_sagawa = ''
                    $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
                        list_details_bill_sagawa +=$(this).parent().parent().find('.detail-id').data('detail')+','
                    })
                    window.location = url_send_shipment_II+"?list_details="+list_details_bill_sagawa+'&screen=3&file_name=sagawa_hiden_'+date_[0]+date_[1]+date_[2];
                    endLoading()
                })
                break;
            case 'bill_yamoto':
            case 'bill_yupack':
                let act = '';
                let bill = 'ヤマト用送り状データのフォーマットで出力します。よろしいですか？'
                if(data_export === 'bill_yupack')
                {
                    act = 'ゆうパック用送り状データ';
                    bill = 'ゆうパック用送り状データのフォーマットで出力します。よろしいですか？'
                }else {
                    act = 'ヤマト用送り状データ';
                }
                $('.modal-confirm').remove(); // xóa class modal nếu có
                let modal_bill = `
                <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">`+bill+`</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="button" class="btn btn-primary" id="bill_confirm_ex" data-dismiss="modal">OK</button>
                    </div>
                    </div>
                </div>
                </div>`
                $('body').append(modal_bill); // thêm class model error
                $('.modal-confirm').modal()
                $('#bill_confirm_ex').click(function (){
                    loading()
                    let params_yamoto = {}
                    let list_details_yamoto = []
                    $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
                        list_details_yamoto.push($(this).parent().parent().find('.detail-id').data('detail'))
                    })
                    params_yamoto = {
                        'arr_detail': list_details_yamoto,
                        'screen': 3,
                        'flag_download_bill': (data_export === 'bill_yupack') ? 'yupack' : 'yamato',
                        'act': act
                    }
                    $.ajax({
                        cache: false,
                        url: url_send_shipment, //GET route 
                        data:  params_yamoto,
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
                            if(data_export === 'bill_yupack'){
                                file_name = 'yubin_' + date_[0] + date_[1] + date_[2];
                            }else {
                                file_name = 'yamato_' + date_[0] + date_[1] + date_[2];
                            }
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
                break;
            case 'notified_amazon':
                $('.modal-confirm').remove(); // xóa class modal nếu có
                let modal_notified_amazon = `
                <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">ECサイト別がAmazonひろしま、Amazonワールド、Amazonリカー以外にしてもAmazon用出荷通知データを出力します。よろしいでしょうか？</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="button" class="btn btn-primary" id="notified_amazon_confirm_ex" data-dismiss="modal">OK</button>
                    </div>
                    </div>
                </div>
                </div>`
                $('body').append(modal_notified_amazon); // thêm class model error
                $('.modal-confirm').modal()
                $('#notified_amazon_confirm_ex').click(function () {
                    loading()
                    let list_details = ''
                    $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
                        list_details +=$(this).parent().parent().find('.detail-id').data('detail')+','
                    })

                    window.location = 'export-notification-amazon?list_details='+list_details+'&screen=3'
                    endLoading()
                })
                break;
            case 'notified_rakuten':
                let date_ = getDateNow().split('/');
                $('.modal-confirm').remove(); // xóa class modal nếu có
                let modal_notified_rakuten = `
                <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">ECサイト別が楽天以外にしても楽天用出荷通知データを出力します。よろしいでしょうか？</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="button" class="btn btn-primary" id="notified_rakuten_confirm_ex" data-dismiss="modal">OK</button>
                    </div>
                    </div>
                </div>
                </div>`
                $('body').append(modal_notified_rakuten); // thêm class model error
                $('.modal-confirm').modal()
                $('#notified_rakuten_confirm_ex').click(function() {
                    loading()        
                    let list_details_rakuten = ''
                    $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
                        list_details_rakuten +=$(this).parent().parent().find('.detail-id').data('detail')+','
                    })
                    endLoading()
                    window.location = url_export_shipment_notification+'?list_details='+list_details_rakuten+'&website=rakuten&file_name=rakuten'+date_[0]+date_[1]+date_[2]+'&screen=3';
                })
                break;
            case 'notified_yahoo':
                $('.modal-confirm').remove(); // xóa class modal nếu có
                let modal_notified_yahoo = `
                <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red ">確認</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="text-align:center">
                        <b style="font-size=16px;">ECサイト別がYahoo以外にしてもYahoo用出荷通知データを出力します。よろしいでしょうか？</b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                        <button type="button" class="btn btn-primary" id="notified_yahoo_confirm_ex" data-dismiss="modal">OK</button>
                    </div>
                    </div>
                </div>
                </div>`
                $('body').append(modal_notified_yahoo); // thêm class model error
                $('.modal-confirm').modal()
                $('#notified_yahoo_confirm_ex').click(function () {
                    loading()
                    let list_details_yahoo = []
                    $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
                        list_details_yahoo.push($(this).parent().parent().find('.detail-id').data('detail'))
                    })
                    let params_yahoo = {
                        'list_details': list_details_yahoo,
                        'website': 'yahoo',
                        'screen': 3
                    }
                    let date_ = getDateNow().split('/');
                    let file_name = 'odstats_order';
                    window.location = url_export_shipment_notification+'?list_details='+list_details_yahoo+'&website=yahoo&file_name='+file_name+'&screen=7';
                    endLoading()
                    // $.ajax({
                    //     cache: false,
                    //     url: url_export_shipment_notification, //GET route 
                    //     data:  params_yahoo,
                    //     contentType: 'application/json; charset=utf-8',
                    //     dataType: 'binary',
                    //     xhrFields: {
                    //         responseType: 'blob',
                    //     },
                    //     success: function (result, status, xhr) {
                    //         let responseType = xhr.getResponseHeader('content-type') || 'application/octet-binary'
                    //         let blob = new Blob([result], { type: responseType });
                    //         let url = URL.createObjectURL(blob);
                    //         let randomId = `download-${Math.floor(Math.random()*1000000)}`;
                    //         let date_ = getDateNow().split('/');
                    //         let file_name = 'odstats_order' + date_[0] + date_[1] + date_[2];
                    //         let link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlxs'>link</a>`;
                    //         $('body').append(link)
                    //         $(`#${randomId}`)[0].click()
                    //         $(`#${randomId}`).remove()
                    //         endLoading()
                    //     },
                    //     error: function (ajaxContext) {
                    //         endLoading()
                    //     }
                    // });
                })
                break;
            default:
                break;
        }
    })

    /**
     * function set data search
     * @author Dat
     * 2019/10/09
     */
    function setData()
    {
        let flagConfirm = [];
        let status_support = [];
        let delivery_method = []
        let delivery_way = []
        let support_cus = []
        let site_type =[]
        let orther = []
        let buyer_name = ''
        let ship_name = ''
        let group = ''
        let category_id = ''
        let buyer_tel = ''
        let ship_tel = ''
        let buyer_address = ''
        let ship_address = ''
        let supplied = ''
        let sku = ''
        let name_product = ''
        let purchase_code = ''
        let date_from = $("#date_from").val()
        let date_to = $("#date_to").val()
        buyer_name = $("#buyer_name").val()
        ship_name = $("#ship_name").val()
        group = $("#category").val()
        if($("#product_id").val() !== null)
        {
            category_id = $("#product_id").val()
        }
        buyer_tel = $("#buyer_tel").val()
        ship_tel = $("#ship_tel").val()
        buyer_address = $("#buyer_address").val()
        ship_address = $("#ship_address").val()
        supplied = $("#supplied").val()
        sku = $("#sku").val()
        name_product = $("#name_product").val()
        purchase_code = $("#purchase_code").val()

        $.each($("input[name='flag_confirm']:checked"), function(){
            flagConfirm.push($(this).val());
        });
        $.each($("input[name='status_support']:checked"), function(){
            status_support.push($(this).val())
        });
        $.each($("input[name='delivery_method']:checked"), function () {
            delivery_method.push($(this).val());            
        })
        $.each($("input[name='delivery_way']:checked"), function () {
            delivery_way.push($(this).val());            
        })
        $.each($("input[name='support_cus']:checked"), function () {
            support_cus.push($(this).val());            
        })
        $.each($("input[name='site_type']:checked"), function () {
            site_type.push($(this).val());            
        })
        $.each($("input[name='orther']:checked"), function () {
            orther.push($(this).val());            
        })

        let searchNCC = $("input[name='searchNCC']:checked").val();
        let type_date = '';
        type_date = $("input[name='type_date']:checked").val();
        let date_created_from = '';
        let date_created_to = '';
        let date_import_from = '';
        let date_import_to = '';
        let date_purchased_from = '';
        let date_purchased_to = '';
        let delivery_date_from = '';
        let delivery_date_to = '';
        let ship_schedule_from = '';
        let ship_schedule_to = '';
        let recive_schedule_from = ''
        let recive_schedule_to = ''
        if(type_date === 'order_created')
        {
            date_created_from = date_from
            date_created_to = date_to 
        }
        if(type_date === 'date_import')
        {
            date_import_from = date_from
            date_import_to = date_to
        }
        if(type_date === 'date_purchased')
        {
            date_purchased_from = date_from
            date_purchased_to = date_to 
        }
        if(type_date === 'ship_date')
        {
            delivery_date_from = date_from
            delivery_date_to = date_to
        }
        if(type_date === 'ship_schedule_from')
        {
            ship_schedule_from = date_from
            ship_schedule_to = date_to
        }
        if(type_date === 'ship_schedule_to')
        {
                recive_schedule_from = date_from
                recive_schedule_to = date_to
        }
        let data = {
            'date_created_from': date_created_from,
            'date_created_to': date_created_to,
            'date_import_from': date_import_from,
            'date_import_to': date_import_to,
            'date_purchased_from': date_purchased_from,
            'date_purchased_to': date_purchased_to,
            'delivery_date_from': delivery_date_from,
            'delivery_date_to': delivery_date_to,
            'ship_schedule_from': ship_schedule_from,
            'ship_schedule_to': ship_schedule_to,
            'recive_schedule_from': recive_schedule_from,
            'recive_schedule_to': recive_schedule_to,
            'flag_confirm': flagConfirm,//array
            'status_support': status_support, // array
            'delivery_method': delivery_method, // array
            'delivery_way': delivery_way, // array
            'support_cus': support_cus, //array
            'site_type': site_type, // array
            'orther': orther, // array
            'buyer_name': buyer_name,
            'ship_name': ship_name,
            'group': group,
            'category_id': category_id,
            'buyer_tel': buyer_tel,
            'ship_tel': ship_tel,
            'buyer_address': buyer_address,
            'ship_address': ship_address,
            'supplied': supplied,
            'sku': sku,
            'name_product': name_product,
            'purchase_code': purchase_code,
            'searchNCC' : searchNCC
        }  
        for(var j in params){
            if(j == 'supplied_id'){
                data.supplied_id = params[j];
            }
        }
        return data;
    }
    /**
     * function set valuetable
     * @author Dat
     * 2019/10/10
     */
    function setReposeTable(data = [])
    {
        let tb_result = `
            <thead>
                <th scope="col" class="title-table" width="5%">番号</th>
                <th scope="col" class="title-table" width="11%">発注ID</th>
                <th scope="col" class="title-table" width="11%">受注ID</th>
                <th scope="col" class="title-table" width="10%">注文主名</th>
                <th scope="col" class="title-table" width="11%">受注ステータス</th>
                <th scope="col" class="title-table" width="11%">発注ステータス</th>
                <th scope="col" class="title-table" width="13%">品名</th>
                <th scope="col" class="title-table" width="8%">送り状番号</th>
                <th scope="col" class="title-table" width="10%">集荷先</th>
                <th scope="col" class="title-table" width="7%">集荷日時</th>
                <th scope="col" style="font-size: 16px; text-align: center" width="3%">
                    <input type="checkbox" name="check_all" id="check_all" value="checked">
                </th>
            </thead>
            <tbody>`;
        if(data.length > 0)
        {
            // check all table
            $('#check_all').prop('checked', false)
            let tbody = '';
            let flag_confirm = ''
            let support_cus = ''
            let name_cus = ''
            var order_code = data[0].order_code;
            let color_arr = {
                true:'#FFF',
                false:'#dcdcdc'
            }
            let color = true;
            data.forEach((element, index) => {  
                if(order_code !== element.order_code){
                    color = !color;
                    order_code = element.order_code;
                }
                let shipment_code = ''
                if(element.shipment_code !== null)
                {
                    shipment_code = element.shipment_code
                }
                // co xac nhan 0: khong co, 1: can xac nhan, 2: dang bao luu
                if(element.p_status === 1){
                    flag_confirm = '未処理';
                } else if(element.p_status === 2){
                    flag_confirm = '発注済、印刷済';
                } else if(element.p_status === 3){
                    flag_confirm = '送り状作成済';
                }else if(element.p_status === 4){
                    flag_confirm = '出荷通知済';
                }else if(element.p_status === 5){
                    flag_confirm = 'キャンセル';
                }
                // trang thai
                if(element.status === 1)
                {
                    support_cus = '新規注文';
                } else if(element.status === 2)
                {
                    support_cus = '入金待ち';
                } else if(element.status === 3)
                {
                    support_cus = '受注処理中';
                } else if(element.status === 4)
                {
                    support_cus = '要確認';
                } else if(element.status === 5)
                {
                    support_cus = '保留中';
                } else if(element.status === 6)
                {
                    support_cus = '完了';
                } else if(element.status === 7)
                {
                    support_cus = 'キャンセル';
                }
                if(element.buyer_name1 !== null)
                {
                    name_cus = element.buyer_name1
                }
                if(element.buyer_name2 !== null)
                {
                    name_cus += element.buyer_name2
                }
                tb_result +=
                `
                <tr style="background-color: `+color_arr[color]+`;">
                    <td class="center-txt" scope="row">
                        <input type="hidden" class="id-order" data-id="`+element.order_code+`">`+(index +1)+`
                        <input type="hidden" class="detail-id" data-detail="`+element.detail_id+`">
                        <input type="hidden" class="supplied-id" data-supplied="`+element.supplied_id+`" data-suppliedName="`+element.supplied_name+`">
                    </td>
                    <td class="center-txt" scope="col">`+element.purchase_code+`</td>
                    <td class="center-txt" scope="col"><a href="edit-order/`+element.order_code+`" target="_blank">`+element.order_code+`</a></td>
                    <td class="middle-txt" scope="col">`+name_cus+`</td>
                    <td class="center-txt" scope="col">`+support_cus+`</td>
                    <td class="center-txt" scope="col">`+flag_confirm +`</td>
                    <td class="middle-txt" scope="col">`+(element.product_name != null ? element.product_name : '')+`</td>
                    <td class="center-txt" scope="col">`+shipment_code+` </td>
                    <td class="middle-txt" scope="col">`+element.supplied_name+` </td>
                    <td class="center-txt" scope="col">`+formatDate_(element.es_shipment_date)+`</td>
                    <td class="center-txt" scope="col" style="font-size: 16px; text-align: center">
                        <input type="hidden" class="process-detail-id" value="`+element.detail_id+`">
                        <input type="hidden" class="product-id" value="`+element.product_id+`">
                        <input type="hidden" class="purchase-id" value="`+element.purchase_id+`">
                        <input type="hidden" class="purchase-code" value="`+element.purchase_code+`">
                        <input type="hidden" class="shipment-code" value="`+element.shipment_code+`">
                        <input type="hidden" class="shipment-id" value="`+element.shipment_id+`">
                        <input type="hidden" class="shipment-deli" value="`+element.shipment_deli+`">
                        <input type="hidden" class="order-code" value="`+element.order_code+`">
                        <input type="hidden" class="order-site-type" value="`+element.site_type+`">
                        <input type="checkbox" class="checkbox">
                    </td>
                </tr>
                `
            });
            tb_result += `</tbody>`;
            $('#table_reponses').html(tb_result);
            setDataTable('#table_reponses');
            if(data.length > 50){
                $('.top').show();
            }else{
                $('.top').hide();
            }
        }
        else
        {
            $('.top').hide();
            tb_result += `<tr>
                                <td class="center-txt" colspan="11">検索条件に該当するデータはありません。</td>
                          </tr>`
            $('#table_reponses').html(tb_result);
        }
    }
    /**
     * function set table total
     * @param {data reponse}  
     * @author Dat
     */
    function setTotalTable (data)
    {
        if(data.length > 0)
        {
            let tbody = '';
            data.forEach((element, index) => {
                let o_watting_money = 0
                let o_proccess = 0
                let o_proccess_purchase = 0
                let o_proccess_wrap = 0
                let o_proccess_ship = 0
                let o_ship_notified = 0
                let o_del = 0
                let o_confirm = 0
                let o_save = 0
                if(element.o_watting_money !== null)
                {
                    o_watting_money = element.o_watting_money
                }
                if(element.o_proccess !== null)
                {
                    o_proccess = element.o_proccess
                }
                if(element.o_proccess_purchase !== null)
                {
                    o_proccess_purchase = element.o_proccess_purchase
                }
                if(element.o_proccess_wrap !== null)
                {
                    o_proccess_wrap = element.o_proccess_wrap
                }
                if(element.o_proccess_ship !== null)
                {
                    o_proccess_ship = element.o_proccess_ship
                }
                if(element.o_ship_notified !== null)
                {
                    o_ship_notified = element.o_ship_notified
                }
                if(element.o_del !== null)
                {
                    o_del = element.o_del
                }
                if(element.o_confirm !== null)
                {
                    o_confirm = element.o_confirm
                }
                if(element.o_save !== null)
                {
                    o_save = element.o_save
                }
                tbody+=`
                    <td scope="col">
                        <a href="#" class="total-order">`+element.total+`件</a>
                    </td> <!--total order -->
                    <td scope="col">
                        <a href="#" class="status-o-watting-money" data-status="1" data-value="`+o_watting_money+`">`+o_watting_money+`件</a>
                    </td> <!--total watting money -->
                    <td scope="col">
                        <a href="#" class="status-o-proccess" data-status="2" data-value="`+o_proccess+`">`+o_proccess+`件</a>
                    </td> <!--total watting money -->
                    <td scope="col">
                        <a href="#" class="status-o-proccess-purchase" data-status="3" data-value="`+o_proccess_purchase+`">`+o_proccess_purchase+`件</a>
                    </td> <!--total proccessing purchase -->
                    <td scope="col">
                        <a href="#" class="status-o-proccess-wrap" data-status="4" data-value="`+o_proccess_wrap+`">`+o_proccess_wrap+`件</a>
                    </td> <!--total processing wrap-->
                    <td scope="col">
                        <a href="#" class="status-o-proccess-ship" data-status="5" data-value="`+o_proccess_ship+`">`+o_proccess_ship+`件</a>
                    </td> <!--total processing shipping -->
                    <td scope="col">
                        <a href="#" class="status-o-ship-notified" data-status="6" data-value="`+o_ship_notified+`">`+o_ship_notified+`件</a>
                    </td> <!--total order notified shipping -->
                    <td scope="col">
                        <a href="#" class="status-o-del" data-status="8" data-value="`+o_del+`">`+o_del+`件</a>
                    </td> <!--total order delete -->
                    <td scope="col">
                        <a href="#" class="flag-o-confirm" data-flagconfirm="1" data-value="`+o_confirm+`">`+o_confirm+`件</a>
                    </td> <!--total order will confirm -->
                    <td scope="col">
                        <a href="#" class="flag-o-save" data-flagconfirm="2" data-value="`+o_save+`">`+o_save+`件</a>
                    </td> <!--total order will save -->
                `;
            });
            $("#table_all tbody").html(tbody);
        }
    }
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

    // function format date
    function formatDate_(date){
        if(date === null)
        {
            return ''
        }
        var d = new Date(date);
        var month = d.getMonth()+1;
        var day = d.getDate();
        var output = d.getFullYear() + '/' + 
        (month<10 ? '0' : '') + month + '/' +
        (day<10 ? '0' : '') + day;
        return output;
    }

    /**
     * function get all checkbox checked table results
     */
    function getDataCheckedTable(params) {
        let data = {};
        let list_orders = [];
        let list_products = [];
        let list_details = [];
        let list_purchases = [];
        let list_purchase_code = [];
        let list_shipment = [];
        // lấy danh sách Code order
        $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
            list_orders.push($(this).parent().parent().find('.id-order').data('id')) // get data in tag input hidden with id of table order
            list_details.push($(this).parent().parent().find('.detail-id').data('detail'))
        })
        // lấy danh sách ID product
        $('input.checkbox:checkbox:checked', '#table_reponses').each(function () {
            list_products.push(parseInt($(this).parent().find('.product-id').val())) // get data in tag input hidden with id of table order
            list_purchases.push(parseInt($(this).parent().find('.purchase-id').val()))
            list_purchase_code.push($(this).parent().find('.purchase-code').val())
            var shipment_item = {
                'id': $(this).parent().find('.shipment-id').val(),
                'delivery_method': $(this).parent().find('.shipment-deli').val(),
                'shipment_code': $(this).parent().find('.shipment-code').val(),
                'order_code': $(this).parent().find('.order-code').val(),
                'site_type': $(this).parent().find('.order-site-type').val()
            }
            list_shipment.push(shipment_item)
        })
        data = {
            'list_orders': list_orders, 
            'list_products': list_products,
            'list_details': list_details,
            'list_purchases': list_purchases,
            'list_purchase_codes': list_purchase_code,
            'list_shipments': list_shipment,
            'screen': 3
        }
        return data
    }
    $(document).on('click', '#btn_search_extend',  function() {
        $("#btn_search").click()
    })
    // date picker
    $(".datepicker").each(function() {    
        $(this).datepicker('setDate', new Date($(this).val()));
    });

    function refreshResult(table){
        let table_load = `
            <thead>
                <th scope="col" class="title-table" width="5%">番号</th>
                <th scope="col" class="title-table" width="11%">発注ID</th>
                <th scope="col" class="title-table" width="11%">受注ID</th>
                <th scope="col" class="title-table" width="10%">注文主名</th>
                <th scope="col" class="title-table" width="11%">受注ステータス</th>
                <th scope="col" class="title-table" width="11%">発注ステータス</th>
                <th scope="col" class="title-table" width="13%">品名</th>
                <th scope="col" class="title-table" width="8%">送り状番号</th>
                <th scope="col" class="title-table" width="10%">集荷先</th>
                <th scope="col" class="title-table" width="7%">集荷日時</th>
                <th scope="col" style="font-size: 16px; text-align: center" width="3%">
                    <input type="checkbox" name="check_all" id="check_all" value="checked">
                </th>
            </thead>
            <tbody>
                <tr>
                    <td class="center-txt" colspan="11">指定条件で該当する件数が多いため、検索条件を絞って下さい。</td>
                </tr>
            </tbody>`;
        $(table).html(table_load)
    }
    
    function getSearchParameters() {
        var prmstr = window.location.search.substr(1);
        return prmstr != null && prmstr != "" ? transformToAssocArray(prmstr) : {};
    }
    
    function transformToAssocArray( prmstr ) {
        var params = {};
        var prmarr = prmstr.split("&");
        for ( var i = 0; i < prmarr.length; i++) {
            var tmparr = prmarr[i].split("=");
            params[tmparr[0]] = tmparr[1];
        }
        return params;
    }
});