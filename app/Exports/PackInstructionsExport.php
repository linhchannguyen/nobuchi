<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // tiêu đề của cột
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // auto size
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

/**
 * document https://docs.laravel-excel.com/2.1/export/format.html
 */
class PackInstructionsExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell, WithTitle, WithColumnFormatting, WithColumnWidths
{
    /**
     * data ['発注書No', '販売先', '', '商品名', '商品コード', '入数', '色・サイズ・ケース', '数量', '原単価', '原価金額']
     */
    private $data = [];
    private $package_info = [];
    private $page = [];
    public function __construct($data = [], $package_info = null, $page = null)
    {
        $this->data = $data;
        $this->package_info = $package_info;
        $this->page = $page;
    }
    public function startCell(): string // cell  bắt đầu của table
    {
        return 'B25';
    }
    public function headings(): array
    {
        return [
            ''
        ];
    }
    public function collection()
    {
        return $this->data;
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class  => function(AfterSheet $event) { // function trước khi xét dữ liệu để xuất Excel
                
                $count_data = count($this->data);
                $table_data = $count_data + 24;
                // set all font
                $event->sheet->getStyle('A1:J'.$table_data)->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'MS Pゴシック',
                        'size'      =>  14,
                    ),
                ));
                // set border title
                $styleBlod =  [
                    'font' => [
                        'name'      =>  'MS Pゴシック',
                    ]
                ];
                $styleAlignment = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ]
                ];
                $center = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];
                $hleft_vcent = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ]
                ];
                $hright_vcent = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => [
                        'name'      =>  'MS Pゴシック',
                    ]
                ];
                $borderBottom = [
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE,
                            'color' => ['argb' => 'black'],
                        ],
                        'top' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE,
                            'color' => ['argb' => 'black'],
                        ]
                    ],
                ];
                $b_bottom = [
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            'color' => ['argb' => 'black'],
                        ],
                    ],
                ];
                $borderMedium = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            'color' => ['argb' => 'black'],
                        ],
                        'all' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            'color' => ['argb' => 'black'],
                        ]
                    ]
                ];
                $all_border = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ];
                // set header
                $styleheader = [
                    'font' => $styleBlod['font'],
                    'alignment' => $styleAlignment['alignment'],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                        'color' => ['argb' => 'E9ECEF']
                    ],
                ];
                $event->sheet->mergeCells('A1:J2');// merge cells
                $event->sheet->setCellValue('A1', '発注明細・梱包指示書'); // xét giá trị của cột A
                $event->sheet->getStyle('A1')->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'MS Pゴシック',
                        'size'      =>  26,
                        'bold'      =>  true,
                    )
                ));
                $event->sheet->getStyle('A1')->applyFromArray($center);
                $event->sheet->getDelegate()->getRowDimension(2)->setRowHeight(23.25);
                $event->sheet->getDelegate()->getRowDimension(3)->setRowHeight(21);
                $event->sheet->getDelegate()->getRowDimension(4)->setRowHeight(21);
                $event->sheet->getDelegate()->getRowDimension(5)->setRowHeight(21);
                $event->sheet->getDelegate()->getRowDimension(6)->setRowHeight(21);
                $event->sheet->getStyle('A1:J2')->applyFromArray($borderBottom);                
                $event->sheet->mergeCells('A4:C5');// merge cells
                $event->sheet->getStyle('A4')->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'MS Pゴシック',
                        'size'      =>  16,
                    ),
                ));
                $event->sheet->getStyle('A4:C5')->applyFromArray($b_bottom);
                $event->sheet->setCellValue('A4', $this->package_info['supplied'].' 御中');
                $event->sheet->getStyle('A4')->applyFromArray($center);
                $event->sheet->getStyle('F4:J6')->applyFromArray($center);
                $event->sheet->mergeCells('F4:G4');// merge cells
                $event->sheet->mergeCells('H4:J4');// merge cells
                $event->sheet->getStyle('F4:J6')->applyFromArray($all_border); 
                $event->sheet->mergeCells('F5:G5');// merge cells
                $event->sheet->mergeCells('H5:J5');// merge cells
                $event->sheet->mergeCells('F6:G6');// merge cells
                $event->sheet->mergeCells('H6:J6');// merge cells
                $event->sheet->getStyle('F4')->applyFromArray($styleheader); 
                $event->sheet->getStyle('F5')->applyFromArray($styleheader); 
                $event->sheet->getStyle('F6')->applyFromArray($styleheader); 
                $event->sheet->setCellValue('F4', '発注年月日'); // xét giá trị của cột A
                $event->sheet->setCellValue('F5', '発注番号'); // xét giá trị của cột A
                $event->sheet->setCellValue('F6', 'ページ番号'); // xét giá trị của cột A
                if($this->package_info['purchase_date'] != null){
                    $date = date("Y/m/d", strtotime($this->package_info['purchase_date']));
                    $arr_date = explode('/', $date);
                    if(sizeof($arr_date) > 0){
                        $date = $arr_date[0].'年'.$arr_date[1].'月'.$arr_date[2].'日';
                    }
                    $event->sheet->setCellValue('H4', $date); // xét giá trị của cột A                    
                }
                $event->sheet->setCellValue('H5', $this->package_info['purchase_code']); // xét giá trị của cột A
                $event->sheet->setCellValue('H6', $this->page[0] . ' / ' . $this->page[1]); // xét giá trị của cột A

                //row 6 
                $event->sheet->getStyle('F7')->applyFromArray(array(
                    'font' => array(
                        'size'      =>  14,
                    ),
                ));  
                $event->sheet->getDelegate()->getStyle('A7:A9')->getAlignment()->setWrapText(false);
                $event->sheet->getDelegate()->getStyle('F7')->getAlignment()->setWrapText(true);
                $event->sheet->setCellValue('A7', 'いつもお世話になります。');
                $event->sheet->setCellValue('A8', '下記の通り発注致します。');
                $event->sheet->setCellValue('A9', '出荷手配のほど宜しくお願い致します。');

                //row 10 - 11       
                $event->sheet->getStyle('B11:B12')->applyFromArray(array(
                    'font' => array(
                        'size'      =>  16,
                    ),
                ));         
                $event->sheet->setCellValue('A11', '注文日');
                $event->sheet->setCellValue('A12', '受注No.');
                $event->sheet->mergeCells('B11:C11');// merge cells
                $event->sheet->mergeCells('B12:C12');// merge cells
                $event->sheet->getStyle('A11:C12')->applyFromArray($all_border); 
                $event->sheet->getStyle('A11')->applyFromArray($styleheader); 
                $event->sheet->getStyle('A12')->applyFromArray($styleheader); 
                $event->sheet->setCellValue('B11', date("Y/m/d H:i:s", strtotime($this->package_info['order_date'])));
                $event->sheet->setCellValue('B12', $this->package_info['order_code']);
                $event->sheet->getDelegate()->getRowDimension(7)->setRowHeight(21);
                $event->sheet->getDelegate()->getRowDimension(8)->setRowHeight(21);
                $event->sheet->getDelegate()->getRowDimension(9)->setRowHeight(21);
                $event->sheet->getDelegate()->getRowDimension(11)->setRowHeight(30.75);
                $event->sheet->getDelegate()->getRowDimension(12)->setRowHeight(30.75);
                $event->sheet->getDelegate()->getRowDimension(13)->setRowHeight(21);
                $event->sheet->getDelegate()->getRowDimension(14)->setRowHeight(30);
                $event->sheet->getDelegate()->getRowDimension(15)->setRowHeight(23.25);
                $event->sheet->getDelegate()->getRowDimension(17)->setRowHeight(23.25);
                $event->sheet->getDelegate()->getRowDimension(18)->setRowHeight(23.25);
                $event->sheet->getDelegate()->getRowDimension(19)->setRowHeight(21);
                $event->sheet->getDelegate()->getRowDimension(20)->setRowHeight(31);
                $event->sheet->getDelegate()->getRowDimension(21)->setRowHeight(31);
                $event->sheet->getDelegate()->getRowDimension(22)->setRowHeight(21);
                $event->sheet->getDelegate()->getRowDimension(23)->setRowHeight(26.25);
                $event->sheet->getDelegate()->getRowDimension(24)->setRowHeight(17.25);
                $event->sheet->getStyle('A11:B12')->applyFromArray($hleft_vcent); 
                
                $event->sheet->mergeCells('F7:J12');// merge cells
                $str = "NPO法人クローバープロジェクト21\n　住所：〒733-0832\n　　　　広島県広島市西区草津港1丁目8-1\n　　　　広島市中央卸売市場関連棟238番\n　TEL：082-276-7500\n　FAX：082-276-7502\n　担当：　窪地　常本　吉田";
                
                $event->sheet->setCellValue('F7', $str);
                $event->sheet->getStyle('F7')->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'MS Pゴシック',
                        'size'      =>  13,
                    ),
                ));
                
                // //row 13
                $event->sheet->mergeCells('A14:C14');// merge cells
                $event->sheet->mergeCells('E14:J14');// merge cells
                $event->sheet->setCellValue('A14','　注文者様　情報　（送り主）');
                $event->sheet->getStyle('A14')->applyFromArray($styleheader); 
                $event->sheet->setCellValue('E14','　お届け先様　情報');
                $event->sheet->getStyle('E14')->applyFromArray($styleheader);                 
                $event->sheet->getStyle('A15')->applyFromArray(array(
                    'font' => array(
                        'size'      =>  11,
                    ),
                ));
                $event->sheet->getStyle('E15')->applyFromArray(array(
                    'font' => array(
                        'size'      =>  11,
                    ),
                ));
                $event->sheet->mergeCells('B15:C15');// merge cells
                $event->sheet->mergeCells('F15:J15');// merge cells
                $event->sheet->setCellValue('A15', '郵便番号');
                $event->sheet->setCellValue('B15', $this->package_info['buyer_zip']);
                $event->sheet->getStyle('A15')->applyFromArray($styleheader); 
                $event->sheet->setCellValue('E15', '郵便番号');
                $event->sheet->setCellValue('F15', $this->package_info['ship_zip']);
                $event->sheet->getStyle('E15')->applyFromArray($styleheader); 
                $event->sheet->getStyle('A16:A18')->applyFromArray(array(
                    'font' => array(
                        'size'      =>  12,
                    ),
                ));
                $event->sheet->getStyle('E16:E18')->applyFromArray(array(
                    'font' => array(
                        'size'      =>  12,
                    ),
                ));
                $event->sheet->mergeCells('F16:J16');// merge cells
                $event->sheet->setCellValue('A16','住所');
                $height_buyer_add = $this->getRowcount($this->package_info['buyer_add'], 70) * 25;
                $height_ship_add = $this->getRowcount($this->package_info['ship_add'], 100) * 25;
                $event->sheet->setCellValue('B16', $this->package_info['buyer_add']);
                $event->sheet->getDelegate()->getStyle('B16:F16')->getAlignment()->setWrapText(true);
                $event->sheet->mergeCells('B16:C16');// merge cells
                $event->sheet->getStyle('A16')->applyFromArray($styleheader); 
                $event->sheet->setCellValue('E16', '住所');
                $event->sheet->setCellValue('F16', $this->package_info['ship_add']);
                $event->sheet->getStyle('E16')->applyFromArray($styleheader); 
                $height_add = ($height_buyer_add > $height_ship_add) ? $height_buyer_add : $height_ship_add;
                $event->sheet->getDelegate()->getRowDimension(16)->setRowHeight($height_add + 30);

                //row 17
                $event->sheet->mergeCells('B17:C17');// merge cells
                $event->sheet->mergeCells('F17:J17');// merge cells
                $event->sheet->setCellValue('A17', '氏名');
                $event->sheet->setCellValue('B17', $this->package_info['buyer_name']);
                $event->sheet->getStyle('A17')->applyFromArray($styleheader); 
                $event->sheet->setCellValue('E17', '氏名');
                $event->sheet->setCellValue('F17', $this->package_info['ship_name']);
                $event->sheet->getStyle('E17')->applyFromArray($styleheader); 
                
                //row 18 slow
                $event->sheet->mergeCells('B18:C18');// merge cells
                $event->sheet->mergeCells('F18:J18');// merge cells
                $event->sheet->setCellValue('A18', 'TEL');
                $event->sheet->setCellValue('B18', $this->package_info['buyer_phone']);
                $event->sheet->getStyle('A18')->applyFromArray($styleheader); 
                $event->sheet->setCellValue('E18', 'TEL');
                $event->sheet->setCellValue('F18', $this->package_info['ship_phone']);
                $event->sheet->getStyle('E18')->applyFromArray($styleheader); 
                $event->sheet->getStyle('A14:C18')->applyFromArray($all_border); 
                $event->sheet->getStyle('E14:J18')->applyFromArray($all_border); 
                //slow
                $event->sheet->getStyle('A14:J14')->applyFromArray($hleft_vcent); 
                $event->sheet->getStyle('A15:a18')->applyFromArray($center); 
                $event->sheet->getStyle('E15:E18')->applyFromArray($center); 
                $event->sheet->getStyle('B15:B18')->applyFromArray($hleft_vcent); 
                $event->sheet->getStyle('F15:F18')->applyFromArray($hleft_vcent); 
                $event->sheet->getStyle('A14:C18')->applyFromArray($borderMedium); 
                $event->sheet->getStyle('E14:J18')->applyFromArray($borderMedium); 
                
                //row 19 - 20
                $event->sheet->mergeCells('B20:D20');// merge cells
                $event->sheet->mergeCells('F20:G20');// merge cells
                $event->sheet->mergeCells('H20:J20');// merge cells
                $event->sheet->mergeCells('B21:D21');// merge cells
                $event->sheet->mergeCells('F21:G21');// merge cells
                // $event->sheet->mergeCells('I21:J21');// merge cells
                $event->sheet->setCellValue('A20', '集荷日時');
                $es_shipping_time = '';
                $es_time = $this->package_info['es_shipment_time'];
                if($es_time != '' || $es_time != null){
                    $es_time = explode('-', $es_time);
                    if(!empty($es_time)){
                        if(count($es_time) == 2){
                            $es_shipping_time = $es_time[0].'時～'.$es_time[1].'時';
                        }
                    }
                }
                $event->sheet->setCellValue('B20', $this->package_info['es_delivery_date'] . ' ' . $es_shipping_time);
                $event->sheet->setCellValue('E20', '配送方法');
                $event->sheet->setCellValue('F20', $this->package_info['delivery_method']);
                $event->sheet->setCellValue('H20', $this->package_info['shipment_code']);
                $event->sheet->setCellValue('A21', '配達日時');
                $event->sheet->setCellValue('B21', $this->package_info['shipment_date'] . ' ' . (($this->package_info['shipment_time'] != '0') ? $this->package_info['shipment_time'] : ''));
                $event->sheet->setCellValue('E21', '納品方法');
                $event->sheet->setCellValue('F21', $this->package_info['delivery_way']);
                $event->sheet->mergeCells('H21:J21');// merge cells
                if($this->package_info['deli_method'] == 7){
                    $event->sheet->setCellValue('H21', '¥'.(($this->package_info['pay_request'] == 1) ? $this->package_info['money_daibiki'] : '0'));
                }
                $event->sheet->getStyle('A20:J21')->applyFromArray($all_border); 
                $event->sheet->getStyle('A20:A21')->applyFromArray($styleheader); 
                $event->sheet->getStyle('E20:E21')->applyFromArray($styleheader);              
                $event->sheet->getStyle('A20:E21')->applyFromArray($hleft_vcent); 
                $event->sheet->getStyle('F20:J20')->applyFromArray($center); 
                $event->sheet->getStyle('F20:F21')->applyFromArray($center); 
                $event->sheet->getStyle('H21')->applyFromArray($hright_vcent); 
                $event->sheet->getStyle('B20:D21')->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'MS Pゴシック',
                        'size'      =>  20,
                        'bold'      =>  true,
                    ),
                )); 
                $event->sheet->getStyle('A20:J21')->applyFromArray($borderMedium); 

                //row 22     
                $event->sheet->getStyle('A23:J'.($table_data + 1))->applyFromArray($all_border);     
                $event->sheet->mergeCells('A23:A24');// merge cells
                $event->sheet->getStyle('A23')->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'MS Pゴシック',
                        'size'      =>  10,
                    ),
                )); 
                $event->sheet->getStyle('I23:J23')->applyFromArray(array(
                    'font' => array(
                        'size'      =>  11,
                    ),
                )); 
                $event->sheet->getStyle('I24:J24')->applyFromArray(array(
                    'font' => array(
                        'size'      =>  10,
                    ),
                )); 
                $event->sheet->setCellValue('A23','商品コード');
                $event->sheet->mergeCells('B23:E24');// merge cells
                $event->sheet->setCellValue('B23','商品名');
                $event->sheet->mergeCells('F23:F24');// merge cells
                $event->sheet->setCellValue('F23',"規格 (入数)");
                $event->sheet->getDelegate()->getStyle('F23')->getAlignment()->setWrapText(true);   
                $event->sheet->mergeCells('G23:G24');// merge cells
                $event->sheet->setCellValue('G23',"発注数");
                $event->sheet->mergeCells('H23:H24');// merge cells
                $event->sheet->setCellValue('H23',"合計数");
                $event->sheet->setCellValue('I23',"原単価");
                $event->sheet->setCellValue('I24',"税抜");
                $event->sheet->setCellValue('J23',"原価合計");
                $event->sheet->setCellValue('J24',"税抜");

                $event->sheet->getStyle('A23:J24')->applyFromArray($center); 
                $event->sheet->getStyle('A23:J24')->applyFromArray($styleheader); 
                $total_price = 0;
                for($cell_data = 25; $cell_data <= $table_data; $cell_data++)
                {
                    $index_data = $cell_data-25;
                    $event->sheet->getDelegate()->getStyle('A'.$cell_data.':E'.$cell_data)->getAlignment()->setWrapText(true);   
                    $event->sheet->getDelegate()->getStyle('I'.$cell_data.':J'.$cell_data)->getAlignment()->setShrinkToFit(true);
                    $event->sheet->getStyle('A'.$cell_data)->applyFromArray($center); 
                    $event->sheet->mergeCells('B'.$cell_data.':E'.$cell_data);// merge cells
                    $event->sheet->getStyle('B'.$cell_data)->applyFromArray($hleft_vcent); 
                    $event->sheet->getStyle('F'.$cell_data.':H'.$cell_data)->applyFromArray($center); 
                    $event->sheet->getStyle('A'.$cell_data)->applyFromArray(array(
                        'font' => [
                            'name'      =>  'MS Pゴシック',
                            'size'      => 11
                        ]
                    ));
                    $event->sheet->getStyle('B'.$cell_data)->applyFromArray(array(
                        'font' => [
                            'name'      =>  'MS Pゴシック',
                            'size'      => 16
                        ]
                    ));
                    $event->sheet->getStyle('F'.$cell_data.':G'.$cell_data)->applyFromArray(array(
                        'font' => [
                            'name'      =>  'MS Pゴシック',
                            'size'      => 18
                        ]
                    ));
                    $event->sheet->getStyle('H'.$cell_data)->applyFromArray(array(
                        'font' => [
                            'name'      =>  'MS Pゴシック',
                            'size'      => 28,
                            'bold'      =>  true
                        ]
                    ));
                    $event->sheet->getStyle('I'.$cell_data.':J'.$cell_data)->applyFromArray(array(
                        'font' => [
                            'name'      =>  'MS Pゴシック',
                            'size'      => 16
                        ]
                    ));
                    $event->sheet->getStyle('I'.$cell_data.':J'.$cell_data)->applyFromArray($hright_vcent); 
                    
                    $height_product_code = $this->getRowcount($this->data[$index_data]['maker_code'], 20) * 40;
                    $height_product_name = $this->getRowcount($this->data[$index_data]['product_name'], 115) * 25;
                    $height_product = ($height_product_code > $height_product_name) ? $height_product_code : $height_product_name;
                    if(!empty($this->data[$index_data]['maker_code']) && $this->data[$index_data]['maker_code'] != "null"){
                        $event->sheet->setCellValue('A'.$cell_data,$this->data[$index_data]['maker_code']);
                    }else {
                        $event->sheet->setCellValue('A'.$cell_data, '');
                    }
                    $event->sheet->setCellValue('B'.$cell_data,$this->data[$index_data]['product_name']);
                    if($height_product <= 58){
                        $event->sheet->getDelegate()->getRowDimension($cell_data)->setRowHeight(58);
                    }else {
                        $event->sheet->getDelegate()->getRowDimension($cell_data)->setRowHeight($height_product);
                    }
                    $quantity_set = $this->data[$index_data]['quantity_set'];
                    if(!is_numeric($quantity_set)){
                        $quantity_set = 1;
                    }
                    $event->sheet->setCellValue('F'.$cell_data, $quantity_set);
                    $event->sheet->setCellValue('G'.$cell_data, $this->data[$index_data]['quantity']);
                    $event->sheet->setCellValue('H'.$cell_data,$quantity_set * $this->data[$index_data]['quantity']);
                    $event->sheet->setCellValue('I'.$cell_data,number_format((float)$this->data[$index_data]['cost_price_tax'], 0, '.', ','));
                    $event->sheet->setCellValue('J'.$cell_data,number_format((float)$this->data[$index_data]['total_price_tax'], 0, '.', ','));
                    $total_price += $this->data[$index_data]['total_price_tax'];
                }
                if($count_data <= 4){
                    $event->sheet->getStyle('A'.($table_data + 1).':J'.($table_data + (4 - $count_data) + 1))->applyFromArray($all_border);
                    for($cell_data_ = 25; $cell_data_ <= $table_data + (4 - $count_data); $cell_data_++){
                        $event->sheet->getDelegate()->getStyle('A'.$cell_data_.':E'.$cell_data_)->getAlignment()->setWrapText(true);   
                        $event->sheet->getDelegate()->getStyle('I'.$cell_data_.':J'.$cell_data_)->getAlignment()->setShrinkToFit(true);
                        $event->sheet->getStyle('A'.$cell_data_)->applyFromArray($center); 
                        $event->sheet->mergeCells('B'.$cell_data_.':E'.$cell_data_);// merge cells
                        $event->sheet->getStyle('B'.$cell_data_)->applyFromArray($hleft_vcent); 
                        $event->sheet->getStyle('F'.$cell_data_.':H'.$cell_data_)->applyFromArray($center); 
                        $event->sheet->getStyle('A'.$cell_data_)->applyFromArray(array(
                            'font' => [
                                'name'      =>  'MS Pゴシック',
                                'size'      => 11
                            ]
                        ));
                        $event->sheet->getStyle('B'.$cell_data_)->applyFromArray(array(
                            'font' => [
                                'name'      =>  'MS Pゴシック',
                                'size'      => 16
                            ]
                        ));
                        $event->sheet->getStyle('F'.$cell_data_.':G'.$cell_data_)->applyFromArray(array(
                            'font' => [
                                'name'      =>  'MS Pゴシック',
                                'size'      => 18
                            ]
                        ));
                        $event->sheet->getStyle('H'.$cell_data_)->applyFromArray(array(
                            'font' => [
                                'name'      =>  'MS Pゴシック',
                                'size'      =>  28,
                                'bold'      =>  true
                            ]
                        ));
                        $event->sheet->getStyle('I'.$cell_data_.':J'.$cell_data_)->applyFromArray(array(
                            'font' => [
                                'name'      =>  'MS Pゴシック',
                                'size'      => 16
                            ]
                        ));
                        $event->sheet->getStyle('I'.$cell_data_.':J'.$cell_data_)->applyFromArray($hright_vcent); 
                        $event->sheet->getDelegate()->getRowDimension($cell_data_)->setRowHeight(58);
                    }
                    $row_total = $table_data + (4 - $count_data) + 1;
                }else {
                    $row_total = $table_data + 1;
                }
                //row total
                $event->sheet->getStyle('A'.$row_total.':I'.$row_total)->applyFromArray(array(
                    'font' => [
                        'name'      =>  'MS Pゴシック',
                        'size'      => 12
                    ]
                ));
                $event->sheet->getStyle('J'.$row_total)->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'MS Pゴシック',
                        'size'      =>  16,
                    )
                ));
                $event->sheet->getDelegate()->getStyle('J'.$row_total)->getAlignment()->setShrinkToFit(true);
                $event->sheet->mergeCells('B'.$row_total.':E'.$row_total);// merge cells
                $event->sheet->setCellValue('B'.$row_total, $this->package_info['page'].'/'.$this->package_info['total'].'頁');
                $event->sheet->setCellValue('I'.$row_total, '合計');                
                $event->sheet->setCellValue('J'.$row_total, number_format((float)$total_price, 0, '.', ','));
                $event->sheet->getStyle('B'.$row_total.':H'.$row_total)->applyFromArray($center); 
                $event->sheet->getStyle('I'.$row_total)->applyFromArray($hleft_vcent); 
                $event->sheet->getStyle('J'.$row_total)->applyFromArray($hright_vcent); 
                $event->sheet->getDelegate()->getRowDimension($row_total)->setRowHeight(40);
                $event->sheet->getStyle('A23:J'.$row_total)->applyFromArray($borderMedium); 

                //row note
                $row_remark = $row_total + 2;
                $event->sheet->getStyle('A'.$row_remark.':J'.($row_remark+7))->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'MS Pゴシック',
                        'size'      =>  14,
                    )
                ));
                $event->sheet->getStyle("A$row_remark:J$row_remark")->applyFromArray($styleheader);
                $event->sheet->getStyle('A'.($row_remark+1).':A'.($row_remark+3))->applyFromArray($styleheader);
                $event->sheet->getStyle('A'.$row_remark.':J'.($row_remark + 7))->applyFromArray($hleft_vcent); 
                $event->sheet->getStyle('A'.$row_remark.':J'.($row_remark + 7))->applyFromArray($all_border); 
                $event->sheet->mergeCells('A'.$row_remark.':E'.$row_remark);// merge cells
                $event->sheet->mergeCells('F'.$row_remark.':J'.$row_remark);// merge cells
                $event->sheet->mergeCells('A'.($row_remark + 1).':B'.($row_remark + 1));// merge cells
                $event->sheet->mergeCells('C'.($row_remark + 1).':E'.($row_remark + 1));// merge cells
                $event->sheet->mergeCells('A'.($row_remark + 2).':B'.($row_remark + 2));// merge cells
                $event->sheet->mergeCells('C'.($row_remark + 2).':E'.($row_remark + 2));// merge cells
                $event->sheet->mergeCells('A'.($row_remark + 3).':B'.($row_remark + 3));// merge cells
                $event->sheet->mergeCells('C'.($row_remark + 3).':E'.($row_remark + 3));// merge cells
                $event->sheet->mergeCells('A'.($row_remark + 4).':E'.($row_remark + 7));// merge cells
                $event->sheet->mergeCells('F'.($row_remark + 1).':J'.($row_remark + 7));// merge cells
                $event->sheet->getStyle('A'.$row_remark.':J'.($row_remark + 7))->applyFromArray($borderMedium); 
                for($i = $row_remark; $i <= ($row_remark + 7); $i++){
                    $event->sheet->getDelegate()->getRowDimension($i)->setRowHeight(27);
                }
                $event->sheet->setCellValue('A'.$row_remark, '＜お客様からのご要望＞');
                $event->sheet->setCellValue('F'.$row_remark, '＜備考＞');
                $event->sheet->getStyle('F'.($row_remark+1))->applyFromArray(array(
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                    ]
                ));
                $event->sheet->getDelegate()->getStyle('F'.($row_remark + 1))->getAlignment()->setWrapText(true);  
                $event->sheet->setCellValue('F'.($row_remark + 1), $this->package_info['comments']);
                $event->sheet->setCellValue('A'.($row_remark + 1), 'のし');
                $event->sheet->setCellValue('A'.($row_remark + 2), 'ラッピング');
                $event->sheet->setCellValue('A'.($row_remark + 3), 'メッセージカード');
                $event->sheet->setCellValue('C'.($row_remark + 1), $this->package_info['gift_wrap']);
                $event->sheet->setCellValue('C'.($row_remark + 2), $this->package_info['wrapping_paper_type']);
                $event->sheet->setCellValue('C'.($row_remark + 3), $this->package_info['message']);
                $event->sheet->getDelegate()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $event->sheet->getDelegate()->getPageSetup()->setVerticalCentered(true);
                $event->sheet->getDelegate()->getPageSetup()->setHorizontalCentered(true);
                // $event->sheet->getDelegate()->getPageSetup()->setScale(56, true);
                // $event->sheet->getDelegate()->getPageSetup()->setFitToWidth(1);
                // $event->sheet->getDelegate()->getPageSetup()->setFitToHeight(1);
                $event->sheet->getDelegate()->getPageMargins()->setTop(1.5*0.39370);
                $event->sheet->getDelegate()->getPageMargins()->setRight(1.5*0.39370);
                $event->sheet->getDelegate()->getPageMargins()->setBottom(1.5*0.39370);
                $event->sheet->getDelegate()->getPageMargins()->setLeft(1.5*0.39370);
                $event->sheet->getDelegate()->getPageMargins()->setHeader(0);
                $event->sheet->getDelegate()->getPageMargins()->setFooter(0);
                $event->sheet->getDelegate()->getPageSetup()->setFitToPage(1);
                $event->sheet->getDelegate()->getPageSetup()->setPrintArea('A1:J'.($row_remark+7));
            },
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->package_info['purchase_code'];
    }

    //Hàm lấy độ cao cho text khi merge
    public function getRowcount($text, $width) {
        $rc = 0;
        $line = explode("\n", $text);
        foreach($line as $source) {
            $rc += intval((strlen($source) / $width) +1);
        }
        return $rc;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12.71,
            'B' => 12.71,            
            'C' => 35.71,            
            'D' => 1.71,            
            'E' => 12.71,            
            'F' => 8.71,            
            'G' => 8.71,            
            'H' => 8.71,            
            'I' => 9.71,            
            'J' => 10.42,            
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}