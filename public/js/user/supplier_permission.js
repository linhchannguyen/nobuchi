$(document).ready(function(){
    $( ".datepicker" ).datepicker({ 
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'yy/mm',
        onClose: function(dateText, inst) { 
            $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
        }
    }); // date picker
    var date = getYearNow()+'/'+getMonthNow();
    $('.search-date').val(date)
    loadData(date)
})

//btn search purchase of supplier
$('.btn-search').on('click', function(){
    var date = $('.search-date').val();
    // Kiểm tra giá trị null khi nhấn tìm kiếm
    if(date == ''){
        $('.error-date').css('display', 'block');
        $('.error-text-date').text('検索日時を選択してください。');
        return false;
    }else {
        loading()
        loadData(date)
    }
})

//function get year now
function getYearNow(){
    var d = new Date();
    var year = d.getFullYear();
    return year;
}

//function get year now
function getMonthNow(){
    var d = new Date();
    var month = d.getMonth()+1;
    return month;
}

function loadData(date){
    date = date.split('/');
    $('.error-date').css('display', 'none');
    $.ajax({
        headers: { 'X-CSRF-TOKEN': token_header},
        type: "POST",
        url: url_search,
        data: {
            year: date[0],
            month: date[1]
        },
        success: function(res){
            if(res.success == false){
                table = `
                <thead>
                    <th style="vertical-align: middle" width="16%">日付</th>
                    <th style="vertical-align: middle" width="14%">発注件数</th>
                    <th style="vertical-align: middle" width="14%">未発注件数</th>
                    <th style="vertical-align: middle" width="14%">発注済件数</th>
                    <th style="vertical-align: middle" width="14%">出荷済件数</th>
                    <th style="vertical-align: middle" width="14%">キャンセル件数</th>
                    <th style="vertical-align: middle" width="14%">発注金額</th>
                </thead>
                <tbody>
                    <td class="text-center" colspan="7" style="vertical-align: middle; color: red;">検索条件に該当するデータがありません。</td>
                </tbody>`;
                $('.table-result').html(table);
            }else {
                table = `
                <thead>
                    <th style="vertical-align: middle" width="16%">日付</th>
                    <th style="vertical-align: middle" width="14%">発注件数</th>
                    <th style="vertical-align: middle" width="14%">未発注件数</th>
                    <th style="vertical-align: middle" width="14%">発注済件数</th>
                    <th style="vertical-align: middle" width="14%">出荷済件数</th>
                    <th style="vertical-align: middle" width="14%">キャンセル件数</th>
                    <th style="vertical-align: middle" width="14%">発注金額</th>
                </thead>
                <tbody>`;
                    for(var i = 0; i < res.data.length; i++){
                table +=`
                    <tr>
                        <td><a href="supplier/purchase-confirm?date=`+formatDate_(res.data[i].p_date)+`&flag_p_status_1=1&flag_p_status_2=2" target="_blank">`+formatDate_(res.data[i].p_date)+`</a></td>
                        <td>`+res.data[i].total_order+`件</td>
                        <td>`+res.data[i].quantity_p1+`件</td>
                        <td>`+res.data[i].quantity_p2_p3+`件</td>
                        <td>`+res.data[i].quantity_p4+`件</td>
                        <td>`+res.data[i].quantity_p5+`件</td>
                        <td>`+number_format(res.data[i].total_price, 0, '.', ',')+`</td>
                    </tr>`;
                    }
                table += `</tbody>`;
                $('.table-result').html(table);
            }
            endLoading()
        }
    })
}

// function format date
function formatDate_(date){
    var d = new Date(date);
    var month = d.getMonth()+1;
    var day = d.getDate();
    var output = d.getFullYear() + '/' + 
    (month<10 ? '0' : '') + month + '/' +
    (day<10 ? '0' : '') + day;
    return output;
}