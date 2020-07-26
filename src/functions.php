<?php
if (!function_exists('request')) {
    /**
     * request
     *
     * @param  mixed $key
     * @param  mixed $defualt
     * @return Yildirim\Routing\Request
     */
    function request($key = null, $defualt = null)
    {
        if (!app()->has('request')) {
            app()->setInstance('request', app(Yildirim\Routing\Request::class));
        }

        return $key ? (app('request')->{$key} ?: $defualt): app('request');
    }
}

if (!function_exists('csrf')) {
    /**
     * csrf
     *
     * @return string
     */
    function csrf()
    {
        return session()->csrf();
    }
}
