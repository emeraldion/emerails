<?php
/**
 *	Project EmeRails - Codename Ocarina
 *
 *	Copyright (c) 2008, 2017 Claudio Procida
 *	http://www.emeraldion.it
 *
 */

require_once dirname(__FILE__) . "/base_controller.php";

/**
 *	@class ErrorController
 *	@short Controller class for HTTP errors.
 *	@details ErrorController is responsible for handling HTTP errors. Feel free to improve this class
 *	by adding your custom error handling code.
 */
class ErrorController extends BaseController
{
    /**
     *	@fn _404
     *	@short Handles 404 Not Found HTTP errors.
     */
    public function _404()
    {
        $this->error();
    }

    /**
     *	@fn _403
     *	@short Handles 403 Forbidden HTTP errors.
     */
    public function _403()
    {
        $this->error();
    }

    /**
     *	@fn _405
     *	@short Handles 405 Method Not Supported HTTP errors.
     */
    public function _405()
    {
        $this->error();
    }

    /**
     *	@fn _500
     *	@short Handles 500 Internal Server Error HTTP errors.
     */
    public function _500()
    {
        $this->error();
    }

    /**
     *	@fn error
     *	@short Private common error handler.
     */
    private function error()
    {
        $this->render(array('layout' => 'default'));
    }
}
?>
