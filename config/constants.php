<?php

return [
    //Phân quyền
    'PERMISSION' => [
        0 => '管理者',
        1 => '運用者',
        2 => '仕入先'
    ],
    //Phương thức giao hàng
    'DELIVERY_METHOD' => [
        1 => '佐川急便',
        2 => 'ヤマト宅急便',
        3 => 'ネコポス',
        4 => 'コンパクト便',
        5 => 'ゆうパック',
        6 => 'ゆうパケット',
        7 => '代引き',
        8 => 'その他',
        9 => '佐川急便(秘伝II)',
    ],
    //Cách giao hàng
    'DELIVERY_WAY' => [
        1 => '直送',
        2 => '引取',
        3 => '配達',
        4 => '仕入'
    ],
    //Loại website import
    'WEB_TYPE' => [
        0 => 'RimacEC',
        1 => '自社', // EC-CUBE
        2 => '楽天', // Rakuten
        3 => 'Yahoo', // Yahoo
        4 => 'Amazonひろしま', // Amazon
        5 => 'Amazonワールド',
        6 =>  'AmazonひろしまFBA',
        7 =>  'AmazonワールドFBA',
        8 => 'Amazonリカー'
    ],
    //Trạng thái order
    'ORDER_STATUS' => [
        1 => '新規注文',// A-1.新規注文 order mới
        2 => '入金待ち',// A-2.入金待ち chở nhập tiền
        3 => '受注処理中',// A-3.受注処理中 đang xử lý nhận order
        4 => '要確認',// A-4.要確認 cần xác nhận
        5 => '保留中',// A-5.保留中 đang bảo lưu
        6 =>  '完了',// A-6.完了 hoàn thành (xong)
        7 =>  'キャンセル',// A-7.キャンセル hủy
    ],
    //Trạng thái đặt hàng
    'PURCHASE_STATUS' => [
        0 => '未処理',//Chưa xử lý
        1 => '発注済、印刷済', //Đã đặt hàng
        2 => '送り状作成済', //Đã tạo shipment
        3 => '出荷通知済', //Đã thông báo xuất hàng
        4 => 'キャンセル', //Hủy
    ],
    //Trạng thái sản phẩm
    'PRODUCT_STATUS' => [
        0 => '冷蔵', //Làm lạnh
        1 => '冷凍', //Đông lạnh
    ],
    //Option quà tặng
    'MTB_OPTION' => [
        0 => "指定なし",
        1 => "お祝い",
        2 => "お歳暮",
        3 => "お中元"
    ]
];