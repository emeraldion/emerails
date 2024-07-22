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
 *	@class Request
 *	@short Request object with parameter accessors.
 *	@details This class is a convenience wrapper around the superglobal arrays already provided by PHP, like <tt>_REQUEST</tt>.
 */
class Request
{
    /**
     * @const METHOD_GET
     * @short Name of the GET method
     */
    const METHOD_GET = 'GET';

    /**
     * @const METHOD_POST
     * @short Name of the POST method
     */
    const METHOD_POST = 'POST';

    /**
     * @const METHOD_PUT
     * @short Name of the PUT method
     */
    const METHOD_PUT = 'PUT';

    /**
     * @const METHOD_HEAD
     * @short Name of the HEAD method
     */
    const METHOD_HEAD = 'HEAD';

    /**
     * @const METHOD_OPTIONS
     * @short Name of the OPTIONS method
     */
    const METHOD_OPTIONS = 'OPTIONS';

    /**
     * @const METHOD_DELETE
     * @short Name of the DELETE method
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * @const METHODS
     * @short Names of all supported methods
     */
    const METHODS = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_HEAD,
        self::METHOD_OPTIONS,
        self::METHOD_DELETE
    ];

    /**
     *	@short The query string used in the HTTP request.
     */
    public $querystring;

    /**
     *	@short The method used in the HTTP request (e.g. POST).
     */
    public $method;

    /**
     *	@fn __construct
     *	@short Default constructor.
     */
    public function __construct()
    {
        $this->querystring = self::purge_querystring();
        $this->method = strtoupper(@$_SERVER['REQUEST_METHOD']);
    }

    /**
     *	@fn is_get
     *	@short Returns <tt>TRUE</tt> if the request method is <tt>GET</tt>.
     */
    public function is_get()
    {
        return self::is_method(self::METHOD_GET);
    }

    /**
     *	@fn is_post
     *	@short Returns <tt>TRUE</tt> if the request method is <tt>POST</tt>.
     */
    public function is_post()
    {
        return self::is_method(self::METHOD_POST);
    }

    /**
     *	@fn is_delete
     *	@short Returns <tt>TRUE</tt> if the request method is <tt>DELETE</tt>.
     */
    public function is_delete()
    {
        return self::is_method(self::METHOD_DELETE);
    }

    /**
     *	@fn is_put
     *	@short Returns <tt>TRUE</tt> if the request method is <tt>PUT</tt>.
     */
    public function is_put()
    {
        return self::is_method(self::METHOD_PUT);
    }

    /**
     *	@fn is_head
     *	@short Returns <tt>TRUE</tt> if the request method is <tt>HEAD</tt>.
     */
    public function is_head()
    {
        return self::is_method(self::METHOD_HEAD);
    }

    /**
     *	@fn is_options
     *	@short Returns <tt>TRUE</tt> if the request method is <tt>OPTIONS</tt>.
     */
    public function is_options()
    {
        return self::is_method(self::METHOD_OPTIONS);
    }

    /**
     * @fn is_method($method)
     * @short Returns <tt>TRUE</tt> if the request method is <tt>method</tt>.
     * @param method The method name to be checked.
     */
    public static function is_method($method)
    {
        return !strncmp(@$_SERVER['REQUEST_METHOD'], $method, strlen($method));
    }

    /**
     * @fn get_parameter($name)
     * @short Returns the value of the requested parameter.
     * @param name The name of the parameter to return.
     * @param fallback The fallback value if the parameter is not set.
     * @return The value of the parameter <tt>name</tt>.
     */
    public function get_parameter($name, $fallback = null)
    {
        return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $fallback;
    }

    /**
     * @fn get_all_parameters()
     * @short Returns all request parameters.
     * @return All request parameters.
     */
    public function get_all_parameters()
    {
        $all_params = $_REQUEST;
        return $all_params;
    }

    /**
     *	@fn purge_querystring
     *	@short Purges framework related values from the query string
     *	@return The purged query string.
     */
    protected static function purge_querystring()
    {
        $pairs = explode('&', @$_SERVER['QUERY_STRING']);
        $newpairs = [];
        foreach ($pairs as $pair) {
            if (!empty($pair)) {
                @[$key, $value] = explode('=', $pair);
                if (in_array($key, ['action', 'controller', 'id'])) {
                    continue;
                }
                $newpairs[] = $pair;
            }
        }
        return implode('&', $newpairs);
    }
}
