$(document).ready(function(){
    selectYear();
    var year = $('.date-payable').val();
    var fee = $('input[name=fee]:checked', '.shipment-form').val();
    loadData(year, fee)
})

$('.check_flag_search').on('click', function(){
    $(this).find('.flag_search').prop('checked', true);
});

//click btn search payable
$('.btn-search-payable').on('click', function(){
    loading()
    var year = $('.date-payable').val();
    var fee = $('input[name=fee]:checked', '.shipment-form').val();
    loadData(year, fee)
})

function loadData(year, fee){
    $.ajax({
        headers: { 'X-CSRF-TOKEN': token_header},
        type: "POST",
        url: url_search,
        data: {
            year: year,
            fee: fee
        },
        success: function(res){
            $('.shipment-table').css('display', 'block');
            table = `
            <thead>
                <th class="title-table" width="5%">番号</th>
                <th class="title-table" width="10%">仕入先</th>`;
                for(var i = 10; i <= 12; i++){
                    table += `<th class="title-table" width="7%">`+i+`月</th>`;
                }
                for(var j = 1; j <= 9; j++){
                    table += `<th class="title-table" width="7%">`+j+`月</th>`;
                }
            table +=
            `</thead>`;
            if(res.data != ''){
                table += `<tbody>`;
                for(var k = 0; k < res.data.length; k++){
                table +=`
                    <tr>
                        <td class="text-center" style="vertical-align: middle">`+(k+1)+`</td>
                        <td style="vertical-align: middle">`+res.data[k].name+`</td>
                        <td `+((res.data[k].month_10 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+(year-1)+`&month=10&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_10, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                        <td `+((res.data[k].month_11 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+(year-1)+`&month=11&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_11, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                        <td `+((res.data[k].month_12 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+(year-1)+`&month=12&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_12, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                        <td `+((res.data[k].month_1 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+year+`&month=1&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_1, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                        <td `+((res.data[k].month_2 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+year+`&month=2&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_2, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                        <td `+((res.data[k].month_3 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+year+`&month=3&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_3, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                        <td `+((res.data[k].month_4 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+year+`&month=4&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_4, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                        <td `+((res.data[k].month_5 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+year+`&month=5&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_5, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                        <td `+((res.data[k].month_6 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+year+`&month=6&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_6, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                        <td `+((res.data[k].month_7 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+year+`&month=7&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_7, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                        <td `+((res.data[k].month_8 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+year+`&month=8&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_8, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                        <td `+((res.data[k].month_9 != null) ? `class="text-right" style="vertical-align: middle"><a href="/payable-detail?year=`+year+`&month=9&supplier_id=`+res.data[k].id+`" target="_blank"><u>`+ number_format(res.data[k].month_9, 0, '.', ',')+`</u></a>` : `style="text-align: center; vertical-align: middle;">`)+`</td>
                    </tr>`
                }
                table +=
                `</tbody>`;
                $('.table-statistical').html(table);
                setDataTable('.table-statistical');
                if(res.data.length > 50){
                    $('.top').show();
                }else {
                    $('.top').hide();
                }
            }else {                    
                table += `
                <tbody>
                    <tr>
                        <td class="text-center" colspan="14" style="color: red;">検索条件に該当するデータがありません。</td>
                    </tr>
                </tbody>`;
                $('.table-statistical').html(table);
                $('.top').hide();
            }
            endLoading()
        }
    })
}

//function formatNumber
function formatNumber(num){
    return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}

//function formatCurrency
function formatCurrency(number){
    var n = number.split('').reverse().join("");
    var n2 = n.replace(/\d\d\d(?!$)/g, "$&,");    
    return  n2.split('').reverse().join('');
}

//function get year now
function getYearNow(){
    var d = new Date();
    var year = d.getFullYear();
    return year;
}

//function select year
function selectYear(){    
    var year = getYearNow();
    for(var i = 15; i > 0; i--){
        $('.date-payable').append($('<option>', { 
            value: year-i,
            text : year-i 
        }));
    }
    $('.date-payable').append($('<option>', { 
        value: year,
        text : year,
        selected: true
    }));
    for(var i = 1; i <= 10; i++){
        $('.date-payable').append($('<option>', { 
            value: year+i,
            text : year+i 
        }));
    }
}