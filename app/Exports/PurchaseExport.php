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
 * https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/
 */
class PurchaseExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomStartCell, WithTitle, WithColumnFormatting, WithColumnWidths
{
    private $data = [];
    private $data_info = [];
    private $page = [];
    public function __construct($data = [], $data_info = null, $page = [])
    {
        $this->data = $data;
        $this->data_info = $data_info;
        $this->page = $page;
    }
    public function startCell(): string // cell  bắt đầu của table
    {
        return 'A6';
    }
    public function headings(): array
    {
        return [
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
                $table_data = $count_data + 6;
                // set all font
                $event->sheet->getStyle('A1:L'.($table_data + 4))->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'ＭＳ Ｐゴシック',
                        'size'      =>  14,
                    ),
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
                $center = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];
                $center_bold = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => [
                        'name'      =>  'ＭＳ Ｐゴシック',
                        'bold'      =>  true,
                    ]
                ];
                $hleft = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
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
                $hleft_vcent_bold = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => [
                        'name'      =>  'ＭＳ Ｐゴシック',
                        'bold'      =>  true,
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
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(26);
                $event->sheet->getDelegate()->getRowDimension(2)->setRowHeight(26);
                $event->sheet->getDelegate()->getRowDimension(3)->setRowHeight(18);
                $event->sheet->getDelegate()->getRowDimension(4)->setRowHeight(16);
                $event->sheet->getDelegate()->getRowDimension(5)->setRowHeight(16); 
                $event->sheet->getDelegate()->getRowDimension(6)->setRowHeight(32); 
                $event->sheet->setCellValue('A1', '発注一覧表');
                $event->sheet->getStyle('A1')->applyFromArray($hleft_vcent);
                $event->sheet->getStyle('A1')->applyFromArray(array(
                    'font' => array(
                        'size'      =>  24,
                        'bold'      =>  true,
                    ),
                    $hleft
                ));
                $event->sheet->setCellValue('B2', $this->data_info[0]." 御中");
                $event->sheet->getStyle('B2')->applyFromArray(array(
                    'font' => array(
                        'size'      =>  18,
                        'bold'      =>  true,
                        'underline' => true
                    ),
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM,
                    ]
                ));
                $event->sheet->getStyle('E1:F4')->applyFromArray($center_bold);
                $event->sheet->getStyle('J1:J2')->applyFromArray($center_bold);
                $event->sheet->getStyle('L1:L5')->applyFromArray($hright_vcent);
                $event->sheet->setCellValue('B3', 'いつもお世話になります。');
                $event->sheet->setCellValue('B4', '下記の通り発注致しますのでよろしくお願い致します。');
                $event->sheet->getDelegate()->getStyle("B3:B4")->getFont()->setSize(12);
                $event->sheet->setCellValue('E1', '御社確認印');
                $event->sheet->mergeCells('E2:E4');
                $event->sheet->setCellValue('F1', '弊社確認印');
                $event->sheet->mergeCells('F2:F4');
                $event->sheet->mergeCells('J1:K1');
                $event->sheet->mergeCells('J2:K2');
                $event->sheet->setCellValue('J1', '発注日');
                $event->sheet->setCellValue('J2', 'ページ番号');
                $event->sheet->setCellValue('G4', '※納品書兼用');
                $event->sheet->setCellValue('L3', 'NPO法人クローバープロジェクト21');
                $event->sheet->getStyle("L3")->applyFromArray(array(
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM,
                    ]
                ));
                $event->sheet->setCellValue('L4', 'TEL：082-276-7500');
                $event->sheet->setCellValue('L5', 'FAX：082-276-7502');
                $event->sheet->getDelegate()->getStyle("G4")->getFont()->setSize(12);
                $event->sheet->getDelegate()->getStyle("L1:L3")->getFont()->setSize(14);
                $event->sheet->getDelegate()->getStyle("L4:L5")->getFont()->setSize(12);
                // merge cell
                if($this->data_info[1] != null){
                    $date = date("Y/m/d", strtotime($this->data_info[1]));
                    $arr_date = explode('/', $date);
                    if(sizeof($arr_date) > 0){
                        $date = $arr_date[0].'年'.$arr_date[1].'月'.$arr_date[2].'日';
                    }
                    $event->sheet->setCellValue('L1', $date);
                }
                $event->sheet->setCellValue('L2', $this->page[0] . ' / ' . $this->page[1]);
                $event->sheet->getStyle('E1:E4')->applyFromArray($all_border);
                $event->sheet->getStyle('F1:F4')->applyFromArray($all_border);
                $event->sheet->getStyle('J1:K2')->applyFromArray($all_border);
                $event->sheet->getStyle('L1:L2')->applyFromArray($all_border);
                $event->sheet->getStyle('E1:F4')->applyFromArray($borderMedium);
                $event->sheet->getStyle('J1:L2')->applyFromArray($borderMedium);
                $event->sheet->getDelegate()->getStyle('L1:L2')->getNumberFormat()->setFormatCode('@');
                // set header
                $styleheader = [
                    'font' => $styleBlod['font'],
                    'borders' => $styleborder['borders'],
                    'alignment' => $styleAlignment['alignment'],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                        'color' => ['argb' => 'E9ECEF']
                    ],
                ]; 
                $event->sheet->getStyle('A6')->applyFromArray(array(
                    'font' => array(
                        'size' =>  10,
                        'bold' => true
                    )
                ));
                $event->sheet->getDelegate()->getStyle('A6')->getAlignment()->setShrinkToFit(true);
                $event->sheet->getStyle('A6:L'.($table_data))->applyFromArray($all_border);
                $event->sheet->getStyle('A6:L6')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);
                $event->sheet->getStyle('A6:L6')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $event->sheet->setCellValue('A6','No.');
                $event->sheet->setCellValue('B6','発注番号');
                $event->sheet->setCellValue('C6','商品名');
                $event->sheet->setCellValue('D6','商品コード');
                $event->sheet->setCellValue('E6','集荷日');
                $event->sheet->setCellValue('F6','送り状番号');
                $event->sheet->setCellValue('G6',"規格\n(入数)");
                $event->sheet->setCellValue('H6','発注数');
                $event->sheet->setCellValue('I6','合計数');
                $event->sheet->setCellValue('J6',"原単価\n(税抜)");
                $event->sheet->setCellValue('K6',"原価合計\n(税抜)");
                $event->sheet->setCellValue('L6','備考');
                $event->sheet->getDelegate()->getStyle("G6:K6")->getAlignment()->setWrapText(true);
                $event->sheet->getStyle('A6:L6')->applyFromArray($center_bold);
                $event->sheet->getDelegate()->getStyle("G6:I6")->getFont()->setSize(12);
                $event->sheet->getDelegate()->getStyle("J6:K6")->getFont()->setSize(11);
                $event->sheet->getDelegate()->getStyle("L6")->getFont()->setSize(14);
                $event->sheet->getDelegate()->getStyle('F23')->getAlignment()->setWrapText(true);
                $event->sheet->getStyle('L6')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $no = 0;
                for($cell_data = 7; $cell_data <= $table_data; $cell_data++)
                {                
                    $index = $cell_data-7;
                    $event->sheet->setCellValue("A$cell_data", ++$no);
                    $event->sheet->getDelegate()->getStyle("B$cell_data")->getNumberFormat()->setFormatCode('0');
                    $event->sheet->setCellValue("B$cell_data", $this->data[$index]['purchase_code']);
                    $event->sheet->setCellValue("C$cell_data", $this->data[$index]['product_name']);
                    $event->sheet->setCellValue("D$cell_data", $this->data[$index]['maker_code']);
                    $event->sheet->setCellValue("E$cell_data", isset($this->data[$index]['es_delivery_date']) ? date('Y/m/d', strtotime($this->data[$index]['es_delivery_date'])) : null);    
                    $event->sheet->getDelegate()->getStyle("E$cell_data")->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                    $event->sheet->setCellValue("F$cell_data", $this->data[$index]['shipment_code']);
                    $event->sheet->getDelegate()->getStyle("F$cell_data")->getNumberFormat()->setFormatCode('0');
                    $event->sheet->setCellValue("G$cell_data", $this->data[$index]['quantity_set']);
                    $event->sheet->setCellValue("H$cell_data", $this->data[$index]['quantity']);
                    $event->sheet->setCellValue("I$cell_data", $this->data[$index]['total_quantity']);
                    $event->sheet->setCellValue("J$cell_data", number_format((float)$this->data[$index]['cost_price'], 0, '.', ','));
                    $event->sheet->setCellValue("K$cell_data", number_format((float)$this->data[$index]['total_price'], 0, '.', ','));
                    $event->sheet->getDelegate()->getStyle("J$cell_data:K$cell_data")->getNumberFormat()->setFormatCode('#,##0');
                    $event->sheet->setCellValue("L$cell_data", $this->data[$index]['comments']);
                    $event->sheet->getDelegate()->getStyle('A'.$cell_data)->getFont()->setSize(10);
                    $event->sheet->getDelegate()->getStyle("B$cell_data:C$cell_data")->getFont()->setSize(14);
                    $event->sheet->getDelegate()->getStyle('D'.$cell_data)->getFont()->setSize(12);
                    $event->sheet->getDelegate()->getStyle('E'.$cell_data.':F'.$cell_data)->getFont()->setSize(14);
                    $event->sheet->getDelegate()->getStyle('G'.$cell_data.':H'.$cell_data)->getFont()->setSize(16);
                    $event->sheet->getDelegate()->getStyle('I'.$cell_data)->getFont()->setSize(22);
                    $event->sheet->getDelegate()->getStyle('J'.$cell_data.':K'.$cell_data)->getFont()->setSize(14);
                    $event->sheet->getDelegate()->getStyle('L'.$cell_data)->getFont()->setSize(11);
                    
                    $event->sheet->getStyle("A$cell_data")->applyFromArray($center);
                    $event->sheet->getDelegate()->getStyle("A$cell_data")->getAlignment()->setShrinkToFit(true);
                    $event->sheet->getDelegate()->getStyle("B$cell_data:D$cell_data")->getAlignment()->setWrapText(true);
                    $event->sheet->getDelegate()->getStyle("E$cell_data")->getAlignment()->setShrinkToFit(true);
                    $event->sheet->getDelegate()->getStyle("F$cell_data")->getAlignment()->setWrapText(true);
                    $event->sheet->getDelegate()->getStyle("G$cell_data:K$cell_data")->getAlignment()->setShrinkToFit(true);
                    $event->sheet->getDelegate()->getStyle("L$cell_data")->getAlignment()->setWrapText(true);
                    $event->sheet->getStyle("B$cell_data")->applyFromArray(array(
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM,
                        ])
                    );
                    $event->sheet->getStyle("C$cell_data")->applyFromArray($hleft_vcent);
                    $event->sheet->getStyle("D$cell_data")->applyFromArray($center);
                    $event->sheet->getStyle('E'.$cell_data)->applyFromArray($hright_vcent);
                    $event->sheet->getStyle("F$cell_data:I$cell_data")->applyFromArray($center);
                    $event->sheet->getStyle("J$cell_data:K$cell_data")->applyFromArray($hright_vcent);
                    $event->sheet->getStyle("L$cell_data")->applyFromArray(array(
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                        ],
                        'font' => array(
                            'size' =>  11
                        )
                    ));

                    $event->sheet->getDelegate()->getRowDimension($cell_data)->setRowHeight(50);
                    $event->sheet->getStyle("L$cell_data")->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                    if($cell_data == $table_data){
                        $event->sheet->getStyle("A$cell_data:L$cell_data")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                    }
                }
                // cell total
                $total_cost_price = 0.0;
                $total_price = 0.0;
                if(sizeof($this->data) > 0){
                    foreach($this->data as $value){
                        $total_cost_price += $value['quantity'] * $value['cost_price'];
                        $total_price += $value['total_price'];
                    }
                }
                $cell_total = $table_data + 1;
                $cell_original_price = $table_data + 2;
                $event->sheet->getDelegate()->getRowDimension($cell_total)->setRowHeight(32);
                $event->sheet->getDelegate()->getRowDimension($cell_original_price)->setRowHeight(32);
                // cell nguyên giá sau khi chỉnh sửa
                $event->sheet->mergeCells("G$cell_total:J$cell_total");
                $event->sheet->mergeCells("G$cell_original_price:J$cell_original_price");
                $event->sheet->mergeCells("K$cell_total:L$cell_total");
                $event->sheet->mergeCells("K$cell_original_price:L$cell_original_price");
                $event->sheet->getStyle('G'.$cell_total.':L'.($cell_original_price))->applyFromArray($all_border);
                $event->sheet->getStyle('G'.$cell_total.':L'.($cell_original_price))->applyFromArray($borderMedium);
                $event->sheet->setCellValue("G$cell_total", '原価金額合計');
                $event->sheet->setCellValue("G$cell_original_price", '訂正後原価金額合計');
                $event->sheet->setCellValue("K$cell_total", $total_cost_price);// number_format((float)$total_cost_price, 0, '.', ','));
                $event->sheet->setCellValue("K$cell_original_price", $total_price);//number_format((float)$total_price, 0, '.', ','));
                $event->sheet->getDelegate()->getStyle('K'.$cell_total.':K'.($cell_original_price))->getFont()->setSize(16);
                $event->sheet->getStyle('G'.$cell_total.':G'.$cell_original_price)->applyFromArray($center_bold);
                $event->sheet->getStyle('K'.$cell_total.':K'.$cell_original_price)->applyFromArray($hright_vcent);
                $event->sheet->getDelegate()->getStyle("K$cell_total:L$cell_original_price")->getNumberFormat()->setFormatCode('#,##0');
                $event->sheet->getDelegate()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $event->sheet->getDelegate()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                // $event->sheet->getDelegate()->getPageSetup()->setVerticalCentered(true);
                $event->sheet->getDelegate()->getPageSetup()->setHorizontalCentered(true);
                // $event->sheet->getDelegate()->getPageSetup()->setFitToWidth(1);
                // $event->sheet->getDelegate()->getPageSetup()->setFitToHeight(1);
                $event->sheet->getDelegate()->getPageMargins()->setTop(0.748031496062992);
                $event->sheet->getDelegate()->getPageMargins()->setRight(0.708661417322835);
                $event->sheet->getDelegate()->getPageMargins()->setBottom(0.708661417322835);
                $event->sheet->getDelegate()->getPageMargins()->setLeft(0.748031496062992);
                $event->sheet->getDelegate()->getPageSetup()->setFitToPage(1);
            },
        ];
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

    /**
     * @return string
     */
    public function title(): string
    {
        return '発注一覧表';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 3.71,
            'B' => 24.71,
            'C' => 46.71,
            'D' => 20.71,
            'E' => 16.71,
            'F' => 16.71,
            'G' => 7.71,
            'H' => 7.71,
            'I' => 7.71,
            'J' => 9.71,
            'K' => 9.71,
            'L' => 26.71,
        ];
    }

    public function columnFormats(): array
    {
        return [
            // 'B' => NumberFormat::FORMAT_NUMBER,
            // 'C' => NumberFormat::FORMAT_NUMBER,
            // 'K' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
        ];
    }
}