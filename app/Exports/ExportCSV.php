<?php

namespace App\Exports;

class ExportCSV {
    public function Export($filename, $col_title, $col_value, $result){
        $data = array();
        // Title
        if(isset($col_title)) {
            $data_detail = array();
            foreach($col_title as $value_title) {
                $value_title = str_replace(array("\r\n", "\n\r", "\n", "\r",'"'), '', $value_title);
                array_push(
                    $data_detail,
                    mb_convert_encoding($value_title, "SJIS-win", 'UTF-8') // Convert data to shift-JIS
                );
            }
            array_push(
                $data,
                $data_detail
            );
        }
        // Data
        foreach ($result as $key => $value) {
            $data_detail = array();
            foreach($col_value as $value_title) {
                if($value_title != null && $value_title != "") {
                $value_data = str_replace(array("\r\n", "\n\r", "\n", "\r",'"','amp;'), '', $value[$value_title]);
                $value_data = html_entity_decode($value_data);
                } else {
                    $value_data = "";
                }
                array_push(
                    $data_detail,
                    mb_convert_encoding($value_data, "SJIS-win", 'UTF-8') // Convert data to shift-JIS
                );
            }
            array_push(
                $data,
                $data_detail
            );  
        }
    
        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename='.$filename.'.csv');
        header('Content-Type: application/csv; charset=Shift_JIS');
        header('Cache-Control: max-age=0');
        $stream = fopen('php://output', 'w');
        foreach($data as $row){
            fputcsv($stream, $row);
        }
    }
}