<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2025
 *
 * @format
 */

class Flash
{
    protected static $instance = null;

    protected function __construct()
    {
        $this->message = $_SESSION['flash']['message'];
        $this->type = $_SESSION['flash']['type'];
    }

    public function get_css_class()
    {
        $cls = ['flash', 'msg'];
        switch ($this->type) {
            case 'info':
                $cls[] = 'info';
                break;
            case 'warning':
                $cls[] = 'warning';
                break;
            case 'success':
                $cls[] = 'success';
                break;
            case 'error':
                $cls[] = 'error';
                break;
        }
        return implode(' ', $cls);
    }

    public static function has()
    {
        return isset($_SESSION['flash']);
    }

    public static function get()
    {
        if (isset($_SESSION['flash'])) {
            if (!get_called_class()::$instance) {
                $classname = get_called_class();
                get_called_class()::$instance = new $classname();
            }
            return get_called_class()::$instance;
        }
        return null;
    }

    public static function destroy()
    {
        get_called_class()::$instance = null;
        unset($_SESSION['flash']);
    }
}
