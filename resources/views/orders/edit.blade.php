@extends('layouts.master')
@section('content')
<!-- load css and js order search -->
<link href="{{ asset('css/orders/edit.css') }}" rel="stylesheet">
<script src="{{ asset('js/orders/edit.js') }}" ></script>
<script src="{{ asset('js/orders/manipulations.js') }}"></script>
<!-- end   class="container-fluid" -->
<div>
<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
    <div class="card-fix">
        <div class="row">
            <div class="col-md-1 col-sm-2">
                <div class="label-form">
                    <h5>受注ID</h5>
                </div>
            </div>
            <div class="col-md-2 input-form">
                <input type="hidden" id="order_id" value="{{$data_order[0]['id']}}">
                <input class="form-control form-control-sm" type="text" id="order_code" readonly value="{{$order_id}}">
            </div>
            <div class="col-md-1 col-sm-2">
                <div class="label-form">
                    <h5>操作</h5>
                </div>
            </div>
            <div class="col-md-2 input-form">
                <select id="control_order" class="form-control form-control-sm">
                    <option></option>
                    <option value="pack_introduction">発注明細・梱包指示書 </option>
                    <option value="pdf_pack_introduction">PDF発注明細・梱包指示書 </option>
                    <option value="copy">注文の複製 </option>
                    <option value="delete">注文の削除 </option>
                </select>
            </div>
            <!-- button -->
            <div class="btn-right col-md-6 col-lg-6 col-sm-12 " style="margin-top: 4px">
                <button type="button" class="btn btn-search-order" id="submit_control_order">実行</button>
                <button type="button" class="btn btn-search-order" id="update_order">変更内容保存</button>
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
                            <option value="<?php echo $supp_value; ?>" <?php $support_cus == $supp_value ? print "selected": print ""?> ><?php echo $supp_text; ?> </option>
                        <?php } ?>
                    </select>
                </td>
                <td width="10%" class="label-form"><b>受注日</b></td>
                <td width="23%" class="disable date-order">{{\Carbon\Carbon::parse($date_order)->format('Y/m/d')}}</td>
                <td width="10%" class="label-form"><b>発注日</b></td>
                <td width="23%">
                    <div class="tooltip-error" style="width: 100%; background: none;">
                        @if($data_order[0]['purchase_date'] != null)
                        <input type="text" id="purchase_date" class="form-control" value="{{\Carbon\Carbon::parse($data_order[0]['purchase_date'])->format('Y/m/d')}}"/>
                        @else
                        <input type="text" id="purchase_date" class="form-control" value=""/>
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td width="10%" class="label-form"></td>
                <td>
                </td>
                <td width="10%" class="label-form"><b>取込日</b></td>
                <td class="disable">{{empty($date_import) ? '': \Carbon\Carbon::parse($date_import)->format('Y/m/d')}}</td>
                <td width="10%" class="label-form"><b>出荷完了日</b></td>
                <td>
                    <div class="tooltip-error" style="width: 100%; background: none;">
                        @if($data_order[0]['delivery_date'] != null)
                        <input type="text" style="width: 100%; background: none;" class="form-control" id="delivery_date" value="{{\Carbon\Carbon::parse($data_order[0]['delivery_date'])->format('Y/m/d')}}"/>
                        @else
                        <input type="text" style="width: 100%; background: none;" class="form-control" id="delivery_date" value=""/>
                        @endif
                        
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
                        <input type="text" class="form-control input-table zipcode-buyer zip-input" maxlength="8" style="width: 70%; float:left" value="{{$buyer_zip}}"  id="buyer_zip"> 
                        <button style="width: 30%; float:right" class="btn-search-zipcode">住所入力</button>
                    </div>
                </td>
                <td width="10%" class="label-form"><b>注文主TEL</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                <td width="15%">
                    <div class="tooltip-error">
                        <input type="text" class="form-control input-table tel-input" id="buyer_tel" value="{{$buyer_tel}}"></td>
                    </div>
                <td width="10%" class="label-form"><b>FAX番号</b></td>
                <td width="15%">
                    <div class="tooltip-error">
                        <input class="form-control input-table fax-input" id="buyer_fax" value ="{{$data_order[0]['fax']}}">
                    </div>
                </td>
                <td width="10%" class="label-form"><b>メールアドレス</b></td>
                <td width="15%"><input class="form-control input-table" id="buyer_email" value="{{$buyer_email}}"></td>
            </tr>
            <tr>
                <td width="10%" class="label-form"><b>注文主名</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                <td colspan="1"><input class="form-control input-table"id="buyer_name1" value="{{$buyer_name1}}" placeholder="Name 1"></td>
                <td colspan="2"><input class="form-control input-table"id="buyer_name2" value="{{$buyer_name2}}" placeholder="Name 2"></td>
                <td width="10%" class="label-form"><b>代引き金額</b></td>
                <td width="10%">
                <span style="float:left; margin-top: 5px;">¥</span>
                    <input type="text" class="money-plus money-daibiki form-control input-table number-input" style="width: 80%; text-align: right; float:right" value="{{number_format($data_order[0]['money_daibiki'],0)}}">
                </td>
                <td width="10%" class="label-form"><b>ECサイト</b></td>
                <td class="disable">{{$website_type}}</td>
            </tr>
            <tr>
                <td width="10%" class="label-form"><b>注文者住所</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                <td colspan="2"><input class="form-control input-table address-1" id="buyer_address1" value="{{$buyer_address1}}"></td>
                <td colspan="3"><input class="form-control input-table address-2" id="buyer_address2" value="{{$buyer_address2}}"></td>
                <td colspan="2"><input class="form-control input-table address-3" id="buyer_address3" value="{{$buyer_address3}}"></td>
            </tr>
        </table>
    </div>
    <!-- table purchase  -->
    <table class="table" id="table_product">
        <tr class="label-form">
            <th class="title-table" width="9%">発注番号<sup style="color: red; font-weight: bold;">(*)</sup></th>
            <th class="title-table" width="8%">送り状番号<sup style="color: red; font-weight: bold;">(*)</sup></th>
            <th class="title-table" width="8%">SKU<sup style="color: red; font-weight: bold;">(*)</sup></th>
            <th class="title-table" width="10%">品名</th>
            <th class="title-table" width="10%">商品ステータス</th>
            <th class="title-table" width="5%">数量<sup style="color: red; font-weight: bold;">(*)</sup></th>
            <th class="title-table" width="7%">売価(税込)</th> <!-- giá bán có thuế -->
            <th class="title-table" width="9%">売価合計(税込)</th> <!--tổng giá bán có thuế -->
            <th class="title-table" width="9%">原価(税抜)</th> <!-- giá mua không thuế -->
            <th class="title-table" width="9%">訂正金額(税抜)</th> <!-- tiền đính chính -->
            <th class="title-table" width="9%">原価合計(税抜)</th> <!-- tổng giá mua không thuế -->
            <th class="title-table" width="7%">操作</th> <!-- xóa sp -->
        </tr>
        <tbody>
            <?php
                $total_price_buy = 0;
                $total_price_sale = 0;
                $ship_code_array = [];
                $ship_code_dulicate = [];
                $total_bill = 0;
                if (is_array($detail)) {
    				$detail_count = count($detail);
				}else{
					$detail_count = 0;
				}
                for($index = 0;$index < $detail_count; $index++)
                {
                    $total_bill++;
                    if($detail[$index]['shipment_code'] != '')
                    {
                        array_push($ship_code_array, $detail[$index]['shipment_code']);// nếu shipment code của sản phẩm đó khác rỗng thì sẽ đưa vào mảng để kiếm tra dulicate
                    }
                    $total_price_buy += $detail[$index]['total_price']; // tong tong gia mua trong order có thuế
                    $total_price_sale += $detail[$index]['total_price_sale_tax']; // tong tong gia ban có thuế
            ?>
                <tr class="product product-edit" data-detailid="{{$detail[$index]['id']}}">
                    <td class="purchase-id">
                        @if(!empty($detail[$index]['purchase_code']))
                            <span style="float:left;width: 100%;" class="input-table purchase-code">{{$detail[$index]['purchase_code']}}<span>
                            <input type="hidden" style="float:left;width: 20%;" class="input-table number purchase-code-page" value="{{$detail[$index]['purchase_code']}}">
                        @else
                            <span style="float:left;width: 80%;" class="input-table purchase-code">{{$detail[$index]['supplied_id']}}-{{\Carbon\Carbon::now()->format('YYYYmmdd')}}-</span>
                            <input type="text" style="float:left;width: 20%; border: 1px solid #ced4da;" class="input-table number purchase-code-page" maxlength="4">
                        @endif
                    </td>
                    <td>
                        <select class="form-control select-bill-list select-bill-{{$index+1}}" data-index="<?php echo $index+1;?>" data-shipcode="{{$detail[$index]['shipment_code']}}">
                        </select>
                    </td>
                    <td>
                        <a href="#" class="sku sku-edit" data-index="{{$index}}" data-toggle="modal" data-target="#modal_product">{{$detail[$index]['sku']}}</a>
                        <input value="{{$detail[$index]['product_id']}}" class="product-id" type="hidden">
                        <input value="{{$detail[$index]['maker_id']}}" class="maker_id" type="hidden">
                        <input value="{{$detail[$index]['maker_code']}}" class="maker_code" type="hidden">
                    </td>
                    <td><input class="form-control input-table name-product" value="{{$detail[$index]['product_name']}}"></td>
                    <td><input class="form-control input-table product-status-id" value="{{$product_status_id[$index]}}" readonly></td>
                    <td><input class="form-control input-table quantity-product number-input" style="text-align: center;" value="{{$detail[$index]['quantity']}}"></td>
                    <td class="">
                        <input type="text" class="form-control input-table price1 price-sale-product number-input money-table" value="{{number_format($detail[$index]['price_sale_tax'], 0)}}">
                    </td>
                    <td class="">
                        <input type="text" class="form-control input-table price2 total-price-sale-product number-input money-table" readonly value="{{number_format($detail[$index]['total_price_sale_tax'],0)}}">
                    </td>
                    <td class="">
                        <input type="hidden" class="form-control input-table price3 price-buy-product number-input money-table" readonly value="{{number_format($detail[$index]['cost_price_tax'], 0)}}">
                        <input type="hidden" class="form-control price-edit" readonly value="{{$detail[$index]['price_edit']}}">
                        <input type="text" class="form-control input-table price3 number-input money-table cost-price" value="{{number_format($detail[$index]['cost_price'], 0)}}">
                    </td>
                    <td class="">
                        <input type="text" class="form-control input-table edit-price-edit number-input money-table" value="{{number_format($detail[$index]['price_edit'], 0)}}">
                    </td>
                    <td class="">
                        <input type="hidden" class="total-price-buy-product" readonly value="{{number_format($detail[$index]['total_price_tax'], 0)}}">
                        <input type="text" readonly class="form-control input-table price4 total-cost-price  number-input money-table" readonly value="{{number_format($detail[$index]['total_price'],0)}}">
                        <input type="hidden" class="updated-at" value="{{$detail[$index]['updated_at']}}">
                    </td>
                    <td class="" style="text-align: center;"><span><a class="btn-remove-product">削除</a></span></td>
                </tr>
            <?php
                }
            ?>
            <tr id="add_row">
            </tr>
            <?php
                if (is_array($detail)) {
    				$detail_count = count($detail);
				}else{
					$detail_count = 0;
				}
                for($index = 0;$index < $detail_count; $index++) {
                    // kiểm tra nếu sản phẩm có shipment code đó là khác rỗng và không nằm trong các shipment_code đã hiện thị rồi thì sẽ hiện thị trong bảng sản phẩm
                    if($detail[$index]['shipment_code'] != '' && in_array($detail[$index]['shipment_code'], $ship_code_dulicate) == 0)
                    {
            ?>
                <tr class="shipment-code" data-shipmentcode="{{$detail[$index]['shipment_code']}}">
                    <td class="disable"></td>
                    <td class="disable code-value-ship">{{$detail[$index]['shipment_code']}}</td>
                    <td></td>
                    <td class="text-stt">送料{{$index+1}}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><input type="text" class="text-left delivery-fee price2 form-control input-table number-input money-table" readonly value="{{number_format($detail[$index]['delivery_fee'], 0)}}"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php 
                }
                if(in_array($detail[$index]['shipment_code'], $ship_code_array) == 1)
                {
                    array_push($ship_code_dulicate, $detail[$index]['shipment_code']); // nếu shipment_code đó nằm trong mảng thì sẽ đưa vào mảng duplicate để kiếm tra không cho hiện thị shipment_code đó lên nữa
                }
             }
            ?>
            <!--  phí dịch vụ-->
            <tr class="fee-service">
                <td class="disable"></td>
                <td></td>
                <td></td>
                <td>手数料 </td>
                <td></td>
                <td>
                    <input style="text-align: center;" type="text" class="form-control input-table quantity-service number-input" value="{{$data_order[0]['quantity_service'] != null ? $data_order[0]['quantity_service']: 0 }}">
                </td>
                <td>
                    <input type="text" class="form-control input-table number-input price-service money-table" value="{{number_format($data_order[0]['price_service'],0)}}">
                </td>
                <td>
                    <input type="text" class="form-control input-table price2 number-input total-service money-table" readonly value="{{number_format($data_order[0]['total_service'],0)}}">
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <!-- add product -->
            <tr class="add-product">
                <td class=""></td>
                <td></td>
                <td><a class="a-href" id="add_product">+ 商品追加</a></td>
                <td></td>
                <td></td>
                <td></td>
                <td><input type="text" class="form-control input-table" readonly></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <!-- total -->
            <tr>
                <td colspan="6" class="label-form" style="text-align:right"><b>合計</b></td>
                <td class="total-price1 number-input">
                    <input type="text" readonly class="form-control input-table">
                </td>
                <td class="total-price2 number-input money-table">{{number_format($total_price_buy, 0)}}</td>
                <td class="total-price3 number-input"></td>
                <td></td>
                <td class="total-price4 number-input money-table">{{number_format($total_price_sale, 0)}}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <!-- bill list information  -->
    <div id="bill_list">
        <?php
            if (is_array($detail)) {
    			$detail_count = count($detail);
			}else{
				$detail_count = 0;
			}
            $arr_purchase_code = [];
            $list_detail = $detail_count;
            $duplicate_bill = []; 
            $id =0;
            for($index = 0; $index < $list_detail; $index++) { 
                if(in_array($detail[$index]['shipment_code'], $duplicate_bill) == 0){
                    $purchase_code = [];
                    $purchase_code['shipment_code'] = $detail[$index]['shipment_code']."";
                    $purchase_code['purchase_code'] = $detail[$index]['purchase_code'];
                    array_push($arr_purchase_code, $purchase_code);
                }
            }
            for($index = 0; $index < $list_detail; $index++) { 
            if(in_array($detail[$index]['shipment_code'], $duplicate_bill) == 0)
            {
                $id++;
        ?>
            <div class="card-infor-buyer bill-card bill-{{$id}}" data-detailid="{{$detail[$index]['id']}}" >
                <div class="title-card-search">
                    <a style="float:right; margin-top: 3px;" data-idbill="{{$id}}" data-indexbill="{{$detail[$index]['shipment_id']}}" class="remove-bill a-href">お届け先削除</a>
                    <?php 
                        $str_purchase_code = '';
                        foreach($arr_purchase_code as $value_pcode){
                            if($value_pcode['shipment_code'] == $detail[$index]['shipment_code']){
                                $str_purchase_code .= $value_pcode['purchase_code'].' | ';
                            }
                        }
                        $str_purchase_code = rtrim($str_purchase_code, ' | ');
                    ?>
                    <h5>お届け先情報 <span class="stt-bill">{{$id}}</span><span class="total-bill">/</span> &nbsp; &nbsp; 発注ID：<span class="purchase_code_in_ship">{{$str_purchase_code}}</span></h5>
                </div>
                <table class="table" data-sttbill="{{$index+1}}">
                    <tr>
                        <td width="10%" class="label-form"><b>送り状番号</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td width="10%">
                            @if($detail[$index]['shipment_code'] == '')
                                <input class="form-control input-table bill-id" data-shipcode-old="{{$detail[$index]['shipment_code']}}" data-deli-method-old="{{$detail[$index]['delivery_method']}}" data-ship-id="{{$detail[$index]['shipment_id']}}" value="{{$detail[$index]['shipment_code']}}" style="float: left; width: 55%" >
                                <button style="float:right;width: 45%;" class="btn-add-shipment">自動採番</button>
                            @else
                                <input class="form-control input-table bill-id" data-shipcode-old="{{$detail[$index]['shipment_code']}}" data-deli-method-old="{{$detail[$index]['delivery_method']}}" data-ship-id="{{$detail[$index]['shipment_id']}}" value="{{$detail[$index]['shipment_code']}}" style="float: left; width: 100%" >
                                <button style="float:right;width: 45%; display:none" class="btn-add-shipment">自動採番</button>
                            @endif
                        </td>
                        <td width="10%" class="label-form"><b>配送方法</b></td>
                        <td width="10%">
                            <select class="form-control delivery-method">
                                <option value="1" <?php $detail[$index]['delivery_method'] == 1 ? print 'selected' : print'' ?> >
                                    佐川急便
                                </option>
                                <option value="9" <?php $detail[$index]['delivery_method'] == 9 ? print 'selected' : print'' ?> >
                                    佐川急便(秘伝II)
                                </option>
                                <option value="2" <?php $detail[$index]['delivery_method'] == 2 ? print 'selected' : print'' ?> >
                                    ヤマト宅急便
                                </option>
                                <option value="3" <?php $detail[$index]['delivery_method'] == 3 ? print 'selected' : print'' ?> >
                                    ネコポス
                                </option>
                                <option value="4" <?php $detail[$index]['delivery_method'] == 4 ? print 'selected' : print'' ?> >
                                    コンパクト便
                                </option>
                                <option value="5" <?php $detail[$index]['delivery_method'] == 5 ? print 'selected' : print'' ?> >
                                    ゆうパック
                                </option>
                                <option value="6" <?php $detail[$index]['delivery_method'] == 6 ? print 'selected' : print'' ?> >
                                    ゆうパケット
                                </option>
                                <option value="7" <?php $detail[$index]['delivery_method'] == 7 ? print 'selected' : print'' ?> >
                                    代引き
                                </option>
                                <option value="8" <?php $detail[$index]['delivery_method'] == 8 ? print 'selected' : print'' ?> >
                                    その他
                                </option>
                            </select>
                        </td>
                        <td width="10%" class="label-form"><b>集荷日時</b></td>
                        <td width="10%">  
                            <div class="tooltip-error">
                                @if(!empty($detail[$index]['es_shipment_date']))
                                    <input class="form-control es-delivery-date" value="{{\Carbon\Carbon::parse($detail[$index]['es_shipment_date'])->format('Y/m/d')}}">
                                @else
                                    <input class="form-control es-delivery-date" value="">
                                @endif
                            </div>
                        </td>
                        <td width="10%">
                            <div class="tooltip-error"> 
                                <input class="form-control input-space input-table delivery-from-to-time" maxlength="5" placeholder="00-00" value="{{$detail[$index]['es_shipment_time']}}">
                            </div>
                            <!-- <select class="form-control delivery-from-to-time">
                                <option value="午前中" <?php echo $detail[$index]['es_shipment_time'] == '午前中' ? 'selected' : '' ?>>午前中</option>
                                <option value="12時～14時" <?php echo $detail[$index]['es_shipment_time'] == '12時～14時' ? 'selected' : '' ?>>12時～14時 </option>
                                <option value="14時～16時" <?php echo $detail[$index]['es_shipment_time'] == '14時～16時' ? 'selected' : '' ?>>14時～16時</option>
                                <option value="16時～18時" <?php echo $detail[$index]['es_shipment_time'] == '16時～18時' ? 'selected' : '' ?>>16時～18時</option>
                                <option value="18時～20時" <?php echo $detail[$index]['es_shipment_time'] == '18時～20時' ? 'selected' : '' ?>>18時～20時</option>
                                <option value="19時以降" <?php echo $detail[$index]['es_shipment_time'] == '19時以降' ? 'selected' : '' ?>>19時以降</option>
                            </select> -->
                        </td>
                        <td width="10%" class="label-form"><b>集荷先</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td width="10%">
                            <input class="form-control input-table supplied" readonly data-index="{{$index}}" data-toggle="modal" data-target="#modal_supplied" value="{{$detail[$index]['supplied']}}">
                            <input type="hidden" class="supplied-id" value="{{$detail[$index]['supplied_id']}}">
                        </td>
                    </tr>
                    <tr>
                        <td width="10%" class="label-form"><b>納品方法</b></td>
                        <td width="10%">
                            <select class="form-control delivery-way">
                                <option value="1" <?php echo $detail[$index]['delivery_way'] == 1 ? 'selected' :'' ?>>直送</option>
                                <option value="2" <?php echo $detail[$index]['delivery_way'] == 2 ? 'selected' :'' ?>>引取</option>
                                <option value="3" <?php echo $detail[$index]['delivery_way'] == 3? 'selected' :'' ?>>配達</option>
                                <option value="4" <?php echo $detail[$index]['delivery_way'] == 4 ? 'selected' :'' ?>>仕入</option>
                            </select>
                        </td>
                        <td width="10%" class="label-form"><b>発注ステータス</b></td>
                        <td width="10%">                        
                            <select id="purchase_status" class="form-control">
                                @foreach($purchase_status as $key => $value)
                                    <option value="{{$key+1}}" {{(($detail[$index]['purchase_status'] == ($key+1)) ? 'selected' : '')}}>{{$value}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td width="10%" class="label-form"><b>配達日時</b></td>
                        <td width="10%" >
                            <div class="tooltip-error">
                                @if(!empty($detail[$index]['shipment_date']))
                                    <input class="form-control delivery-date receive-date" value="{{\Carbon\Carbon::parse($detail[$index]['shipment_date'])->format('Y/m/d')}}">
                                @else
                                    <input class="form-control delivery-date receive-date" value="">
                                @endif
                            </div>
                        </td>
                        <td width="10%"> 
                            <!-- <div class="tooltip-error"> 
                                <input class="form-control input-space input-table receive-time" maxlength="5" placeholder="00-00" value="{{$detail[$index]['shipment_time']}}">
                            </div> -->
                            <select class="form-control receive-time">
                                <option value="0" <?php echo $detail[$index]['shipment_time'] == '0' ? 'selected' : '' ?>>----</option>
                                <option value="午前中" <?php echo $detail[$index]['shipment_time'] == '午前中' ? 'selected' : '' ?>>午前中</option>
                                <option value="12時～14時" <?php echo $detail[$index]['shipment_time'] == '12時～14時' ? 'selected' : '' ?>>12時～14時 </option>
                                <option value="14時～16時" <?php echo $detail[$index]['shipment_time'] == '14時～16時' ? 'selected' : '' ?>>14時～16時</option>
                                <option value="16時～18時" <?php echo $detail[$index]['shipment_time'] == '16時～18時' ? 'selected' : '' ?>>16時～18時</option>
                                <option value="18時～20時" <?php echo $detail[$index]['shipment_time'] == '18時～20時' ? 'selected' : '' ?>>18時～20時</option>
                                <option value="19時以降" <?php echo $detail[$index]['shipment_time'] == '19時以降' ? 'selected' : '' ?>>19時以降</option>
                            </select>
                        </td>
                        <td width="10%" class="label-form"><b>のし</b></td>
                        <td width="10%"><input class="form-control input-table gift-wrap" value="{{$detail[$index]['gift_wrap']}}"></td>
                    </tr>
                    <tr>
                        <td width="10%" class="label-form"><b>お届け先〒</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td width="13%">
                            <div class="tooltip-error">
                                <input class="form-control input-table ship-zip zip-input" maxlength="8" style="float: left; width: 55%" value="{{$detail[$index]['ship_zip']}}">
                                <button style="float:right;width: 45%" class="btn-search-zipcode-ship">住所入力</button>
                            </div>
                        </td>
                        <td width="10%" class="label-form"><b>お届け先TEL</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td width="10%">
                            <div class="tooltip-error">
                                <input class="form-control input-table ship-phone tel-input"  value="{{$detail[$index]['ship_phone']}}">
                            </div>
                        </td>
                        <td width="10%" class="label-form"><b>送料</b></td>
                        <td width="10%" colspan="2">
                            <span style="float:left; margin-top:3px;">¥</span>
                            <input class="delivery-fee number-input" style="width: 96%; text-align: right; border:none" value="{{number_format($detail[$index]['delivery_fee'],0)}}">
                        </td>
                        <td width="10%" class="label-form"><b>ラッピング</b></td>
                        <td width="10%"><input class="form-control input-table wrapping-paper-type" value="{{$detail[$index]['wrapping_paper_type']}}"></td>
                    </tr>
                    <tr>
                        <td width="10%" class="label-form"><b>お届け先名</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td colspan="3"><input class="form-control input-table ship-name1" value="{{$detail[$index]['ship_name1']}}"></td>
                        <td width="10%" class="label-form"><b>代引き請求</b></td>
                        <td colspan="2">
                            <span><input type="radio" name="pay_request" class="pay-request" value="1" <?php $detail[$index]['pay_request'] == '1' ? print 'checked': print'' ?>>
                                &nbsp; この荷物で請求する   &nbsp; &nbsp; &nbsp; 
                            </span>
                        </td>
                        <td width="10%" class="label-form"><b>メッセージカード</b></td>
                        <td><input class="form-control input-table message" value="{{$detail[$index]['message']}}"></td>
                    </tr>
                    <tr>
                        <td width="10%" class="label-form"><b>お届け先住所</b><sup style="color: red; font-weight: bold;">(*)</sup></td>
                        <td colspan="2"><input class="form-control input-table ship-address1 address-1" value="{{$detail[$index]['ship_address1']}}"></td>
                        <td colspan="3"><input class="form-control input-table ship-address2 address-2" value="{{$detail[$index]['ship_address2']}}"></td>
                        <td colspan="3"><input class="form-control input-table ship-address3 address-3" value="{{$detail[$index]['ship_address3']}}"></td>
                    </tr>
                </table>
            </div>
        <?php }
        if(in_array($detail[$index]['shipment_code'], $ship_code_array) == 1)
        {
            array_push($duplicate_bill, $detail[$index]['shipment_code']);
        }
            }?>
    </div>
    <!-- address shipment -->
    <div class="card-address-shipment">
        <div class="title-card-search">
            <h5>新規お届け先追加 </h5>
        </div>
        <div class="body-card">
            <a id="add_bill" class="a-href">＋ お届け先追加</a>
        </div>
    </div>
    <!-- comment -->
    <div class="card-address-shipment" style="border-bottom: 0px !important">
        <div class="title-card-search">
            <h5>備考 </h5>
        </div>
        <!-- <div class="body-card" contenteditable="true" style="outline:0px;" class="comment" id="comment"> -->
        <textarea class="comment" id="comment" style="width:100%; margin:0px; border-left:2px solid;border-right:2px solid;border-bottom:2px solid"><?php echo ($data_order[0]['comments'] != null) ? $data_order[0]['comments'] : "" ?></textarea>
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
    var urlUpdate = "{{url('order/ajax-edit-order')}}";
    var urlCopyOrder = "{{url('order/ajax-copy-order')}}";
    var urlSearch = "{{url('order/search-order')}}";
    var urlDeleteOrder = "{{url('order/ajax-delete-order')}}";
    var urlGetShipmentCode = "{{url('shipment/ajax-get-shipment-code')}}";
    var totalBill = "{{$id}}";
    var url_export_one = "{{ url('purchase/ajax-export-purchase') }}";
    var url_check_shipcode = "{{ route('ajax_check_shipcode') }}";
    var purchase_status = <?php echo  ((!empty($purchase_status)) ? json_encode($purchase_status) : null) ?>;
    var listIdDetail = [];
    var listSupplied = [];
    <?php 
        if(!empty($detail))
        {
            foreach($detail as $value)
            {
                ?>
                var data = {};
                data = {
                    'detail_id':  "<?=$value['id'] ?>",
                    'sup': "<?=$value['supplied_id'] ?>",
                    'name_supplied': "<?=$value['supplied'] ?>"
                };
                listSupplied.push("<?=$value['supplied_id'] ?>");
                listIdDetail.push(data);
                <?php
            }
        }
    ?>
</script>
@stop