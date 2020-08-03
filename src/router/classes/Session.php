<?php
namespace Yildirim\Classes;

use Yildirim\Traits\HasAttributes;

class Session
{

    use HasAttributes;

    /**
     * __construct
     *
     * @param  mixed $attributes
     * @return void
     */
    public function __construct()
    {
        session_start();
    }

    /**
     * get
     *
     * @param  mixed $key
     * @return  mixed
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * csrf
     *
     * @return void
     */
    public function csrf()
    {
        if (!$this->has('post_csrf')) {
            $this->put('post_csrf', strtoupper(md5(time() . rand(1, 10000000000) . time())));
        }

        return $this->get('post_csrf');
    }

    /**
     * put
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return mixed
     */
    public function put($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * forget
     *
     * @return void
     */
    public function forget($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * has
     *
     * @param  mixed $key
     * @return bool
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

}
