<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // tiêu đề của cột
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // auto size
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;

/**
 * document https://docs.laravel-excel.com/2.1/export/format.html
 */
class OrdersExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    private $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function startCell(): string // cell  bắt đầu của table
    {
        return 'A4';
    }
    public function headings(): array
    {
        return [
            'id', 
            'site_type', 
            'import_id', 
            'purchase_id', 
            'ship_id', 
            'order_code', 
            'order_date', 
            'buyer_name1', 
            'buyer_name2', 
            'buyer_name1_kana', 
            'buyer_name2_kana', 
            'buyer_country', 
            'buyer_address_1', 
            'buyer_address_2', 
            'buyer_address_3', 
            'buyer_address_1_kana', 
            'buyer_address_2_kana', 
            'buyer_address_3_kana', 
            'buyer_email', 
            'buyer_zip1', 
            'buyer_zip2', 
            'buyer_tel1', 
            'buyer_tel2', 
            'buyer_tel3', 
            'buyer_sex', 
            'buyer_birthday', 
            'tax', 
            'fax', 
            'charge', 
            'sub_total', 
            'price_untax', 
            'order_sub_total', 
            'order_delivery_fee', 
            'order_gift_wrap_price', 
            'order_tax', 
            'order_charge', 
            'order_discount', 
            'order_total', 
            'use_point', 
            'payment_total', 
            'order_site_charge', 
            'comments', 
            'noshi_type', 
            'noshi_name', 
            'payment_id', 
            'payment_method', 
            'credit_type', 
            'supplier_id', 
            'supplier_name', 
            'supplier_zip1', 
            'supplier_zip2', 
            'supplier_addr1', 
            'supplier_addr2', 
            'supplier_addr3', 
            'supplier_tel1', 
            'supplier_tel2', 
            'supplier_tel3', 
            'supplier_code_sagawa', 
            'supplier_code_kuroneko', 
            'cargo_schedule_day', 
            'cargo_schedule_time_from', 
            'cargo_schedule_time_to', 
            'status', 
            'support_cus', 
            'flag_confirm', 
            'purchase_date', 
            'delivery_date', 
            'comment', 
            'money_daibiki', 
            'quantity_service', 
            'price_service', 
            'total_service', 
            'created_at', 
            'updated_at'
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
                $event->sheet->mergeCells('A1:A2');// merge cells
                $event->sheet->setCellValue('A1', 'AAAA'); // xét giá trị của cột A
                $cellRange = 'A4:W4'; // chọn cell để xét css
                $styleArray = [ // xet css cho file excel
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'black'],
                        ],
                    ],
                ];
                $event->sheet->getStyle($cellRange)->applyFromArray($styleArray); // xét css cho file excel
                $event->sheet->getDelegate()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $event->sheet->getDelegate()->getPageSetup()->setFitToWidth(1);
                $event->sheet->getDelegate()->getPageSetup()->setFitToHeight(1);
            },
        ];
    }
}