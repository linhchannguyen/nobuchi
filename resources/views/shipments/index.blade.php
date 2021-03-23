@extends('layouts.master')
@section('css')
    <link href="{{ asset('css/shipments/shipment.css') }}" rel="stylesheet">
@endsection
@section('script')
    <script>
        var url_search = "{{ url('shipment/ajax-search-shipment') }}";
        var url_send_shipment = "{{ url('shipment/ajax-export-shipment') }}";
        var url_send_shipment_II = "{{ url('shipment/ajax-export-shipment-II') }}";
        var url_get_list_supplier_by_delivery_method = "{{ url('shipment/ajax-get-list-supplier-by-delivery-method') }}";
        var url_export_sagawa_shipment  = "{{url('shipment/ajax-export-sagawa-shipment')}}";
        var url_export_purchase = "{{ url('purchase/ajax-export-purchase') }}";
        var url_export_shipment_bill = "{{ url('shipment/ajax-export-shipment-bill') }}";
        var token_header = $('meta[name="csrf-token"]').attr('content');
    </script>
@endsection
@section('content')
    <div class="shipment">
        <div class="shipment-search">
            <div class="shipment-title">
                <label><b>検索範囲</b></label>
            </div>
            <div class="shipment_form">
                <div id="form_shipment">
                    <div class="form-group">
                        <div class="error-require" style="display: none;">
                            <li class="error-text-require" style="list-style: none; color: red;"></li>
                        </div>
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
                        <input type="text" name="date_from" class="date-from datepicker"> ～ <input type="input" name="date_to" class="date-to datepicker">
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
            <div class="shipment_form">
                <div id="form_stage">
                    <span class="check_flag_stage" style="margin-right: 90px;"><input class="flag_stage" type="checkbox" name="checkbox_stage" value="1"> &nbsp; 要確認を除外する</span>
                    <span class="check_flag_stage" style="margin-right: 90px;"><input class="flag_stage" type="checkbox" name="checkbox_stage" value="2"> &nbsp; 保留中を除外する</span>
                    <span class="check_flag_stage">
                        <input type="hidden" class="check-update-status" value="0">
                        <input class="flag_stage" type="checkbox" name="checkbox_stage" value="3"> &nbsp; 発注ステータスを送り状作成済に変更する
                    </span>
                </div>
            </div>
        </div>
        <div class="search-result" style="display: none;">
            <!-- <div class="shipment-table">
                <table class="table table-status">
                </table>
            </div> -->
            <div class="shipment-table-search">
                <table class="table table-shipment-method">
                </table>
            </div>   
        </div>     
    </div>  
    <script src="{{ asset('js/shipments/shipment.js') }}" ></script> 
@stop