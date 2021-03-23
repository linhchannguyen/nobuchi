@extends('layouts.master')
@section('css')
    <link href="{{ asset('css/user/supplier_permission.css') }}" rel="stylesheet">
    <!-- show year and month, display date of month -->
    <style>    
        .ui-datepicker-calendar {
            display: none;
        }
    </style>
@endsection
@section('script')
    <script>
        var token_header = $('meta[name="csrf-token"]').attr('content');
        var url_search = "{{ url('supplier/ajax-search-purchase') }}";
    </script>
@endsection
@section('content')
    <div class="payable">  
        <div class="supplier-stage">
            <div class="supplier-title">
                <label><b>検索範囲</b></label>
            </div>
            <div class="supplier-form">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">                 
                            <div class="error-date" style="display: none;">
                                <li class="error-text-date" style="list-style: none; color: red;"></li>
                            </div>
                            <label for="">検索月度 &nbsp; &nbsp;</label>
                            <input type='text' class="datepicker search-date" name="search-date" />
                        </div>
                        <div class="col-md-4">
                            <span class="btn-right" style="float: right"><a class="btn-search">検索</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="supplier-table">
            <div class="row">
                <div class="col-md-10">
                    <table class="table table-result">
                        
                    </table>
                </div>
            </div>
        </div>
    </div>    
    <script src="{{ asset('js/user/supplier_permission.js') }}" ></script> 
@stop