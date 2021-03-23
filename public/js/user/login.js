$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $("#submit_login").click(function () {
        $('.error-date').css('display', 'none');
        $('.error-text-date').text('');
        let login_id = $("#login_id").val();
        let login_pass = $("#login_password").val();
        if(login_id === '' && login_pass === '')
        {
            $('.error-date').css('display', 'block');
            $('.error-text-date').text('ユーザID及びパスワードを入力してください。');
            $("#login_id").addClass('empty-login');
            $("#login_password").addClass('empty-login');
            return false ;
        }
        if(login_id === '')
        {
            $('.error-date').css('display', 'block');
            $('.error-text-date').text('ユーザIDを入力してください。');
            $("#login_id").addClass('empty-login');
            return false ;
        }
        if(login_pass === '')
        {
            $('.error-date').css('display', 'block');
            $('.error-text-date').text('パスワードを入力してください。');
            $("#login_password").addClass('empty-login');
            return false ;
        }
        let loginId = $("#login_id").val();
        let loginPassword = $("#login_password").val();
        let data = {
            loginId: loginId,
            loginPassword: loginPassword
        }
        
        let loading = ''
        loading = `<div class="loading-full-page">Loading&#8230;</div>`
        $('body').append(loading); // loading fill page
        $.ajax({
            type: "POST",
            url: "login",
            data: data,
            success: function (response) {
                if(response.status === 'true')
                {
                    window.location.href = url + response.url;
                }
                if(response.status === 'false')
                {
                    $(".login-fail").show();
                    $('.loading-full-page').remove()
                    return ;
                }
            }
        });

    })
    $("#login_id").focus(function (e) { 
        $(this).removeClass('empty-login');
        $(".login-fail").hide();
    });
    $("#login_password").focus(function (e) { 
        $(this).removeClass('empty-login');
        $(".login-fail").hide();
    });
    $(document).on('keypress keydown', '#login_password', function(e) {
        if(e.keyCode === 13)
        {
            $("#submit_login").click()
        }
    })
    $(document).on('keypress keydown', '#login_id', function(e) {
        if(e.keyCode === 13)
        {
            $("#submit_login").click()
        }
    })
})