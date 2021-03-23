@extends('layouts.master')
@section('css')
    <link href="{{ asset('css/payable/payable_detail.css') }}" rel="stylesheet">
@endsection
@section('script')
    <script>
        var url_update_order_detail = "{{ url('payable-detail/ajax-update-order-detail') }}";
        var url_search_order = "{{ url('payable-detail/ajax-search-order-detail') }}";
        var url_order_payable = "{{ url('payable-detail/ajax-order-payable') }}";
        var url_export_purchase = "{{ url('purchase/ajax-export-purchase') }}";
        var token_header = $('meta[name="csrf-token"]').attr('content');
        var length_table = "<?php echo count($payabledetail) ?>";
        var month_detail = "<?php echo $month ?>";
        var year_detail = "<?php echo $year ?>";
        var supplier_id_detail = "<?php echo $supplier_id ?>";
    </script>
@endsection
@section('content')
    <div class="payable">  
        <div class="payable-search-direct">
            <div class="payable-title">
                <label><b>直接検索</b></label>
            </div>
            <div class="payable-form">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-5">
                                <label for="">発注ID</label> &nbsp; &nbsp; &nbsp; &nbsp;
                                <input type="text" name="id_purchase" value="{{(isset($_GET['purchase_id']) ? $_GET['purchase_id'] : '')}}" class="id-purchase" style="width: 300px;">
                        </div>
                        <div class="col-md-4">
                                <label for="">受注ID</label> &nbsp; &nbsp; &nbsp; &nbsp;
                                <input type="text" name="id_order" value="{{(isset($_GET['order_id']) ? $_GET['order_id'] : '')}}" class="id-order" style="width: 300px;">
                        </div>
                        <div class="col-md-3">
                            <span style="float: right;"><a class="btn-search-direct">直接検索</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="payable-stage">
            <div class="payable-title">
                <label><b>検索範囲</b></label>
            </div>
            <div class="payable-form">
                <div class="form-group">
                    <form action="{{ route('search-payable-detail') }}" method="get" accept-charset="UTF-8" class="form-search">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="">年度 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</label>
                                    <select class="date-payable" name="year" value="{{old('year')}}" style="width: 200px;">
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="">月度 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</label>
                                    <select class="month-payable" name="month" style="width: 200px;">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">仕入・発送先 &nbsp; &nbsp;</label>
                                    <select class="supplier" name="supplier_id" style="width: 200px;">
                                    <option value="-1"></option>
                                        @if(!empty($suppliers))
                                            @foreach($suppliers as $value)
                                            <option <?php if($value['id'] == 0) echo 'style="display: none;"' ?> value="{{ $value['id'] }}"
                                            <?php if($value['id'] == $supplier_id)
                                                echo "selected";
                                            ?>>
                                            {{ $value['id'] }}: {{ $value['name'] }}
                                            </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <span style="float: right;"><button type="submit" class="btn-search-payable">検索</button></span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="payable-search-mani">
            <div class="payable-title">
                <label><b>操作</b></label>
            </div>
            <div class="payable-form">
                <div class="form-group">
                    <label for="">データ出力</label> &nbsp;
                    <select class="sel_download" style="width: 200px;">
                        <option value="0"></option>
                        <option value="1">発注一覧表</option>
                        <option value="22">PDF発注一覧表</option>
                        <option value="2">発注明細・梱包指示書</option>
                        <option value="33">PDF発注明細・梱包指示書</option>
                        <option value="3">支払通知書</option>
                        <option value="44">PDF支払通知書</option>
                    </select>&nbsp; &nbsp;
                    <span><a class="btn-search-download">ダウンロード</a></span> &nbsp; &nbsp;
                    <!-- <span style="float: right;"><a class="btn-search-save">入力内容を保存</a></span> -->
                </div>
            </div>
        </div>
        <div class="table-fee">
            <div class="row">
                <div class="col-md-4">
                    <table class="table table-fee-f">
                        <thead>
                            <th width="10%">税抜金額合計</th>
                            <th width="10%">消費税</th>
                            <th width="10%">税込金額合計</th>
                        </thead>
                        <tbody>
                        @if(count($payabledetail) > 0)
                            <?php
                                $total_price = 0;
                                $total_price_tax = 0;
                            ?>
                            @foreach($payabledetail as $value)
                                <?php
                                    $total_price += $value['od_total_price']; 
                                    $total_price_tax += $value['od_total_price_tax']; 
                                ?>
                            @endforeach
                            <td class="text-center">{{ number_format($total_price, 0, '.', ',') }}</td>
                            <td class="text-center">{{ number_format($total_price_tax -  $total_price, 0, '.', ',') }}</td>
                            <td class="text-center">{{ number_format($total_price_tax, 0, '.', ',') }}</td>
                        @else
                            <td style="color: red;" class="text-center" colspan="3">検索条件に該当するデータがありません。</td>
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="col-md-8">
                </div>
            </div>
        </div>        
        <div class="row process">
            <div class="col-md-4">
                @if(count($payabledetail) > 0)
                    <span style="float: left;">{{ $payabledetail->appends(Request::all())->links() }}</span>
                @endif
            </div>
            <div class="col-md-8">
                <div class="btn-process">
                    <span style="float: right; position: relative; top: 65px"><a class="btn-search-save" style="padding: 15px;">入力内容を保存</a></span>
                </div>
            </div>
        </div>
        <?php 
            $style = '';
            if(!isset($_GET['page'])){
                if($payabledetail->lastPage() <= 1){
                    $style = '30px';
                }
            }
        ?>
        <div class="payable-search" style="margin-top: <?php echo $style; ?>">
            <table class="table table-fee-l">
                <thead>
                    <th width="5%">番号</th>
                    <th width="6%">発注日</th>
                    <th width="8%">納品日<br>(集荷日時)</th>
                    <th width="14%">受注ID</th>
                    <th width="14%">発注ID</th>
                    <th width="15%">商品明細</th>
                    <th width="15%">お届け先</th>
                    <th width="5%">個数</th>
                    <th width="8%">仕入金額</th>
                    <th width="8%">訂正金額</th>
                    @if(count($payabledetail) > 0)
                    <th width="2%" class="text-center" style="padding-right: 10px;"><input style="vertical-align: middle;" type="checkbox" id="check_all"></th>
                    @endif
                </thead>
                <tbody>
                @if(count($payabledetail) > 0)
                    <?php 
                        $page = 0;
                        if(isset($_GET['page'])){
                            if($_GET['page'] > 1){
                                for($i = 1; $i < $_GET['page']; $i++){
                                    $page += 50;
                                }
                            }
                        }
                    ?>
                    @foreach($payabledetail as $key => $value)
                    <tr>
                        <td class="text-center" style="vertical-align: middle">
                            <input type="hidden" class="supplied-id" value="{{ $value['supplied_id'] }}">
                            <input type="hidden" class="supplied-name" value="{{ $value['supplied'] }}">
                            <input type="hidden" class="o-id" value="{{ $value['o_id'] }}">
                            <input type="hidden" class="o-code" value="{{ $value['o_order_id'] }}">
                            <input type="hidden" class="o-detail-id" value="{{ $value['o_detail_id'] }}">
                            <input type="hidden" class="o-cost-price" value="{{ $value['od_cost_price'] }}">
                            <input type="hidden" class="o-updated-at" value="{{ $value['o_updated_at'] }}">
                            <input type="hidden" class="p-id" value="{{ $value['p_id'] }}">
                            <input type="hidden" class="p-code" value="{{ $value['p_code'] }}">
                            <input type="hidden" class="od-tax" value="{{ $value['od_tax'] }}">
                            <input type="hidden" class="ship-id" value="{{$value['ship_id']}}">
                            <input type="hidden" class="delivery-method" value="{{$value['delivery_method']}}">
                            <input type="hidden" class="old-deliv-date" value="{{ date('Y/m/d', strtotime($value['od_deliv_date'])) }}">
                            {{ $key + 1 + $page}}
                        </td>
                        <td style="vertical-align: middle">
                            @if(isset($value['p_created_at']))
                                {{ date('Y/m/d', strtotime($value['p_created_at'])) }}
                            @endif
                        </td>
                        <td class="text-center" style="vertical-align: middle">
                        <div class="tooltip-error">
                            <input type="text" class="input-width datepicker text-center deliv-date" name="deliv-date" value="{{ date('Y/m/d', strtotime($value['od_deliv_date'])) }}"/>
                            <span class="tt-deliv-date">無効な日付です。再入力してください。</span>
                        </div>
                        </td>
                        <td class="text-center" style="vertical-align: middle"><a href="order/edit-order/{{ $value['o_order_id'] }}" target="_blank"><u>{{ $value['o_order_id'] }}</u></a></td>
                        <td class="text-center" style="vertical-align: middle">{{ $value['p_code'] }}</td>
                        <td class="text-center" style="vertical-align: middle">{{ $value['od_product_name'] }}</td>
                        <td class="text-left" style="vertical-align: top">{{ $value['od_ship_address1'].$value['od_ship_address2'].$value['od_ship_address3']}}</td>
                        <td class="text-center" style="vertical-align: middle">{{ $value['od_quantity'] }}</td>
                        <td class="text-center" style="vertical-align: middle"><a class="total-price">{{ number_format($value['od_total_price'], 0) }}</a></td>
                        <td class="text-center" style="vertical-align: middle">                        
                        <div class="tooltip-error">
                            <input type="hidden" class="old-price-edit" value="{{ number_format($value['p_price_edit'], 0, '.', ',') }}">
                            <input type="text" class="input-width price-edit" value="{{number_format($value['p_price_edit'], 0, '.', ',')}}">
                            <span class="tt-price-edit">無効な金額</span>
                        </div>
                        </td>
                        <td class="text-center" style="vertical-align: middle"><input class="check_one" style="vertical-align: middle" type="checkbox" name="check_one"></td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="color: red;" class="text-center" colspan="10">検索条件に該当するデータがありません。</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
    <script src="{{ asset('js/payables/payable_detail.js') }}" ></script>
@endsection