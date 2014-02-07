<?php namespace PragmaRX\Support;

use Illuminate\Http\Response as IlluminateResponse;

class Response {

    public static function make($content = '', $status = 200, array $headers = array())
    {
        return new IlluminateResponse($content, $status, $headers);
    }

}
