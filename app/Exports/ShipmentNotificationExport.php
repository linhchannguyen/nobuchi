<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // tiêu đề của cột
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // auto size
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

/**
 * document https://docs.laravel-excel.com/2.1/export/format.html
 * https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/
 */
class ShipmentNotificationExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomStartCell,WithCustomCsvSettings, WithColumnFormatting
{
    private $data = [];
    private $site_type = '';
    public function __construct($data = [], $site_type = '')
    {
        $this->data = $data;
        $this->site_type = $site_type;
    }
    public function startCell(): string // cell  bắt đầu của table
    {
        return 'A1';
    }
    public function headings(): array
    {
        $heading = [];
        if($this->site_type == "rakuten"){            
            $heading = [
                '注文番号',
                '送付先ID',
                '発送明細ID',
                'お荷物伝票番号',
                '配送会社',
                '発送日'
            ];
        }else if($this->site_type == "yahoo"){
            $heading = [
                'OrderId',
                'ShipCompanyCode',
                'ShipInvoiceNumber1',
                'ShipInvoiceNumber2',
                'ShipDate',
                'ShipStatus',
            ];
        }
        return $heading;
    }
    
    public function getCsvSettings(): array
    {
        return [
            'enclosure' => false
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
                $table_data = $count_data + 1;
                    $event->sheet->getStyle('A1:F'.$table_data)->applyFromArray(array(
                        'font' => array(
                            'name'      =>  'ＭＳ ゴシック',
                            'size'      =>  11,
                        ),
                    ));
                    $event->sheet->getColumnDimension('A')->setAutoSize(false);
                    $event->sheet->getColumnDimension('B')->setAutoSize(false);
                    $event->sheet->getColumnDimension('C')->setAutoSize(false);
                    $event->sheet->getColumnDimension('D')->setAutoSize(false);
                    $event->sheet->getColumnDimension('E')->setAutoSize(false);
                    $event->sheet->getColumnDimension('F')->setAutoSize(false);
            },
        ];
    }

    public function columnFormats(): array
    {
        if($this->site_type == 'rakuten'){
            return [
                'D' => NumberFormat::FORMAT_NUMBER,
                'F' => NumberFormat::FORMAT_TEXT,
            ];
        }else {
            return [
                'A' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_NUMBER
            ];
        }
    }
}