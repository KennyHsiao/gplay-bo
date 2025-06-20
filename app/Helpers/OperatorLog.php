<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class OperatorLog {

    public static function insert($input="", $ip = "", $method = "", $path = "", $user_id = "") {
        DB::table('admin_operation_log')->insert([
            'user_id' => $user_id,
            'path' => $path,
            'method' => $method,
            'ip' => $ip,
            'input' => json_encode(['msg' => $input]),
        ]);
    }
}
