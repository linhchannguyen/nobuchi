@extends('layouts.master')
@section('content')
<!-- load css and js order search -->
<link href="{{ asset('css/masters/users/index.css') }}" rel="stylesheet">
<script src="{{ asset('js/masters/users/index.js') }}" ></script>
<!-- end   class="container-fluid"-->
<div>
    <?php
        $count_checkbox = 0;
        if(isset($_GET['user-permission'])){
            $count_checkbox = count($_GET['user-permission']);
        }
    ?>
    <h4>ユーザー管理</h4>
    <div class="card-seard">
        <div class="title-card-search">
            <h5>検索範囲</h5>
        </div>
        <div class="body-card-search">
            <div class="col-md-12 col-lg-12 col-sm-12">
                <form action="{{route('user-list')}}" method="get" class="user-search-form">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="row">    
                                <div class="col-md-2 col-lg-2 col-sm-6 col-sx-6">
                                    <label style="position: relative; top: 7px;" for="id_order">ユーザー名</label>
                                </div>
                                <div class="col-md-3 col-lg-3 col-sm-6 col-sx-6">
                                    <input type="text" class="form-control search-user-name" name="search-user-name" value="{{(isset($_GET['search-user-name']) ? $_GET['search-user-name']: '')}}">
                                    <div class="error-search-user-name" style="display: none;">
                                        <li class="error-text-search-user-name" style="list-style: none; color: red;"></li>
                                    </div>
                                </div>
                                <div class="col-md-2 col-lg-2 col-sm-6 col-sx-6">
                                    <label style="position: relative; top: 7px;" for="id_order">仕入先名</label>
                                </div>
                                <div class="col-md-5 col-lg-5 col-sm-6 col-sx-6">
                                    <input type="text" class="form-control search-supplier-name" name="search-supplier-name" value="{{(isset($_GET['search-supplier-name']) ? $_GET['search-supplier-name']: '')}}">
                                    <div class="error-supplier-name" style="display: none;">
                                        <li class="error-text-supplier-name" style="list-style: none; color: red;"></li>
                                    </div>
                                </div>
                            </div>
                            <div style="margin-top: 10px;">
                                <label for="id_order">権限</label>
                                <span class="check_flag_user_permission" style="margin: 0 15px 0 80px;"><input type="checkbox" class="flag_user_permission" name="user-permission[]" value="0"
                                    @if(isset($_GET['user-permission']))
                                        @if(in_array(0, $_GET['user-permission']))
                                            checked
                                        @endif
                                    @endif
                                    >&nbsp;管理者</span>
                                <span class="check_flag_user_permission" style="margin-right: 15px;"><input type="checkbox" class="flag_user_permission" name="user-permission[]" value="1"
                                    @if(isset($_GET['user-permission']))
                                        @if(in_array(1, $_GET['user-permission']))
                                            checked
                                        @endif
                                    @endif
                                    >&nbsp;運用者</span>
                                <span class="check_flag_user_permission"><input type="checkbox" class="flag_user_permission" name="user-permission[]" value="2"
                                    @if(isset($_GET['user-permission']))
                                        @if(in_array(2, $_GET['user-permission']))
                                            checked
                                        @endif
                                    @endif
                                    >&nbsp;仕入先</span>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <button type="submit" class="btn-user-process btn-search-user">検索</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 25px;">
        <div class="col-md-4">            
            @if(!empty($user_list))
                <div>{{ $user_list->appends(Request::all())->links() }}</div>
            @endif
        </div>
        @if($user_login == 0)
        <div class="col-md-8">            
            <div style="text-align: right">
                <button class="btn btn-user btn-user-update" id="btn_add_user">ユーザー追加</button>
            </div>
        </div>
        @endif
    </div>
    <!-- table user -->
    <table class="table" id="tb_users">
        <thead>
            <tr>
                <th width="15%" scope="col" class="title-table">ユーザー名</th>
                <th width="8%" scope="col" class="title-table">権限</th>
                <th width="20%" scope="col" class="title-table">仕入先名</th>
                <th width="45%" scope="col" class="title-table">最終更新履歴</th>
                <th width="12%" scope="col" class="title-table">操作</th>
            </tr>
        </thead>
        <tbody>
            @if(count($user_list) > 0)
                @foreach($user_list as $key => $user)
                <tr class="user">
                    <td>
                        {{$user['name']}}
                        <input type="hidden" class="user-id" value="{{$user['id']}}">
                        <input type="hidden" class="user-name" value="{{$user['name']}}">
                        <input type="hidden" class="user-type" value="{{$user['type']}}">
                    </td>
                    <!-- <td>
                        <input type="password" class="user-pass" value="dummypass">
                        <input type="hidden" class="user-pass-check" value>
                    </td> -->
                    <td class="text-center">
                        <?php echo $user['type'] === 0 ? '管理者' : ''?>
                        <?php echo $user['type'] === 1 ? '運用者' : ''?>
                        <?php echo $user['type'] === 2 ? '仕入先' : ''?>
                    </td>
                    <td class="select-supplier">
                        @if($user['type'] == 2)
                            {{$user['supplier_name']}}
                            <input type="hidden" class="supplied-name" value="{{$user['supplier_name']}}">
                            <input type="hidden" class="supplied-id" value="{{$user['supplier_id']}}">
                        @endif
                    </td>
                    <td>
                        <?php echo $user['process_user'] ?>
                    </td>
                    <td class="text-center">
                        <div 
                            @if($user['type'] != 0)
                                style="text-align:center"
                            @else
                            @endif
                            >
                            @if($user_login == 0)
                                <span><button class="btn" id="btn_update_user">編集</button></span>
                                @if($user['type'] != 0)
                                    <span><button class="btn" id="btn_delete_user">削除</button></span>
                                @endif
                            @endif
                            @if($user_login == 1)
                                @if($user['type'] == 1 && $user_name == $user['name'])
                                    <span><button class="btn" id="btn_update_user">編集</button></span>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td class="text-center" colspan="5">検索条件に該当するデータはありません。</td>
                </tr>
            @endif
        </tbody>
    </table>
    <h4>更新履歴 </h4>
    <!-- nội dung update -->
    <div class="card-seard">
        <div class="title-card-search">
            <h5>検索範囲</h5>
        </div>
        <div class="body-card-search">
            <div class="col-md-12 col-lg-12 col-sm-12">
                <form class="form-radio-custom">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="row">    
                                <div class="col-md-2 col-lg-2 col-sm-6 col-sx-6">
                                    <label style="position: relative; top: 7px;" for="id_order">ユーザー名</label>
                                </div>
                                <div class="col-md-3 col-lg-3 col-sm-6 col-sx-6">
                                    <input type="text" class="form-control history-user-name">        
                                    <div class="error-user-name" style="display: none;">
                                        <li class="error-text-user-name" style="list-style: none; color: red;"></li>
                                    </div>
                                </div>
                                <div class="col-md-2 col-lg-2 col-sm-6 col-sx-6">
                                    <label style="position: relative; top: 7px;" for="id_order">更新日時</label>
                                </div>
                                <div class="col-md-5 col-lg-5 col-sm-6 col-sx-6">
                                    <div class="row">
                                        <span class="col-md-5"><input type="text" class="form-control date-jp date-from history-date-from"></span>
                                        <span style="position: relative; top: 5px;" class="col-md-1 text-center">～</span>
                                        <span class="col-md-5"><input type="text" class="form-control date-jp date-to history-date-to"></span>          
                                        <div class="error-date" style="display: none;">
                                            <li class="error-text-date" style="list-style: none; color: red;"></li>
                                        </div>
                                    </div>
                                </div>     
                            </div>
                            <div style="margin-top: 10px;">
                                <label for="id_order">権限</label>
                                <span class="check_flag_h_permission" style="margin: 0 15px 0 80px;"><input type="checkbox" class="flag_h_permission" name="history-permission" value="0">&nbsp;管理者</span>
                                <span class="check_flag_h_permission" style="margin-right: 15px;"><input type="checkbox" class="flag_h_permission" name="history-permission" value="1">&nbsp;運用者</span>
                                <span class="check_flag_h_permission"><input type="checkbox" class="flag_h_permission" name="history-permission" value="2">&nbsp;仕入先</span>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <button type="button" class="btn-user-process btn-search-his-process">検索</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div style="margin-top: 15px;">
        <table class="table tb-process-history">
            <thead>
                <tr>
                    <th width="10%" scope="col" class="title-table">更新日時</th>
                    <th width="10%" scope="col" class="title-table">ユーザー名</th>
                    <th width="10%" scope="col" class="title-table">権限</th>
                    <th width="10%" scope="col" class="title-table">操作画面</th>
                    <th width="60%" scope="col" class="title-table">更新内容</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    
    <div class="supplied-modal">
    </div>
    @include('components.modalSearchSupplied')
</div>
<script>
    var getListProcessHistory = "{{route('get-list-process-history')}}";
    var middleware_admin = "{{Auth::user()->type}}";
    var ajax_add_users = "{{route('ajax-add-users')}}";
    var ajax_delete_users = "{{route('ajax-delete-users')}}";
    var ajax_update_users = "{{route('ajax-update-users')}}";
    var count_checkbox = "{{$count_checkbox}}";
</script>
@stop