<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2026
 *
 * @format
 */

/**
 *	@class Response
 *	@short Response object, with convenience methods.
 */
class Response
{
    /* Response statuses, sorted by status code */
    const STATUS_CONTINUE = 100;
    const STATUS_SWITCHING_PROTOCOLS = 101;
    const STATUS_PROCESSING = 102;
    const STATUS_EARLY_HINTS = 103;
    const STATUS_OK = 200;
    const STATUS_CREATED = 201;
    const STATUS_ACCEPTED = 202;
    const STATUS_NON_AUTHORITATIVE_INFORMATION = 203;
    const STATUS_NO_CONTENT = 204;
    const STATUS_RESET_CONTENT = 205;
    const STATUS_PARTIAL_CONTENT = 206;
    const STATUS_MULTI_STATUS = 207;
    const STATUS_ALREADY_REPORTED = 208;
    const STATUS_IM_USED = 226;
    const STATUS_MULTIPLE_CHOICES = 300;
    const STATUS_MOVED_PERMANENTLY = 301;
    const STATUS_FOUND = 302;
    const STATUS_SEE_OTHER = 303;
    const STATUS_NOT_MODIFIED = 304;
    const STATUS_TEMPORARY_REDIRECT = 307;
    const STATUS_PERMANENT_REDIRECT = 308;
    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_PAYMENT_REQUIRED = 402;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_METHOD_NOT_ALLOWED = 405;
    const STATUS_NOT_ACCEPTABLE = 406;
    const STATUS_PROXY_AUTHENTICATION_REQUIRED = 407;
    const STATUS_REQUEST_TIMEOUT = 408;
    const STATUS_CONFLICT = 409;
    const STATUS_GONE = 410;
    const STATUS_LENGTH_REQUIRED = 411;
    const STATUS_PRECONDITION_FAILED = 412;
    const STATUS_CONTENT_TOO_LARGE = 413;
    const STATUS_URI_TOO_LONG = 414;
    const STATUS_UNSUPPORTED_MEDIA_TYPE = 415;
    const STATUS_RANGE_NOT_SATISFIABLE = 416;
    const STATUS_EXPECTATION_FAILED = 417;
    const STATUS_I_M_A_TEAPOT = 418;
    const STATUS_MISDIRECTED_REQUEST = 421;
    const STATUS_UNPROCESSABLE_CONTENT = 422;
    const STATUS_LOCKED = 423;
    const STATUS_FAILED_DEPENDENCY = 424;
    const STATUS_TOO_EARLY = 425;
    const STATUS_UPGRADE_REQUIRED = 426;
    const STATUS_PRECONDITION_REQUIRED = 428;
    const STATUS_TOO_MANY_REQUESTS = 429;
    const STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    const STATUS_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const STATUS_INTERNAL_SERVER_ERROR = 500;
    const STATUS_NOT_IMPLEMENTED = 501;
    const STATUS_BAD_GATEWAY = 502;
    const STATUS_SERVICE_UNAVAILABLE = 503;
    const STATUS_GATEWAY_TIMEOUT = 504;
    const STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;
    const STATUS_VARIANT_ALSO_NEGOTIATES = 506;
    const STATUS_INSUFFICIENT_STORAGE = 507;
    const STATUS_LOOP_DETECTED = 508;
    const STATUS_NOT_EXTENDED = 510;
    const STATUS_NETWORK_AUTHENTICATION_REQUIRED = 511;

    const STATUSES = [
        self::STATUS_CONTINUE,
        self::STATUS_SWITCHING_PROTOCOLS,
        self::STATUS_PROCESSING,
        self::STATUS_EARLY_HINTS,
        self::STATUS_OK,
        self::STATUS_CREATED,
        self::STATUS_ACCEPTED,
        self::STATUS_NON_AUTHORITATIVE_INFORMATION,
        self::STATUS_NO_CONTENT,
        self::STATUS_RESET_CONTENT,
        self::STATUS_PARTIAL_CONTENT,
        self::STATUS_MULTI_STATUS,
        self::STATUS_ALREADY_REPORTED,
        self::STATUS_IM_USED,
        self::STATUS_MULTIPLE_CHOICES,
        self::STATUS_MOVED_PERMANENTLY,
        self::STATUS_FOUND,
        self::STATUS_SEE_OTHER,
        self::STATUS_NOT_MODIFIED,
        self::STATUS_TEMPORARY_REDIRECT,
        self::STATUS_PERMANENT_REDIRECT,
        self::STATUS_BAD_REQUEST,
        self::STATUS_UNAUTHORIZED,
        self::STATUS_PAYMENT_REQUIRED,
        self::STATUS_FORBIDDEN,
        self::STATUS_NOT_FOUND,
        self::STATUS_METHOD_NOT_ALLOWED,
        self::STATUS_NOT_ACCEPTABLE,
        self::STATUS_PROXY_AUTHENTICATION_REQUIRED,
        self::STATUS_REQUEST_TIMEOUT,
        self::STATUS_CONFLICT,
        self::STATUS_GONE,
        self::STATUS_LENGTH_REQUIRED,
        self::STATUS_PRECONDITION_FAILED,
        self::STATUS_CONTENT_TOO_LARGE,
        self::STATUS_URI_TOO_LONG,
        self::STATUS_UNSUPPORTED_MEDIA_TYPE,
        self::STATUS_RANGE_NOT_SATISFIABLE,
        self::STATUS_EXPECTATION_FAILED,
        self::STATUS_I_M_A_TEAPOT,
        self::STATUS_MISDIRECTED_REQUEST,
        self::STATUS_UNPROCESSABLE_CONTENT,
        self::STATUS_LOCKED,
        self::STATUS_FAILED_DEPENDENCY,
        self::STATUS_TOO_EARLY,
        self::STATUS_UPGRADE_REQUIRED,
        self::STATUS_PRECONDITION_REQUIRED,
        self::STATUS_TOO_MANY_REQUESTS,
        self::STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE,
        self::STATUS_UNAVAILABLE_FOR_LEGAL_REASONS,
        self::STATUS_INTERNAL_SERVER_ERROR,
        self::STATUS_NOT_IMPLEMENTED,
        self::STATUS_BAD_GATEWAY,
        self::STATUS_SERVICE_UNAVAILABLE,
        self::STATUS_GATEWAY_TIMEOUT,
        self::STATUS_HTTP_VERSION_NOT_SUPPORTED,
        self::STATUS_VARIANT_ALSO_NEGOTIATES,
        self::STATUS_INSUFFICIENT_STORAGE,
        self::STATUS_LOOP_DETECTED,
        self::STATUS_NOT_EXTENDED,
        self::STATUS_NETWORK_AUTHENTICATION_REQUIRED
    ];

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
    public function add_header($name, $value): self
    {
        $this->headers[] = [$name, $value];

        return $this;
    }

    /**
     *	@fn set_status($status)
     *	@short Sets the HTTP response status
     *	@param status The response status
     */
    public function set_status(int $status): self
    {
        http_response_code($status);

        return $this;
    }
}
