<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // tiêu đề của cột
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // auto size
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ShipmenSagawaIIExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomCsvSettings
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
            'お届け先住所１',
            'お届け先名称１',
            'お届け先電話番号',
            'お届け先郵便番号',
            'ご依頼主電話番号',
            'ご依頼主郵便番号',
            'ご依頼主住所１',
            'ご依頼主名称１',
            '品名１',
            '品名２',
            '品名３',
            '品名４',
            '品名５',
            '便種（商品）',
            '配達日',
            '配達指定時間帯',
            '代引金額'
        ];
        foreach ($headers as $key => $value) {
            $value = mb_convert_encoding($value, "SJIS", "auto");
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
        $arr_data = [];
        foreach ($this->data as $key => $value) {
            $value['ship_add'] = mb_convert_encoding($value['ship_add'], "SJIS", "auto");
            $value['ship_name'] = mb_convert_encoding($value['ship_name'], "SJIS", "auto");
            $value['ship_phone'] = mb_convert_encoding($value['ship_phone'], "SJIS", "auto");
            $value['ship_zip'] = mb_convert_encoding($value['ship_zip'], "SJIS", "auto");
            $value['buyer_tel1'] = mb_convert_encoding($value['buyer_tel1'], "SJIS", "auto");
            $value['buyer_zip'] = mb_convert_encoding($value['buyer_zip'], "SJIS", "auto");
            $value['buyer_add'] = mb_convert_encoding($value['buyer_add'], "SJIS", "auto");
            $value['buyer_name'] = mb_convert_encoding($value['buyer_name'], "SJIS", "auto");
            $value['order_code'] = mb_convert_encoding($value['order_code'], "SJIS", "auto");
            $value['purchase_code'] = mb_convert_encoding($value['purchase_code'], "SJIS", "auto");
            $value['product_name'] = mb_convert_encoding($value['product_name'], "SJIS", "auto");
            $value['product_name4'] = mb_convert_encoding($value['product_name4'], "SJIS", "auto");
            $value['product_name5'] = mb_convert_encoding($value['product_name5'], "SJIS", "auto");
            $value['productStatus'] = mb_convert_encoding($value['productStatus'], "SJIS", "auto");
            $value['delivery_date'] = mb_convert_encoding($value['delivery_date'], "SJIS", "auto");
            $value['shipment_time'] = mb_convert_encoding($value['shipment_time'], "SJIS", "auto");
            $value['money_daibiki'] = mb_convert_encoding($value['money_daibiki'], "SJIS", "auto");
            array_push($arr_data, $value);
        }
        $this->data = collect($arr_data);
        return $this->data;
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class  => function(AfterSheet $event) {
                $event->sheet->getStyle('A2')->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(50);
            },
        ];
    }
}