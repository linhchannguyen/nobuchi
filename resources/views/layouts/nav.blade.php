<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#"></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item header-pull-right {{(isset($active) && $active == 1) ? 'active' : ''}}">
                <a class="nav-link" href="{{url('order/search-order')}}">注文検索</a>
            </li>
            <li class="nav-item header-pull-right {{(isset($active) && $active == 2) ? 'active' : ''}}">
                <a class="nav-link" href=" {{ url('/import') }}">取込設定</a>
            </li>
            <li class="nav-item header-pull-right {{(isset($active) && $active  == 3) ? 'active' : ''}}">
                <a class="nav-link" href="{{ url('/purchase') }}">発注書出力・送信</a>
            </li>
            <li class="nav-item header-pull-right {{(isset($active) && $active == 4) ? 'active' : ''}}">
                <a class="nav-link" href="{{ url('/shipment') }}">送り状出力</a>
            </li>
            <li class="nav-item header-pull-right {{(isset($active) && $active == 5) ? 'active' : ''}}">
                <a class="nav-link" href="{{ url('/shipment-notification') }}">出荷通知</a>
            </li>
            <li class="nav-item header-pull-right {{(isset($active) && $active == 6) ? 'active' : ''}}">
                <a class="nav-link" href="{{ url('/payable') }}">仕入先別買掛一覧</a>
            </li>
            <li class="nav-item header-pull-right header-pull-right-end  {{(isset($active) && $active == 7) ? 'active' : ''}}">
                <a class="nav-link" href="{{ url('master/users') }}">ユーザー管理</a>
            </li>
        </ul>
    </div>
</nav>