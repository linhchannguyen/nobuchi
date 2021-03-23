@extends('layouts.master')
@section('css')
    <link href="{{ asset('css/shipments/shipment_notifi.css') }}" rel="stylesheet">
@endsection
@section('script')
    <script>
        var url_search = "{{ url('shipment-notification/ajax-search-shipment') }}";
        var url_get_list_supplier_by_site_type = "{{ url('shipment-notification/ajax-get-list-supplier-by-site-type') }}";
        var url_export_purchase = "{{ url('purchase/ajax-export-purchase') }}";
        var export_notification_amazon = "{{ url('shipment-notification/export-notification-amazon') }}";
        var url_export_shipment_notification = "{{ url('shipment-notification/ajax-export-shipment-notification') }}";
        var url_export_shipment_bill = "{{ url('shipment/ajax-export-shipment-bill') }}";
        var url_import_ship = "{{route('import-shipbill')}}";
        var token_header = $('meta[name="csrf-token"]').attr('content');
    </script>
@endsection
@section('content')
    <div class="shipment">
        <div class="shipment-search">
            <div class="shipment-title">
                <label><b>検索範囲</b></label>
            </div>
            <div class="shipment-form">
                <div id="form_shipment">
                    <div class="form-group">
                        <span class="check_flag_search"><input class="flag_search" type="radio" name="radio_date" value="0"> &nbsp; 受注日 &nbsp; &nbsp; &nbsp; </span>
                        <span class="check_flag_search"><input class="flag_search" type="radio" name="radio_date" value="1" checked> &nbsp; 取込日 &nbsp; &nbsp; &nbsp; </span>
                        <span class="check_flag_search"><input class="flag_search" type="radio" name="radio_date" value="2"> &nbsp; 発注日 &nbsp; &nbsp; &nbsp; </span>
                        <span class="check_flag_search"><input class="flag_search" type="radio" name="radio_date" value="3"> &nbsp; 出荷完了日 &nbsp; &nbsp; &nbsp; </span>
                        <span class="check_flag_search"><input class="flag_search" type="radio" name="radio_date" value="4"> &nbsp; 集荷日時 &nbsp; &nbsp; &nbsp; </span>
                        <span class="check_flag_search"><input class="flag_search" type="radio" name="radio_date" value="5"> &nbsp; 配達日時</span>
                    </div>
                    <div class="form-group">                                              
                        <div class="error-date" style="display: none;">
                            <li class="error-text-date" style="list-style: none; color: red;"></li>
                        </div>
                        <label for="">検索日時</label>
                        <input type="text" name="date_from" class="date-from datepicker"> ～ <input type="text" name="date_to" class="date-to datepicker">
                        <div class="btn-right search-center">
                            <span class="btn-right" style="margin-top: -5px;"><a class="btn-search">検索</a></span>
                            <span class="btn-right" style="margin-right: 25px;"><a class="btn-search-from-to">今日～昨日</a></span>
                            <span class="btn-right" style="margin-right: 25px;"><a class="btn-search-today">今日</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="shipment-stage">
            <div class="shipment-title">
                <label><b>データダウンロード時の追加操作 </b></label>
            </div>
            <div class="shipment-check">
                <div id="form_stage">
                    <span class="check_flag_stage" style="margin-right: 90px;"><input class="flag_stage" type="checkbox" name="checkbox_stage" value="1"> &nbsp; 要確認を除外する</span>
                    <span class="check_flag_stage" style="margin-right: 90px;"><input class="flag_stage" type="checkbox" name="checkbox_stage" value="2"> &nbsp; 保留中を除外する</span>
                    <span class="check_flag_stage" style="margin-right: 90px;"><input class="flag_stage" type="checkbox" name="checkbox_stage" value="3"> &nbsp; 発注ステータスを出荷通知済に変更する</span>
                </div>
            </div>
        </div>
        <div class="shipment-import">
            <div class="shipment-import-title">
                <label><b>送り状番号取込</b></label>
            </div>
            <div class="shipment-import-sel">
                <div id="form_stage">                 
                    <form class="import-form" method="post" enctype="multipart/form-data" action="{{route('import-shipbill')}}">
                        {{ csrf_field() }}
                        <span>送り状番号ファイル選択</span>
                        <span style="margin-right: 10px;"><input type="text" class="file-name" value="" style="width: 300px; cursor: not-allowed;" readonly></span>       
                        <span style="margin-right: 15px;">
                            <button type="button" class="btn-sel-file" onclick="document.getElementById('getFile').click()">参照...</button>
                            <input type='file' name="result_file" id="getFile" style="display:none">
                        </span>
                        <span style="margin-right: 25px;"><button type="button" class="btn-import">CSV取込</button></span>
                        @if(\Session::has('error'))
                            <span class="alert alert-danger" role="alert">
                                {{\Session::get('error')}}
                            </span>
                        @elseif(\Session::has('success'))
                            @if(count(\Session::get('list_error')) == 0)
                                <span class="alert alert-success" role="alert">
                                    {{\Session::get('success')}}
                                </span>
                            @endif
                            <?php $str_error = ''; ?>
                            @if(\Session::get('list_error'))
                                @foreach(\Session::get('list_error') as $val_err)
                                    <?php $str_error .= $val_err . '、'; ?>
                                @endforeach
                                <?php $str_error = rtrim($str_error, '、'); ?>
                                &nbsp;&nbsp;
                                <span class="alert alert-danger" role="alert">下記の行目だけは取込めません。再確認してください。行目：
                                    {{ $str_error }}
                                </span>
                            @endif
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <div class="search-result" style="display: none;">
            <!-- <div class="shipment-table">
                <table class="table table-status">
                </table>
            </div> -->
            <div class="shipment-table-search">
                <table class="table table-site-type">
                </table>
            </div>     
        </div>   
    </div>   

    <script src="{{ asset('js/shipments/shipment_notifi.js') }}" ></script>
@endsection