<?php

namespace App\Admin\Controllers\Traits;

use Illuminate\Http\Request;
use App\Admin\Controllers\Traits\Common;
use App\Models\Player\Member;
use Xn\Admin\Facades\Admin;

trait LINEBotUserExt
{

    use Common;

    /**
     * exportXLS
     */
    public function exportXLS() {
        // $start_at = request()->input('start_at');
        // $end_at = request()->input('end_at');
        // $myFile = \Excel::create('Filename', function($excel) use($start_at, $end_at) {

        //     $excel->sheet('Sheetname', function($sheet) use($start_at, $end_at) {
        //         //交易日期  單號    付款方式  姓名  聯絡電話    Email	地址	金額 發票 統一編號 公司名稱 備註
        //         $a = $this->getExportData($start_at, $end_at);
        //         \Log::info(json_encode($a));
        //         $rows = collect($this->getExportData($start_at, $end_at))->map(function ($item) {
        //             return array_only($item, [
        //                 'display_name',
        //                 'created_at',
        //                 'info',
        //             ]);
        //         });
        //         $sheet->appendRow(['顯示名稱', '加入日期', '姓名', '性別', '生日', '聯絡電話', '電子信箱', '郵遞區號', '縣市', '行政區', '地址']);

        //         foreach($rows as $row) {
        //             $sheet->appendRow([
        //                 $row['display_name'],
        //                 $row['created_at'],
        //                 "{$row['info']['last_name']}{$row['info']['first_name']}",
        //                 $row['info']['gender'],
        //                 $row['info']['birth_date'],
        //                 $row['info']['phone'],
        //                 $row['info']['email'],
        //                 $row['info']['zipcode'],
        //                 $row['info']['county'],
        //                 $row['info']['district'],
        //                 $row['info']['address']
        //             ]);
        //         }

        //     });

        // });
        // $myFile = $myFile->string('xlsx'); //change xlsx for the format you want, default is xls
        // $response =  array(
        //    'name' => "$start_at - $end_at -會員資料", //no extention needed
        //    'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($myFile) //mime type of used format
        // );
        // return response()->json($response, 200);
    }

    private function getExportData($start_at, $end_at) {
        return Member::with('info')->where(function($q) use($start_at, $end_at){
            # 管理者ID
            $adminId = Admin::user()->merchant_code;
            #
            $q->whereRaw("date_format(created_at, '%Y-%m-%d')>='$start_at'")
            ->whereRaw("date_format(created_at, '%Y-%m-%d')<='$end_at'")
            ->whereRaw("merchant_code ='{$adminId}'");
        })->get()->toArray();
    }
}
