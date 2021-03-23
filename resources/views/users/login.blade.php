<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <meta http-equiv="X-UA-Compatible" content="ie=edge"> -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- load css -->
    <link href="{{ asset('css/user/login.css') }}" rel="stylesheet">
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <!-- load js -->
    <script src="{{ asset('bootstrap/js/jquery-3.3.1.min.js') }}" ></script>
    <script src="{{ asset('bootstrap/js/bootstrap.min.js') }}" ></script>
    <script src="{{ asset('/js//user/login.js') }}"></script>
    <title>Login RimacEC</title>
</head>
<body>
    <div class="login-page">
        <div class="form-login">
            <div class="login-logo">
                <b>RimacECサイト管理システム</b> <br>
                <span>ログイン</span>
            </div>
            <div class="form-login-sub">
                <form>
                    <div class="form-group row">
                        <label for="staticEmail" class="col-sm-4 col-form-label">ユーザーID</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="login_id">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="inputPassword" class="col-sm-4 col-form-label">パスワード</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" id="login_password">
                        </div>
                    </div>
                    <label class="col-sm-12 col-form-label login-fail">ユーザーIDとパスワードが一致しません。再入力ください。</label>                        
                    <label class="error-date" style="display: none;">
                        <li class="error-text-date" style="list-style: none; color: red;"></li>
                    </label>
                    <div class="login-button">
                        <button type="button" class="btn btn-secondary" id="submit_login">ログイン</button>
                    </div>
                </form>
            </div>
            <div class="login-footer">
                <span>©Rimac CO.LTD. All Rights Reserved.</span>
            </div>
        </div>
    </div>
</body>
</html>
<script>
var url = "{{url('')}}";
</script>