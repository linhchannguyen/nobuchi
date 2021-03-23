<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // tiêu đề của cột

class NotificationYahooExport implements FromCollection, WithHeadings
{
    private $data = [];
    public function __construct($data = [])
    {
        $this->data = $data;
    }
    public function headings(): array
    {

        return [
            'OrderId',
            'ShipMethod',
            'ShipCompanyCode',
            'ShipInvoiceNumber1',
            'ShipInvoiceNumber2',
            'ShipInvoiceNumberEmptyReason',
            'ShipUrl',
            'ShipDate', 
            'ShipStatus',
            'StoreStatus'
        ];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->data;
    }
}
