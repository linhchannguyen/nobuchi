<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // tiêu đề của cột
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // auto size
use Maatwebsite\Excel\Events\AfterSheet;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;

use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;


class NotificationRakutenExport implements FromCollection, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithCustomCsvSettings
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

        return [
            '注文番号',
            '送付先ID',
            '発送明細ID',
            'お荷物伝票番号',
            '配送会社',
            '発送日'
        ];
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
            'use_bom' => 'true' // true UTF-8
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
