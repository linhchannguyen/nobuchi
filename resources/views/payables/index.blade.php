@extends('layouts.master')
@section('css')
    <link href="{{ asset('css/payable/payable.css') }}" rel="stylesheet">
@endsection
@section('script')
    <script>
        var url_search = "{{ url('payable/ajax-money-owed-to-suppliers') }}";
        var token_header = $('meta[name="csrf-token"]').attr('content');
    </script>
@endsection
@section('content')
    <div class="payable">  
        <div class="shipment-stage">
            <div class="shipment-title">
                <label><b>検索範囲</b></label>
            </div>
            <div class="shipment-form">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-5">
                            <label for="">検索年度 &nbsp; &nbsp;</label>
                            <select class="date-payable" style="width: 100px;">
                            </select>
                            &nbsp; &nbsp; &nbsp; &nbsp;
                            <span>
                                <span class="check_flag_search"><input class="flag_search" type="radio" name="fee" value="1" checked>税込表示</span>&nbsp; &nbsp;
                                <span class="check_flag_search"><input class="flag_search" type="radio" name="fee" value="0">税抜表示</span>
                            </span>
                        </div>
                        <div class="col-md-2">
                            <span class="btn-right" style="float: left"><a class="btn-search-payable">検索</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="shipment-table" style="display: none;">
            <table class="table table-statistical">
            </table>
        </div>
    </div>
    <script src="{{ asset('js/payables/payable.js') }}" ></script>
@endsection