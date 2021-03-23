<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // tiêu đề của cột
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // auto size
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

/**
 * document https://docs.laravel-excel.com/2.1/export/format.html
 * https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/
 */
class ShipmentExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomStartCell, WithColumnFormatting
{
    private $data = [];
    private $data_search = [];
    private $flag_bill = [];
    public function __construct($data = [], $data_search = null, $delivery_method, $flag_bill)
    {
        $this->data = $data;
        $this->data_search = $data_search;
        $this->delivery_method = $delivery_method;
        $this->flag_bill = $flag_bill;
    }
    public function startCell(): string // cell  bắt đầu của table
    {
        return 'A9';
    }
    public function headings(): array
    {
        if ((($this->delivery_method == 5 || $this->delivery_method == 6) && $this->flag_bill == '')  || ($this->flag_bill != '' && $this->flag_bill == 'yupack')) {
            return [
                'サイト名',
                '受注ID',
                '注文主名',
                '注文者住所',
                '注文主〒',
                '注文主TEL',
                '取込日時',
                'お届け先名',
                'お届け先住所',
                'お届け先〒',
                'お届け先TEL',
                '商品コード',
                '商品名',
                '商品購入数',
                '大分類',
                '中分類',
                '小分類',
                'その他1',
                'その他2',
                '発注ID',
                '集荷日時',
                '配達日',
                '配達時間',
                '商品ステータス',
                '配送方法',
            ];
        } else {
            return [
                'サイト名',
                '受注ID',
                '注文主名',
                '注文主住所1',
                '注文主住所2',
                '注文主住所3',
                '注文主〒',
                '注文主TEL',
                '取込日時',
                'お届け先名',
                'お届け先住所1',
                'お届け先住所2',
                'お届け先住所3',
                'お届け先〒',
                'お届け先TEL',
                '商品コード',
                '商品名',
                '商品購入数',
                '大分類',
                '中分類',
                '小分類',
                'その他1',
                'その他2',
                '発注ID',
                '集荷日時',
                '配達日',
                '配達時間',
                '商品ステータス',
                '配送方法',
            ];
        }
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
                $table_data = $count_data + 9;
                // set all font
                $event->sheet->getStyle('A1:AC'.$table_data)->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'ＭＳ Ｐゴシック',
                        'size'      =>  14,
                        'bold'      =>  false,
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
                $borderBottom = [
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            'color' => ['argb' => 'black'],
                        ]
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
                $all_border = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ];
                
                $event->sheet->getColumnDimension('A')->setAutoSize(false);
                $event->sheet->getColumnDimension('A')->setWidth(40);
                $event->sheet->getColumnDimension('B')->setAutoSize(false);
                $event->sheet->getColumnDimension('B')->setWidth(30);
                $event->sheet->getColumnDimension('C')->setAutoSize(false);
                $event->sheet->getColumnDimension('C')->setWidth(30);
                $event->sheet->getColumnDimension('D')->setAutoSize(false);
                $event->sheet->getColumnDimension('D')->setWidth(50);
                $event->sheet->getColumnDimension('E')->setAutoSize(false);
                $event->sheet->getColumnDimension('E')->setWidth(25);
                $event->sheet->getColumnDimension('F')->setAutoSize(false);
                $event->sheet->getColumnDimension('F')->setWidth(25);
                $event->sheet->getColumnDimension('G')->setAutoSize(false);
                $event->sheet->getColumnDimension('G')->setWidth(25);
                $event->sheet->getColumnDimension('H')->setAutoSize(false);
                $event->sheet->getColumnDimension('H')->setWidth(30);
                $event->sheet->getColumnDimension('I')->setAutoSize(false);
                $event->sheet->getColumnDimension('I')->setWidth(50);
                $event->sheet->getColumnDimension('J')->setAutoSize(false);
                $event->sheet->getColumnDimension('J')->setWidth(25);
                $event->sheet->getColumnDimension('K')->setAutoSize(false);
                $event->sheet->getColumnDimension('K')->setWidth(25);
                $event->sheet->getColumnDimension('L')->setAutoSize(false);
                $event->sheet->getColumnDimension('L')->setWidth(25);
                $event->sheet->getColumnDimension('M')->setAutoSize(false);
                $event->sheet->getColumnDimension('M')->setWidth(70);
                $event->sheet->getColumnDimension('N')->setAutoSize(false);
                $event->sheet->getColumnDimension('N')->setWidth(20);
                $event->sheet->getColumnDimension('O')->setAutoSize(false);
                $event->sheet->getColumnDimension('O')->setWidth(20);
                $event->sheet->getColumnDimension('P')->setAutoSize(false);
                $event->sheet->getColumnDimension('P')->setWidth(20);
                $event->sheet->getColumnDimension('Q')->setAutoSize(false);
                $event->sheet->getColumnDimension('Q')->setWidth(20);
                $event->sheet->getColumnDimension('R')->setAutoSize(false);
                $event->sheet->getColumnDimension('R')->setWidth(20);
                $event->sheet->getColumnDimension('S')->setAutoSize(false);
                $event->sheet->getColumnDimension('S')->setWidth(20);
                $event->sheet->getColumnDimension('T')->setAutoSize(false);
                $event->sheet->getColumnDimension('T')->setWidth(20);
                $event->sheet->getColumnDimension('U')->setAutoSize(false);
                $event->sheet->getColumnDimension('U')->setWidth(20);
                $event->sheet->getColumnDimension('V')->setAutoSize(false);
                $event->sheet->getColumnDimension('V')->setWidth(20);
                $event->sheet->getColumnDimension('W')->setAutoSize(false);
                $event->sheet->getColumnDimension('W')->setWidth(20);
                $event->sheet->getColumnDimension('X')->setAutoSize(false);
                $event->sheet->getColumnDimension('X')->setWidth(20);
                $event->sheet->getColumnDimension('Y')->setAutoSize(false);
                $event->sheet->getColumnDimension('Y')->setWidth(20);
                $event->sheet->getColumnDimension('Z')->setAutoSize(false);
                $event->sheet->getColumnDimension('Z')->setWidth(20);
                $event->sheet->getColumnDimension('AA')->setAutoSize(false);
                $event->sheet->getColumnDimension('AA')->setWidth(20);
                $event->sheet->getColumnDimension('AB')->setAutoSize(false);
                $event->sheet->getColumnDimension('AB')->setWidth(20);
                $event->sheet->getColumnDimension('AC')->setAutoSize(false);
                $event->sheet->getColumnDimension('AC')->setWidth(20);
                $event->sheet->mergeCells('A1:C1');// merge cells
                $event->sheet->setCellValue('A1', '受注一覧表'); // xét giá trị của cột A
                $date = Carbon::now();
                if ((($this->delivery_method == 5 || $this->delivery_method == 6) && $this->flag_bill == '')  || ($this->flag_bill != '' && $this->flag_bill == 'yupack')) {
                    $event->sheet->getDelegate()->getStyle('A1:Y'.$table_data)->getAlignment()->setWrapText(true);
                    $event->sheet->getDelegate()->getStyle('Y1')->getAlignment()->setWrapText(false);
                    $event->sheet->setCellValue('Y1', $date.'現在'); // xét giá trị của cột A
                    $event->sheet->getStyle('Y1')->applyFromArray($hright_vcent);
                }else {
                    $event->sheet->getDelegate()->getStyle('A1:AC'.$table_data)->getAlignment()->setWrapText(true);
                    $event->sheet->getDelegate()->getStyle('AC1')->getAlignment()->setWrapText(false);
                    $event->sheet->setCellValue('AC1', $date.'現在'); // xét giá trị của cột A
                    $event->sheet->getStyle('AC1')->applyFromArray($hright_vcent);
                }
                $event->sheet->getStyle('A1')->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'ＭＳ Ｐゴシック',
                        'size'      =>  22,
                        'bold'      =>  true,
                    ),
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ));
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(30);
                $event->sheet->getStyle('A1:C1')->applyFromArray($borderBottom);
                $event->sheet->getStyle('A3:S'.$table_data)->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'ＭＳ Ｐゴシック',
                        'size'      =>  12,
                    ),
                ));
                $event->sheet->getDelegate()->getStyle('A3')->getAlignment()->setWrapText(false);
                $A3_text = '検索条件…ECサイト種別：指定無し '.$this->data_search['range'].'FROM：'.$this->data_search['date_from'].' '.$this->data_search['range'].'TO：'.$this->data_search['date_to'];
                $event->sheet->setCellValue('A3', $A3_text);
                $event->sheet->getStyle('A5:H6')->applyFromArray($center);
                $event->sheet->getStyle('A5:H6')->applyFromArray($all_border); // xét css cho file excel
                $event->sheet->getStyle('A5:H5')->applyFromArray($styleheader); // xét css cho file excel
                $event->sheet->setCellValue('A5', '受注件数');
                $event->sheet->setCellValue('B5', '合計商品金額');
                $event->sheet->setCellValue('C5', '合計配送料');
                $event->sheet->setCellValue('D5', '合計手数料');
                $event->sheet->setCellValue('E5', '合計値引き');
                $event->sheet->setCellValue('F5', '合計金額');
                $event->sheet->setCellValue('G5', '合計ポイント使用');
                $event->sheet->setCellValue('H5', '合計最終支払金額');
                $event->sheet->getDelegate()->getRowDimension(5)->setRowHeight(20);
                $arr_order = [];
                foreach($this->data as $value){
                    if(!in_array($value['order_code'], $arr_order)){
                        array_push($arr_order, $value['order_code']);
                    }
                }
                $event->sheet->setCellValue('A6', count($arr_order));
                $event->sheet->getDelegate()->getRowDimension(9)->setRowHeight(30);
                if ((($this->delivery_method == 5 || $this->delivery_method == 6) && $this->flag_bill == '')  || ($this->flag_bill != '' && $this->flag_bill == 'yupack')) {
                    $event->sheet->getStyle('A9:Y9')->applyFromArray($styleheader); // xét css cho file excel
                    $event->sheet->getStyle('A9:Y9')->applyFromArray($center); // xét css cho file excel
                    $event->sheet->getStyle('A9:Y'.$table_data)->applyFromArray($all_border); // xét css cho file excel
                    $event->sheet->getStyle('A10:Y'.$table_data)->applyFromArray($hleft_vcent);
                }else {
                    $event->sheet->getStyle('A9:AC9')->applyFromArray($styleheader); // xét css cho file excel
                    $event->sheet->getStyle('A9:AC9')->applyFromArray($center); // xét css cho file excel
                    $event->sheet->getStyle('A9:AC'.$table_data)->applyFromArray($all_border); // xét css cho file excel
                    $event->sheet->getStyle('A10:AC'.$table_data)->applyFromArray($hleft_vcent);
                }
                $event->sheet->getDelegate()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $event->sheet->getDelegate()->getPageSetup()->setFitToWidth(1);
                $event->sheet->getDelegate()->getPageSetup()->setFitToHeight(1);
            },
        ];
    }

    public function columnFormats(): array
    {
        if ((($this->delivery_method == 5 || $this->delivery_method == 6) && $this->flag_bill == '')  || ($this->flag_bill != '' && $this->flag_bill == 'yupack')) {
            return [
                'F' => NumberFormat::FORMAT_NUMBER,
                'K' => NumberFormat::FORMAT_TEXT,
            ];
        }else {
            return [
                'H' => NumberFormat::FORMAT_NUMBER,
                'O' => NumberFormat::FORMAT_NUMBER
            ];
        }
    }
}