$(document).ready(function () {
    /**
     * file set điều kiện tìm kiếm từ các màn hình trả về màn hình tìm kiếm
     */
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
    var conditions = {}
    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');
        conditions[sParameterName[0]] = decodeURI(sParameterName[1])
    }
    for(condition in conditions)
    {
        // ngay tao hoa don
        if(condition==='date_created_from')
        {
            $("input[name='type_date'][value='order_created']").prop('checked', true)
            $("#date_from").val(conditions[condition])
        }
        if(condition === 'date_created_to')
        {
            $("#date_to").val(conditions[condition])
        }
        // ngay import
        if(condition === 'date_import_from')
        {
            $("input[name='type_date'][value='date_import']").prop('checked', true)
            $("#date_from").val(conditions[condition])
        }
        if(condition === 'date_import_to')
        {
            $("#date_to").val(conditions[condition])
        }
        // ngay dat hang
        if(condition === 'date_purchased_from')
        {
            $("input[name='type_date'][value='date_purchased']").prop('checked', true)
            $("#date_from").val(conditions[condition])
        }
        if(condition === 'date_purchased_to')
        {
            $("#date_to").val(conditions[condition])
        }
        // ngay giao hang
        if(condition === 'delivery_date_from')
        {
            $("input[name='type_date'][value='ship_date']").prop('checked', true)
            $("#date_from").val(conditions[condition])
        }
        if(condition === 'delivery_date_to')
        {
            $("#date_to").val(conditions[condition])
        }
        // lấy ngày dự định giao hàng từ ngày
        if( condition === 'ship_schedule_from')
        {
            $("input[name='type_date'][value='ship_schedule_from']").prop('checked', true)
            $("#date_from").val(conditions[condition])
        }
        if(condition ==='ship_schedule_to')
        {
            $("#date_to").val(conditions[condition])
        }
        // ngay nhan hang
        if(condition === 'recive_schedule_from')
        {
            $("input[name='type_date'][value='ship_schedule_to']").prop('checked', true)
            $("#date_from").val(conditions[condition])
        }
        if(condition === 'recive_schedule_to')
        {
            $("#date_to").val(conditions[condition])
        }
        // end
        // tinh trang ho tro
        if(condition === 'status_support')
        {
            // $("#update_status_support_value").val(conditions[condition])
            $("input[name='status_support'][value='"+conditions[condition]+"']").prop('checked', true)
            
        }
        // NCC
        if(condition ==='supplied')
        {
            $("#supplied").val(conditions[condition])
        }
        // co xac nhan
        if( condition === 'flag_confirm')
        {
            $("input[name='flag_confirm'][value='"+conditions[condition]+"']").prop('checked', true)
            // $("#update_flag_value").val(conditions[condition])
        }
        // phương thức giao hàng
        if( condition === 'delivery_method')
        {
            $("input[name='delivery_method'][value='"+conditions[condition]+"']").prop('checked', true)
            // $("#update_delivery_method_value").val(conditions[condition])
        }
        // loai website
        if(condition === 'site_type')
        {
            $("input[name='site_type'][value='"+conditions[condition]+"']").prop('checked', true)
        }
    }
});