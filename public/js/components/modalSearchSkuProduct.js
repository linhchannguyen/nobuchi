$(document).ready(function () {
/**
 * function select category
 * @author Dat2019/10/14
 */
$('.category-select-modal').change(function () {
    let category_id = $(this).val()
    $.ajax({
        'url': urlGetCategoryModal,
        'method': 'GET',
        'data': {group: category_id},
        'success': function (response) {
            let options = '<option value=""></option>'
                if(response.length > 0)
                { 
                    response.forEach(element => {
                        options+=`
                            <option value="`+element.id+`">`+element.name+`</option>
                        `       
                    });
                $(".product-select-modal").html(options)
            }
        }
    })
})
    
/**
 * function click search sku product
 * @author Dat
 * 2019/10/14
 */
$("#search_product_modal").click(function () {
    let data = {}
    let delivery_method = []
    let status_flg = ''
    let handling_flg = ''
    let orther = []
    let sku = ''
    let supplied_id = ''
    let group = '' 
    let category_id = ''
    let product_name = ''
    // set data
    $.each($("input[name='delivery_method_modal']:checked"), function(){
        delivery_method.push($(this).val());
    });
    $.each($("input[name='orther_modal']:checked"), function(){
        orther.push($(this).val());
    });
    status_flg = $('input[name="status_flg"]:checked').val()
    handling_flg = $('input[name="handling_flg"]:checked').val()
    sku = $("#sku_modal").val()
    supplied_id = $("#supplied_modal").val()
    group = $("#category_modal").val()
    category_id = $("#product_modal").val()
    product_name = $("#name_product_modal").val()
    data = {
        'delivery_method': delivery_method,
        'status_flg': status_flg,
        'handling_flg': handling_flg,
        'orther': orther,
        'sku': sku,
        'supplied_id': supplied_id,
        'group': group,
        'category_id': category_id,
        'product_name': product_name
    }
    let load = `<tr class=""><td colspan="8" style="text-align:center"><b> Loading <i class="fa fa-spinner fa-spin"></i></b></td></tr>`
    $("#table_products tbody").html(load)
    let tb_result = `
        <thead>
            <th scope="col" style="text-align: center" width="5%">番号</th>
            <th scope="col" style="text-align: center" width="13%">SKU</th>
            <th scope="col" style="text-align: center" width="27%">品名 </th>
            <th scope="col" style="text-align: center" width="20%">仕入先 </th>
            <th scope="col" style="text-align: center" width="10%">配送方法</th>
            <th scope="col" style="text-align: center" width="10%">原価(税抜) </th>
            <th scope="col" style="text-align: center" width="10%">売価(税込) </th>
            <th scope="col" style="font-size: 16px; text-align: center" width="5%"></th>
        </thead>
        <tbody>`;
    $.ajax({
        'url': url_search_sku,
        'data': data,
        'method': 'GET',
        'success': function (response) {
            if(response.length > 0)
            {
                response.forEach((element, index) => {
                    let cost_price_untax = 0;
                    let sale_price_intax = 0;
                    let tax = 0
                    if(element.tax_rate != null)
                    {
                        tax = element.tax_rate / 100;
                    }
                    sale_price_intax = parseFloat(element.price_sale_2)+(element.price_sale_2*tax)
                    index++;
                    tb_result +=`
                        <tr>
                            <td class="text-center" style="vertical-align: middle" scope="col">`+index+`</td>
                            <td class="text-center sku" style="vertical-align: middle" scope="col" data-sku="`+element.sku+`">`+element.sku+`</td> 
                            <td style="vertical-align: middle" scope="col" class="name" data-name="`+element.short_name+`">`+element.short_name+`</td> 
                            <td style="vertical-align: middle" scope="col" class="supplier-name" data-supplier-name="`+element.sup_name+`">`+element.sup_name+`</td> 
                            <td class="text-center" style="vertical-align: middle" scope="col">`+element.delivery_name+`</td> 
                            <td class="text-right price" style="vertical-align: middle" scope="col">
                                <input type="hidden" class="product-id" value ="`+element.product_id+`"> 
                                <input type="hidden" class="maker_id" value ="`+element.maker_id+`"> 
                                <input type="hidden" class="maker_code" value ="`+element.maker_code+`"> 
                                <input type="hidden" class="supplied-id" value ="`+element.supplied_id+`"> 
                                <input type="hidden" class="cost-price" value ="`+element.cost_price+`"> 
                                <input type="hidden" class="price-sale" value ="`+element.price_sale+`">
                                <input type="hidden" class="price-sale-2" value ="`+element.price_sale_2+`">
                                <input type="hidden" class="sale-price-intax" value ="`+sale_price_intax+`">
                                <input type="hidden" class="cost-price-untax" value ="`+cost_price_untax+`">
                                <input type="hidden" class="product-status-id" value ="`+((element.product_status_id == 6) ? '冷蔵' : (element.product_status_id == 7) ? '冷凍' : '')+`">
                                <input type="hidden" class="tax-rate" value ="`+tax+`">                                
                                `
                                +number_format(element.cost_price, 0,'.', ',')+
                                `
                            </td> 
                            <td class="text-right" style="vertical-align: middle" scope="col">`+number_format(sale_price_intax, 0,'.', ',')+`</td> 
                            <td style="vertical-align: middle" scope="col"><button type="button" class="btn btn-search-order btn-selected-sku-modal text-center">選択</button> </td> 
                        </tr>`;
                });
                tb_result += '</tbody>'
                $('.loading-full-page').remove()
                $("#table_products").html(tb_result);
                setDataTable('#table_products');
                $('.top').show();
            }
            else
            {
                $('.top').hide();
                tb_result += `<tr>
                                    <td style="text-align:center" colspan="8">検索条件に該当するデータがありません。</td>
                                </tr>
                            </tbody>`;
                $('#table_products').html(tb_result);
            }
        }
    });
    
})

/**
 * click choise sku
 */
$(document).on("click", ".btn-selected-sku-modal", function(event){
    let sku = $(this).parent().parent().find('.sku').data('sku')// get sku value
    let product_id = ''
    let maker_id = ''
    let maker_code = ''
    let name = ''
    let price_buy = 0
    let cost_price_sale = 0;
    let cost_price = 0;
    let price_sale_2= 0;
    let supplied_id = 0;
    let cost_price_untax = 0;
    let sale_price_intax = 0;
    product_id =$(this).parent().parent().find('.price').find('.product-id').val()
    maker_id =$(this).parent().parent().find('.price').find('.maker_id').val()
    maker_code =$(this).parent().parent().find('.price').find('.maker_code').val()
    tax_rate =$(this).parent().parent().find('.price').find('.tax-rate').val()
    name = $(this).parent().parent().find('.name').data('name')
    price_buy = $(this).parent().parent().find('.price').find('.price-buy').val()
    cost_price =  $(this).parent().parent().find('.price').find('.cost-price').val() // giá mua không thuế
    cost_price_sale =  $(this).parent().parent().find('.price').find('.cost-price-sale').val()
    price_sale_2 = $(this).parent().parent().find('.price').find('.price-sale-2').val() // giá bán không thuế
    supplied_id =$(this).parent().parent().find('.price').find('.supplied-id').val()
    cost_price_untax = $(this).parent().parent().find('.price').find('.cost-price-untax').val()
    sale_price_intax = $(this).parent().parent().find('.price').find('.sale-price-intax').val()
    product_status_id = $(this).parent().parent().find('.price').find('.product-status-id').val()
    // set prop infor product
    var $tag =  $('div>.product-modal')
    let infoProduct = {}
    infoProduct =  {
        'sku': sku, // mã sản phầm
        'product_id': product_id, // id sản phẩm
        'maker_id': maker_id, // id nhà sản xuất
        'maker_code': maker_code, // mã nhà sản xuất
        'tax_rate': tax_rate, // thuế
        'name': name, // tên sản phẩm
        'product_status_id': product_status_id, // status sản phẩm
        'price_buy': price_buy, // giá mua 
        'cost_price_sale': cost_price_sale,
        'price_sale_2': price_sale_2,// giá bán không thuế
        'cost_price_untax': cost_price_untax,
        'sale_price_intax': sale_price_intax,
        'cost_price': cost_price,
        'supplied_id': supplied_id
    }
    $tag.prop('info',info(infoProduct))
    $tag.click();
    // end
    $("#sku").val(sku)// set sku value
    // fresh modal search sku
    refreshModalSku()
})
/**
 * function return infor
 */
function info(infoProduct)
{
    return infoProduct
}
});

function refreshModalSku(){    
    $("input[name='delivery_method_modal']").prop('checked', false)
    $("input[name='orther_modal']").prop('checked', false)
    $("#sku_modal").val('')
    $("#supplied_modal").val('')
    $("#category_modal").val('')
    $("#product_modal").val('')
    $("#name_product_modal").val('')
    let table_load = `
        <thead>
            <th scope="col" style="text-align: center" width="5%">番号</th>
            <th scope="col" style="text-align: center" width="13%">SKU</th>
            <th scope="col" style="text-align: center" width="27%">品名 </th>
            <th scope="col" style="text-align: center" width="20%">仕入先 </th>
            <th scope="col" style="text-align: center" width="10%">配送方法</th>
            <th scope="col" style="text-align: center" width="10%">原価(税抜) </th>
            <th scope="col" style="text-align: center" width="10%">売価(税込) </th>
            <th scope="col" style="font-size: 16px; text-align: center" width="5%"></th>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:center" colspan="8">指定条件で該当する件数が多いため、検索条件を絞って下さい。</td>
            </tr>
        </tbody>`;
    $("#table_products").html(table_load)
    $("#modal_product").modal('hide') // hide modal product
}