@extends('layouts.master')
@section('content')
<!-- load css and js order search -->
<link href="{{ asset('css/orders/create.css') }}" rel="stylesheet">
<script src="{{ asset('js/orders/create.js') }}"></script>
<!-- end   class="container-fluid"-->
<div>
    <div class="card-fix">
        <div class="row">
            <div class="col-md-1 col-sm-2">
                <div class="label-form">
                    <h5>受注ID</h5>
                </div>
            </div>
            <div class="col-md-3 input-form">
                <input type="hidden" id="order_id" value="">
                <input class="form-control form-control-sm" type="text" id="order_code" value="{{$order_code}}" readonly title="require order id">
            </div>
            
            <div class="col-md-1 col-sm-2">
                <div class="label-form">
                    <h5>操作</h5>
                </div>
            </div>
            <!-- button -->
            <div class="btn-right col-md-4 col-lg-4 col-sm-12 " style="margin-top: 4px">
                <button type="button" class="btn btn-search-order" id="create_order">保存</button>
                &nbsp;<span style="position: relative; top: 5px; color: red; font-weight: bold">(*)は入力必須です</span>
            </div>
        </div>
    </div>
    <!-- card table tinh trang ho tro -->
    <div class="card-support" id="support_order">
        <table class="table">
            <tr>
                <td width="10%" class="label-form"><b>受注ステータス</b></td>
                <td width="23%">
                    <select id="supp_status" class="form-control">
                        <?php for($supp_value = 1; $supp_value < 8; $supp_value++) {?>
                            <?php $supp_text = '';
                                if($supp_value == 1)
                                {
                                    $supp_text = '新規注文';
                                } elseif($supp_value == 2)
                                {
                                    $supp_text = '入金待ち';
                                } elseif($supp_value == 3)
                                {
                                    $supp_text = '受注処理中';
                                }elseif($supp_value == 4)
                                {
                                    $supp_text = '要確認';
                                }elseif($supp_value == 5)
                                {
                                    $supp_text = '保留中';
                                }elseif($supp_value == 6)
                                {
                                    $supp_text = '完了';
                                }
                                elseif($supp_value == 7)
                                {
                                    $supp_text = 'キャンセル';
                                }
                            ?>
                            <option value="<?php echo $supp_value; ?>"><?php echo $supp_text; ?> </option>
                        <?php } ?>
                    </select>
                </td>
                <td width="10%" class="label-form"><b>受注日</b></td>
                <td width="23%" class="">
                    <div class="tooltip-error" style="width: 100%; background: none;">
                        <input type="text" id="create_date" class="form-control" value="">
                    </div>
                </td>
                <td width="10%" class="label-form"><b>発注日</b></td>
                <td width="23%">
                    <div class="tooltip-error" style="width: 100%; background: none;">
                        <input type="text" id="purchase_date" class="form-control" value="">
                    </div>
                </td>
            </tr>
            <tr>
                <td width="10%" class="label-form"><b></b></td>
                <td>
                <td width="10%" class="label-form"><b>取込日</b></td>
                <td class="disable"></td>
                <td width="10%" class="label-form"><b>出荷完了日</b></td>
                <td>
                    <div class="tooltip-error" style="width: 100%; background: none;">
                        <input type="text" class="form-control" value="" id="delivery_date">
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <!-- table infor buyer -->
    <div class="card-infor-buyer" id="infor_buyer">
        <div class="title-card-search">
            <h5>注文者情報 </h5>
        </div>
        <table class="table">
            <tr>
                <td width="10%" class="label-form"><b>注文主〒</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                <td width="20%">
                    <div class="tooltip-error" style="display: block">
                        <input type="text" class="input-table  form-control zipcode-buyer zip-input" maxlength="8" style="width: 70%; float:left;" value="" id="buyer_zip"> 
                        <button style="width: 30%; float:right" class="btn-search-zipcode">住所入力</button>
                    </div>
                </td>
                <td width="10%" class="label-form"><b>注文主TEL</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                <td width="15%">
                    <div class="tooltip-error">
                        <input type="text" class="input-table form-control tel-input input-tel-fax" id="buyer_tel" value="" tyle="width: 100%;" maxlength="12">
                    </div>
                </td>
                <td width="10%" class="label-form"><b>FAX番号</b></td>
                <td width="15%">
                    <div class="tooltip-error">
                        <input class="input-table  form-control fax-input" id="buyer_fax" value=""  maxlength="12">
                    </div>
                </td>
                <td width="10%" class="label-form"><b>メールアドレス</b></td>
                <td width="15%"><input class="input-table  form-control" id="buyer_email" value=""></td>
            </tr>
            <tr>
                <td width="10%" class="label-form"><b>注文主名</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                <td colspan="1"><input class="input-table  form-control"id="buyer_name" value="" placeholder="Name 1"></td>
                <td colspan="2"><input class="input-table  form-control" id="buyer_name2" value="" placeholder="Name 2"></td>
                <td width="10%" class="label-form"><b>代引き金額</b></td>
                <td>
                    <span style="float:left">¥</span>
                    <input class="money-daibiki money-plus input-table number-input" style="width: 96%; text-align: right;" value="">
                </td>
                <td width="10%" class="label-form"><b>ECサイト</b></td>
                <td class="disable type-order">RimacEC</td>
            </tr>
            <tr>
                <td width="10%" class="label-form"><b>注文主住所</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                <td colspan="2"><input class="input-table  form-control address-1" id="buyer_address1" value=""></td>
                <td colspan="3"><input class="input-table  form-control address-2" id="buyer_address2" value=""></td>
                <td colspan="2"><input class="input-table  form-control address-3" id="buyer_address3" value=""></td>
            </tr>
        </table>
    </div>
    <!-- table purchase  -->
    <table class="table" id="table_product">
        <tr class="label-form">
            <th width="9%" class="title-table">発注番号<sup style="color: red; font-weight: bold;">(*)</sup></th>
            <th width="8%" class="title-table">送り状番号<sup style="color: red; font-weight: bold;">(*)</sup></th>
            <th width="8%" class="title-table">SKU<sup style="color: red; font-weight: bold;">(*)</sup></th>
            <th width="10%" class="title-table">品名</th>
            <th width="10%" class="title-table">商品ステータス</th>
            <th width="5%" class="title-table">数量<sup style="color: red; font-weight: bold;">(*)</sup></th>
            <th width="7%" class="title-table">売価(税込)</th> <!-- giá bán có thuế -->
            <th width="9%" class="title-table">売価合計(税込)</th> <!--tổng giá bán có thuế -->
            <th width="9%" class="title-table">原価(税抜)</th> <!-- giá mua không thuế -->
            <th width="9%" class="title-table">訂正金額(税抜)</th> <!-- tiền đính chính -->
            <th width="9%" class="title-table">原価合計(税抜)</th> <!-- tổng giá mua không thuế -->
            <th width="7%" class="title-table">操作</th> <!-- xóa sp -->
        </tr>
        <tbody>
            <tr id="add_row">
            </tr>
            <!--  phí dịch vụ-->
            <tr class="fee-service">
                <td class="disable"></td>
                <td></td>
                <td></td>
                <td>手数料 </td>
                <td></td>
                <td>
                    <input type="text" class="form-control text-center number-input input-table quantity-service" value="0">
                </td>
                <td>
                    <input type="text" class="form-control input-table number-input price-service" value="0">
                </td>
                <td>
                    <input type="text" class="form-control input-table number-input total-service" readonly value="0">
                </td>
                <td>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <!-- add product -->
            <tr class="add-product">
                <td class="disable"></td>
                <td></td>
                <td><a class="a-href" id="add_product">+ 商品追加</a></td>
                <td></td>
                <td></td>
                <td></td>
                <td><input type="text" readonly style="background: #fff !important;" class="form-control input-table"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <!-- total -->
            <tr>
                <td colspan="6" class="label-form" style="text-align:right"><b>合計</b></td>
                <td><input type="text" readonly class="total-price1 number-input form-control input-table"></td>
                <td><input type="text" style="background: #fff !important;" readonly class="total-price2 number-input form-control input-table"></td>
                <td><input type="text" style="background: #fff !important;" readonly class="total-price3 form-control input-table"></td>
                <td></td>
                <td><input type="text" style="background: #fff !important;" readonly class="total-price4 number-input form-control input-table"></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <!-- bill list information  -->
    <div id="bill_list">
            <div class="card-infor-buyer bill-card bill-1">
                <div class="title-card-search">
                    <a style="float:right; margin-top:5px" data-idbill="1" class="remove-bill a-href">お届け先削除</a>
                    <h5>お届け先情報 <span class="stt-bill">1/1</span> &nbsp; &nbsp; 発注ID：<span class="purchase_code_in_ship"></span></h5>
                </div>
                <table class="table" data-sttbill="1">
                    <tr>
                        <td width="10%" min-width="10%" class="label-form"><b>送り状番号</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td width="10%" min-width="10%">
                            <input class="input-table  form-control bill-id"value="" style="float:left; width: 55%;">
                            <button style="float:right;width: 45%;" class="btn-add-shipment">自動採番</button>
                        </td>
                        <td width="10%" min-width="10%" class="label-form"><b>配送方法</b></td>
                        <td width="10%" min-width="10%">
                            <select class="form-control delivery-method">
                                <option value="1">
                                    佐川急便
                                </option>
                                <option value="9">
                                    佐川急便(秘伝II)
                                </option>
                                <option value="2">
                                    ヤマト宅急便
                                </option>
                                <option value="3">
                                    ネコポス
                                </option>
                                <option value="4">
                                    コンパクト便
                                </option>
                                <option value="5">
                                    ゆうパック
                                </option>
                                <option value="6">
                                    ゆうパケット
                                </option>
                                <option value="7">
                                    その他
                                </option>
                            </select>
                        </td>
                        <td width="10%" min-width="10%" class="label-form"><b>集荷日時</b></td>
                        <td width="10%" min-width="10%">
                            <div class="tooltip-error">
                                <input class="form-control es-delivery-date" value="">
                            </div>
                        </td>
                        <td width="10%" min-width="10%">
                            <div class="tooltip-error"> 
                                <input class="form-control input-space input-table delivery-from-to-time" maxlength="5" placeholder="00-00" value="">
                            </div>
                            <!-- <select class="form-control delivery-from-to-time">
                                <option value="午前中">午前中</option>
                                <option value="12時～14時">12時～14時 </option>
                                <option value="14時～16時">14時～16時</option>
                                <option value="16時～18時">16時～18時</option>
                                <option value="18時～20時">18時～20時</option>
                                <option value="19時以降">19時以降</option>
                            </select> -->
                        </td>
                        <td width="10%" min-width="10%" class="label-form"><b>集荷先</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td width="10%" min-width="10%">
                            <input class="input-table  form-control supplied supplier-id-1" readonly data-index="0" data-toggle="modal" data-target="#modal_supplied" value="">
                            <input type="hidden" class="supplied-id" value="">
                        </td>
                    </tr>
                    <tr>
                    <td width="10%" min-width="10%" class="label-form"><b>納品方法</b></td>
                        <td width="10%" min-width="10%">
                            <select class="form-control delivery-way">
                                <option value="1" selected>直送</option>
                                <option value="2">引取</option>
                                <option value="3">配達</option>
                                <option value="4">仕入</option>
                            </select>
                        </td>
                        <td width="10%" min-width="10%" class="label-form"><b>発注ステータス</b></td>
                        <td width="10%" min-width="10%">                        
                            <select id="purchase_status" class="form-control">
                                @foreach($purchase_status as $key => $value)
                                    <option value="{{$key+1}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td width="10%" min-width="10%" class="label-form"><b>配達日時</b></td>
                        <td width="10%" min-width="10%" >
                            <div class="tooltip-error">
                                <input class="form-control delivery-date receive-date" value="">
                            </div>    
                        </td>
                        <td width="10%" min-width="10%">
                            <!-- <div class="tooltip-error"> 
                                <input class="form-control input-space input-table receive-time" maxlength="5" placeholder="00-00" value="">
                            </div> -->
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
                        <td width="10%" min-width="10%" class="label-form"><b>のし</b></td>
                        <td width="10%" min-width="10%"><input class="input-table  form-control gift-wrap" value=""></td>
                    </tr>
                    <tr>
                        <td width="10%" class="label-form"><b>お届け先〒</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td width="13%">
                            <div class="tooltip-error">
                                <input class="input-table form-control ship-zip zip-input ship-zip-1" maxlength="8" style="float: left; width: 55%" value="">
                                <button style="float:right; width: 45%;" class="btn-search-zipcode-ship">住所入力</button>
                            </div>
                        </td>
                        <td width="10%" class="label-form"><b>お届け先TEL</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td width="10%">
                            <div class="tooltip-error">
                                <input class="input-table  form-control ship-phone ship-phone-1 tel-input" value="">
                            </div>
                        </td>
                        <td width="10%" class="label-form"><b>送料</b></td>
                        <td width="10%" colspan="2"><span style="float:left">¥</span>
                            <input class="delivery-fee number-input" style="width: 96%; text-align: right; border:none" value="0">
                        </td>
                        <td width="10%" class="label-form"><b>ラッピング</b></td>
                        <td width="10%"><input class="input-table  form-control wrapping-paper-type" value=""></td>
                    </tr>
                    <tr>
                        <td width="10%" class="label-form"><b>お届け先名</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td colspan="3"><input class="input-table  form-control ship-name1 ship-name-1" value=""></td>
                        <td width="10%" class="label-form"><b>代引き請求</b></td>
                        <td colspan="2">
                            <span><input type="radio" name="pay_request" class="pay-request" value="1"> &nbsp; この荷物で請求する   &nbsp; &nbsp; &nbsp; </span>
                        </td>
                        <td width="10%" class="label-form"><b>メッセージカード</b></td>
                        <td><input class="input-table  form-control message" value=""></td>
                    </tr>
                    <tr>
                        <td width="10%" class="label-form"><b>お届け先住所</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td colspan="2"><input class="input-table  form-control ship-address1 address-1 ship-addr-1" value=""  placeholder="●●県●●市●●区●●町" ></td>
                        <td colspan="3"><input class="input-table  form-control ship-address2 address-2 ship-addr2-1" value="" placeholder="0丁目00-0000"></td>
                        <td colspan="3"><input class="input-table  form-control ship-address3 address-3" value="" placeholder="●●●号室-0000"></td>
                    </tr>
                </table>
            </div>
    </div>
    <!-- address shipment -->
    <div class="card-address-shipment">
        <div class="title-card-search">
            <h5>新規お届け先追加 </h5>
        </div>
        <div class="body-card">
            <a id="add_bill" class=" a-href">＋ お届け先追加</a>
        </div>
    </div>
    <!-- comment -->
    <div class="card-address-shipment" style="border-bottom: 0px !important">
        <div class="title-card-search">
            <h5>備考 </h5>
        </div>
        <!-- <div class="body-card" contenteditable="true" style="outline:0px;" class="comment" id="comment"> -->
        <textarea class="comment" id="comment" style="width:100%; margin:0px; border-left:2px solid;border-right:2px solid;border-bottom:2px solid"></textarea>
        <!-- </div> -->
    </div>
    <!-- 
        - modal input sku  include modal in components
        - parameter:+ List category ($categories_product) set from controller.
                    + call modal with  data-toggle="modal" data-target="#modal_product" of input
                    + Get sku from tag input id ="sku"
                    + ex: <input type="text" id="sku" data-toggle="modal" data-target="#modal_product">
    -->
    <div class="product-modal">
    </div>
    @include('components.modalSearchSkuProduct')
    <!--
        - modal search supplied
        + call modal with  data-toggle="modal" data-target="#modal_supplied" of input
        + Get supplied from tag input id ="supplied"
        + ex: <input type="text" id="supplied" data-toggle="modal" data-target="#modal_supplied">
    -->
    <div class="supplied-modal">
    </div>
    @include('components.modalSearchSupplied')
    <!-- end -->
</div>
<script>
    var urlCreate = "{{url('order/ajax-create-order')}}";
    var totalBill = "1";
    var urlGetShipmentCode = "{{url('shipment/ajax-get-shipment-code')}}";
    var url_check_shipcode = "{{ route('ajax_check_shipcode') }}";
    var purchase_status = <?php echo  ((!empty($purchase_status)) ? json_encode($purchase_status) : null) ?>;
</script>
@stop