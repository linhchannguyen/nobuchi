<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // tiêu đề của cột
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // auto size
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ShipmentBillExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomCsvSettings
{
    private $data = [];
    public function __construct($data = [])
    {
        $this->data = $data;
    }
    public function startCell(): string // cell  bắt đầu của table
    {
        return 'A1';
    }
    public function headings(): array
    {
        $headers = [
            '受注ID',
            '発注ID',
            '送り状番号',
            '配送方法',
            '発注ステータス',
            '出荷日'
        ];
        foreach ($headers as $key => $value) {
            $value = mb_convert_encoding($value, "SJIS", "UTF-8");
        }
        return $headers;
    }
    /**
     * function config file csv
     * document https://docs.laravel-excel.com/3.1/exports/custom-csv-settings.html
     * Available settings
     * [delimiter, enclosure, line_ending, use_bom,include_separator_line, excel_compatibility]
     */
    public function getCsvSettings(): array
    {
        return [
            'use_bom' => false,
            'enclosure' => false
        ];
    }
    public function collection() {
        foreach ($this->data as $key => $value) {
            $value['order_code'] = mb_convert_encoding($value['order_code'], "SJIS", "UTF-8");
            $value['purchase_code'] = mb_convert_encoding($value['purchase_code'], "SJIS", "UTF-8");
            $value['shipment_code'] = mb_convert_encoding($value['shipment_code'], "SJIS", "UTF-8");
            $value['delivery_method'] = mb_convert_encoding($value['delivery_method'], "SJIS", "UTF-8");
            $value['purchase_status'] = mb_convert_encoding($value['purchase_status'], "SJIS", "UTF-8");
            $value['delivery_date'] = mb_convert_encoding($value['delivery_date'], "SJIS", "UTF-8");
        }
        return $this->data;
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class  => function(AfterSheet $event) {     
                $event->sheet->getColumnDimension('A')->setAutoSize(true);
                // $event->sheet->getColumnDimension('A')->setWidth(30);
                $event->sheet->getColumnDimension('B')->setAutoSize(true);
                // $event->sheet->getColumnDimension('B')->setWidth(30);
                $event->sheet->getColumnDimension('C')->setAutoSize(true);
                // $event->sheet->getColumnDimension('C')->setWidth(30);
                $event->sheet->getColumnDimension('D')->setAutoSize(true);
                // $event->sheet->getColumnDimension('D')->setWidth(40);
                $event->sheet->getColumnDimension('E')->setAutoSize(true);
                // $event->sheet->getColumnDimension('E')->setWidth(30);
                $event->sheet->getColumnDimension('F')->setAutoSize(true);
                // $event->sheet->getColumnDimension('F')->setWidth(30);
                $event->sheet->getStyle('A2')->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(50);
            },
        ];
    }
}