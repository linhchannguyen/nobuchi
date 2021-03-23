$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).on('click', '#submit_control_order', () => {
        let type_controll = $('#control_order').val()
        if(type_controll === '') 
        {
            ModalError('操作の内容を選択してください。', '', 'OK')
            return false
        }
        if (type_controll === 'pack_introduction')
        {
            // download giấy chỉ dẫn đóng gói
            listSupplied = [... new Set (listSupplied)]
            let data_download = []
            listSupplied.forEach(function (element_su) {
                let detail_su =[]
                let details_suplied = []
                detail_su['name_supplied']
                listIdDetail.forEach(function (element, index) {
                    if(element.sup === element_su)
                    {
                        details_suplied.push(element.detail_id)
                        detail_su['details'] = details_suplied
                        detail_su['name_supplied'] = element.name_supplied
                    }
                })
                data_download.push(detail_su)
            })
            var param = {};
            let loading = ''
            loading = `<div class="loading-full-page">Loading&#8230;</div>`
            $('body').append(loading); // loading fill page
            $.each(data_download, function( key, value ) {    
                param = {
                    'arr_detail': value.details,
                    'sel_download': 3,
                    'screen': 10
                }
                $.ajax({
                    cache: false,
                    url: url_export_one, //GET route 
                    data:  param,
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'binary',
                    xhrFields: {
                        responseType: 'blob',
                    },
                    success: function (result, status, xhr) {
                        let responseType = xhr.getResponseHeader('content-type') || 'application/octet-binary'
                        let blob = new Blob([result], { type: responseType });
                        let url = URL.createObjectURL(blob);
                        let randomId = `download-${Math.floor(Math.random()*1000000)}`;
                        let date_ = getDateNow().split('/');
                        let file_name = value.name_supplied + date_[0] + date_[1] + date_[2];
                        let link = `<a id='${randomId}' href=${url} download='`+file_name+`'.xlsx'>link</a>`;
                        $('body').append(link)
                        $(`#${randomId}`)[0].click()
                        $(`#${randomId}`).remove()
                        $('.loading-full-page').remove() // xóa loading page
                    },
                    error: function (ajaxContext) {
                        toastr.error('Export error: '+ ajaxContext.responseText);
                        endLoading()
                    }
                });
            })
        }
        if (type_controll === 'pdf_pack_introduction')
        {
            // download giấy chỉ dẫn đóng gói
            listSupplied = [... new Set (listSupplied)]
            let data_download = []
            listSupplied.forEach(function (element_su) {
                let detail_su =[]
                let details_suplied = []
                detail_su['name_supplied']
                listIdDetail.forEach(function (element, index) {
                    if(element.sup === element_su)
                    {
                        details_suplied.push(element.detail_id)
                        detail_su['details'] = details_suplied
                        detail_su['name_supplied'] = element.name_supplied
                    }
                })
                data_download.push(detail_su)
            })
            var param = {};
            let loading = ''
            loading = `<div class="loading-full-page">Loading&#8230;</div>`
            $('body').append(loading); // loading fill page
            doExport(data_download, 0, null, 3, 10, url_export_one, null)
            // $.each(data_download, function( key, value ) {    
            //     param = {
            //         'arr_detail': value.details,
            //         'sel_download': 3,
            //         'screen': 10,
            //         'supplier_name': value.name_supplied,
            //         'pdf': true
            //     }
            //     $.ajax({
            //         cache: false,
            //         url: url_export_one, //GET route 
            //         data:  param,
            //         contentType: 'application/json; charset=utf-8',
            //         dataType: 'binary',
            //         xhrFields: {
            //             responseType: 'blob',
            //         },
            //         success: function (result, status, xhr) {
            //             let responseType = xhr.getResponseHeader('content-type') || 'application/octet-binary'
            //             let blob = new Blob([result], { type: responseType });
            //             let url = URL.createObjectURL(blob);
            //             let randomId = `download-${Math.floor(Math.random()*1000000)}`;
            //             let date_ = getDateNow().split('/');
            //             let file_name = value.name_supplied + date_[0] + date_[1] + date_[2];
            //             let link = '<a id='+randomId+' href='+url+' download='+file_name+'.pdf>link</a>';
            //             $('body').append(link)
            //             $(`#${randomId}`)[0].click()
            //             $(`#${randomId}`).remove()
            //             $('.loading-full-page').remove() // xóa loading page
            //         },
            //         error: function (ajaxContext) {
            //             toastr.error('Export error: '+ ajaxContext.responseText);
            //             endLoading()
            //         }
            //     });
            // })
        }
        if (type_controll === 'copy')
        {
        // copy hóa đơn
        let data = {}
        let message_confirm = "注文をコピーします。本当によろしいですか？"
        data = {
            order_code: [$('#order_code').val()]
        }

        ModalConfirm(message_confirm, 'キャンセル', 'Ok', urlCopyOrder, data, 'POST')
        }
        if (type_controll === 'delete')
        {
            let data = {}
            data = {
                order_code: [$('#order_code').val()]
            }
            let message_confirm = ""
            message_confirm = '受注を削除します。よろしいでしょうか？'
            ModalConfirm(message_confirm, 'キャンセル', 'Ok', urlDeleteOrder, data, 'POST', urlSearch)
        // xóa hóa đơn
            
        }
    });
});