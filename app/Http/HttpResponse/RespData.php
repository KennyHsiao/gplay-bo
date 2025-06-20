<?php

namespace App\Http\HttpResponse;

class RespData {

    public $code;

    public $message;

    public $data;

    public function __construct($code, $message, $data = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public function toJson($httpCode = 200)
    {
        // $tracer = app()->get('context.tracer.globalSpan');

        $respData = [
            'code' => $this->code,
            'message' => $this->message,
            // 'resp_time' => micro_timestamp(),
            // 'trace_id' => dechex($tracer->getContext()->spanId)
        ];
        if (!empty($this->data)) {
            $respData['data'] = $this->data;
        }

        // $tracer->setTag('request_resp', $respData);

        return response()->json($respData, $httpCode);
    }

    public function toArray()
    {

        $respData = [
            'code' => $this->code,
            'message' => $this->message,
            // 'resp_time' => micro_timestamp(),
            // 'trace_id' => dechex($tracer->getContext()->spanId)
        ];
        if (!empty($this->data)) {
            $respData['data'] = $this->data;
        }

        return $respData;
    }
}
