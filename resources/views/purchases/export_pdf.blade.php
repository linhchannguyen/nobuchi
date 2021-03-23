<!doctype html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
        <script src="{{ asset('bootstrap/js/jquery-3.3.1.min.js') }}" type="text/javascript"></script> 
        <title><?php echo ($sel_download == 22) ? '発注一覧表' : ($sel_download == 33) ? '発注明細・梱包指示書' : '発注一覧表＋発注明細・梱包指示書' ?></title>
        <style type="text/css">
            @font-face {
                font-family: 'mgenplus';
                font-style: normal;
                font-weight: 400;
                src: url(fonts/rounded-mgenplus-1c-regular.ttf) format('truetype');
            }
            .ft0{font: 14px;}
            *{ font-family: mgenplus !important;}

            .table tbody tr td {
                border: 1px solid black;
                padding: -8px 4px 2px 4px !important;
            }
            .table tr td {
                border: 1px solid black;
                padding: 0 0 8px 0 !important;
            }
            .table thead{
                border: 1px solid black;
                background: #dbdbdb;
            }
            .table tbody{
                border: 1px solid black;
            }
            .table thead th{
                border-bottom: 1px solid black !important;
            }
            .font_weight{
                font-size: 1.5em;
            }
            .col1 {
                border-left: 1px solid #fff !important;
                border-top: 1px solid #fff !important;
                border-bottom: 1px solid #fff !important;
            }
            .col2 {
                border-top: 1px solid #fff !important;
                border-bottom: 1px solid #fff !important;
            }
            .title-header{
                margin-top: -15px;
            }
            .tb-package tr .td-title-header{
                font-size: 2em;
                border: 3px double #000 !important;
                border-left:  1px solid #fff !important;
                border-right:  1px solid #fff !important;
            }
        </style>
    </head>
    <body>
        <!-- In pdf giấy đặt hàng -->
        @if(!empty($purchase_info))
            @foreach($purchase_info as $key => $value_info)
            <div class="content-purchase" 
                <?php 
                if($key > 0){
                    echo 'style="page-break-before:always"';
                }
                ?>
                >
                <div class="content-header">
                    <table class="table" width="100%" style="width:100%">
                        <tr>
                            <td class="col1" rowspan="2" style="width: 50%;">
                                <div style="font-size: 1.5em; margin-top: -15px; margin-bottom: -15px">発注一覧表</div>
                                <div style="margin-top: -15px; margin-bottom: -15px">{{$value_info[0]}}　御中</div>
                            </td>
                            <td style="width: 10%; text-align: center; background-color: #dbdbdb;">御社確認印</td>
                            <td style="width: 10%; text-align: center; background-color: #dbdbdb;">弊社確認印</td>
                            <td class="col2" style="width: 5%;"></td>
                            <td style="width: 5%; background-color: #dbdbdb;">発注日</td>
                            <td class="text-right" style="width: 20%;">
                            <?php   
                                if($value_info[1] != null){
                                    $date = date("Y/m/d", strtotime($value_info[1]));
                                    $arr_date = explode('/', $date);
                                    if(sizeof($arr_date) > 0){
                                        $date = $arr_date[0].'年'.$arr_date[1].'月'.$arr_date[2].'日';
                                    }
                                }
                            ?>
                                {{$date}}
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2"></td>
                            <td rowspan="2"></td>
                            <td class="col2"></td>
                            <td style="background-color: #dbdbdb;">発注番号</td>
                            <td class="text-right">{{$value_info[2]}}</td>
                        </tr>
                        <tr>
                            <td class="col1">
                                <div style="margin-top: -15px; margin-bottom: -15px">いつもお世話になります。</div>
                                <div style="margin-top: -15px; margin-bottom: -15px">下記の通り発注致します。</div>
                            </td>
                            <td class="col2"></td>
                            <td style="background-color: #dbdbdb;">発注枚数</td>
                            <td class="text-right">{{$key+1}} / {{count($purchase_info)}}</td>
                        </tr>
                    </table>
                </div>
                <?php
                    $total_qty = 0.0;
                    $total_cost_price = 0.0;
                    $total_price = 0.0;
                    foreach($export_purchase[$key] as $value){
                        $total_qty += $value['quantity'];
                        $total_cost_price += $value['quantity'] * $value['cost_price'];
                        $total_price += $value['total_price'];
                    }
                ?>
                <div class="tb-result">
                    <table class="table" width="100%">
                        <thead>
                            <tr class="text-center">
                                <td width="25%">商品名</td>
                                <td width="10%">商品コード</td>
                                <td width="5%">入数</td>
                                <td width="10%">色・サイズ・ケース</td> 
                                <td width="5%">数量</td>
                                <td width="7%">原単価</td>
                                <td width="7%">原価金額</td>
                                <td width="9%">集荷日</td> 
                                <td width="11%">送り状番号</td>
                                <td width="11%">備考</td> 
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="vertical-align: middle;">
                                <td style="word-wrap: break-word">{{$export_purchase[$key][0]['product_name']}}</td>
                                <td class="text-center">{{$export_purchase[$key][0]['product_code']}}</td>
                                <td class="text-center">{{$export_purchase[$key][0]['quantity_set']}}</td>
                                <td>{{$export_purchase[$key][0]['product_info']}}</td>
                                <td class="text-center">{{$export_purchase[$key][0]['quantity']}}</td>
                                <td>{{$export_purchase[$key][0]['cost_price']}}</td>
                                <td>{{$export_purchase[$key][0]['total_price']}}</td>
                                <td class="text-right">{{$export_purchase[$key][0]['es_delivery_date']}}</td>
                                <td class="text-center">{{$export_purchase[$key][0]['shipment_code']}}</td>
                                <td>{{$export_purchase[$key][0]['message']}}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="background-color: #dbdbdb;">合計</td>
                                <td class="text-center">{{$total_qty}}</td>
                                <td style="background-color: #dbdbdb;">合計</td>
                                <td>{{$total_cost_price}}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td colspan="2" style="background-color: #dbdbdb; text-align: center;">訂正後原価金額</td>
                                <td colspan="3" class="text-right">NPO法人クローバープロジェクト21</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="vertical-align: middle; text-align: center;" colspan="2" rowspan="2">{{$total_price}}</td>
                                <td colspan="3" class="text-right" style="border-top: 2px solid #fff !important;">TEL：082-276-7500</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td colspan="3" class="text-right" style="border-top: 2px solid #fff !important;">FAX：082-276-7502</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>    
            @endforeach
        @endif
        <!-- In pdf giấy đặt hàng -->

        <!-- In pdf giấy chỉ dẫn đóng gói, chi tiết đặt hàng -->
        @if(!empty($package_info))
            @foreach($package_info as $key => $value_info)
            <div class="content-package" 
                <?php 
                if($key > 0){
                    echo 'style="page-break-before:always"';
                }
                ?>
                >
                <div class="content-header">
                    <table class="table tb-package" width="100%" style="width:100%">
                        <tr>
                            <td colspan="5" style="width: 100%;" class="text-center td-title-header">
                                <div class="title-header">発注明細・梱包指示書</div>
                            </td>   
                        </tr>
                        <tr>
                            <td colspan="5">A
                            </td>   
                        </tr> 
                        <tr class="text-center">
                            <td width="25%">商品名</td>
                            <td width="25%">商品コード</td>
                            <td width="5%">入数</td>
                            <td width="10%">色・サイズ・ケース</td> 
                            <td width="35%">数量</td>
                        </tr> 
                    </table>
                </div>
                <div class="tb-result">
                    <table class="table" width="100%">
                        <thead>
                            <tr class="text-center">
                                <td width="25%">商品名</td>
                                <td width="10%">商品コード</td>
                                <td width="5%">入数</td>
                                <td width="10%">色・サイズ・ケース</td> 
                                <td width="5%">数量</td>
                                <td width="7%">原単価</td>
                                <td width="7%">原価金額</td>
                                <td width="9%">集荷日</td> 
                                <td width="11%">送り状番号</td>
                                <td width="11%">備考</td> 
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>    
            @endforeach
        @endif
        <!-- In pdf giấy chỉ dẫn đóng gói, chi tiết đặt hàng -->
    </body>
</html>