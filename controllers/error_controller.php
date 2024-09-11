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

require_once __DIR__ . '/base_controller.php';

/**
 *	@class ErrorController
 *	@short Controller class for HTTP errors.
 *	@details ErrorController is responsible for handling HTTP errors. Feel free to improve this class
 *	by adding your custom error handling code.
 */
class ErrorController extends BaseController
{
    /**
     *	@fn _400
     *	@short Handles 400 Bad Request HTTP errors.
     */
    public function _400()
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
     *	@fn _404
     *	@short Handles 404 Not Found HTTP errors.
     */
    public function _404()
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
     *	@fn _409
     *	@short Handles 409 Conflict HTTP errors.
     */
    public function _409()
    {
        $this->error();
    }

    /**
     *	@fn _418
     *	@short Handles 418 I'm a Teapot HTTP errors.
     */
    public function _418()
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
     *	@fn _501
     *	@short Handles 501 Not Implemented HTTP errors.
     */
    public function _501()
    {
        $this->error();
    }

    /**
     *	@fn error
     *	@short Private common error handler.
     */
    private function error()
    {
        $this->render(['layout' => 'default']);
    }
}
