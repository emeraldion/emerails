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

/**
 *	@class Response
 *	@short Response object, with convenience methods.
 */
class Response
{
    /**
     *	@attr headers
     *	@short Array of headers to send to the client.
     */
    public $headers = [];

    /**
     *	@attr body
     *	@short The body of the response to send to the client.
     */
    public $body = '';

    /**
     *	@fn flush($only_headers)
     *	@short Flushes headers and response body to the client.
     *	@param only_headers Set to <tt>TRUE</tt> to flush only headers.
     */
    public function flush($only_headers = false)
    {
        foreach ($this->headers as $header) {
            header("{$header[0]}: {$header[1]}");
        }
        header('Content-Length: ' . strlen($this->body));
        if (!$only_headers) {
            print $this->body;
        }
        exit();
    }

    /**
     *	@fn add_header($name, $value)
     *	@short Sets a header to be sent to the client.
     *	@param name The header name.
     *	@param value The header value.
     */
    public function add_header($name, $value)
    {
        $this->headers[] = [$name, $value];
    }
}
