@extends('layouts.master')
@section('content')
<!-- load css imports  -->
<link href="{{ asset('css/imports/index.css') }}" rel="stylesheet">
<script src="{{ asset('js/imports/index.js') }}" ></script>
<script>
    var url_master_import = "{{route('imoport-master')}}";
</script>
<!-- end  class="container-fluid"-->
<div>
    <div class="card-search">
        <div class="title-card-search">
          <h5>
            受注取込
          </h5>  
        </div>
        <div class="body-card-import">
            <div class="row">
                <div class="col-12">                
                    <div class="row row-content">
                        <div class="col-md-2 col-lg-2 col-sm-4">
                            <div class="label-import">
                                <label for="">取込方法</label>    
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-8 col-sm-8" style="margin-left: -40px">
                            <div class="row">
                                <div class="col-12">
                                    <form>
                                        <div class="form-check form-check-inline">
                                            <span class="check_flag_search">
                                                <input type="radio" class="form-check-input flag_search" name="option_import" value="1" checked> API連携
                                            </span>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <span class="check_flag_search">
                                                <input type="radio" class="form-check-input flag_search" name="option_import" value="1"> ECキューブから同期
                                            </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- date -->
                    <div class="row row-content">
                        <div class="col-md-2 col-lg-2 col-sm-4">
                            <div class="label-import">
                                <label for=""> 取込期間(受注日)</label>    
                            </div>
                        </div>
                        <div class="col-md-10 col-lg-10 col-sm-8 content-select" style="margin-left: -40px">
                            <div class="row">
                                <div class="col-md-4 col-lg-4 col-sm-12">
                                    <input type="text" class="date-jp form-control" id="date_from"/>
                                </div>
                                <span style="font-size: 16px">～</span>
                                <div class="col-md-4 col-lg-4 col-sm-12">
                                    <input type="text" class="date-jp form-control" id="date_to"/>
                                </div>
                            </div>       
                            <div class="error-date" style="display: none;">
                                <li class="error-text-date" style="list-style: none; color: red;"></li>
                            </div>
                        </div>
                    </div>
                    <!-- site type -->
                    <div class="row row-content">
                        <div class="col-md-2 col-lg-2 col-sm-4">
                            <div class="label-import">
                                <label for="">取込ECサイト</label>    
                            </div>
                        </div>
                        <div class="col-md-10 col-lg-10 col-sm-8" style="margin-left: -40px">
                            <div class="row">
                                <div class="col-12">
                                    <form>
                                        <div class="form-check form-check-inline check_flag_stage">
                                            <input type="checkbox" class="form-check-input flag_stage" name="site_type" value="4"> Amazonひろしま
                                        </div>
                                        <div class="form-check form-check-inline check_flag_stage">
                                            <input type="checkbox" class="form-check-input flag_stage" name="site_type" value="5"> Amazonワールド
                                        </div>
                                        <div class="form-check form-check-inline check_flag_stage">
                                            <input type="checkbox" class="form-check-input flag_stage" name="site_type" value="2"> 楽天
                                        </div>
                                        <div class="form-check form-check-inline check_flag_stage">
                                            <input type="checkbox" class="form-check-input flag_stage" name="site_type" value="3"> Yahoo
                                        </div>
                                        <div class="form-check form-check-inline check_flag_stage">
                                            <input type="checkbox" class="form-check-input flag_stage" name="site_type" value="1"> 自社
                                        </div>
                                        <div class="form-check form-check-inline check_flag_stage">
                                            <input type="checkbox" class="form-check-input flag_stage" name="site_type" value="6"> AmazonひろしまFBA
                                        </div>
                                        <div class="form-check form-check-inline check_flag_stage">
                                            <input type="checkbox" class="form-check-input flag_stage" name="site_type" value="7"> AmazonワールドFBA
                                        </div>
                                        <div class="form-check form-check-inline check_flag_stage">
                                            <input type="checkbox" class="form-check-input flag_stage" name="site_type" value="8"> Amazonリカー
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- điều kiện import -->
                    <div class="row row-content">
                        <div class="col-md-2 col-lg-2 col-sm-4">
                            <div class="label-import">
                                <label for="">取込条件</label>    
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-8 col-sm-8" style="margin-left: -40px">
                            <div class="row">
                                <div class="col-md-9">
                                    <form>
                                        <div class="form-check form-check-inline check_flag_stage">
                                            <input type="checkbox" class="form-check-input flag_stage" name="import_condition" value="1"> お急ぎ便のみ
                                        </div>
                                        <div class="form-check form-check-inline check_flag_stage">
                                            <input type="checkbox" class="form-check-input flag_stage" name="import_condition" value="2"> 取込エラーの注文のみ
                                        </div>
                                        <div class="form-check form-check-inline check_flag_stage">
                                            <input type="checkbox" class="form-check-input flag_stage" name="import_condition" value="3"> 予約注文のみ
                                        </div>
                                    </form>
                                </div>    
                            </div>
                        </div>                            
                        <!-- button import -->
                        <div class="col-md-4 col-lg-2 col-sm-12">
                            <button class="btn btn-search-order" id="btn_import" style="padding: 5px 50px;">受注取込</button>
                        </div>
                    </div>
                    <!-- option import -->
                    <!-- <div class="row row-content">
                        <div class="col-md-2 col-lg-2 col-sm-4">
                            <div class="label-import">
                                <label for="">取込オプション</label>    
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-8 col-sm-8" style="margin-left: -40px">
                            <div class="row">
                                <div class="col-12">
                                    <form>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input" name="option_import" value="1">
                                            <label class="form-check-label" for="exampleCheck1">取り込んだ注文に要対応フラグを付ける</label>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 col-sm-12">
                            <button class="btn btn-search-order" id="btn_import" style="padding: 5px">この条件で今すぐ取込</button>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    
    <div class="card-search" style="margin-top: 10px;">
        <div class="title-card-search">
          <h5>
            マスタ取込
          </h5>  
        </div>
        <div class="body-card-import">
            <div class="row">
                <div class="col-12">                
                    <div class="row row-content" style="margin-bottom: 0px !important;">
                        <div class="col-md-2 col-lg-2 col-sm-4">
                            <div class="label-import">
                                <label for="">マスタ取込機能</label>    
                            </div>
                        </div>
                        <!-- <div class="col-md-6 col-lg-8 col-sm-8" style="margin-left: -40px">
                            <div class="row">
                                <div class="col-12">
                                    <form>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input" name="option_import" value="1" checked>
                                            <label class="form-check-label" for="exampleCheck1">商品マスタ</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input" name="option_import" value="1">
                                            <label class="form-check-label" for="exampleCheck1">仕入先マスタ</label>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div> -->
                        <!-- button import -->
                        <div class="col-md-6 col-lg-8 col-sm-8" style="margin-left: -40px">
                            <button class="btn btn-search-order" id="btn_import_master" style="padding: 5px 42px">マスタ取込</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- tiêu đề của import table -->
    <div class="title-import-table">
        <h5>成功件数/取込件数</h5>
    </div>
    <!-- table import -->
    <div class="row">
        <div class="col-12">
            @if(!empty($data))
                {{ $data->appends(Request::all())->links() }}
            @endif
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" class="title-table" width="10%">取込時間</th>
                        <th scope="col" class="title-table" width="10%">取込条件</th>
                        <th scope="col" class="title-table" width="10%">ECサイト</th>
                        <th scope="col" class="title-table" width="10%">取込件数</th>
                        <th scope="col" class="title-table" width="10%">成功件数</th>
                        <th scope="col" class="title-table" width="10%">重複件数</th>
                        <th scope="col" class="title-table" width="10%">エラー件数</th>
                        <th scope="col" class="title-table" width="7%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $value)
                    <?php $site_type= '';?>
                    <!-- check loại import 1:自社, 2:楽天 3:Yahoo, 4: Amazonひろしま, 5: Amazonワールド , 6:AmazonひろしまFBA, 7:AmazonワールドFBA, 8: Amazonリカー  -->
                    @if($value['type'] == 1)
                        <?php $site_type = '自社';?>
                    @endif
                    @if($value['type'] == 2)
                        <?php $site_type = '楽天';?>
                    @endif
                    @if($value['type'] == 3)
                        <?php $site_type = 'Yahoo';?>
                    @endif
                    @if($value['type'] == 4)
                        <?php $site_type = 'Amazonひろしま';?>
                    @endif
                    @if($value['type'] == 5)
                        <?php $site_type = 'Amazonワールド';?>
                    @endif
                    @if($value['type'] == 6)
                        <?php $site_type = 'AmazonひろしまFBA';?>
                    @endif
                    @if($value['type'] == 7)
                        <?php $site_type = 'AmazonワールドFBA';?>
                    @endif
                    @if($value['type'] == 8)
                        <?php $site_type = 'Amazonリカー';?>
                    @endif
                    <tr style="text-align: center;">
                        <td>{{$value['date_import']}}</td>
                        <td>
		                    @if($value['import_set_from'] != '' || $value['import_set_to'] != '' )
		                    	{{$value['import_set_from']}}～{{$value['import_set_to']}}
		                    @endif
		                </td>
                        <td>{{$site_type}}</td>
                        <td>{{$value['number_order']}}</td>
                        <td>{{$value['number_success']}}</td>
                        <td>{{$value['number_duplicate']}}</td>
                        <td>
                        @if($value['number_error'] > 0)
                            <span style="color:red">{{$value['number_error']}}</span>
                        @else
                            <span>{{$value['number_error']}}</span>
                        @endif
                        </td>
                        <td>
                            @if($value['number_error'] > 0)
                                <button class="btn re-import">再取り込み </button>
                                <input type="hidden" class="import-id" value="{{$value['id']}}">
                                <input type="hidden" class="site-type" value="{{$value['type']}}">
                                <input type="hidden" class="error-id" value="{{$value['error_id']}}">
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop