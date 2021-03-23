$(document).ready(function () {    
/**
 * function click search sku product
 * @author Dat
 * 2019/10/14
 */
$("#search_supplier_modal").click(function () {
    let data = {}
    let delivery_method = []
    let type = ''
    let purchase_method = []
    let date_off = []
    let supplier_name= ''
    // set data
    $.each($("input[name='modal_sup_delivery_method']:checked"), function(){
        delivery_method.push($(this).val());
    });
    $.each($("input[name='modal_sup_purchase_method']:checked"), function(){
        purchase_method.push($(this).val());
    });
    $.each($("input[name='modal_sup_date_off']:checked"), function(){
        date_off.push($(this).val());
    });
    type = $("input[name='modal_sup_type']:checked").val();
    supplier_name = $("#modal_sup_name").val()
    data = {
        'delivery_method': delivery_method,
        'type': type,
        'purchase_method': purchase_method,
        'date_off': date_off,
        'supplier_name': supplier_name
    }
    let load = `<tr class=""><td colspan="8" style="text-align:center"><b> Loading <i class="fa fa-spinner fa-spin"></i></b></td></tr>`
    $('#table_modal_sup_result tbody').html(load);
    let tb_result = `
        <thead>
            <th scope="col" style="text-align: center" width="5%">番号</th>
            <th scope="col" style="text-align: center" width="15%">集荷先名</th>
            <th scope="col" style="text-align: center" width="15%">連絡先 </th>
            <th scope="col" style="text-align: center" width="15%">リードタイム</th>
            <th scope="col" style="text-align: center" width="15%">休業日 </th>
            <th scope="col" style="text-align: center" width="15%">配送方法 </th>
            <th scope="col" style="text-align: center" width="15%">発注方法 </th>
            <th scope="col" width="5%" style="font-size: 16px; text-align: center"></th>
        </thead>
        <tbody>`;
    $.ajax({
        'url': url_search_supplier,
        'data': data,
        'method': 'GET',
        'success': function (response) {
            if(response.length > 0)
            {
                let date_off = ''
                let purchase_method = ''
                let delivery_name = ''
                response.forEach((element, index) => {
                    index++;
                    if(element.date_off === '2')
                    {
                        date_off = '月曜日'
                    } else if(element.date_off === '3')
                    {
                        date_off = '火曜日'
                    } else if(element.date_off === '4')
                    {
                        date_off = '水曜日'
                    } else if(element.date_off === '5')
                    {
                        date_off = '木曜日'
                    } else if(element.date_off === '6')
                    {
                        date_off = '金曜日'
                    } else if(element.date_off === '7')
                    {
                        date_off = '土曜日'
                    } else if(element.date_off === '1')
                    {
                        date_off = '日曜日'
                    }
                    // set purchase method
                    if(element.edi_type === 0)
                    {
                        purchase_method = 'FAX'
                    } else if (element.edi_type === 1)
                    {
                        purchase_method = 'EDI'
                    } else if (element.edi_type === 2)
                    {
                        purchase_method = 'メール '
                    }else if (element.edi_type === 3)
                    {
                        purchase_method = 'その他'
                    }
                    // xet phương thức giao hàng
                    
                    if(element.shipping_method === 1)
                    {
                        delivery_name = '佐川急便 '
                    } else if (element.shipping_method === 2)
                    {
                        delivery_name = 'ヤマト宅急便'
                    } else if (element.shipping_method === 3)
                    {
                        delivery_name = 'ネコポス '
                    }else if (element.shipping_method === 4)
                    {
                        delivery_name = 'コンパクト便 '
                    } else if (element.shipping_method === 5)
                    {
                        delivery_name = 'ゆうパケット  '
                    }else if (element.shipping_method === 6)
                    {
                        delivery_name = 'ゆうパケット '
                    }else if (element.purchase_method === 8)
                    {
                        delivery_name = 'その他'
                    }
                    // xet dia chi
                    let address = '';
                    if(element.addr01 !== null)
                    {
                        address += element.addr01
                    }else if (element.addr02 !== null){
                        address += element.addr02
                    }
                    tb_result +=`
                        <tr>
                            <td class="text-center" scope="col"><input type="hidden" class="supplied-id" value="`+element.id+`"> `+index+`</td>
                            <td scope="col" class="name-supplier-modal" data-name="`+element.name+`">`+element.name+`</td> 
                            <td scope="col">`+address +`</td> 
                            <td scope="col">`+((element.cargo_schedule_day !== null) ? element.cargo_schedule_day : '')+`</td> 
                            <td scope="col">`+date_off+`</td> 
                            <td scope="col">`+((element.shipping_method !== null) ? element.shipping_method : '')+`</td> 
                            <td scope="col">`+delivery_name+`</td> 
                            <td scope="col"><button type="button" class="btn btn-search-order btn-selected-supplier-modal">選択</button> </td> 
                        </tr>`;
                });
                tb_result +=`</tbody>`;
                $('.loading-full-page').remove()
                $("#table_modal_sup_result").html(tb_result);
                setDataTable("#table_modal_sup_result");
                $('.top').show();
            }
            else
            {                
                $('.top').hide();
                tb_result += `<tr>
                            <td style="text-align:center" colspan="8">検索条件に該当するデータがありません。</td>
                          </tr>
                          </tbody>`;
                $('#table_modal_sup_result').html(tb_result);
            }
        }
    });    
})

/**
 * click choise sku
 */
$(document).on("click", ".btn-selected-supplier-modal", function(event){
    let sup_name = $(this).parent().parent().find('.name-supplier-modal').data('name')// get name value
    let supplie_id = $(this).parent().parent().find('.supplied-id').val()
    let supplied = {}
    supplied = {
        'name': sup_name,
        'supplie_id': supplie_id 
    }
    var $tag = $('div>.supplied-modal');
    $tag.prop('info', info(supplied))
    $tag.click();
    $("#supplied").val(sup_name)// set name value
    // fresh modal search supplier
    refreshModalSupplier()
})
/**
 * 
 */
 function info (supplied)
 {
     return supplied
 }
 $('#modal_sup_name').keypress(function (e) {
     if(e.which === 13)
     {
        $("#search_supplier_modal").click()
     }
 })
});

function refreshModalSupplier(){     
    $("input[name='modal_sup_type']").prop('checked', false)
    $("input[name='modal_sup_delivery_method']").prop('checked', false)
    $("input[name='modal_sup_purchase_method']").prop('checked', false)
    $("input[name='modal_sup_date_off']").prop('checked', false)
    $("#modal_sup_name").val('') 
    let table_load = `
        <thead>
            <th scope="col" style="text-align: center" width="5%">番号</th>
            <th scope="col" style="text-align: center" width="15%">集荷先名</th>
            <th scope="col" style="text-align: center" width="15%">連絡先 </th>
            <th scope="col" style="text-align: center" width="15%">リードタイム</th>
            <th scope="col" style="text-align: center" width="15%">休業日 </th>
            <th scope="col" style="text-align: center" width="15%">配送方法 </th>
            <th scope="col" style="text-align: center" width="15%">発注方法 </th>
            <th scope="col" width="5%" style="font-size: 16px; text-align: center"></th>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:center" colspan="8">指定条件で該当する件数が多いため、検索条件を絞って下さい。</td>
            </tr>
        </tbody>`;
    $("#table_modal_sup_result").html(table_load)
    $("#modal_supplied").modal('hide') // hide modal product
}