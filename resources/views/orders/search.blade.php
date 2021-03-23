@extends('layouts.master')
@section('content')
<!-- load css and js order search -->
<link href="{{ asset('css/orders/searchorder.css') }}" rel="stylesheet">
<script src="{{ asset('js/orders/search.js') }}" ></script>
<script src="{{ asset('js/orders/setconditions.js') }}" ></script>
<script>
        var url_export_one = "{{ url('purchase/ajax-export-purchase') }}";
</script>
<!-- end   class="container-fluid"-->
<div>
    <div class="card-seard">
        <div class="title-card-search">
            <h5>直接検索</h5>
        </div>
        <div class="body-card-search">
            <div class="col-md-12 col-lg-12 col-sm-12">
                <form class="form-radio-custom">
                    <div class="row">
                        <div class="col-md-1 col-lg-1 col-sm-6 col-sx-6">
                            <label for="id_order" class="label-rimac">受注ID</label>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6 col-sx-6">
                            <input type="text" class="form-control" id="order_id_input">
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6 col-sx-6 btn-search">
                            <button type="button" class="btn btn-search-order" id="search_order">注文内容編集 </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="title-card-search">
            <h5>検索範囲</h5>
        </div>
        <div class="body-card-search">
            <div class="row">
                <div class="col-md-12 col-lg-12 col-sm-12">
                    <form class="form-radio-custom">
                        <div class="col-md-12 col-lg-12 col-sm-12">
                            <span class="check_flag_search"><input class="flag_search" type="radio" name="type_date" value="order_created"> &nbsp; 受注日 &nbsp; &nbsp; &nbsp;</span>
                            <span class="check_flag_search"><input class="flag_search" type="radio" name="type_date" value="date_import"  checked="checked"> &nbsp; 取込日 &nbsp; &nbsp; &nbsp; </span>
                            <span class="check_flag_search"><input class="flag_search" type="radio" name="type_date" value="date_purchased"> &nbsp; 発注日 &nbsp; &nbsp; &nbsp; </span>
                            <span class="check_flag_search"><input class="flag_search" type="radio" name="type_date" value="ship_date"> &nbsp; 出荷完了日 &nbsp; &nbsp; &nbsp; </span>
                            <span class="check_flag_search"><input class="flag_search" type="radio" name="type_date" value="ship_schedule_from"> &nbsp; 集荷日時 &nbsp; &nbsp; &nbsp; </span>
                            <span class="check_flag_search"><input class="flag_search" type="radio" name="type_date" value="ship_schedule_to"> &nbsp; 配達日時 &nbsp; &nbsp; &nbsp; </span>
                        </div>
                    </form>
                </div>
                <div class="col-md-12 col-lg-12 col-sm-12">
                    <form class="form-radio-custom">
                        <div class="col-md-12 col-lg-12 col-sm-12">
                            <div class="row row-date">
                                <div class="col-lg-1 col-md-2 col-sm-6">
                                    <label for="label-title">検索日時</label>
                                </div>
                                <div class="col-md-10">    
                                    <div class="row"> 
                                        <div class="col-md-3 col-lg-3 col-sm-12">
                                            <input type="text" id="date_from" name="date_from" class="form-control date-from date-jp datepicker">
                                        </div>
                                        <div class="col-md-0.2">
                                            <span class="date-search" > ～ </span>
                                        </div>
                                        <div class="col-md-3  col-lg-3 col-sm-12">
                                            <input type="text" id="date_to" name="date_to" class="form-control date-to date-jp datepicker">
                                        </div>
                                        <div style="float: right">
                                            <button type="button" class="btn btn-search-order" id="select_day">今日</button>
                                            <button type="button" class="btn btn-search-order" id="select_yesterday">今日～昨日</button>
                                        </div>   
                                    </div>            
                                    <div class="error-date" style="display: none;">
                                        <li class="error-text-date" style="list-style: none; color: red;"></li>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="title-card-search">
            <a href="#" class="dropdown-extend-search" style="float: right" id="buttonExtend"><i class="fas fa-caret-square-down"></i></a>
            <h5>絞り込み条件</h5>
        </div>
        <!-- card-extend -->
        <div class="body-card-search" style="border-bottom: none;">
            <div class="row">
                <div class="col-md-12 col-lg-12 col-sm-12">
                    <form class="form-radio-custom">
                        <div class="col-md-12 col-lg-12 col-sm-12">
                            <div class="row row-input">
                                <div class="col-md-2 col-sm-2">
                                    <label for="label-title">発注ID</label>
                                </div>
                                <div class="col-md-2 col-sm-2">
                                    <input type="text" id="purchase_code">
                                </div>
                                <div class="col-md-2 col-sm-2">
                                    <label for="label-title">品名</label>
                                </div>
                                <div class="col-md-2 col-sm-2">
                                    <input type="text" id="name_product">
                                </div>
                            </div>
                            <div class="row row-input" style="margin-bottom: -32px;">
                                <div class="col-md-2 col-sm-2">
                                    <label for="label-title">注文主名</label>
                                </div>
                                <div class="col-md-2 col-sm-2">
                                    <input type="text" id="buyer_name">
                                </div>
                                <div class="col-md-2 col-sm-2">
                                    <label for="label-title">お届け先名</label>
                                </div>
                                <div class="col-md-2 col-sm-2">
                                    <input type="text" id="ship_name">
                                </div>                   
                                <div class="col-md-4 col-sm-4" style="position: relative; top: -40px;">
                                    <button type="button" id="btn_search" class="btn btn-search-order">検索</button>
                                </div>
                            </div>  
                        </div>
                        <!-- support -->
                        <div class="search-extend"><!-- input -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row row-input">
                                    <div class="col-md-2 col-sm-2">
                                        <label for="label-title">注文主TEL</label>
                                    </div>
                                    <div class="col-md-2 col-sm-2">
                                        <input type="text" id="buyer_tel" class="tel-input">
                                    </div>
                                    <div class="col-md-2 col-sm-2">
                                        <label for="label-title">お届け先TEL</label>
                                    </div>
                                    <div class="col-md-2 col-sm-2">
                                        <input type="text" id="ship_tel" class="tel-input">
                                    </div>
                                </div>
                                <div class="row row-input">
                                    <div class="col-md-2 col-sm-2">
                                        <label for="label-title">注文主住所 </label>
                                    </div>
                                    <div class="col-md-2 col-sm-2">
                                        <input type="text" id="buyer_address">
                                    </div>
                                    <div class="col-md-2 col-sm-2">
                                        <label for="label-title">お届け先住所</label>
                                    </div>
                                    <div class="col-md-2 col-sm-2">
                                        <input type="text" id="ship_address">
                                    </div>
                                    <div class="col-md-1 col-sm-2">
                                        <label for="label-title">分類指定</label>
                                    </div>
                                    <div class="col-md-2 col-sm-2">
                                        <select class="form-control form-control-sm category-select" id="category">
                                            <option value="">選択してください</option>
                                            <option value="1">大分類</option>
                                            <option value="2">中分類</option>
                                            <option value="3">小分類</option>
                                            <option value="4">その他1</option>
                                            <option value="5">その他2</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row row-input">
                                    <div class="col-md-2 col-sm-2">
                                        <label for="label-title">集荷先</label>
                                    </div>
                                    <div class="col-md-2 col-sm-2">
                                        <input type="text" id="supplied" value="">
                                        <span class="search-sup search-supplier" style="position: absolute; top: 2px; right: 25px;" data-toggle="modal" data-target="#modal_supplied"><i class="fa fa-search"></i></span>
                                    </div>
                                    <div class="col-md-2 col-sm-2">
                                        <label for="label-title">SKU</label>
                                    </div>
                                    <div class="col-md-2 col-sm-2">
                                        <input type="text" id="sku">
                                        <span class="search-sup search-sku" style="position: absolute; top: 2px; right: 25px;" data-toggle="modal" data-target="#modal_product"><i class="fa fa-search"></i></span>
                                    </div>
                                    <div class="col-md-3 col-sm-4">
                                        <select class="form-control form-control-sm product-select" id="product_id">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- information NCC -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                        <label for="label-title">集荷先チェック </label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-10">
                                        <span class="check_flag_stage">
                                            <input class="flag_stage" type="radio" checked name="searchNCC" value="all"> &nbsp; すべて  &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage">
                                            <input class="flag_stage" type="radio" name="searchNCC" value="yes"> &nbsp; 集荷先情報有り  &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage">
                                            <input class="flag_stage" type="radio" name="searchNCC" value="no"> &nbsp; 集荷先情報無し  &nbsp; &nbsp; &nbsp; </span>
                                    </div>
                                </div>
                            </div>               
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                        <label for="label-title">受注ステータス </label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-10">
                                    <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="status_support" value="1"> &nbsp; 新規注文 &nbsp; &nbsp; &nbsp; </span>
                                    <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="status_support" value="2"> &nbsp; 入金待ち &nbsp; &nbsp; &nbsp; </span>
                                    <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="status_support" value="3"> &nbsp; 受注処理中 &nbsp; &nbsp; &nbsp; </span>
                                    <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="status_support" value="4"> &nbsp; 要確認 &nbsp; &nbsp; &nbsp; </span>
                                    <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="status_support" value="5"> &nbsp; 保留中 &nbsp; &nbsp; &nbsp; </span>
                                    <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="status_support" value="6"> &nbsp; 完了 &nbsp; &nbsp; &nbsp; </span>
                                    <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="status_support" value="7"> &nbsp; キャンセル &nbsp; &nbsp; &nbsp; </span>
                                    </div>
                                </div>
                            </div>
                            <!-- confirm flag -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                        <label for="label-title">発注ステータス </label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-10">                                        
                                        @foreach($purchase_status as $p_key => $p_status)
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="flag_confirm" value="{{$p_key+1}}"> &nbsp; {{$p_status}} &nbsp; &nbsp; &nbsp; </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <!-- delivery -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                        <label for="label-title">配送方法 </label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-10">
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_method" value="1"> &nbsp; 佐川急便 &nbsp;&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_method" value="9"> &nbsp; 佐川急便(秘伝II) &nbsp;&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_method" value="2"> &nbsp; ヤマト宅急便 &nbsp;&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_method" value="3"> &nbsp; ネコポス &nbsp;&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_method" value="4"> &nbsp; コンパクト便 &nbsp;&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_method" value="5"> &nbsp; ゆうパック &nbsp;&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_method" value="6"> &nbsp; ゆうパケット &nbsp;&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_method" value="7"> &nbsp; 代引き &nbsp;&nbsp;</span> <!-- Tiền daibiki -->
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_method" value="8"> &nbsp; その他 &nbsp;&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                            <!-- method shipment  -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                            <label for="label-title">納品方法 </label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-10">
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_way" value="1"> &nbsp; 直送 &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_way" value="2"> &nbsp; 引取 &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_way" value="3"> &nbsp; 配達 &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="delivery_way" value="4"> &nbsp; 仕入 &nbsp; &nbsp; &nbsp; </span>
                                    </div>
                                </div>
                            </div>
                            <!-- support customer -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                        <label for="label-title">顧客対応  </label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-10">
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="support_cus" value="1"> &nbsp; 代引注文 &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="support_cus" value="2"> &nbsp; 配達日時有  &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="support_cus" value="3"> &nbsp; 備考有 &nbsp; &nbsp; &nbsp; </span>
                                        <!-- <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="support_cus" value="4"> &nbsp; ギフト対応 &nbsp; &nbsp; &nbsp; </span> -->
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="support_cus" value="5"> &nbsp; 沖縄・離島 &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="support_cus" value="6"> &nbsp; 同梱  &nbsp; &nbsp; &nbsp; </span>
                                        <!-- <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="support_cus" value="7"> &nbsp; 重複注文 &nbsp; &nbsp; &nbsp; </span> -->
                                    </div>
                                </div>
                            </div>
                            <!-- type order -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                        <label for="label-title">ECサイト   </label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-10">
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="site_type" value="4"> &nbsp; Amazonひろしま&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="site_type" value="5"> &nbsp; Amazonワールド&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="site_type" value="2"> &nbsp; 楽天&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="site_type" value="3"> &nbsp; Yahoo&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="site_type" value="1"> &nbsp; 自社&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="site_type" value="6"> &nbsp; AmazonひろしまFBA&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="site_type" value="7"> &nbsp; AmazonワールドFBA&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="site_type" value="8"> &nbsp; Amazonリカー&nbsp;</span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="site_type" value="9"> &nbsp; その他&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                            <!-- orther -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                        <label for="label-title">その他 </label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-10">
                                        <!-- <span><input type="checkbox" name="" value="0"> &nbsp; 送り状未採番  &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="orther" value="8"> &nbsp; 送料無料 &nbsp; &nbsp; &nbsp; </span> -->
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="orther" value="6"> &nbsp; 冷蔵  &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input class="flag_stage" type="checkbox" name="orther" value="7"> &nbsp; 冷凍  &nbsp; &nbsp; &nbsp; </span>
                                    </div>
                                </div>
                            </div>                    
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="card-table">
        <!-- table  table-bordered table-control-->
        <!-- control -->
        <div class="title-card-control">
        <a class="dropdown-extend-search extend-control" style="float: right"><i class="fas fa-caret-square-down"></i></a>
            <!-- <b>操作</b> -->
            <h5>操作</h5>
        </div>
        <div class="card-body-control controll-extend" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <button style="margin-left: 0px; width: 250px;" type="button" class="btn btn-search-order" id="create_order">新規受注登録</button>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="row control-select">
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <label for="">注文登録・削除</label>
                        </div>
                        <div class="col-md-6 col-lg-6 col-sm-6">
                            <select class="form-control form-control-sm" id="control_order">
                                <option> </option>
                                <option value="create">新規注文登録 </option>
                                <option value="copy">選択中の注文をコピー  </option>
                                <option value="delete">選択中の商品を削除 </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-search-order" id="btn_control_order">更新</button>
                        </div>
                    </div>
                    <!-- tình trạng hỗ trợ -->
                    <div class="row control-select">
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <label for="">受注ステータス</label>
                        </div>
                        <div class="col-md-6 col-lg-6 col-sm-6">
                            <select class="form-control form-control-sm" id="update_status_support_value">
                                <option></option>
                                <option value="1">新規注文</option>
                                <option value="2">入金待ち</option>
                                <option value="3">受注処理中</option>
                                <option value="4">要確認</option>
                                <option value="5">保留中</option>
                                <option value="6">完了</option>
                                <option value="7">キャンセル</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-search-order" id="btn_update_status_support_value">更新</button>
                        </div>
                    </div>
                    <!-- cờ xác nhận -->
                    <div class="row control-select">
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <label for="">発注ステータス</label>
                        </div>
                        <div class="col-md-6 col-lg-6 col-sm-6">
                            <select class="form-control form-control-sm" id="update_flag_value">
                                <option></option>
                                @foreach($purchase_status as $p_key => $p_status)
                                    <option value="{{$p_key+1}}">{{$p_status}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-search-order" id="btn_update_flag_value">更新</button>
                        </div>
                    </div>
                </div>
                <!-- phương thức giao hàng -->
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="row control-select">
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <label for="">配送方法</label>
                        </div>
                        <div class="col-md-6 col-lg-6 col-sm-6">
                            <select class="form-control form-control-sm" id="update_delivery_method_value">
                                <option></option>
                                <option value="1">佐川急便</option>
                                <option value="9">佐川急便(秘伝II)</option>
                                <option value="2">ヤマト宅急便</option>
                                <option value="3">ネコポス</option>
                                <option value="4">コンパクト</option>
                                <option value="5">ゆうパック</option>
                                <option value="6">ゆうパケット</option>
                                <option value="7">代引き</option>
                                <option value="8">その他</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-search-order" id="btn_update_delivery_method">更新</button>
                        </div>
                    </div>
                    <!-- cách giao hàng -->
                    <div class="row control-select">
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <label for="">納品方法</label>
                        </div>
                        <div class="col-md-6 col-lg-6 col-sm-6">
                            <select class="form-control form-control-sm" id="update_delivery_way_value">
                                <option> </option>
                                <option value="1">直送 </option>`
                                <option value="2">引取  </option>
                                <option value="3">配達 </option>
                                <option value="4">仕入 </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-search-order" id="btn_update_delivery_way">更新</button>
                        </div>
                    </div>
                    <div class="row control-select">
                        <div class="col-md-3 col-lg-3 col-sm-6">
                            <label for="">データ出力</label>
                        </div>
                        <div class="col-md-6 col-lg-6 col-sm-6">
                            <select class="form-control form-control-sm" id="export_data">
                                <option></option>
                                <option value="purchase">発注一覧表</option>
                                <option value="purchase_pdf">PDF発注一覧表</option>
                                <option value="pack_intro">発注明細・梱包指示書</option>
                                <option value="pack_intro_pdf">PDF発注明細・梱包指示書</option>
                                <option value="bill_sagawa">佐川用送り状データ</option>
                                <option value="bill_sagawa_II">佐川急便(秘伝II)送り状発行データ</option>
                                <option value="bill_yamoto">ヤマト用送り状データ</option>
                                <option value="bill_yupack">ゆうパック用送り状データ</option>
                                <option value="notified_amazon">Amazon用出荷通知データ</option>
                                <option value="notified_rakuten">楽天用出荷通知データ</option>
                                <option value="notified_yahoo">Yahoo用出荷通知データ </option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <button href="#" class="btn btn-search-order" target="_tbank" id="btn_export_data">ダウンロード</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- card table results -->
    <div class="card-table-results">
            <!-- table -->
            <table class="table rp-table-search" id="table_reponses">
            <thead>
                <th scope="col" class="title-table text-center" width="5%">番号</th>
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
                <?php if(!empty($list_orders['data']))
                {
                    $order_code = $list_orders['data'][0]['order_code'];
                    $color_arr = [
                        true => '#FFF',
                        false => '#dcdcdc'
                    ];
                    $color = true;
                    foreach($list_orders['data'] as $key => $item )  {                        
                        if($order_code !== $item['order_code']){
                            $color = !$color;
                            $order_code = $item['order_code'];
                        }
                        ?>
                <tr style="background-color: <?php echo $color_arr[$color] ;?>">
                    <td class="center-txt" colspan="middle" scope="row">
                        <?php echo($key +1); ?>
                        <input type="hidden" class="id-order" data-id="{{$item['order_code']}}">
                        <input type="hidden" class="detail-id" data-detail="{{$item['detail_id']}}">
                        <input type="hidden" class="supplied-id" data-supplied="{{$item['supplied_id']}}" data-suppliedName="{{$item['supplied_name']}}">
                    </td>                    
                    <td class="center-txt" scope="col"><?php echo $item['purchase_code']; ?></td>
                    <td class="center-txt" scope="col"><a href="{{url('order/edit-order/'.$item['order_code'])}}" target="_blank"><?php echo $item['order_code']; ?></a></td>
                    <td class="middle-txt" scope="col"> <?php echo $item['buyer_name1']; echo $item['buyer_name2']; ?></td>
                    <td class="center-txt" scope="col">
                    <?php
                            if($item['status'] == 1)
                            {
                                echo '新規注文';
                            } elseif ($item['status'] == 2)
                            {
                                echo '入金待ち';
                            } elseif ($item['status'] == 3)
                            {
                                echo '受注処理中';
                            } elseif ($item['status'] == 4)
                            {
                                echo '要確認';
                            } elseif ($item['status'] == 5)
                            {
                                echo '保留中';
                            } elseif ($item['status'] == 6)
                            {
                                echo '完了';
                            } elseif ($item['status'] == 7)
                            {
                                echo 'キャンセル';
                            }
                    ?>
                    </td>
                    <td class="center-txt" scope="col">
                        <?php
                            if($item['p_status'] == 1)
                            {
                                echo '未処理';
                            } elseif ($item['p_status'] == 2)
                            {
                                echo '発注済、印刷済';
                            } elseif ($item['p_status'] == 3)
                            {
                                echo '送り状作成済';
                            }elseif ($item['p_status'] == 4)
                            {
                                echo '出荷通知済';
                            }elseif ($item['p_status'] == 5)
                            {
                                echo 'キャンセル';
                            }
                        ?>
                    </td>
                    <td class="top-txt" scope="col">{{$item['product_name']}}</td>
                    <td class="center-txt" scope="col">{{empty($item['shipment_code']) ?'':$item['shipment_code'] }}</td>
                    <td class="center-txt" scope="col">{{empty($item['supplied_name']) ?'':$item['supplied_name'] }}</td>
                    <td class="center-txt" scope="col">{{empty($item['es_shipment_date'])? '': date('Y/m/d', strtotime($item['es_shipment_date']))}}</td>
                    <td class="center-txt" scope="col" style="font-size: 16px;">
                        <input type="hidden" class="process-detail-id" value="{{$item['detail_id']}}">
                        <input type="hidden" class="purchase-code" value="{{$item['purchase_code']}}">
                        <input type="hidden" class="shipment-code" value="{{$item['shipment_code']}}">
                        <input type="hidden" class="shipment-id" value="{{$item['shipment_id']}}">
                        <input type="hidden" class="shipment-deli" value="{{$item['shipment_deli']}}">
                        <input type="hidden" class="order-code" value="{{$item['order_code']}}">
                        <input type="hidden" class="order-site-type" value="{{$item['site_type']}}">
                        <input type="hidden" class="product-id" value="{{$item['product_id']}}">    
                        <input type="hidden" class="purchase-id" value="{{$item['purchase_id']}}">    
                        <input type="checkbox" class="checkbox">
                    </td>
                </tr>
                <?php }
                } else {?>
                <!-- <tr>
                    <td class="center-txt" colspan="11">指定条件で該当する件数が多いため、検索条件を絞って下さい。</td>
                </tr> -->
                <?php }?>
            </tbody>
        </table>
    </div>
    <!-- 
        - modal input sku  include modal in components
        - parameter:+ List category ($categories_product) set from controller.
                    + call modal with  data-toggle="modal" data-target="#modal_product" of input
                    + Get sku from tag input id ="sku"
                    + ex: <input type="text" id="sku" data-toggle="modal" data-target="#modal_product">
    -->
    @include('components.modalSearchSkuProduct')
    <!-- end -->
    <!--
        - modal search supplied
        + call modal with  data-toggle="modal" data-target="#modal_supplied" of input
        + Get supplied from tag input id ="supplied"
        + ex: <input type="text" id="supplied" data-toggle="modal" data-target="#modal_supplied">
    -->
    @include('components.modalSearchSupplied')
    <!-- end -->
</div>
<script>
const requests = '<?php echo json_encode(app('request')->input());?>';
var urlGetCategory = "{{url('/product/categories')}}";
var urlUpdateorder = "{{url('/order/ajax-update-order')}}";
var urlSearch = "{{url('order/search-order')}}";
var urlCopyOrder = "{{url('order/ajax-copy-order')}}";
var urlDeleteOrder = "{{url('order/ajax-delete-order')}}";
var url_send_shipment = "{{ url('shipment/ajax-export-shipment') }}";
var url_export_shipment_notification = "{{ url('shipment-notification/ajax-export-shipment-notification') }}";
var url_export_sagawa_shipment  = "{{url('shipment/ajax-export-sagawa-shipment')}}";
var url_create_order = "{{url('/order/create')}}";
var url_send_shipment_II = "{{ url('shipment/ajax-export-shipment-II') }}";
var count_data = "<?php echo (!empty($list_orders['data'])) ? count($list_orders['data']) : 0 ?>";
</script>
@stop