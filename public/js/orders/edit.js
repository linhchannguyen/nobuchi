$(document).ready(function () {
    var flag_check_exist_shipcode = 0;
    //Ẩn phân trang trước khi load modal
    $(document).on('click', '.sku', function(){
        $('.top').hide();
        refreshModalSku()
    });
    $(document).on('click', '.supplied', function(){
        $('.top').hide();
        refreshModalSupplier()
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
    var check_es_date = true
    // set total bill default
    $('.total-bill').text('/'+totalBill)
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    setOptionBillSelect() // set default select bill
    sumTotalPriceProduct() // tinh tong lai cac dong trong bang san pham
    checkProductBill() 
    // click add product
    $(document).on('click', '#add_product', function () {
        let lengthtable = $('#table_product > tbody > .product').length
        let tds = ''
        tds = 
        `
        <tr class="product product-add">
            <td>
                <div class="tooltip-error">
                    <span style="float:left;width: 100%;" class="input-table purchase-code"></span>
                    <input type="text" class="input-table number purchase-code-page" maxlength="4">
                </div>
            </td>
            <td>
                <select class="form-control select-bill-list">
                </select>
            </td>
            <td>
                <input value="" class="form-control input-table sku sku-edit sku-`+(lengthtable)+`" data-index="`+(lengthtable)+`" data-toggle="modal" data-target="#modal_product">
                <input value="" class="product-id" type="hidden">
                <input value="" class="maker_id" type="hidden">
                <input value="" class="maker_code" type="hidden">
            </td>
            <td><input class="form-control input-table name-product" value=""></td>
            <td><input class="form-control input-table product-status-id" value="" readonly></td>
            <td><input style="text-align: center;" class="form-control input-table quantity-product number-input" value="0"></td>
            <td class="">
                <input type="text" class="form-control input-table price1 price-sale-product number-input money-table" value="">
            </td>
            <td class="">
                <input type="text" class="form-control input-table price2 total-price-sale-product number-input money-table" readonly value="">
            </td>
            <td class="">
                <input type="hidden" class="form-control input-table price3 price-buy-product number-input money-table" readonly value="">
                <input type="hidden" class="form-control price-edit" readonly value="0">
                <input type="text" class="form-control input-table price3 number-input money-table cost-price" value="">
            </td>
            <td class="">
                <input type="text" class="form-control input-table edit-price-edit number-input money-table" value="0">
            </td>
            <td class="">
                <input type="hidden" class="total-price-buy-product" readonly value="">
                <input type="text" readonly class="form-control input-table price4 total-cost-price  number-input money-table" readonly value="">
            </td>
            <td class="" style="text-align: center;"><span><a class="btn-remove-product">削除</a></span></td>
        </tr>
        `
        $('#table_product tbody #add_row').before(tds);
        setOptionBillSelect()
        sumTotalPriceProduct() // tinh tong lai cac dong trong bang san pham
    })    
    // change bill id div
    $(document).on('focusout', '.bill-id', function () {
        setOptionBillSelect()
    })
    // change bill id div
    $(document).on('blur', '.bill-id', function (e) {
        if (e.which == 32){
            return false
        }    
        $(this).css('width','100%')
        $(this).parent().find('.btn-add-shipment').css('display', 'none')
        var ship_code_blur = $(this).val()
        var duplicate_ship_code = 0;
        if(ship_code_blur != ""){
            $("#bill_list> .bill-card> table").each((index, element) => {
                var ship_code = $(element).find('.bill-id').val()
                if(ship_code == ship_code_blur){
                    duplicate_ship_code++;
                }
            })
        }else {
            $(this).css('width','45%')
            $(this).parent().find('.btn-add-shipment').show()
        }
        if(duplicate_ship_code > 1){
            $(this).val("")
            $(this).focusout()
            ModalError('送り状番号は重複されています。他の送り状番号を入力してください。', '保存出来ていません。', 'OK')
        }
    })
    // remove-purchase click
    var arr_ship_edit_del = [];
    $(document).on('click', '.remove-bill', function () {
        let ship_code = $(".bill-"+$(this).data('idbill')).find('.bill-id').val()
        let ship_index = $(this).data('indexbill')
        if(typeof ship_index !== 'undefined'){
            arr_ship_edit_del.push(ship_index);
        }
        $(".bill-"+$(this).data('idbill')).remove();
        // setup lai stt.
        let stt = 0
        let totalStt = $("#bill_list> .bill-card> table").length
        // setup lai option trong select
        setOptionBillSelect()
        // xóa dòng có dùng id bill xóa
        $("#table_product>tbody>.shipment-code").each((index, element) => {
            if(ship_code === $(element).find('.code-value-ship').text())
            {
                $(element).remove()
            }
        })
        // đánh số thứ tự của bill gửi hàng
        $("#bill_list> .bill-card> table").each((index, element) => {
            stt++
            $(element).parent().find('.title-card-search').find('.stt-bill').text(stt)
            $(element).data('sttbill', stt)
            $(element).parent().find('.total-bill').text('/'+totalStt)
            // for set lai tren bang products
            $("#table_product>tbody>.shipment-code").each((index, element_child) => {
                if($(element).find('.bill-id').val() === $(element_child).find('.code-value-ship').text())
                {
                    let text_stt = '送料'+$(element).parent().find('.stt-bill').text().trim()
                    $(element_child).find('.text-stt').text(text_stt)
                }
            })
        })
    })
    //Remove product
    var arr_product_edit_del = [];
    $(document).on('click', '.btn-remove-product', function () {
        let detail_id = $(this).parent().parent().parent().data('detailid');
        let arr_ship = [];
        //Add detail_id để xóa sản phẩm trong order
        if(typeof detail_id !== 'undefined'){
            arr_product_edit_del.push(detail_id);
        }
        $(this).parent().parent().parent().remove();
        let index = -1;
        $("#table_product>tbody>.product-edit").each((index_, element_) => {
            index++;
            $(element_).find('.sku-edit').data('index', index);
            arr_ship.push($(element_).find('.select-bill-list').val())
        });
        $("#table_product>tbody>.product-add").each((index_, element_) => {
            index++;
            $(element_).find('.sku-edit').data('index', index);            
            arr_ship.push($(element_).find('.select-bill-list').val())
        });
        //Kiểm tra nếu xóa hết sản phẩm có chọn shipment_code thì remove dòng phí ship
        $('.shipment-code').each(function(index_, element){
            let flag_remove = 0;
            for(let i = 0; i < arr_ship.length; i++){
                if(arr_ship[i] === String($(element).data('shipmentcode')) && arr_ship[i] !== ''){
                    flag_remove++;
                }
            }
            if(flag_remove == 0){
                $(element).remove();                        
            }
        })
        updatePurchaseCode();
        sumTotalPriceProduct ()
    });
    // click add bill
    $(document).on('click', '#add_bill', function() {
        let now = getDateNow()
        let addBill = '';
        let idbill = 0;
        let dataIndex = 0
        let totalId = $("#bill_list > .bill-card").length + 1
        $("#bill_list > .bill-card").each((index, element) => {
            idbill = index + 1;
            dataIndex = index+1
            $(element).find('.stt-bill').text(idbill)
            $(element).find('.total-bill').text('/'+totalId)
        });
        idbill = idbill + 1
        addBill = `
            <div class="card-infor-buyer bill-add bill-card bill-`+idbill+`">
                <div class="title-card-search">
                    <a style="float:right; margin-top: 3px;" data-idbill="`+idbill+`" class="remove-bill a-href">お届け先削除</a>
                    <h5>お届け先情報 <span class="stt-bill">`+idbill+`</span><span class="total-bill">/`+idbill+`</span> &nbsp; &nbsp; 発注ID：<span class="purchase_code_in_ship"></span></h5>
                </div>
                <table class="table" data-sttbill="`+idbill+`">
                    <tbody>
                        <tr>
                            <td width="10%"  class="label-form"><b>送り状番号</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                            <td width="10%" >
                                <input class="form-control input-table bill-id" data-deli-method-old="-1" data-shipcode-old="-1" data-ship-id="0" value="" style="float: left; width: 45%">
                                <button style="float:right;width: 45%" class="btn-add-shipment">自動採番</button>
                            </td>
                            <td width="10%"  class="label-form"><b>配送方法</b></td>
                            <td width="10%" >
                            <select name="" id="" class="form-control delivery-method">
                                <option value="1">佐川急便</option>
                                <option value="9">佐川急便(秘伝II)</option>
                                <option value="2">ヤマト宅急便</option>
                                <option value="3">ネコポス</option>
                                <option value="4">コンパクト便</option>
                                <option value="5">ゆうパック</option>
                                <option value="6">ゆうパケット</option>
                                <option value="7">代引き</option>
                                <option value="8">その他</option>
                            </select>
                            </td>
                            <td width="10%"  class="label-form"><b>集荷日時</b></td>
                            <td width="10%" >
                                <div class="tooltip-error">
                                    <input class="form-control es-delivery-date" value="">
                                </div>
                            </td>
                            <td width="10%">
                                <div class="tooltip-error"> 
                                    <input class="form-control input-space input-table delivery-from-to-time" maxlength="5" placeholder="00-00" value="">
                                </div>
                            </td>
                            <td width="10%"  class="label-form"><b>集荷先</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                            <td width="10%" > 
                                <input class="form-control input-table supplied" data-toggle="modal" readonly data-index="`+dataIndex+`" data-target="#modal_supplied" value="">
                                <input type="hidden" class="supplied-id" value="">
                            </td>
                        </tr>
                    <tr>
                    <td width="10%" class="label-form"><b>納品方法</b></td>
                        <td width="10%">
                            <select name="" id="" class="form-control delivery-way">
                                <option value="1" selected>直送</option>
                                <option value="2">引取</option>
                                <option value="3">配達</option>
                                <option value="4">仕入</option>
                            </select>
                        </td>
                        <td width="10%"  class="label-form"><b>発注ステータス</b></td>
                        <td width="10%">                 
                        <select id="purchase_status" class="form-control">`;
                        for(let i = 0; i < purchase_status.length; i++){
                            addBill += `
                            <option value="`+(i+1)+`">`+purchase_status[i]+`</option>`;
                        }
                        addBill += `
                        </select></td>
                        <td width="10%"  class="label-form"><b>配達日時</b></td>
                        <td width="10%" >
                            <div class="tooltip-error">
                                <input class="form-control delivery-date receive-date" value="">
                            </div>
                        </td>
                        <td width="10%">
                            <select class="form-control receive-time">
                                <option value="0">----</option>
                                <option value="午前中">午前中</option>
                                <option value="12時～14時">12時～14時 </option>
                                <option value="14時～16時">14時～16時</option>
                                <option value="16時～18時">16時～18時</option>
                                <option value="18時～20時">18時～20時</option>
                                <option value="19時以降">19時以降</option>
                            </select>
                        </td>
                        <td width="10%"  class="label-form"><b>のし</b></td>
                        <td width="10%" ><input class="form-control input-table gift-wrap" value=""></td>
                    </tr>
                    <tr>
                        <td width="10%" class="label-form"><b>お届け先〒</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td width="13%">
                            <div class="tooltip-error">
                                <input class="input-table form-control  form-control ship-zip zip-input" maxlength="8" style="float: left; width: 45%" value="">
                                <button style="float:right;width: 45%" class="btn-search-zipcode-ship">住所入力</button>
                            </div>
                        </td>
                        <td width="10%" class="label-form"><b>お届け先TEL</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td width="10%"><input class="form-control input-table ship-phone tel-input" value=""></td>
                        <td width="10%" class="label-form"><b>送料</b></td>
                        <td width="10%" colspan="2"><span style="float:left">¥</span><input class="delivery-fee number-input" style="width: 96%; text-align: right; border:none" value="0"></td>
                        <td width="10%" class="label-form"><b>ラッピング</b></td>
                        <td width="10%"><input class="form-control input-table wrapping-paper-type" value=""></td>
                    </tr>
                    <tr>
                        <td width="10%" class="label-form"><b>お届け先名</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td colspan="3"><input class="form-control input-table ship-name1" value=""></td>
                        <td width="10%" class="label-form"><b>代引き請求</b></td>
                        <td colspan="2"><span style="float:left">
                        <span><input type="radio" name="pay_request" class="pay-request" value="1"> &nbsp; この荷物で請求する   &nbsp; &nbsp; &nbsp; </span>
                        </td>
                        <td width="10%" class="label-form"><b>メッセージカード</b></td>
                        <td><input class="form-control input-table message" value=""></td>
                    </tr>
                    <tr>
                        <td width="10%" class="label-form"><b>お届け先住所</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td colspan="2"><input class="form-control input-table ship-address1 address-1" value="" placeholder="●●県●●市●●区●●町" ></td>
                        <td colspan="3"><input class="form-control input-table ship-address2" value="" placeholder="0丁目00-0000" ></td>
                        <td colspan="3"><input class="form-control input-table ship-address3" value="" placeholder="●●●号室-0000"></td>
                    </tr>
                </tbody></table>
            </div>
        `;
        $("#bill_list").append(addBill);
        setOptionBillSelect()
    })
   /**
    * function set options bill select
    * @author Dat
    * */ 
   function setOptionBillSelect()
   {
        $("#table_product>tbody>tr>td").find('.select-bill-list').each((indexpr, elementpr) => {
            let options = `<option></option>`;
            $('#bill_list > .bill-card').each(function (index, element) {
                var id_bill = '';
                var id_bill = $(element).find('table').find('.bill-id').val();
                var delivery_method = $(element).find('table').find('.delivery-method').val();
                var shipcode_old = $(element).find('table').find('.bill-id').data('shipcode-old');
                var deli_method_old = $(element).find('table').find('.bill-id').data('deli-method-old');
                var ship_id = $(element).find('table').find('.bill-id').data('ship-id');
                if(id_bill !== '')
                {
                    if(id_bill === String($(elementpr).data('shipcode')))
                    {
                        options += `<option data-shipment-id=`+ship_id+` data-deli-method-old="`+deli_method_old+`" data-shipment-code-old="`+shipcode_old+`" data-deli-method="`+delivery_method+`" value="`+id_bill+`" selected>`+id_bill+`</option>`
                    } else
                    {
                        options += `<option data-shipment-id=`+ship_id+` data-deli-method-old="`+deli_method_old+`" data-shipment-code-old="`+shipcode_old+`" data-deli-method="`+delivery_method+`" value="`+id_bill+`">`+id_bill+`</option>`
                    }
                }
            })
            $(elementpr).html(options)
        })
   }
   /**
    * function set data order
    * @author Dat
    * 2019-10-19
    */
   function setDataOrder ()
   {
    let order_code = $('#order_code').val()
    let supp_status = $('#supp_status').val()
    let purchase_date = $('#purchase_date').val()
    let delivery_date = $('#delivery_date').val()
    let buyer_zip = $('#buyer_zip').val().replace("-", "")
    let buyer_tel = $('#buyer_tel').val()
    let buyer_fax = $('#buyer_fax').val()
    let buyer_email = $('#buyer_email').val()
    let buyer_name1 = $('#buyer_name1').val()
    let buyer_name2 = $('#buyer_name2').val()
    let buyer_address1 = $('#buyer_address1').val()
    let buyer_address2 = $('#buyer_address2').val()
    let buyer_address3 = $('#buyer_address3').val()
    let comment = $('#comment').val()
    let money_daibiki = 0
    let detail_data = []
    let data_order = {}
    let add_detail = []
    let shipments_id = []
    let data = {}
    let updated_at = []
    let invalidate = true
    if(purchase_date != '' && _validatedate(purchase_date) == false){
        let tooltip_error = '<span style="width: 320px;" class="tooltip-text">無効な日付です。再入力してください。</span>'
        $('#purchase_date').parent().append(tooltip_error);
        $('#purchase_date').parent().find('.tooltip-text').css("top", "-25px");
        $('#purchase_date').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
        invalidate = false
        return false
    }
    if(delivery_date != '' && _validatedate(delivery_date) == false){
        let tooltip_error = '<span style="width: 340px;" class="tooltip-text">無効な日付です。再入力してください。</span>'
        $('#delivery_date').parent().append(tooltip_error);
        $('#delivery_date').parent().find('.tooltip-text').css("top", "-25px");
        $('#delivery_date').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
        invalidate = false
        return false
    }
    data_order = {
        'order_code': order_code,
        'status': supp_status,
        'purchase_date': purchase_date,
        'flag_confirm': 0,//flag_confirm Không dùng đến nên set = 0
        'delivery_date': delivery_date,
        'buyer_zip1': buyer_zip.substring(0, 3),
        'buyer_zip2': buyer_zip.substring(3, 8),
        'buyer_tel1': buyer_tel,
        'buyer_tel2': '',
        'buyer_tel3': '',
        'fax': buyer_fax,
        'buyer_email': buyer_email,
        'buyer_name1': buyer_name1,
        'buyer_name2': buyer_name2,
        'buyer_address_1': buyer_address1,
        'buyer_address_2': buyer_address2,
        'buyer_address_3': buyer_address3,
        'comments': comment,
        'money_daibiki': parseFloat($('.money-daibiki').val().replace(/\,/g, ''))
    }
    let check_buyer_phone_edit = true;
    let check_buyer_fax = true;
    let tooltip_error_length = '<span class="tooltip-text">注文主TELは必須です。入力してください。。</span>'
    let tooltip_error_fax = '<span class="tooltip-text">無効なFAX番号です。再度入力してください。</span>'
    if (data_order.buyer_tel1.length > 25 || data_order.buyer_tel1.length < 1 || !isValidPhone(data_order.buyer_tel1) || data_order.buyer_tel1.split('-').includes('')) {
        $('#buyer_tel').parent().append(tooltip_error_length);
        $('#buyer_tel').parent().find('.tooltip-text').css("top", "-50px");
        $('#buyer_tel').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
        check_buyer_phone_edit = false
    }

    if(data_order.fax.length != 0){
        if (data_order.fax && (data_order.fax.length > 25 || data_order.fax.length < 1 || !isValidPhone(data_order.fax) || data_order.fax.split('-').includes(''))) {
            $('#buyer_fax').parent().append(tooltip_error_fax);
            $('#buyer_fax').parent().find('.tooltip-text').css("top", "-50px");
            $('#buyer_fax').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
            check_buyer_fax = false
        }  
    }

    money_daibiki = parseFloat($('.money-daibiki').val().replace(/\,/g, ''))
    if(money_daibiki > 0) // nếu có tiền daibiki thì sẽ lưu trạng thái hỗ trợ khách hàng là tiền daibiki
    {
        data_order.money_daibiki = money_daibiki
        data_order.support_cus = 1
    }
    // xet phi dich vu
    $('#table_product>tbody>.fee-service').each((index, element) =>{
        data_order.quantity_service = parseFloat($(element).find('.quantity-service').val().replace(/\,/g, '')) 
        data_order.price_service = parseFloat($(element).find('.price-service').val().replace(/\,/g, '')) 
        data_order.total_service = parseFloat($(element).find('.total-service').val().replace(/\,/g, '')) 
    })

    let check_dil_way_edit = true;
    let check_shipadd1_edit = true;
    let check_shipname_edit = true;
    let check_supplied_edit = true;
    let check_zip_edit = true;
    let check_tel_edit = true;
    let check_es_date_time_edit = true;
    // let check_date_time_edit = true;
    $("#table_product>tbody>.product-edit").each((index, element) => {
        let product = {}
        let purchase_code_page = ''
        let purchase_code = ''
        if($(element).find('.purchase-code-page').val() !== '' && $(element).find('.purchase-code-page').val() !== $(element).find('.purchase-code').text().trim())
        {
            purchase_code_page = $(element).find('.purchase-code-page').val()
            purchase_code = $(element).find('.purchase-code').text().trim() + purchase_code_page
        }else if($(element).find('.purchase-code-page').val() === $(element).find('.purchase-code').text().trim())
        {
            purchase_code = $(element).find('.purchase-code-page').val()
        }
        if($(element).find('.select-bill-list').val() !== '' && purchase_code === '')
        {
            ModalError('商品リストで発注番号を入力してください。', '保存出来ていません。', 'OK')
            invalidate = false
            return false
        } else if($(element).find('.select-bill-list').val() === '') 
        {

            ModalError('商品リストで送り状番号を入力してください。', '保存出来ていません。', 'OK')
            invalidate = false
            return false
        }
        if($(element).find('.sku').text().trim() === '')
        {
            ModalError('商品リストでSKU、品名、数量を入力してください。', '保存出来ていません。', 'OK')
            invalidate = false
            return false
        }
        if($(element).find('.quantity-product').val() === '' || $(element).find('.quantity-product').val() === "0")
        {
            ModalError('商品リストでSKU、品名、数量を入力してください。', '保存出来ていません。', 'OK')
            invalidate = false
            return false
        }
        product = {
            'order_id': $('#order_id').val(),
            'id': $(element).data('detailid'), 
            'sku': $(element).find('.sku').text().trim(),
            'product_code': $(element).find('.sku').text().trim(),
            'product_id': $(element).find('.product-id').val(),
            'maker_id': $(element).find('.maker_id').val(),
            'maker_code': $(element).find('.maker_code').val(),
            'product_name': $(element).find('.name-product').val(),
            'quantity': $(element).find('.quantity-product').val(),
            'cost_price': $(element).find('.cost-price').val().replace(/\,/g, ''),
            'total_price': $(element).find('.total-cost-price').val().replace(/\,/g, ''),
            'cost_price_tax': parseFloat($(element).find('.price-buy-product').val().replace(/\,/g, '')),
            'total_price_tax': parseFloat($(element).find('.total-price-buy-product').val().replace(/\,/g, '')),
            'price_sale_tax': parseFloat($(element).find('.price-sale-product').val().replace(/\,/g, '')),
            'total_price_sale_tax': parseFloat($(element).find('.total-price-sale-product').val().replace(/\,/g, '')),
            'purchase_id':purchase_code,
            'purchase_date': purchase_date,
            'shipment_id': $(element).find('.select-bill-list').val(),
            'price_edit': parseFloat($(element).find('.edit-price-edit').val().replace(/\,/g, ''))
        }
        shipments_id.push($(element).find('.select-bill-list option:selected').data('shipment-id'))
        // set updated at
        updated_at.push({
            'detail_id': $(element).data('detailid'),
            'updated_at': $(element).find('.updated-at').val()
        })
        $("#table_product > tbody >.shipment-code").each((indexship, elementship) => {
            if(product.shipment_id === $(elementship).find('.code-value-ship').text().trim())
            {
                product.delivery_fee = parseFloat($(elementship).find('.delivery-fee').val().replace(/\,/g, ''))
            }
        })
        $("#bill_list>.bill-card").each((indexship, elementbill) =>  {
            if(product.shipment_id === $(elementbill).find('table').find('.bill-id').val() && product.shipment_id !== '')
            {
                let es_delivery_date_from = ''
                es_delivery_date_from =  $(elementbill).find('table').find('.es-delivery-date').val()
                let pay_request = $(elementbill).find('table').find('input[name="pay_request"]:checked').val()
                product.pay_request = ''
                if(pay_request !== undefined)
                {
                    product.pay_request = parseInt(pay_request)
                }
                let delivery_method = parseInt($(elementbill).find('table').find('.delivery-method').val())
                switch (delivery_method) {
                    case 1:
                    case 7:
                        product.invoice_id = 1
                        break;
                    case 2:
                    case 3:
                    case 4:
                        product.invoice_id = 2
                        break;
                    case 5:
                    case 6: 
                        product.invoice_id = 3
                        break
                    default:
                        product.invoice_id = 4
                        break;
                }
                // validate data                
                if($(elementbill).find('table').find('.delivery-way').val() == 0){
                    check_dil_way_edit = false;
                }
                if($(elementbill).find('table').find('.ship-address1').val() == ''){
                    check_shipadd1_edit = false;
                }
                if($(elementbill).find('table').find('.ship-address2').val() == ''){
                    check_shipadd2_edit = false;
                }
                if($(elementbill).find('table').find('.ship-name1').val() == ''){
                    check_shipname_edit = false;
                }
                var es_date_time_edit = $(elementbill).find('table').find('.delivery-from-to-time').val();
                if(es_date_time_edit != ''){
                    var arr_time = es_date_time_edit.split('-')
                    if(arr_time.length != 2){
                        let tooltip_error = '<span class="tooltip-text">無効な時間です。再度入力してください。</span>'
                        $(elementbill).find('table').find('.delivery-from-to-time').parent().append(tooltip_error);
                        $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').css("top", "-50px");
                        $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
                        check_es_date_time_edit = false;
                    }else {
                        if(isNaN(arr_time[0]) == true || isNaN(arr_time[1]) == true){
                            let tooltip_error = '<span class="tooltip-text">無効な時間です。再度入力してください。</span>'
                            $(elementbill).find('table').find('.delivery-from-to-time').parent().append(tooltip_error);
                            $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').css("top", "-50px");
                            $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
                            check_es_date_time_edit = false;
                        }
                    }
                }
                product.delivery_method = $(elementbill).find('table').find('.delivery-method').val()
                product.es_delivery_date_from = es_delivery_date_from
                product.es_delivery_time_from = $(elementbill).find('table').find('.delivery-from-to-time').val()
                product.receive_date = $(elementbill).find('table').find('.receive-date').val()
                product.receive_time = $(elementbill).find('table').find('.receive-time').val()
                product.supplied = $(elementbill).find('table').find('.supplied').val()
                product.supplied_id = $(elementbill).find('table').find('.supplied-id').val()
                product.delivery_way = $(elementbill).find('table').find('.delivery-way').val()
                product.delivery_date = $(elementbill).find('table').find('.receive-date').val()
                product.gift_wrap = $(elementbill).find('table').find('.gift-wrap').val()
                product.ship_zip = $(elementbill).find('table').find('.ship-zip').val()
                product.ship_phone = $(elementbill).find('table').find('.ship-phone').val()
                if (product.ship_phone.length > 25 || product.ship_phone.length < 1 || !isValidPhone(product.ship_phone) || product.ship_phone.split('-').includes('')) {
                    $(elementbill).find('table').find('.ship-phone').parent().append(tooltip_error_length);
                    $(elementbill).find('table').find('.ship-phone').parent().find('.tooltip-text').css("top", "-50px");
                    $(elementbill).find('table').find('.ship-phone').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
                }
                product.wrapping_paper_type = $(elementbill).find('table').find('.wrapping-paper-type').val()
                product.ship_name1 = $(elementbill).find('table').find('.ship-name1').val()
                product.message = $(elementbill).find('table').find('.message').val()
                product.ship_address1 = $(elementbill).find('table').find('.ship-address1').val()
                product.ship_address2 = $(elementbill).find('table').find('.ship-address2').val()
                product.ship_address3 = $(elementbill).find('table').find('.ship-address3').val()
                product.purchase_status = $(elementbill).find('table').find('#purchase_status').val()
                if(typeof $(elementbill).find('.remove-bill').data('indexbill') != "undefined"){
                    product.shipment_index = $(elementbill).find('.remove-bill').data('indexbill')
                }else {
                    product.shipment_index = -1;
                }

            } else if(product.shipment_id === '' && index === indexship)
            {
                let es_delivery_date_from = ''
                es_delivery_date_from =  $(elementbill).find('table').find('.es-delivery-date').val()
                let pay_request = $(elementbill).find('table').find('input[name="pay_request"]:checked').val()
                product.pay_request = ''
                if(pay_request !== undefined)
                {
                    product.pay_request = parseInt(pay_request)
                }
                let delivery_method = parseInt($(elementbill).find('table').find('.delivery-method').val())
                switch (delivery_method) {
                    case 1:
                    case 7:
                        product.invoice_id = 1
                        break;
                    case 2:
                    case 3:
                    case 4:
                        product.invoice_id = 2
                        break;
                    case 5:
                    case 6: 
                        product.invoice_id = 3
                        break
                    default:
                        product.invoice_id = 4
                        break;
                }
                var es_date_time_edit = $(elementbill).find('table').find('.delivery-from-to-time').val();
                if(es_date_time_edit != ''){
                    var arr_time = es_date_time_edit.split('-')
                    if(arr_time.length != 2){
                        let tooltip_error = '<span class="tooltip-text">無効な時間です。再度入力してください。</span>'
                        $(elementbill).find('table').find('.delivery-from-to-time').parent().append(tooltip_error);
                        $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').css("top", "-20px");
                        $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
                        check_es_date_time_edit = false;
                    }else {
                        if(isNaN(arr_time[0]) == true || isNaN(arr_time[1]) == true){
                            let tooltip_error = '<span class="tooltip-text">無効な時間です。再度入力してください。</span>'
                            $(elementbill).find('table').find('.delivery-from-to-time').parent().append(tooltip_error);
                            $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').css("top", "-20px");
                            $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
                            check_es_date_time_edit = false;
                        }
                    }
                }
                product.delivery_method = $(elementbill).find('table').find('.delivery-method').val()
                product.es_delivery_date_from = es_delivery_date_from
                product.es_delivery_time_from = $(elementbill).find('table').find('.delivery-from-to-time').val()
                product.receive_date = $(elementbill).find('table').find('.receive-date').val()
                product.receive_time = $(elementbill).find('table').find('.receive-time').val()
                product.supplied = $(elementbill).find('table').find('.supplied').val()
                product.supplied_id = $(elementbill).find('table').find('.supplied-id').val()
                product.delivery_way = $(elementbill).find('table').find('.delivery-way').val()
                product.delivery_date = $(elementbill).find('table').find('.receive-date').val() // lấy ngày giao hàng bằng ngày nhận hàng
                product.gift_wrap = $(elementbill).find('table').find('.gift-wrap').val()
                product.ship_zip = $(elementbill).find('table').find('.ship-zip').val()
                product.ship_phone = $(elementbill).find('table').find('.ship-phone').val()
                product.wrapping_paper_type = $(elementbill).find('table').find('.wrapping-paper-type').val()
                product.ship_name1 = $(elementbill).find('table').find('.ship-name1').val()
                product.message = $(elementbill).find('table').find('.message').val()
                product.ship_address1 = $(elementbill).find('table').find('.ship-address1').val()
                product.ship_address2 = $(elementbill).find('table').find('.ship-address2').val()
                product.ship_address3 = $(elementbill).find('table').find('.ship-address3').val()
            }
        })

        detail_data.push(product)
    })
    // add new product or bill

    $("#table_product>tbody>.product-add").each((index, element) => {
        let check_bill = false
        let product = {}
        let purchase_code_page = ''
        let purchase_code = ''
        if($(element).find('.purchase-code-page').val() !== undefined && $(element).find('.purchase-code-page').val() !== '')
        {
            purchase_code_page = $(element).find('.purchase-code-page').val()
            purchase_code = $(element).find('.purchase-code').text().trim() + purchase_code_page
        }
        if($(element).find('.select-bill-list').val() !== '' && purchase_code === '')
        {
            ModalError('商品リストで発注番号を入力してください。', '保存出来ていません。', 'OK')
            invalidate = false
            return false
        } else if($(element).find('.select-bill-list').val() === '') 
        {

            ModalError('商品リストで送り状番号を入力してください。', '保存出来ていません。', 'OK')
            invalidate = false
            return false
        }
        if($(element).find('.sku').val() === '')
        {
            ModalError('商品リストでSKU、品名、数量を入力してください。', '保存出来ていません。', 'OK')
            invalidate = false
            return false
        }
        if($(element).find('.quantity-product').val() === '' || $(element).find('.quantity-product').val() === "0")
        {
            ModalError('商品リストでSKU、品名、数量を入力してください。', '保存出来ていません。', 'OK')
            invalidate = false
            return false
        }
        product = {
            'order_id': $('#order_id').val(),
            'order_code': $('#order_code').val(),
            'sku': $(element).find('.sku').val(),
            'product_code': $(element).find('.sku').val(),
            'product_id': $(element).find('.product-id').val(),
            'maker_id': $(element).find('.maker_id').val(),
            'maker_code': $(element).find('.maker_code').val(),
            'product_name': $(element).find('.name-product').val(),
            'quantity': $(element).find('.quantity-product').val(),
            'cost_price': $(element).find('.cost-price').val().replace(/\,/g, ''),
            'total_price': $(element).find('.total-cost-price').val().replace(/\,/g, ''),
            'cost_price_tax': $(element).find('.price-buy-product').val().replace(/\,/g, ''),
            'total_price_tax': $(element).find('.total-price-buy-product').val().replace(/\,/g, ''),
            'price_sale_tax': $(element).find('.price-sale-product').val().replace(/\,/g, ''),
            'total_price_sale_tax': $(element).find('.total-price-sale-product').val().replace(/\,/g, ''),
            'purchase_id': purchase_code,
            'purchase_date': purchase_date,
            'shipment_id': $(element).find('.select-bill-list').val(),
            'price_edit': parseFloat($(element).find('.edit-price-edit').val().replace(/\,/g, ''))
        }
        shipments_id.push($(element).find('.select-bill-list option:selected').data('shipment-id'))
        $("#table_product > tbody >.shipment-code").each((indexship, elementship) => {
            product.delivery_fee = 0
            if(product.shipment_id === $(elementship).find('.code-value-ship').text().trim())
            {
                product.delivery_fee = $(elementship).find('.delivery-fee').val().replace(/\,/g, '')
            }   
        })
        $("#bill_list>.bill-card").each((indexship, elementbill) =>  {
            if(product.shipment_id === $(elementbill).find('table').find('.bill-id').val() && product.shipment_id !== '')
            {
                let es_delivery_date_from = ''
                let pay_request = $(elementbill).find('table').find('input[name="pay_request"]:checked').val()
                product.pay_request = ''
                if(pay_request !== undefined)
                {
                    product.pay_request = parseInt(pay_request)
                }
                let delivery_method = parseInt($(elementbill).find('table').find('.delivery-method').val())
                switch (delivery_method) {
                    case 1:
                    case 7:
                        product.invoice_id = 1
                        break;
                    case 2:
                    case 3:
                    case 4:
                        product.invoice_id = 2
                        break;
                    case 5:
                    case 6: 
                        product.invoice_id = 3
                        break
                    default:
                        product.invoice_id = 4
                        break;
                }
                es_delivery_date_from =  $(elementbill).find('table').find('.es-delivery-date').val()         
                if($(elementbill).find('table').find('.delivery-way').val() == 0){
                    check_dil_way_edit = false;
                }
                if($(elementbill).find('table').find('.ship-address1').val() == ''){
                    check_shipadd1_edit = false;
                }
                if($(elementbill).find('table').find('.ship-name1').val() == ''){
                    check_shipname_edit = false;
                }
                if($(elementbill).find('table').find('.supplied').val() == ''){
                    check_supplied_edit = false;
                }
                if($(elementbill).find('table').find('.ship-zip').val() == ''){
                    check_zip_edit = false;
                }
                if($(elementbill).find('table').find('.ship-phone').val() == ''){
                    check_tel_edit = false;
                }
                var es_date_time_edit = $(elementbill).find('table').find('.delivery-from-to-time').val();
                if(es_date_time_edit != ''){
                    var arr_time = es_date_time_edit.split('-')
                    if(arr_time.length != 2){
                        let tooltip_error = '<span class="tooltip-text">無効な時間です。再度入力してください。</span>'
                        $(elementbill).find('table').find('.delivery-from-to-time').parent().append(tooltip_error);
                        $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').css("top", "-20px");
                        $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
                        check_es_date_time_edit = false;
                    }else {
                        if(isNaN(arr_time[0]) == true || isNaN(arr_time[1]) == true){
                            let tooltip_error = '<span class="tooltip-text">無効な時間です。再度入力してください。</span>'
                            $(elementbill).find('table').find('.delivery-from-to-time').parent().append(tooltip_error);
                            $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').css("top", "-20px");
                            $(elementbill).find('table').find('.delivery-from-to-time').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
                            check_es_date_time_edit = false;
                        }
                    }
                }
                product.delivery_method = $(elementbill).find('table').find('.delivery-method').val()
                product.es_delivery_date_from = es_delivery_date_from
                product.es_delivery_time_from = $(elementbill).find('table').find('.delivery-from-to-time').val()
                product.receive_date = $(elementbill).find('table').find('.receive-date').val()
                product.receive_time = $(elementbill).find('table').find('.receive-time').val()
                product.delivery_fee = parseFloat($(elementbill).find('.delivery-fee').val().replace(/\,/g, ''))
                product.supplied = $(elementbill).find('table').find('.supplied').val()
                product.supplied_id = $(elementbill).find('table').find('.supplied-id').val()
                product.delivery_way = $(elementbill).find('table').find('.delivery-way').val()
                product.delivery_date = $(elementbill).find('table').find('.receive-date').val()
                product.gift_wrap = $(elementbill).find('table').find('.gift-wrap').val()
                product.ship_zip = $(elementbill).find('table').find('.ship-zip').val()
                product.ship_phone = $(elementbill).find('table').find('.ship-phone').val()
                product.wrapping_paper_type = $(elementbill).find('table').find('.wrapping-paper-type').val()
                product.ship_name1 = $(elementbill).find('table').find('.ship-name1').val()
                product.message = $(elementbill).find('table').find('.message').val()
                product.ship_address1 = $(elementbill).find('table').find('.ship-address1').val()
                product.ship_address2 = $(elementbill).find('table').find('.ship-address2').val()
                product.ship_address3 = $(elementbill).find('table').find('.ship-address3').val()
                product.purchase_status = $(elementbill).find('table').find('#purchase_status').val()
                check_bill = true
            }
        })
        if(check_bill === false){
            ModalError('商品リストで送り状番号を選択してください。', '保存出来ていません。', 'OK');
            invalidate = false
            return false
        }
        add_detail.push(product)
    })    
    if(check_dil_way_edit == false){
        ModalError('納品方法を選択してください。', '保存出来ていません。', 'OK');
        return false;
    }
    if(check_shipadd1_edit == false){
        ModalError('お届け先住所（部分１）を入力してください。', '保存出来ていません。', 'OK');
        return false;
    }
    if(check_shipname_edit == false){
        ModalError('お届け先名を入力してください。', '保存出来ていません。', 'OK');
        return false;
    }
    if(check_buyer_phone_edit == false){
        ModalError('注文主TELは必須です。入力してください。', '保存出来ていません。', 'Ok');
        return false;
    }
    if(check_supplied_edit == false){
        ModalError('集荷先は指定されていません。選択してください。', '保存出来ていません。', 'OK');
        return false;
    }
    if(check_zip_edit == false){
        ModalError('無効な郵便番号です。再度入力してください。', '保存出来ていません。', 'OK');
        return false;
    }
    if(check_tel_edit == false){
        ModalError('無効なお届け先TELです。再度入力してください。', '保存出来ていません。', 'OK');
        return false;
    }
    if(check_buyer_fax == false){
        ModalError('無効なFAX番号です。再度入力してください。', '保存出来ていません。', 'OK');
        return false;
    }
    if(check_es_date_time_edit == false){
        ModalError('集荷日時で時間を再確認してください。', '保存出来ていません。', 'OK');
        return false;
    }
    data = {
        'order': data_order,
        'detail': detail_data,
        'add_detail': add_detail,
        'updated_at': updated_at,
        'list_order_del': arr_product_edit_del,
        'list_shipment_del': arr_ship_edit_del,//
        'shipments_id': shipments_id//Mảng shipment_id dùng để kiểm tra nếu không còn shipment_id cũ thì xóa luôn record của shipments
    }
    if(invalidate === false)
    {
        return false
    } else
    {
        return data
    }
   }
   /**
    * update order btn click
    * @author Dat
    */
   $(document).on('click', '#update_order', function () {
        // kiểm tra thông tin và số sản phẩm order
        let lengthProductEdit = $('#table_product>tbody>.product-edit').length
        let lengthProductAdd = $('#table_product>tbody>.product-add').length
        if(lengthProductAdd <= 0 && lengthProductEdit <= 0)
        {
            ModalError('注文する商品を入力してください。', '保存出来ていません。', 'OK');
            return validate
        }//  kiểm tra số lượng thêm sản phẩm
        var count_ship_code_empty = 0;
        var count_deli_method_empty = 0;
        $("#bill_list> .bill-card> table").each((index, element) => {
            var ship_code = $(element).find('.bill-id').val()
            if(ship_code == ""){
                count_ship_code_empty++;
            }    
            var delivery_method = $(element).find('.delivery-method').val()
            if(delivery_method === '')
            {
                count_deli_method_empty++;
            }
        })
        //Kiểm tra shipcode trống thì báo lỗi
        if(count_deli_method_empty > 0){
            ModalError('配送方法を選択してから自動採番ボタンを押してください。', '保存出来ていません。', 'OK');
            return false
        }
        if(count_ship_code_empty > 0){
            ModalError("お届け先情報で送り状番号を選択してください。", '保存出来ていません。', 'OK')
            return false;
        }
        if(check_es_date === false)
        {
            $('.tooltip-text').trigger('mouseenter');
            return false
        }
        // return
        $('.modal-confirm').remove(); // xóa class modal nếu có
        let modal = ''
        modal = `
        <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle" style="color:red">確認</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="text-align:center">
                    <b style="font-size=16px;">変更内容を保存します。よろしいでしょうか？</b>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" id="submit_modal_update" data-dismiss="modal">OK</button>
                </div>
                </div>
            </div>
        </div>`;
        var arr_shipcode = [];
        $("#table_product>tbody>.product-edit").each((index, element) => {
            var deli_method = $(element).find('.select-bill-list option:selected').data('deli-method')
            if(deli_method == 1 || deli_method == 7){
                var ship_id = $(element).find('.select-bill-list option:selected').data('shipment-id')
                var shipcode_new = $(element).find('.select-bill-list').val()+"";
                if(ship_id == 0){
                    if(arr_shipcode.includes(shipcode_new) == false){
                        arr_shipcode.push(shipcode_new)
                    }
                }else {
                    var deli_method_old = $(element).find('.select-bill-list option:selected').data('deli-method-old')
                    if(deli_method_old != -1){
                        if(deli_method_old != deli_method){
                            if(arr_shipcode.includes(shipcode_new) == false){
                                arr_shipcode.push(shipcode_new)
                            }
                        }else {
                            var shipcode_old = $(element).find('.select-bill-list option:selected').data('shipment-code-old')+""
                            if(shipcode_new != shipcode_old){
                                if(arr_shipcode.includes(shipcode_new) == false){
                                    arr_shipcode.push(shipcode_new)
                                }
                            }
                        }
                    }else {
                        if(arr_shipcode.includes(shipcode_new) == false){
                            arr_shipcode.push(shipcode_new)
                        }
                    }
                }
            }
        })
        $("#table_product>tbody>.product-add").each((index, element) => {
            var deli_method = $(element).find('.select-bill-list option:selected').data('deli-method')
            if(deli_method == 1 || deli_method == 7){
                var ship_id = $(element).find('.select-bill-list option:selected').data('shipment-id')
                var shipcode_new = $(element).find('.select-bill-list').val()+"";
                if(ship_id == 0){
                    if(arr_shipcode.includes(shipcode_new) == false){
                        arr_shipcode.push(shipcode_new)
                    }
                }else {
                    var deli_method_old = $(element).find('.select-bill-list option:selected').data('deli-method-old')
                    if(deli_method_old != -1){
                        if(deli_method_old != deli_method){
                            if(arr_shipcode.includes(shipcode_new) == false){
                                arr_shipcode.push(shipcode_new)
                            }
                        }else {
                            var shipcode_old = $(element).find('.select-bill-list option:selected').data('shipment-code-old')+""
                            if(shipcode_new != shipcode_old){
                                if(arr_shipcode.includes(shipcode_new) == false){
                                    arr_shipcode.push(shipcode_new)
                                }
                            }
                        }
                    }else {
                        if(arr_shipcode.includes(shipcode_new) == false){
                            arr_shipcode.push(shipcode_new)
                        }
                    }
                }
            }
        })
        if(arr_shipcode.length > 0){
            $.ajax({
                method: 'post',
                url: url_check_shipcode,
                data: {
                    'arr_shipcode': arr_shipcode
                },
                success: function(res){
                    if(res != ''){
                        modal = `
                        <div class="modal fade modal-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content" style="width: 560px !important">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle" style="color:red">確認</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div style="text-align:center">
                                            <b style="font-size=16px;">下記の送り状番号が既に存在します。下記の該当なオプションボタンを選択してください。</b>
                                        </div>
                                        <div style="padding-left: 10px">
                                            <input type="hidden" class="ship_exist" value="`+res+`"/>
                                            <b style="font-size=16px;">送り状番号: `+res+`</b>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" id="submit_modal_auto_update" data-dismiss="modal">送り状番号自動更新後、保存</button>
                                        <button type="button" class="btn btn-primary" id="submit_modal_update" data-dismiss="modal">現送り状番号で保存</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                        $('body').append(modal);
                        $('.modal-confirm').modal()
                    }else {
                        $('body').append(modal);
                        $('.modal-confirm').modal()
                    }
                }
            })
        }else {
            $('body').append(modal);
            $('.modal-confirm').modal()
        }
   })
   /**
    * click oke modal
    */
   $(document).on('click','#submit_modal_auto_update', function () {
        let dataUpdate =  setDataOrder();
        let check = false;

        if(dataUpdate) {
            dataUpdate.detail.forEach(function(item, index) {
                if (item.ship_phone.length > 25 || item.ship_phone.length < 1) {
                    ModalError('お届け先TELは1～25半角数字で入力してください。', '保存出来ていません。', 'OK');     
                    check = true;
                    return false;
                }
                if (!isValidPhone(item.ship_phone) || item.ship_phone.split('-').includes('')) {
                    ModalError('無効な配送電話番号 ' + item.ship_phone, '保存出来ていません。', 'OK');
                    check = true;
                    return false;
                }
            });
            var buyer_zip = dataUpdate.order.buyer_zip1+dataUpdate.order.buyer_zip2;
            if(buyer_zip.length != 7){
                $('#buyer_zip').parent().append('<span class="tooltip-text">無効な郵便番号です。再度入力してください。</span>');
                $('#buyer_zip').parent().find('.tooltip-text').css("top", "-50px");
                $('#buyer_zip').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
                return false;
            }
            if(dataUpdate.order.fax.length != 0){
                if(dataUpdate.order.fax.length > 25 || dataUpdate.order.fax.length < 1) {
                    ModalError('FAX番号は1～25半角数字で入力してください。', '保存出来ていません。', 'OK');     
                    check = true;
                    return false;
                }
                if(!isValidPhone(dataUpdate.order.fax) || dataUpdate.order.fax.split('-').includes('')) {
                    ModalError('ファックス番号 ' + dataUpdate.order.fax + ' はフォーマットではありません', '保存出来ていません。', 'OK');
                    check = true;
                    return false;
                }
            }
        }
        if(dataUpdate === false || check) {
            return false
        }
        let order_id = $('#order_id').val()
        let ship_exist = $('.ship_exist').val();
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        $.ajax({
            type: "POST",
            url: urlUpdate+'/'+order_id,
            data: {
                data: dataUpdate,
                ship_exist: ship_exist
            },
            success: function (response) {
                if(response.status === true)
                {
                    ModalSuccess('変更内容を保存しました', 'Ok')                         
                    setTimeout(function(){
                        window.location.reload()
                    }
                    , 2000);
                }
                if(response.status === false)
                {
                    if(response.require !== undefined && response.require === 'reload')
                    {
                        ModalError('この受注伝票の情報（金額、送り状番号、納品日など）は変更されました。画面をリロードして最新データで再度ご確認してくださいませ。', '保存出来ていません。', 'OK')
                    }else {
                        ModalError(response.message, '保存出来ていません。', 'OK')
                    }
                }
                $('.loading-full-page').remove()
            }
        });
   })
   $(document).on('click','#submit_modal_update', function () {
        let dataUpdate =  setDataOrder();
        let check = false;

        if(dataUpdate) {
            dataUpdate.detail.forEach(function(item, index) {
                if (item.ship_phone.length > 25 || item.ship_phone.length < 1) {
                    ModalError('お届け先TELは1～25半角数字で入力してください。', '保存出来ていません。', 'OK');     
                    check = true;
                    return false;
                }
                if (!isValidPhone(item.ship_phone) || item.ship_phone.split('-').includes('')) {
                    ModalError('無効な配送電話番号 ' + item.ship_phone, '保存出来ていません。', 'OK');
                    check = true;
                    return false;
                }
            });
            var buyer_zip = dataUpdate.order.buyer_zip1+dataUpdate.order.buyer_zip2;
            if(buyer_zip.length != 7){
                $('.zipcode-buyer').parent().append('<span class="tooltip-text">無効な郵便番号です。再度入力してください。</span>');
                $('.zipcode-buyer').parent().find('.tooltip-text').css("top", "-50px");
                $('.zipcode-buyer').parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
                return false;
            }
            if(dataUpdate.order.fax.length != 0){
                if(dataUpdate.order.fax.length > 25 || dataUpdate.order.fax.length < 1) {
                    ModalError('ファックス ' + dataUpdate.order.fax + ' は1より大きく25未満でなければなりません', '保存出来ていません。', 'OK');     
                    check = true;
                    return false;
                }
                if(!isValidPhone(dataUpdate.order.fax) || dataUpdate.order.fax.split('-').includes('')) {
                    ModalError('ファックス番号 ' + dataUpdate.order.fax + ' はフォーマットではありません', '保存出来ていません。', 'OK');
                    check = true;
                    return false;
                }
            }
        }
        if(dataUpdate === false || check) {
            return false
        }
        let order_id = $('#order_id').val()
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        $.ajax({
            type: "POST",
            url: urlUpdate+'/'+order_id,
            data: {data: dataUpdate},
            success: function (response) {
                if(response.status === true)
                {
                    ModalSuccess('変更内容を保存しました', 'Ok')                         
                    setTimeout(function(){
                        window.location.reload()
                    }
                    , 2000);
                }
                if(response.status === false)
                {
                    if(response.require !== undefined && response.require === 'reload')
                    {
                        ModalError('この受注伝票の情報（金額、送り状番号、納品日など）は変更されました。画面をリロードして最新データで再度ご確認してくださいませ。', '保存出来ていません。', 'OK')
                    }else {
                        ModalError(response.message, '保存出来ていません。', 'OK')
                    }
                }
                $('.loading-full-page').remove()
            }
        });
   })
   /**
    * click select bill list
    */
   $(document).on('change', '.select-bill-list', function () {
    updatePurchaseCode();
    let addRow = true
    let ship_code = $(this).val()
    $('#table_product>tbody>.shipment-code').each(function (index, element) {
        if($(element).find('.code-value-ship').text() === ship_code  || ship_code === '')
        {
            addRow = false
        } else
        {
            // $(element).remove()
        }
    })
    let tds = ''
    let delivery_fee = 0
    let sttbill = 0
    let element_delivery = ''
    $(this).data('shipcode', ship_code)
    $("#bill_list> .bill-card> table").each((index, element) => {
        if(ship_code === $(element).find('.bill-id').val())
        {
            sttbill = $(element).data('sttbill')
            element_delivery = $(element).parent().find('.delivery-payment')
            delivery_fee = $(element).find('.delivery-fee').val()
        }
    })
    tds = 
    `
    <tr class="shipment-code">
        <td class="disable"></td>
        <td class="disable code-value-ship">`+ ship_code+`</td>
        <td></td>
        <td class="text-stt">送料`+sttbill+`</td>
        <td></td>
        <td></td>
        <td></td>
        <td> <input type="text" class="text-left delivery-fee price2 form-control input-table money-table" readonly value="`+delivery_fee+`"></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    `
    if(addRow === true)
    {
        $('#table_product tbody .fee-service').before(tds);
    }
    // tính tổng tiền giao hàng
    let totalprice = 0
    let total_delivery = ''
    $("#table_product>tbody>tr").each((index, element) => {
        let ship_code_selected= ''
        ship_code_selected = $(element).find('td>.select-bill-list').val()
        if(ship_code_selected === ship_code)
        {
            totalprice = totalprice + parseFloat($(element).find('td>.price2').val().replace(/\,/g, ''))
        }
    })
    total_delivery = number_format(totalprice+parseFloat(delivery_fee.replace(/\,/g, '')), 0,'.', ',')
    element_delivery.val(total_delivery)
    sumTotalPriceProduct() // tinh tong lai cac dong trong bang san pham
    // xóa những shipment_code không chọn
    let arr_shipment_code = [];
    $('.select-bill-list').each((index, element)=> {
        arr_shipment_code.push($(element).val())
    })
    $('.code-value-ship').each((index, element) => {
        if(arr_shipment_code.indexOf($(element).text().trim()) === -1)
        {
            $(element).parent().remove()
        }else 
        {
        }
    })
   })
   /**
    * function input delivery fee
    */
   $(document).on('blur', '.delivery-fee' , function () {
       let value = ''
       let ship_code = $(this).parent().parent().parent().find('.bill-id').val() // lấy ship code của table đang sửa fee bill
       value = number_format(parseFloat($(this).val().replace(/\,/g, '')),0,'.',',')
       // vòng lặp chạy để thay đổi số tiền phí
        $("#table_product> tbody> .shipment-code").each((index, element) => {
            if(ship_code === $(element).find('.code-value-ship').text())
            {
                $(element).find('.delivery-fee').val(value)
            }
        })
        sumTotalPriceProduct() // tinh tong lai cac dong trong bang san pham
   })
   /**
    * sku slick
    */
   var classSKu = ''
   $(document).on('click', '.sku', function (event) {
        classSKu = $(this).data('index')
    })
   $(document).on('click', '.product-modal', function () {
        let date = getStringDateNow();
        // let dateMonthYear = String(date.getFullYear()) + String(date.getMonth() + 1).toString().padStart(2, "0")+String(date.getDate())
        var element = $("#table_product>tbody>.product")[classSKu]
        var info_product = $(this).prop('info')
        let supplied_id = info_product.supplied_id
        if(parseInt(info_product.supplied_id) < 10)
        {
            supplied_id = '000'+info_product.supplied_id;
        } else if(parseInt(info_product.supplied_id) < 100)
        {
            supplied_id = '00'+info_product.supplied_id;
        } else if(parseInt(info_product.supplied_id) < 1000)
        {
            supplied_id = '0'+info_product.supplied_id;
        }
        let purchase_code = supplied_id + '-'+ date + '-';
        var check_product_exist = false;
        $("#table_product>tbody>.product-edit").each((index, element) => {
            if($(element).find('.product-id').val() == info_product.product_id){
                check_product_exist = true;
                var quantity = $(element).find('.quantity-product').val();
                quantity = parseInt(quantity) + 1;
                $(element).find('.quantity-product').val(quantity);
                let price_buy = parseFloat($(element).find('.price-buy-product').val().replace(/\,/g, ''))
                let price_sale = parseFloat($(element).find('.price-sale-product').val().replace(/\,/g, ''))
                let price_edit = parseFloat($(element).find('.edit-price-edit').val().replace(/\,/g, ''))
                let cost_price = parseFloat($(element).find('.cost-price').val().replace(/\,/g, ''))
                let total_price_buy = 0
                let total_price_sale = 0
                let total_cost_price = 0
                total_price_buy = price_buy * quantity
                total_price_sale = price_sale * quantity
                total_cost_price = (cost_price * quantity) + price_edit
                $(element).find('.total-price-buy-product').val(number_format(total_price_buy,0,'.',','))
                $(element).find('.total-cost-price').val(number_format(total_cost_price,0,'.', ','))
                $(element).find('.total-price-sale-product').val(number_format(total_price_sale,0,'.', ','))
                sumTotalPriceProduct()
            }
        })
        $("#table_product>tbody>.product-add").each((index, element) => {
            if($(element).find('.product-id').val() == info_product.product_id){
                check_product_exist = true;
                var quantity = $(element).find('.quantity-product').val();
                quantity = parseInt(quantity) + 1;
                $(element).find('.quantity-product').val(quantity);
                let price_buy = parseFloat($(element).find('.price-buy-product').val().replace(/\,/g, ''))
                let price_sale = parseFloat($(element).find('.price-sale-product').val().replace(/\,/g, ''))
                let price_edit = parseFloat($(element).find('.edit-price-edit').val().replace(/\,/g, ''))
                let cost_price = parseFloat($(element).find('.cost-price').val().replace(/\,/g, ''))
                let total_price_buy = 0
                let total_price_sale = 0
                let total_cost_price = 0
                total_price_buy = price_buy * quantity
                total_price_sale = price_sale * quantity
                total_cost_price = (cost_price * quantity) + price_edit
                $(element).find('.total-price-buy-product').val(number_format(total_price_buy,0,'.',','))
                $(element).find('.total-cost-price').val(number_format(total_cost_price,0,'.', ','))
                $(element).find('.total-price-sale-product').val(number_format(total_price_sale,0,'.', ','))
                sumTotalPriceProduct()
            }
        })
        if(check_product_exist == false){
            if($(element).find('.purchase-code').text().trim() === '')
            {
                $(element).find('.purchase-code').text(purchase_code)
            }
            // sku
            $(element).find('.sku').val(info_product.sku)
            $(element).find('.sku').text(info_product.sku)
            $(element).find('.product-id').val(info_product.product_id)
            $(element).find('.maker_id').val(info_product.maker_id)
            $(element).find('.maker_code').val(info_product.maker_code)
            $(element).find('.name-product').val(info_product.name)
            $(element).find('.product-status-id').val(info_product.product_status_id)
            $(element).find('.price-buy-product').val(number_format(parseFloat(info_product.cost_price),0,'.',','))
            $(element).find('.cost-price').val(number_format(parseFloat(info_product.cost_price),0,'.',','))
            $(element).find('.price-sale-product').val(number_format(parseFloat(info_product.sale_price_intax),0,'.',','))
            $(element).find('.quantity-product').val(0)
            $(element).find('.total-price-buy-product').val('')
            $(element).find('.total-price-sale-product').val('')
            checkProductBill() 
            sumTotalPriceProduct()
        }
   })
   /**
    * function input quantity product
    */
   $(document).on('blur', '.quantity-product', function () {
       let element = $(this).parent().parent()
       let price_buy = parseFloat(element.find('.price-buy-product').val().replace(/\,/g, ''))
       let price_sale = parseFloat(element.find('.price-sale-product').val().replace(/\,/g, ''))
       let price_edit = parseFloat(element.find('.edit-price-edit').val().replace(/\,/g, ''))
       let cost_price = parseFloat(element.find('.cost-price').val().replace(/\,/g, ''))
       let quantity = $(this).val()
       let total_price_buy = 0
       let total_price_sale = 0
       let total_cost_price = 0
       total_price_buy = price_buy * quantity
       total_price_sale = price_sale * quantity
       total_cost_price = (cost_price * quantity) + price_edit
    //    xet tổng giá mua và giá bán
        element.find('.total-price-buy-product').val(number_format(total_price_buy,0,'.',','))
        element.find('.total-cost-price').val(number_format(total_cost_price,0,'.', ','))
        element.find('.total-price-sale-product').val(number_format(total_price_sale,0,'.', ','))
        sumTotalPriceProduct()
   })
   /**
   * function input price sale product
   */
    $(document).on('blur', '.price-sale-product', function () {
        let element = $(this).parent().parent()
        let price_buy = parseFloat(element.find('.price-buy-product').val().replace(/\,/g, ''))
        let price_sale = parseFloat(element.find('.price-sale-product').val().replace(/\,/g, ''))
        let price_edit = parseFloat(element.find('.edit-price-edit').val().replace(/\,/g, ''))
        let cost_price = parseFloat(element.find('.cost-price').val().replace(/\,/g, ''))
        let quantity = parseFloat(element.find('.quantity-product').val().replace(/\,/g, ''))
        let total_price_buy = 0
        let total_price_sale = 0
        let total_cost_price = 0
        total_price_buy = price_buy * quantity
        total_price_sale = price_sale * quantity
        total_cost_price = (cost_price * quantity)+price_edit
    //    xet tổng giá mua và giá bán
        element.find('.total-price-buy-product').val(number_format(total_price_buy,0,'.',','))
        element.find('.total-price-sale-product').val(number_format(total_price_sale,0,'.', ','))
        element.find('.total-cost-price').val(number_format(total_cost_price,0,'.', ','))
        sumTotalPriceProduct()
    })
  /**
   * function input price buy product
   */
    $(document).on('blur', '.cost-price', function () {
        let element = $(this).parent().parent()
        let price_buy = parseFloat(element.find('.price-buy-product').val().replace(/\,/g, ''))
        let price_sale = parseFloat(element.find('.price-sale-product').val().replace(/\,/g, ''))
        let price_edit = parseFloat(element.find('.edit-price-edit').val().replace(/\,/g, ''))
        let cost_price = parseFloat(element.find('.cost-price').val().replace(/\,/g, ''))
        let quantity = parseFloat(element.find('.quantity-product').val().replace(/\,/g, ''))
        let total_price_buy = 0
        let total_price_sale = 0
        let total_cost_price = 0
        total_price_buy = price_buy * quantity
        total_price_sale = price_sale * quantity
        total_cost_price = (cost_price * quantity) + price_edit
    //    xet tổng giá mua và giá bán
        element.find('.total-price-buy-product').val(number_format(total_price_buy,0,'.',','))
        element.find('.total-price-sale-product').val(number_format(total_price_sale,0,'.', ','))
        element.find('.total-cost-price').val(number_format(total_cost_price,0,'.', ','))
        sumTotalPriceProduct()
    })
    $(document).on('blur', '.edit-price-edit', function () {
        let element = $(this).parent().parent()
        let price_edit = parseFloat(element.find('.edit-price-edit').val().replace(/\,/g, ''))      
        let cost_price = parseFloat(element.find('.cost-price').val().replace(/\,/g, ''))
        let quantity = parseFloat(element.find('.quantity-product').val().replace(/\,/g, ''))
        let total_cost_price = 0
        total_cost_price = (cost_price * quantity) + price_edit
    //    xet tổng giá mua và giá bán
        element.find('.total-cost-price').val(number_format(total_cost_price,0,'.', ','))
        sumTotalPriceProduct()
    })
   /**
    * sumTotalPriceProduct function
    */
   function sumTotalPriceProduct () {
        let totalprice1 = 0
        let totalprice2 = 0
        let totalprice3 = 0
        let totalprice4 = 0
        $("#table_product>tbody").find('.price1').each((index, element) => {
            totalprice1=totalprice1+parseFloat($(element).val().replace(/\,/g, ''))
        })
        $("#table_product>tbody").find('.price2').each((index, element) => {
            totalprice2=totalprice2+parseFloat($(element).val().replace(/\,/g, ''))
        })
        $("#table_product>tbody").find('.price3').each((index, element) => {
            totalprice3=totalprice3+parseFloat($(element).val().replace(/\,/g, ''))
        })
        $("#table_product>tbody").find('.price4').each((index, element) => {
            totalprice4=totalprice4+parseFloat($(element).val().replace(/\,/g, ''))
        })
        // xet tong cac dong
        $("#table_product>tbody").find('.total-price2').text(number_format(totalprice2,0,'.', ','))
        $("#table_product>tbody").find('.total-price4').text(number_format(totalprice4,0,'.', ','))
   }
   /**
    * click supplied
    */
   var classBill = ''
   $(document).on('click', '.supplied', function (event) {
        classBill = $(this).data('index')
    })
   $(document).on('click', '.supplied-modal', function() {
       let element = $("#bill_list>.bill-card>table")[classBill]
       let info = {}
       info = $(this).prop('info')
       $(element).find('.supplied').val(info.name)
       $(element).find('.supplied-id').val(info.supplie_id)
   })
   /**
    * function check validate product and billid
    */
   function checkProductBill ()
   {
       let arr_bill =[]
        $("#table_product>tbody>.product").each((index, element) => {
            let id_bill = ''
            let product = ''
            let arr = []
            id_bill = $(element).find('td').find('.select-bill-list').val()
            product = $(element).find('td').find('.sku').text()
            if(id_bill != '')
            {
                arr.push(id_bill, product) // mảng bao gồm số bill và sku của sản phẩm
                includes = arr_bill.some(a => arr.every((v, i) => v === a[i])) // kiểm tra nếu trùng mã sku và mã bill thì sẽ trả về true
                if(includes === true)
                {
                    $(element).find('td>.sku').focus()
                    $(element).find('td>.tooltip-error>.tooltip-text').show().delay(3000).fadeOut("slow")
                }
                arr_bill.push(arr) // các sản phẩm và các số bill
            }
        })
   }
   /**
    * click function search zipcode buyer
    */
   $("#infor_buyer>table>tbody>tr").each((index, element) => {
       $(element).find('.btn-search-zipcode').click(function() {
            $('.modal-confirm').remove(); // xóa class modal nếu có
           let zipcode = $(this).parent().find('.zipcode-buyer').val().replace('-','')
           let class_address = $(this).parent().parent().parent().parent()
           getZipcode(zipcode,class_address)
       })
   })
   /**
    * function click search ship code
    */
   $(document).on('click', '.btn-search-zipcode-ship', function () {
        // $('.modal').remove(); // xóa class modal nếu có
        $('.modal-backdrop').remove()
        let zipcode = $(this).parent().find('.ship-zip').val().replace('-','')
        let class_address = $(this).parent().parent().parent().parent()
        getZipcode(zipcode,class_address)
})
   /**
    * function click zipcode
    * use zipcloud.ibsnet
    * tutorial: http://program-memo.com/archives/41
    * @param {*} zipcode 
    * @param {*} class_address 
    */
    function getZipcode (zipcode,class_address) {
        var element_adrress1 = class_address.find('.address-1') // lấy class địa chỉ 1 để set địa chỉ
        if(zipcode === '')
        {
            $('#modal_error').remove()
            $('.modal-backdrop').remove()
            ModalError('郵便番号を入力してから住所入力ボタンを押してください。', '', 'Ok')
        }
        var param = {'zipcode': zipcode}
        var results = {}
        var send_url = "http://zipcloud.ibsnet.co.jp/api/search";
        $.ajax({
            type: "GET",
            cache: false,
            data: param,
            url: send_url,
            dataType: "jsonp",
            success: function (res) {                
                if(res.results != null){
                    // chay vòng lặp kết quả để gắn địa chỉ cho từng class
                    res.results.forEach(element => {
                        element_adrress1.val(element.address1 + element.address2 + element.address3)
                    });
                }else {
                }
            }
        });
        return results
    }
    // click nhập số lượng dịch vụ
    $(document).on('blur', '.quantity-service', function () {
        var $element = $(this)
        var $total_service = $(this).parent().parent().find('.total-service');
        var $price_service = $(this).parent().parent().find('.price-service');
        var value = 0;
        value = number_format(parseInt($element.val())*parseFloat($price_service.val().replace(/\,/g, '')), 0, '.', ',')
        $total_service.val(value)
        sumTotalPriceProduct()
    })
    
    // click nhập tiền dịch vụ
    $(document).on('blur', '.price-service', function () {
        var $element = $(this)
        var $total_service = $(this).parent().parent().find('.total-service');
        var $quantity_service = $(this).parent().parent().find('.quantity-service');
        var value = 0;
        value = number_format(parseInt($element.val().replace(/\,/g, ''))*parseFloat($quantity_service.val()), 0, '.', ',')
        $total_service.val(value)
        sumTotalPriceProduct()
    })
    /**
     * btn click tự động đánh bill gửi hàng.
     */
    var times_shipcode = 1;
    var code_yamoto = 0;
    var code_sagawa_II = 0;
    var code_post_off = 0
    var code_orther = 0
    //  tăng thứ tự từng mã shipment_code
    $('.bill-id').each((index, element) => {
        if($(element).val().search(/ヤマト運輸/g) === 0)
        {
            code_yamoto++;
        }
        else if ($(element).val().search(/日本郵便/g) === 0 )
        {
            code_post_off++
        }else if ($(element).val().search(/その他/g) === 0 )
        {
            code_orther++
        }
    })

    $(document).on('click', '.btn-add-shipment', function () {
        let delivery_method = ''
        $btn_element = $(this)
        delivery_method = $(this).parent().parent().find('.delivery-method').val()
        if(delivery_method === '')
        {
            ModalError('配送方法を選択してから自動採番ボタンを押してください。', '', 'OK');
            return false
        }
        let shipment_code = ''
        if(parseInt(delivery_method) === 1 || parseInt(delivery_method) === 7)
        {
            $.ajax({
                'url': urlGetShipmentCode,
                'method': 'GET',
                'data': {times: times_shipcode},
                'success': function(response) {
                    shipment_code = response.ship_code
                    $btn_element.parent().find('.bill-id').val(shipment_code)
                    $btn_element.parent().find('.bill-id').css('width',' 100%')
                    setOptionBillSelect()
                    times_shipcode++
                    $btn_element.hide()
                }
            })
        } else if (parseInt(delivery_method) >= 2 && parseInt(delivery_method) <= 4){
            shipment_code = 'ヤマト運輸'
            if(code_yamoto > 0)
            {
                shipment_code = 'ヤマト運輸' +code_yamoto
            }
            code_yamoto++
            $btn_element.parent().find('.bill-id').val(shipment_code)
            $btn_element.parent().find('.bill-id').css('width',' 100%')
            setOptionBillSelect()
            $btn_element.hide()
        } else if (parseInt(delivery_method) >= 5 && parseInt(delivery_method) <= 6){
            shipment_code = '日本郵便'
            if(code_post_off > 0)
            {
                shipment_code = '日本郵便' +code_post_off
            }
            code_post_off++
            $btn_element.parent().find('.bill-id').val(shipment_code)
            $btn_element.parent().find('.bill-id').css('width',' 100%')
            setOptionBillSelect()
            $btn_element.hide()
        } else if(parseInt(delivery_method) === 8)
        {
            shipment_code = 'その他'
            if(code_orther > 0)
            {
                shipment_code = 'その他' +code_orther
            }
            code_orther++
            $btn_element.parent().find('.bill-id').val(shipment_code)
            $btn_element.parent().find('.bill-id').css('width',' 100%')
            setOptionBillSelect()
            $btn_element.hide()
        }else if (parseInt(delivery_method) === 9){
            shipment_code = '佐川急便(秘伝II)'
            if(code_sagawa_II > 0)
            {
                shipment_code = '佐川急便(秘伝II)' +code_sagawa_II
            }
            code_sagawa_II++
            $btn_element.parent().find('.bill-id').val(shipment_code)
            $btn_element.parent().find('.bill-id').css('width',' 100%')
            setOptionBillSelect()
            $btn_element.hide()
        }
    })
    /**
     * focus lấy option hiện tại của phương thức gửi hàng
     * @author Dat
     */
    $(document).on('focus', '.delivery-method', function () {
        let $element_select = $(this)
        let delivery_method = $element_select.val()
        let class_name = ''
        if(parseInt(delivery_method) === 1 || parseInt(delivery_method) === 7){
            class_name = 'sagawa'
        }else if(parseInt(delivery_method) >= 2 && parseInt(delivery_method) <= 4){
            class_name = 'yamoto'
        }else if(parseInt(delivery_method) >= 5 && parseInt(delivery_method) <= 6){
            class_name = 'post-office'
        } else if(parseInt(delivery_method) === 8){
            class_name = 'orther'
        }else if(parseInt(delivery_method) === 9){
            class_name = 'sagawa_II'
        }
        let input_save_code = '<input type="text" class="'+class_name+'" value="'+$element_select.parent().parent().find('.bill-id').val()+'" >'
        $element_select.parent().parent().find('.bill-id').append(input_save_code)
    })
    /**
     * select phương thức gửi hàng
     */
    $(document).on('change', '.delivery-method', function () {
        let $element_select = $(this)
        $element_select.parent().parent().find('.bill-id').val('')
        $element_select.parent().parent().find('.bill-id').css('width','45%')
        $element_select.parent().parent().find('.btn-add-shipment').show()
        setOptionBillSelect()
    })
    /**
     * validate ngày đặt hàng
     */
    let date_order_now = getDateNow()
    $(document).on('blur', '#purchase_date', function (){
        if(!_validatedate($(this).val())){
            $(this).val('')
        }
    })
    $(document).on('blur', '#purchase_date', function (){
        $(this).parent().find('.tooltip-text').remove()
    })
    /**
     * validate ngày giao hàng của order
     */
    $(document).on('blur', '#delivery_date', function (){
        if(!_validatedate($(this).val())){
            $(this).val('')
        }
    })
    $(document).on('blur', '#delivery_date', function (){
        $(this).parent().find('.tooltip-text').remove()
    })
    /**
     * validate ngay du dinh giao hang 
     */
    $(document).on('blur', '.es-delivery-date', function (){
        if(!_validatedate($(this).val())){
            $(this).val('')
        }
    })
    $(document).on('blur', '.es-delivery-date', function (){
        $(this).parent().find('.tooltip-text').remove()
    })
    /**
     * validate ngay du dinh giao hang 
     */
    $(document).on('blur', '.delivery-date', function (){
        if(!_validatedate($(this).val())){
            $(this).val('')
        }
    })
    $(document).on('blur', '.delivery-date', function (){
        $(this).parent().find('.tooltip-text').remove()
    })
    
    $(document).on('blur', '.ship-zip', function (){
        let ship_zip = $(this).val()
        var $this = $(this)
        check_zipcode(ship_zip, $this)
    })

    $(document).on('blur', '.zipcode-buyer', function (){
        let ship_zip = $(this).val()
        var $this = $(this)
        check_zipcode(ship_zip, $this)
    })

    $(document).on('blur', '.purchase-code-page', function (){
        let check_purchase_code_edit = true;
        let purchase_code_ = $(this).parent().find('.purchase-code').text().trim()
        let purchase_code_page = purchase_code_ + $(this).val()
        $("#table_product>tbody>.product-edit").each((index, element) => {
            let purchase_code = $(element).find('.purchase-code').text().trim()
            if(purchase_code_page == purchase_code){
                let tooltip_error = '<span class="tooltip-text">発注番号が複製されています。再入力ください。</span>'
                $(this).parent().append(tooltip_error);
                $(this).parent().find('.tooltip-text').css("top", "-50px");
                $(this).parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
                check_es_date = false
                check_purchase_code_edit = false;
            }else {
                $(this).parent().find('.tooltip-text').remove()
                check_es_date = true
                check_purchase_code_edit = true;
            }
        });
        if(check_purchase_code_edit == false){
            return false;
        }
        let check_purchase_code = 0;
        $("#table_product>tbody>.product-add").each((index, element) => {
            let purchase_code = $(element).find('.purchase-code').text().trim()
            let page = $(element).find('.purchase-code-page').val()
            if(purchase_code_page == purchase_code+page){
                check_purchase_code++;
            }
        });
        if(check_purchase_code >= 2 && check_purchase_code_edit == true){            
            let tooltip_error = '<span class="tooltip-text">発注番号が複製されています。再入力ください。</span>'
            $(this).parent().append(tooltip_error);
            $(this).parent().find('.tooltip-text').css("top", "-50px");
            $(this).parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
            check_es_date = false
        }else {
            $(this).parent().find('.tooltip-text').remove()
            check_es_date = true
        }
        updatePurchaseCode();
    })

    function updatePurchaseCode(){
        $("#bill_list> .bill-card> table").each((index, element) => {
            let str_purchase_id = '';
            $("#table_product>tbody>.product-edit").each((index_, element_) => {
                let purchase_code = $(element_).find('.purchase-code').text().trim()
                let shipment_id = $(element_).find('.select-bill-list').val()
                if(shipment_id == $(element).find('.bill-id').val() && $(element).find('.bill-id').val() != ''){
                    str_purchase_id += purchase_code + ' | ';
                }
            });
            $("#table_product>tbody>.product-add").each((index, element_) => {
                let purchase_code = $(element_).find('.purchase-code').text().trim()
                let page = $(element_).find('.purchase-code-page').val()
                let shipment_id = $(element_).find('.select-bill-list').val()
                if(shipment_id == $(element).find('.bill-id').val() && $(element).find('.bill-id').val() != ''){
                    str_purchase_id += purchase_code+page + ' | ';
                }
            });
            str_purchase_id = str_purchase_id.substring(0,str_purchase_id.length - 3);
            $(element).parent().find('.purchase_code_in_ship').text(str_purchase_id);
        })
    }

    function isValidPhone(phone) {
        var phoneRe = /(\+?([0-9]{1}))$/;
        var digits = phone.replace(/\D/g, "");
        return phoneRe.test(digits);
    }

    function check_zipcode(ship_zip, $this){        
        var param = {'zipcode': ship_zip}
        var send_url = "http://zipcloud.ibsnet.co.jp/api/search";
        $.ajax({
            type: "GET",
            cache: false,
            data: param,
            url: send_url,
            dataType: "jsonp",
            success: function (res) {
                if(res.results == null){
                    let message = `無効な郵便番号です。再度入力してください。`;
                    let tooltip_error = '<span class="tooltip-text">'+message+'</span>'
                    $this.parent().append(tooltip_error)
                    $this.parent().find('.tooltip-text').css("top", "-50px");
                    $this.parent().find('.tooltip-text').show().delay(3000).fadeOut("slow");
                }else{
                    $this.parent().find('.tooltip-text').remove()
                }
            }
        });
    }
    
    $(".datepicker").each(function() {    
        $(this).datepicker('setDate', new Date($(this).val()));
    });
    
    $(document).on('keydown keyup', '.input-space', function (e){
        if (e.which == 32 || e.which == 110 || e.which == 188) {
            e.preventDefault();
            return false;
        }
    })
});