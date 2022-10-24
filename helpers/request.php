<?php
/**
 *      Project EmeRails - Codename Ocarina
 *
 *      Copyright (c) 2008, 2017 Claudio Procida
 *      http://www.emeraldion.it
 *
 */

/**
 *	@class Request
 *	@short Request object with parameter accessors.
 *	@details This class is a convenience wrapper around the superglobal arrays already provided by PHP, like <tt>_REQUEST</tt>.
 */
class Request
{
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
        return self::is_method('GET');
    }

    /**
     *	@fn is_post
     *	@short Returns <tt>TRUE</tt> if the request method is <tt>POST</tt>.
     */
    public function is_post()
    {
        return self::is_method('POST');
    }

    /**
     *	@fn is_delete
     *	@short Returns <tt>TRUE</tt> if the request method is <tt>DELETE</tt>.
     */
    public function is_delete()
    {
        return self::is_method('DELETE');
    }

    /**
     *	@fn is_put
     *	@short Returns <tt>TRUE</tt> if the request method is <tt>PUT</tt>.
     */
    public function is_put()
    {
        return self::is_method('PUT');
    }

    /**
     *	@fn is_head
     *	@short Returns <tt>TRUE</tt> if the request method is <tt>HEAD</tt>.
     */
    public function is_head()
    {
        return self::is_method('HEAD');
    }

    /**
     *	@fn is_method($method)
     *	@short Returns <tt>TRUE</tt> if the request method is <tt>method</tt>.
     *	@param method The method name to be checked.
     */
    public static function is_method($method)
    {
        return !strncmp(@$_SERVER['REQUEST_METHOD'], $method, strlen($method));
    }

    /**
     *	@fn get_parameter($name)
     *	@short Returns the value of the requested parameter.
     *	@param name The name of the parameter to return.
     *	@return The value of the parameter <tt>name</tt>.
     */
    public function get_parameter($name)
    {
        return @$_REQUEST[$name];
    }

    /**
     *	@fn purge_querystring
     *	@short Purges framework related values from the query string
     *	@return The purged query string.
     */
    protected static function purge_querystring()
    {
        $pairs = explode('&', @$_SERVER['QUERY_STRING']);
        $newpairs = array();
        foreach ($pairs as $pair) {
            if (!empty($pair)) {
                @list($key, $value) = explode('=', $pair);
                if (in_array($key, array('action', 'controller', 'id'))) {
                    continue;
                }
                $newpairs[] = $pair;
            }
        }
        return implode('&', $newpairs);
    }
}

?>
