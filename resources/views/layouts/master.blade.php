<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RimacECサイト管理システム</title>
    <!-- load css -->
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('bootstrap/css/jquery.jpDatePicker.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.20/datatables.min.css"/>
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <link href="{{asset('fontawesome5.11.1/css/all.css')}}" rel="stylesheet">
    <!-- <link href="{{ asset('bootstrap/css/jquery-ui.css') }}" rel="stylesheet"> -->
    <link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <!-- load js -->
    <!-- <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.min.js') }}" ></script> -->
    <!-- load js -->
    <script src="{{ asset('bootstrap/js/jquery-3.3.1.min.js') }}" type="text/javascript"></script> 
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.20/datatables.min.js"></script>
    <!-- <script src="{{ asset('bootstrap/js/jquery-ui.js') }}" type="text/javascript"></script> -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" type="text/javascript"></script>
    <script src="{{ asset('js/layouts/rimac.js') }}"></script>
     <!--https://github.com/ztms/jquery.jpDatePicker  -->
    <script src="{{ asset('bootstrap/js/holidays.js') }}" ></script>
    <script src="{{ asset('bootstrap/js/jquery.jpDatePicker.js') }}" ></script>
    <!-- end  jpDatePicker-->
    <script src="{{ asset('bootstrap/js/bootstrap.min.js') }}" ></script>
    <script src="{{ asset('fontawesome5.11.1/js/all.js') }}" ></script>
    <!-- tipso -->
    <link href="{{asset('tipso/tipso.min.css')}}">
    <script src="{{asset('tipso/tipso.min.js')}}"></script>
    @yield('script')
    @yield('css')
</head>
<body>
    <div class="container-fluid">
        <!-- load header -->
        @include('layouts.header')
        @if(\App\Untils::checkRoute(['supplier-index', 'search-purchase']) === false)
        <div style="width: 80%">
            <table class="table table-status">
                <thead>
                    <tr><th colspan="5">受注処理未完了件数(直近3ヶ月間)</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>新規受注</td>
                        <td>入金待ち</td>
                        <td>受注処理中</td>
                        <td>要確認</td>
                        <td>保留中</td>
                    </tr>
                    <tr>
                        <td>{{$totalOrderSidebar['status_1'] ? $totalOrderSidebar['status_1'] : 0}}件</td>
                        <td>{{$totalOrderSidebar['status_2'] ? $totalOrderSidebar['status_2'] : 0}}件</td>
                        <td>{{$totalOrderSidebar['status_3'] ? $totalOrderSidebar['status_3'] : 0}}件</td>
                        <td>{{$totalOrderSidebar['status_4'] ? $totalOrderSidebar['status_4'] : 0}}件</td>
                        <td>{{$totalOrderSidebar['status_5'] ? $totalOrderSidebar['status_5'] : 0}}件</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
        <!-- load breadcrumbs -->
        @include('layouts.breadcrumbs')
        <!-- load content -->
        @yield('content')
        <!-- load footer -->
        @include('layouts.footer')
    </div>
</body>
</html>