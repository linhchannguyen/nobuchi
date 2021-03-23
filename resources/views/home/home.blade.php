@extends('layouts.master')
@section('content')
<!-- load css and js home -->
<link href="{{ asset('css/homes/home.css') }}" rel="stylesheet">
<!-- <script src="{{ asset('js/orders/create.js') }}"></script> -->
<div>
    <!-- table giới thiệu -->
    <div class="card-menu">
        <table class="table">
            <tr>
                <td width="20%" class="label-form title-menu-table"><b>注文検索 </b></td>
                <td width="80%"  class="description-menu-table"><span>注文データの詳細な検索、及び編集を行います。</span></td>
            </tr>
            <tr>
                <td width="10%" class="label-form title-menu-table"><b>発注書出力・送信 </b></td>
                <td width="80%"  class="description-menu-table"><span>仕入先別に発注書・梱包指示書の送信、印刷を行います。</span></td>
            </tr>
            <tr>
                <td width="10%" class="label-form title-menu-table"><b>送り状出力 </b></td>
                <td width="80%"  class="description-menu-table"><span>送り状印刷、集荷依頼のための各運送会社用データを出力します。</span></td>
            </tr>
            <tr>
                <td width="10%" class="label-form title-menu-table"><b>出荷通知 </b></td>
                <td width="80%"  class="description-menu-table"><span>お客様に荷物を出荷したことをお知らせするための各ECサイト用データを出力します。</span></td>
            </tr>
            <tr>
                <td width="10%" class="label-form title-menu-table"><b>売上分析 </b></td>
                <td width="80%"  class="description-menu-table"><span>様々な実績データを出力します。</span></td>
            </tr>
            <tr>
                <td width="10%" class="label-form title-menu-table"><b>仕入先別買掛一覧 </b></td>
                <td width="80%"  class="description-menu-table"><span>仕入実績の明細を出力します。</span></td>
            </tr>
        </table>
    </div>
    <!-- title -->
    <!-- <div class="label-form title-revenue">
        <h3>{{\Carbon\Carbon::parse(now('Europe/London'))->format('Y/m/d')}}の売上(取込日ベース) </h3>
    </div> -->
    <!-- table web current -->
    <!-- <div class="web-current">
        <h4>
            サイト別実績
        </h4>
        <table class="table">
            <th class="label-form" width="12%">ECサイト</th>
            <th class="label-form" width="8%">売上件数</th>
            <th class="label-form" width="8%">取込エラー</th>
            <th class="label-form" width="10%">受注金額</th>
            <th class="label-form" width="10%">今月の売上</th>
            <th class="label-form" width="10%">昨年実績</th>
            <th class="label-form" width="10%">昨対比</th>
            <tbody>
                @if(count($data) > 0)
                    @foreach($data as $value)
                    <tr>
                        <td>{{$value['name']}}</td>
                        <td class="text-center">{{$value['import_suc']}}件</td>
                        <td class="text-center">{{$value['import_err']}}件</td>
                        <td class="text-right">{{number_format($value['total_price'], 2)}}</td>
                        <td class="text-right">{{number_format($value['turn_over'], 2)}}</td>
                        <td class="text-right">{{number_format($value['last_year_achievement'], 2)}}</td>
                        <?php 
                            $phantram = 0;
                            if(($value['turn_over'] + $value['last_year_achievement']) == 0){
                                $phantram = 0;
                            }else if ($value['last_year_achievement'] == 0){
                                $phantram = $value['turn_over'];
                            }
                            else {
                                $phantram = 100 + ((($value['turn_over'] - $value['last_year_achievement']) / $value['last_year_achievement']) * 100);
                            }
                        ?>
                        <td class="text-center">{{number_format($phantram, 2)}}%</td>
                    </tr>
                    @endforeach
                @else
                    <td class="text-center" colspan="9" style="vertical-align: middle; color: red;">取込検索条件に該当するデータがありません。</td>
                @endif
            </tbody>
        </table>
    </div> -->
    <!-- table import -->
    <!-- <div class="web-current">
        <h4>
            受注ランキング
        </h4>
        <nav aria-label="...">
        <?php
        $paginations = ($ranking->total()/$ranking->perPage());
        $page_sub = floor($paginations);
        if($paginations > $page_sub)
        {
            $paginations = $paginations +1;
        }
        ?>
            <ul class="pagination pagination-sm">
                @for($i=1; $i<=$paginations; $i++)
                <li class="page-item {{$i==$ranking->currentPage() ? 'active': ''}}" data-page="$i">
                    <a class="page-link pagination-users" href="?page={{$i}}&per_page=10">{{$i}}</a>
                </li>
                @endfor
            </ul>
        </nav>
        <table class="table">
            <th class="label-form" width="5%">順位</th>
            <th class="label-form" width="20%">ECサイト</th>
            <th class="label-form" width="14%">SKU</th>
            <th class="label-form" width="25%">品名</th>
            <th class="label-form" width="9%">個数</th>
            <th class="label-form" width="9%">売上金額</th>
            <th class="label-form" width="9%">原価</th>
            <th class="label-form" width="9%">昨対比</th>
            <tbody>
                @if(count($ranking) > 0)
                    @foreach($ranking as $key => $value)
                    <tr>
                        <td class="text-center" style="vertical-align: middle">{{$key+1}}</td>
                        <td style="vertical-align: middle">{{$value['site_name']}}</td>
                        <td class="text-center" style="vertical-align: middle">{{$value['product_code']}}</td>
                        <td style="vertical-align: top">{{$value['product_name']}}</td>
                        <td class="text-center" style="vertical-align: middle">{{$value['quantity']}}</td>
                        <td class="text-right" style="vertical-align: middle">{{number_format($value['turn_over'], 2)}}</td>
                        <td class="text-right" style="vertical-align: middle">{{number_format($value['total_price_tax'], 2)}}</td>
                        <?php 
                            $phantram = 0;
                            if(($value['turn_over'] + $value['last_year_achievement']) == 0){
                                $phantram = 0;
                            }else if ($value['last_year_achievement'] == 0){
                                $phantram = $value['turn_over'];
                            }
                            else {
                                $phantram = 100 + ((($value['turn_over'] - $value['last_year_achievement']) / $value['last_year_achievement']) * 100);
                            }
                        ?>
                        <td class="text-right" style="vertical-align: middle">{{(float)(number_format($phantram, 2,'.',''))}}%</td>
                    </tr>
                    @endforeach
                @else
                    <td class="text-center" colspan="8" style="vertical-align: middle; color: red;">取込条件に該当するデータがありません。</td>
                @endif
            </tbody>
        </table>
    </div> -->
</div>
@stop