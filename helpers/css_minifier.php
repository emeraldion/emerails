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

use tubalmartin\CssMin\Minifier as CSSMin;

class CSSMinifier implements Minifier
{
    private static $instance;

    private $cssmin;

    private function __construct()
    {
        $this->cssmin = new CSSMin();
    }

    public static function get_instance($options = array())
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function minify($text, $options = array())
    {
        return $this->cssmin->run($text);
    }
}
