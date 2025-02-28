<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2024
 *
 * @format
 */

require_once __DIR__ . '/minifier.php';

use JShrink\Minifier as JSShrink;

class JSMinifier implements Minifier
{
    private static $instance;

    private $jsshrink;

    private function __construct() {}

    public static function get_instance($options = [])
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function minify($text, $options = [])
    {
        return JSShrink::minify($text);
    }
}
