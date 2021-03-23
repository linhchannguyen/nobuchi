@extends('layouts.master')
@section('css')
    <link href="{{ asset('css/user/purchase_confirm.css') }}" rel="stylesheet">
@endsection
@section('script')
    <script>
        var token_header = $('meta[name="csrf-token"]').attr('content');
        var length_table = "<?= $data_length ?>";
        var supplied = "<?= $supplied ?>";
        var data_old = <?php echo json_encode($data_old) ?>;
        var url_update_order_detail = "{{ url('supplier/ajax-update-order-detail') }}";
        var url_export_purchase = "{{ url('purchase/ajax-export-purchase') }}";
        var url_update_status_purchase = "{{ url('supplier/ajax-update-status-purchase') }}";
    </script>
@endsection
@section('content')
    <div class="purchase-confirm">  
        <div class="purchase-confirm-search-direct">
            <div class="purchase-confirm-title">
                <label><b>直接検索</b></label>
            </div>
            <div class="purchase-confirm-form">                
                <div class="form-group">                        
                <form action="{{ route('search-purchase') }}" method="get" accept-charset="UTF-8">
                    @if($message != '')
                    <div class="error-date">
                        <li style="list-style: none; color: red;"> {{ $message }}</li>
                    </div>
                    @endif
                    <label for="">検索日時</label>
                    @if($date_to != '')
                    <input type="text" name="date" class="datepicker date-from-hover" value="{{ $date }}"> ～ <input type="input" name="date_to" class="datepicker date-to-hover" value="{{ $date_to }}">
                    @else
                    <input type="text" name="date" class="datepicker date-from-hover" value="{{ $date }}"> ～ <input type="input" name="date_to" class="datepicker date-to-hover" value="{{ $date }}">                                            
                    @endif
                    <span style="margin-left: 100px; margin-right: 50px;" class="check_flag_p_status">
                        <input type="checkbox" class="flag_p_status" name="flag_p_status_1" value="1" {{($flag_p_status_1 == 1) ? 'checked' : ''}}> 未出荷
                    </span>
                    <span style="margin-right: 50px;" class="check_flag_p_status">
                        <input type="checkbox" class="flag_p_status" name="flag_p_status_2" value="2" {{($flag_p_status_2 == 2) ? 'checked' : ''}}> 出荷済
                    </span>
                    <span class="check_flag_p_status">
                        <input type="checkbox" class="flag_p_status" name="flag_p_status_3" value="3" {{($flag_p_status_3 == 3) ? 'checked' : ''}}> キャンセル
                    </span>
                    <span style="margin-left: 100px;"><button type="submit" class="btn-search">検索</button></span>
                </form>
                </div>                
            </div>
        </div>
        <div class="row process">
            <div class="col-md-4">
                @if($data_length > 0)
                    <span>{{ $data->appends(Request::all())->links() }}</span>
                @endif 
            </div>
            <div class="col-md-8">
                <div class="btn-process">
                    <span><a class="btn-print">チェックした発注書をダウンロード</a></span> &nbsp; &nbsp;
                    <span><a class="btn-save">入力内容を保存</a></span> &nbsp; &nbsp;
                    <span><a class="btn-update">出荷済にする</a></span>
                </div>
            </div>
        </div>
        <?php 
            $style = '';
            if(!isset($_GET['page']) && $data_length > 0){
                if($data->lastPage() <= 1){
                    $style = '30px';
                }
            }else {
                $style = '30px';
            }
        ?>
        <div class="payable-search" style="margin-top: <?php echo $style; ?>">
            <table class="table table-result">
                <thead class="text-center vertical-thead">
                    <th width="5%">番号</th>
                    <th width="6%">発注日</th>
                    <th width="8%">納品日<br>(出荷日)</th>
                    <th width="12%">発注ID</th>
                    <th width="10%">送り状番号</th>
                    <th width="7%">対応状況</th>
                    <th width="15%">商品明細</th>
                    <th width="15%">お届け先</th>
                    <th width="5%">個数</th>
                    <th width="6%">金額</th>
                    <th width="8%">訂正金額</th>                    
                    @if($data_length > 0)
                    <th width="3%" class="text-center" style="padding-right: 10px;"><input style="vertical-align: middle;" type="checkbox" id="check_all"></th>
                    @endif
                </thead>
                <tbody>
                @if($data_length > 0)
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
                    @foreach($data as $key => $value)
                    <?php
                        $bg_color = '';
                        if ($value['p_status'] == 4){
                            $bg_color = '#FCE4D6';
                        }else if ($value['p_status'] == 5){
                            $bg_color = '#BFBFBF';
                        }
                    ?>
                    <tr style="background-color: <?php echo $bg_color ?>;">
                        <td class="text-center" style="vertical-align: middle">                        
                            <input type="hidden" class="o-detail-id" value="{{ $value['o_detail_id'] }}">
                            <input type="hidden" class="o-updated-at" value="{{ $value['o_updated_at'] }}">
                            <input type="hidden" class="o-status" value="{{ $value['status'] }}">
                            <input type="hidden" class="o-code" value="{{ $value['o_order_id'] }}">
                            <input type="hidden" class="p-id" value="{{ $value['p_id'] }}">
                            <input type="hidden" class="p-status" value="{{ $value['p_status'] }}">
                            <input type="hidden" class="od-tax" value="{{ $value['od_tax'] }}">
                            <input type="hidden" class="o-id" value="{{ $value['o_id'] }}">
                            <input type="hidden" class="p-code" value="{{ $value['p_code'] }}">
                            <input type="hidden" class="p-date" value="{{ date('Y/m/d', strtotime($value['p_created_at'])) }}">
                            <input type="hidden" class="delivery-method" value="{{$value['delivery_method']}}">
                            <input type="hidden" class="supplied-id" value="{{ $value['sup_id'] }}">
                            <input type="hidden" class="old-deliv-date" value="{{ $value['od_deliv_date'] }}">
                            <input type="hidden" class="old-bill-number" value="{{ $value['shipment_code'] }}">
                            {{ ($key + 1 + $page) }}
                        </td>
                        <td class="text-center" style="vertical-align: middle">{{ date('Y/m/d', strtotime($value['p_created_at'])) }}</td>
                        <td class="text-center" style="vertical-align: middle">
                            <div class="tooltip-error">
                                <input style="width: 100%; background-color: <?php echo $bg_color ?>;" type="text" class="input-width text-center deliv-date datepicker {{($value['p_status'] == 4 || $value['p_status'] == 5) ? 'no-allowed' : 'check_one'}}" name="deliv-date" value="{{ date('Y/m/d', strtotime($value['od_deliv_date'])) }}"/>
                                <span class="tt-deliv-date">入力した日付は無効です</span>
                            </div>
                        </td>
                        <td class="text-center" style="vertical-align: middle">{{ $value['p_code'] }}</td>
                        <td class="text-center" style="vertical-align: middle">
                            <input type="hidden" class="ship-id" value="{{$value['ship_id']}}">
                            <div class="tooltip-error">
                                <input style="background-color: <?php echo $bg_color ?>;" type="text" class="text-center bill-number {{($value['p_status'] == 4 || $value['p_status'] == 5) ? 'no-allowed' : 'check_one'}}" name="bill-number" value="{{$value['shipment_code']}}"/>
                                <span class="tt-bill-number-empty">送り状番号を選択してください。</span>
                            </div>
                        </td>
                        <td style="vertical-align: middle; text-align: center">                         
                            <?php
                                if($value['p_status'] == 1 || $value['p_status'] == 2 || $value['p_status'] == 3)
                                {
                                    echo '未出荷';
                                }elseif ($value['p_status'] == 4)
                                {
                                    echo '出荷済';
                                }elseif ($value['p_status'] == 5)
                                {
                                    echo 'キャンセル';
                                }
                            ?>
                        </td>
                        <td style="vertical-align: middle"> {{ $value['od_product_name'] }} </td>
                        <td style="vertical-align: middle"> {{ $value['ship_name1'] }}</td>
                        <td class="text-center" style="vertical-align: middle"> {{ $value['od_quantity'] }} </td>
                        <td class="text-center" style="vertical-align: middle"><a class="total-price"> {{ number_format($value['od_total_price'], 0, '.', ',') }} </a></td>
                        <td class="text-center" style="vertical-align: middle">
                            <div class="tooltip-error">
                                <div class="tooltip-error">
                                    <input type="hidden" class="old-price-edit" value="{{number_format($value['p_price_edit'], 0, '.', ',')}}">
                                    <input style="background-color: <?php echo $bg_color ?>;" type="text" class="input-width price-edit {{($value['p_status'] == 4|| $value['p_status'] == 5) ? 'no-allowed' : 'check_one'}}" value="{{ number_format($value['p_price_edit'], 0, '.', ',') }}">
                                <span class="tt-price-edit">無効な金額</span>
                            </div>
                        </td>
                        <td class="text-center" style="vertical-align: middle">
                            <input style="vertical-align: middle" type="checkbox" name="check_one" class="check_one">
                        </td>
                    </tr>
                    @endforeach
                @else                
                    <tr>
                        <td style="color: red;" class="text-center" colspan="11">検索条件に該当するデータがありません。</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
    <script src="{{ asset('js/user/purchase_confirm.js') }}" ></script>
@endsection