<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // tiêu đề của cột
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // auto size
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;

/**
 * document https://docs.laravel-excel.com/2.1/export/format.html
 */
class NotifiedPayExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomStartCell, WithTitle
{
    /**
     * data ['発注書No', '販売先', '', '商品名', '商品コード', '入数', '色・サイズ・ケース', '数量', '原単価', '原価金額']
     */
    private $data = [];
    private $data_info = [];
    private $page = 0;
    public function __construct($data = [], $data_info = [], $page = 0)
    {
        $this->data = $data;
        $this->data_info = $data_info;
        $this->page = $page;
    }
    public function startCell(): string // cell  bắt đầu của table
    {
        return 'A10';
    }
    public function headings(): array
    {
        return [
            '出荷日',
            '発注書No.',
            '販売先',
            '',
            '商品名',
            '商品コード',
            '入数',
            '色・サイズ・ケース',
            '数量',
            '原単価',
            '原価金額'
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
                $table_data = $count_data + 10;
                // set all font
                $event->sheet->getStyle('A1:J'.$table_data)->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'ＭＳ Ｐゴシック',
                        'size'      =>  14,
                        'bold'      =>  false,
                    ),
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ],
                ));    
                $styleBlod =  [
                    'font' => [
                        'name'      =>  'ＭＳ Ｐゴシック',
                        'bold'      =>  true,
                    ]
                ];         
                $styleAlignment = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ]
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
                $all_border = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
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
                        'name'      =>  'ＭＳ Ｐゴシック',
                        'bold'      =>  false,
                    ]
                ];
                
                $event->sheet->getColumnDimension('A')->setAutoSize(false);
                $event->sheet->getColumnDimension('A')->setWidth(30);
                $event->sheet->getColumnDimension('B')->setAutoSize(false);
                $event->sheet->getColumnDimension('B')->setWidth(30);
                $event->sheet->getColumnDimension('C')->setAutoSize(false);
                $event->sheet->getColumnDimension('C')->setWidth(25);
                $event->sheet->getColumnDimension('D')->setAutoSize(false);
                $event->sheet->getColumnDimension('D')->setWidth(20);
                $event->sheet->getColumnDimension('E')->setAutoSize(false);
                $event->sheet->getColumnDimension('E')->setWidth(40);
                $event->sheet->getColumnDimension('F')->setAutoSize(false);
                $event->sheet->getColumnDimension('F')->setWidth(40);
                $event->sheet->getColumnDimension('G')->setAutoSize(false);
                $event->sheet->getColumnDimension('G')->setWidth(20);
                $event->sheet->getColumnDimension('H')->setAutoSize(false);
                $event->sheet->getColumnDimension('H')->setWidth(35);
                $event->sheet->getColumnDimension('I')->setAutoSize(false);
                $event->sheet->getColumnDimension('I')->setWidth(20);
                $event->sheet->getColumnDimension('J')->setAutoSize(false);
                $event->sheet->getColumnDimension('J')->setWidth(20);
                $event->sheet->getColumnDimension('K')->setAutoSize(false);
                $event->sheet->getColumnDimension('K')->setWidth(20);
                $event->sheet->getStyle('A1:A2')->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'ＭＳ Ｐゴシック',
                        'size'      =>  20,
                        'bold'      =>  true,
                        'text-align' => 'middle',
                    ),
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ],
                    ));
                $event->sheet->setCellValue('A3', $this->data_info['supplied'].'　御中');
                $event->sheet->getStyle('A3')->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'ＭＳ Ｐゴシック',
                        'size'      =>  14,
                        'bold'      =>  true,
                        'underline' => true
                    ),
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ],
                    ));
                    // set border title
                    $styleBlod =  [
                        'font' => [
                            'name'      =>  'ＭＳ Ｐゴシック',
                            'bold'      =>  true,
                        ]
                    ];
                    $styleborder = [
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => 'black'],
                            ],
                            'all' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => 'black'],
                            ]
                        ]
                    ];
                    $styleAlignment = [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ]
                    ];
                // tiêu đề
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);
                $event->sheet->getDelegate()->getRowDimension(2)->setRowHeight(20);
                $event->sheet->getDelegate()->getRowDimension(3)->setRowHeight(20);
                $event->sheet->mergeCells('A1:B2');// merge cells
                $event->sheet->setCellValue('A1', 'お支払通知書'); // set tiêu đề
                // set số hóa đơn, ngày
                $event->sheet->getStyle('J1:J2')->applyFromArray($hright_vcent);
                $event->sheet->setCellValue('I1', '発行日');
                // $event->sheet->setCellValue('H2', '支払No.');
                $event->sheet->setCellValue('I2', '枚数');
                $event->sheet->setCellValue('J1', date('Y/m/d'));//Ngày đặt hàng
                // if (isset($this->data_info['purchase_date'])) {
                //     $date = date("Y/m/d", strtotime($this->data_info['purchase_date']));

                //     $arr_date = explode('/', $date);
                //     if(sizeof($arr_date) > 0){
                //         $date = $arr_date[0].'年'.$arr_date[1].'月'.$arr_date[2].'日';
                //     }
                //     $event->sheet->setCellValue('I1', $date);//Ngày đặt hàng
                // } else {
                //     $event->sheet->setCellValue('I1', null);//Ngày đặt hàng
                // }
                
                // $event->sheet->setCellValue('I2', ;//Mã chi trả
                $event->sheet->setCellValue('J2', $this->page[0] . ' / ' . $this->page[1]);//Số trang
                // merge cell
                $event->sheet->mergeCells('J1:K1');
                $event->sheet->mergeCells('J2:K2');
                // $event->sheet->mergeCells('I3:J3');
                $event->sheet->getStyle('I1:I2')->applyFromArray($styleheader);
                $event->sheet->getStyle('I1:K2')->applyFromArray($all_border); // xét css cho file excel
                // text cố định
                $event->sheet->getDelegate()->getRowDimension(5)->setRowHeight(20);
                $event->sheet->getDelegate()->getRowDimension(6)->setRowHeight(20);
                $event->sheet->setCellValue('A5', 'いつもお世話になります。下記金額をお振込いたします。');
                $event->sheet->setCellValue('A6', 'なお、相違等ございましたらご連絡のほどお願い申し上げます。');
                // Tổng tiền chưa thuế
                $total = 0;
                $total_tax = 0;
                foreach($this->data as $value){
                    $total += $value['total_price'];
                    $total_tax += $value['total_price_tax'];
                }
                $event->sheet->setCellValue('A7', '税抜合計');
                $event->sheet->setCellValue('B7', '消費税');
                $event->sheet->setCellValue('C7', 'お支払予定額');
                $event->sheet->setCellValue('A8', number_format((float)$total, 0, '.', ','));
                $event->sheet->setCellValue('B8', number_format((float)($total_tax - $total), 0, '.', ','));
                $event->sheet->setCellValue('C8', number_format((float)($total + ($total_tax - $total)), 0, '.', ','));
                $event->sheet->getStyle('A7:C7')->applyFromArray($styleheader);
                $event->sheet->getStyle('A7:C7')->applyFromArray($center);
                $event->sheet->getStyle('A7:C8')->applyFromArray($all_border); // xét css cho file excel
                $event->sheet->getStyle('A8:C8')->applyFromArray($center);
                // kỳ hạn thống kê
                $event->sheet->setCellValue('E7', '集計期間');
                $year = $this->data_info['payment_term']['year'];
                $month = $this->data_info['payment_term']['month'];
                $last_date = date("Y/m/t", strtotime($year.'/'.$month.'/01'));
                $event->sheet->setCellValue('E8', $year.'/'.$month.'/01'."～".$last_date);
                $event->sheet->setCellValue('F7', 'お支払予定日');
                if($month == 12 || $month == "12"){
                    $year++;
                    $month = 1;
                }else {
                    $month++;
                }
                $last_month_date = date("Y/m/t", strtotime($year.'/'.$month.'/01'));
                $event->sheet->setCellValue('F8', $last_month_date);
                $event->sheet->getStyle('E7:F7')->applyFromArray($styleheader);
                $event->sheet->getStyle('E7:F8')->applyFromArray($center);
                $event->sheet->getStyle('E7:F8')->applyFromArray($all_border); // xét css cho file excel
                // thông tin công ty
                $event->sheet->mergeCells("I5:K8");// merge cells
                $event->sheet->getDelegate()->getRowDimension(7)->setRowHeight(40);
                $event->sheet->getDelegate()->getRowDimension(8)->setRowHeight(47.5);
                $event->sheet->getDelegate()->getStyle('I5')->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getStyle('E8')->getAlignment()->setWrapText(true);
                $event->sheet->getStyle('I5')->applyFromArray($hleft_vcent); // xét css cho file excel
                $event->sheet->setCellValue('I5', 
                    "NPO法人クローバープロジェクト21\n住所：〒733-0832\n広島県広島市西区草津港1丁目8-1\n広島市中央卸売市場関連棟238番\nTEL：082-276-7500\nFAX：082-276-7502
                ");

                //row 10
                $event->sheet->getDelegate()->getRowDimension(10)->setRowHeight(30);
                $event->sheet->getStyle('A10:K10')->applyFromArray($styleheader); // xét css cho file excel
                $event->sheet->getStyle('A10:K10')->applyFromArray($center);
                $event->sheet->getStyle('A10:K'.$table_data)->applyFromArray($all_border); // xét css cho file excel
                $event->sheet->mergeCells("D10:E10");
                $event->sheet->setCellValue("D10", '商品名');
                for($cell_data = 11; $cell_data <= $table_data; $cell_data++)
                {            
                    $event->sheet->mergeCells("D$cell_data:E$cell_data");// merge cells                    
                    $event->sheet->getDelegate()->getStyle("D$cell_data")->getAlignment()->setWrapText(true);
                    $event->sheet->getStyle('A'.$cell_data)->applyFromArray($center); // xét css cho file excel
                    $event->sheet->getStyle('B'.$cell_data.':D'.$cell_data)->applyFromArray($hleft_vcent); // xét css cho file excel
                    $event->sheet->getStyle('E'.$cell_data.':K'.$cell_data)->applyFromArray($center); // xét css cho file excel
                    $index_data = $cell_data-11;
                    // $height_product_code = $this->getRowcount($this->data[$index_data]['product_code'], 20) * 40;
                    // $height_product_name = $this->getRowcount($this->data[$index_data]['product_name'], 60) * 25;
                    // $height_product = ($height_product_code > $height_product_name) ? $height_product_code : $height_product_name;
                    $event->sheet->setCellValue('A'.$cell_data,$this->data[$index_data]['shipment_date']);
                    $event->sheet->setCellValue('B'.$cell_data,$this->data[$index_data]['purchase_code']);
                    $event->sheet->setCellValue('C'.$cell_data,$this->data[$index_data]['site_name']);
                    $event->sheet->setCellValue('D'.$cell_data,$this->data[$index_data]['product_name']);
                    $event->sheet->setCellValue('F'.$cell_data,$this->data[$index_data]['maker_code']);
                    $quantity_set = $this->data[$index_data]['quantity_set'];
                    if(!is_numeric($quantity_set)){
                        $quantity_set = 1;
                    }
                    $event->sheet->setCellValue('G'.$cell_data, $quantity_set);
                    $event->sheet->getDelegate()->getRowDimension($cell_data)->setRowHeight(47.5);
                    $event->sheet->setCellValue('H'.$cell_data,$this->data[$index_data]['product_info']);
                    $event->sheet->setCellValue('I'.$cell_data,$this->data[$index_data]['quantity_in_set']);
                    $event->sheet->setCellValue('J'.$cell_data,number_format((float)$this->data[$index_data]['cost_price'], 0, '.', ','));
                    $event->sheet->setCellValue('K'.$cell_data,number_format((float)$this->data[$index_data]['total_price'], 0, '.', ','));
                }
                $event->sheet->getDelegate()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $event->sheet->getDelegate()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                // $event->sheet->getDelegate()->getPageSetup()->setVerticalCentered(true);
                $event->sheet->getDelegate()->getPageSetup()->setHorizontalCentered(true);
                $event->sheet->getDelegate()->getPageSetup()->setFitToWidth(1);
                $event->sheet->getDelegate()->getPageSetup()->setFitToHeight(1);
            },
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->data_info['purchase_code'];
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
}