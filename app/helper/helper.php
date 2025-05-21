<?php

function responseModel($code, $message, $data)
{
    return response()->json([
        'success' => $code >= 400 ? false : true,
        'status' => $code,
        'message' => $message,
        'data' => $data
    ], $code);
}
