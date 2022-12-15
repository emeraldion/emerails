<?php
/**
 *  Project EmeRails - Codename Ocarina
 *
 *  Copyright (c) 2008, 2017 Claudio Procida
 *  http://www.emeraldion.it
 *
 * @format
 */

require_once __DIR__ . '/../include/common.inc.php';
require_once __DIR__ . '/../include/tag_support.inc.php';
require_once __DIR__ . '/../helpers/localization.php';
require_once __DIR__ . '/../helpers/http.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/query_string.php';

use Emeraldion\EmeRails\Config;

/**
 *  @class BaseController
 *  @short Abstract base class for action controllers.
 *  @details A subclass of BaseController is in charge of handling a set of actions. It creates the network of model objects
 *  that will be rendered by the views, handles and validates postback data, defines and applies before and after filters.
 */
class BaseController
{
    /**
     *  @attr name
     *  @short The name of the controller.
     */
    public $name;

    /**
     *  @attr action
     *  @short The name of the action.
     */
    public $action;

    /**
     *  @attr title
     *  @short A title for the page.
     */
    public $title;

    /**
     *  @attr type
     *  @short An extension for action pages.
     */
    protected $type = 'html';

    /**
     *  @attr mimetype
     *  @short A MIME type for the response.
     */
    protected $mimetype = 'text/html';

    /**
     *  @attr base_path
     *  @short Base path for the application. Override via <code>set_base_path($path)</code> to run as a composer dependency.
     */
    protected $base_path = __DIR__ . '/..';

    /**
     *  @attr headers
     *  @short An array of headers for the response.
     */
    private $headers;

    /**
     *  @attr rendered
     *  @short Flag to tell if the response has been already rendered.
     */
    private $rendered = false;

    /**
     *  @attr before_filters
     *  @short Array of filters that should be called before the response has been rendered.
     */
    private $before_filters = array();

    /**
     *  @attr after_filters
     *  @short Array of filters that should be called after the response has been rendered.
     */
    private $after_filters = array();

    /**
     *  @attr pages_cached
     *  @short Array of pages that should be cached.
     */
    private $pages_cached = array();

    /**
     *  @attr actions_cached
     *  @short Array of actions that should be cached.
     */
    private $actions_cached = array();

    /**
     *  @fn __construct
     *  @short Default constructor for controller objects.
     *  @details Subclassers should not override this method. Do your specialized
     *  initialization in the <tt>init</tt> method.
     */
    public function __construct()
    {
        $this->response = new Response();
        $this->request = new Request();
        $this->initialize();
        $this->init();
    }

    /**
     *  @fn initialize
     *  @short Initializes the controller object.
     *  @details Subclassers should not override this method. Do your specialized
     *  initialization in the <tt>init</tt> method.
     */
    protected function initialize()
    {
        $classname = get_class($this);
        $parts = explode('\\', $classname);
        $classname = $parts[count($parts) - 1];
        $this->name = camel_case_to_joined_lower(substr($classname, 0, strpos($classname, 'Controller')));
        if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
            $action = basename($_REQUEST['action']);

            if (is_callable(array($this, $action))) {
                $this->action = $action;
            } else {
                $this->unknown_action();
            }
        } else {
            $this->action = 'index';
        }

        $this->title = 'EmeRails';
    }

    /**
     *  @fn init
     *  @short Custom initialization for the controller object.
     *  @details Subclassers should override this method to perform specialized
     *  initialization. The default implementation does nothing.
     */
    protected function init()
    {
        // Default implementation does nothing.
    }

    /**
     *  Action filtering
     */

    /**
     *  @fn before_filter($filter, $params)
     *  @short Registers a function as a filter to be executed before the action is invoked.
     *  @details EmeRails allows the developer to call one or more functions before the action is actually invoked.
     *
     *  This may be useful to execute common code which is required before different action methods, to perform
     *  authentication checks, etc.
     *
     *  The controller keeps a list (FIFO) of filters that are invoked before the action method.
     *  Everywhere in the filters chain, you can redirect the request or simply return silently to resign
     *  control to the next filter, or the action itself.
     *  You can customize the way the filter is invoked by passing the optional parameter <tt>params</tt>.
     *
     *  Examples:
     *
     *  <tt>$this->before_filter(array('foo', 'bar'));</tt>
     *
     *  Executes the <tt>foo</tt> and <tt>bar</tt> filters (in this order) whatever action is requested.
     *
     *  <tt>$this->before_filter('foo', array('only' => 'index'));</tt>
     *
     *  Executes the <tt>foo</tt> filter only when the <tt>index</tt> action is requested.
     *
     *  <tt>$this->before_filter('foo', array('except' => array('index', 'list')));</tt>
     *
     *  Executes the <tt>foo</tt> filter unless the <tt>index</tt> or <tt>list</tt> actions are requested.
     *
     *  @param filter The name (or an array of names) of the method to be executed as filter.
     *  Currently, it must be a method of the controller.
     *  @param params Parameters that define when the filter has to be invoked.
     */
    protected function before_filter($filter, $params = null)
    {
        if (is_array($filter)) {
            foreach ($filter as $f) {
                $this->before_filter($f, $params);
            }
            return;
        }
        $this->before_filters[] = array($filter, $params);
    }

    /**
     *  @fn after_filter($filter, $params)
     *  @short Registers a function as a filter to be executed after the action is invoked.
     *  @details EmeRails allows the developer to call one or more functions after the action has been invoked.
     *
     *  This allows the developer to perform manipulation of the response, to enable markup expansion, etc.
     *
     *  The controller keeps a list (FIFO) of filters that are invoked after the action method has returned.
     *  Everywhere in the filters chain, you can redirect the request or simply return silently to resign
     *  control to the next filter, or the response be flushed to the client.
     *  You can customize the way the filter is invoked by passing the optional parameter <tt>params</tt>.
     *
     *  Examples:
     *
     *  <tt>$this->after_filter(array('foo', 'bar'));</tt>
     *
     *  Executes the <tt>foo</tt> and <tt>bar</tt> filters (in this order) whatever action is requested.
     *
     *  <tt>$this->after_filter('foo', array('only' => 'index'));</tt>
     *
     *  Executes the <tt>foo</tt> filter only when the <tt>index</tt> action is requested.
     *
     *  <tt>$this->after_filter('foo', array('except' => array('index', 'list')));</tt>
     *
     *  Executes the <tt>foo</tt> filter unless the <tt>index</tt> or <tt>list</tt> actions are requested.
     *
     *  @param filter The name (or an array of names) of the method to be executed as filter.
     *  Currently, it must be a method of the controller.
     *  @param params Parameters that define when the filter has to be invoked.
     */
    protected function after_filter($filter, $params = null)
    {
        if (is_array($filter)) {
            foreach ($filter as $f) {
                $this->after_filter($f, $params);
            }
            return;
        }
        $this->after_filters[] = array($filter, $params);
    }

    /**
     *  @fn filter_applicable($conditions)
     *  @short Determines if a filter is applicable for the current action.
     *  @details This method checks the <tt>conditions</tt> argument in order to determine if
     *  the filter (whatever it is) can be applied for the current action.
     *  @param conditions An array containing directives for the application of filters.
     *  It has the same format of the second parameter of methods <tt>before_filter</tt> and
     *  <tt>after_filter</tt>.
     */
    private function filter_applicable($conditions)
    {
        if (is_array($conditions)) {
            // If an 'except' key exists, check that the action is not included
            if (array_key_exists('except', $conditions)) {
                $except = $conditions['except'];
                if (is_array($except)) {
                    return !in_array($this->action, $except);
                } elseif ($except != $this->action) {
                    return true;
                }
            }
            // If an 'only' key exists, check that the action is included
            elseif (array_key_exists('only', $conditions)) {
                $only = $conditions['only'];
                if (is_array($only)) {
                    return in_array($this->action, $only);
                } elseif ($only == $this->action) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /**
     *  Page caching
     */

    /**
     *  @fn caches_page($page)
     *  @short Requests that a page be cached.
     *  @details This method registers the page <tt>page</tt> to be cached after it has been
     *  executed and rendered.
     *  @param page The name (or an array of names) of a page that should be cached.
     */
    protected function caches_page($page)
    {
        if (is_array($page)) {
            foreach ($page as $p) {
                $this->caches_page($p);
            }
            return;
        }
        $this->pages_cached[] = $page;
    }

    /**
     *  @fn conditionally_caches_page($page, $condition)
     *  @short Requests that a page be cached when a condition is verified.
     *  @details Convenience method to cache the requested page only if the
     *  <tt>condition</tt> argument evaluates as <tt>TRUE</tt>.
     *  @param page The name (or an array of names) of a page that should be cached.
     *  @param condition Flag to decide if the page should be cached.
     */
    protected function conditionally_caches_page($page, $condition = true)
    {
        if ($condition) {
            $this->caches_page($page);
        }
    }

    /**
     *  @fn caches_action($page)
     *  @short Requests that an action be cached.
     *  @details This method is currently unused.
     *  @param page The name of a page that should be cached.
     */
    protected function caches_action($page)
    {
        $this->actions_cached[] = $page;
    }

    /**
     *  @fn cached_page_exists
     *  @short Checks if a cached version of the current action exists.
     *  @details This method checks the existence of the file whose name is returned by <tt>cached_page_filename</tt>.
     */
    protected function cached_page_exists()
    {
        return file_exists($this->cached_page_filename());
    }

    /**
     *  @fn cached_page_filename
     *  @short Returns a name for the cached page of current action.
     *  @details This method creates a filename that is uniquely associated with the current controller, action,
     *  argument identifier and language.
     */
    protected function cached_page_filename()
    {
        $lang = isset($_COOKIE['hl']) ? $_COOKIE['hl'] : 'en';
        $id = isset($_REQUEST['id']) ? "@{$_REQUEST['id']}" : '';
        $cachefile = sprintf('%s/caches/%s/%s%s-%s.cached', $this->base_path, $this->name, $this->action, $id, $lang);

        return $cachefile;
    }

    /**
     *  @fn expire_cached_page($params)
     *  @short Requests that the page(s) defined by <tt>params</tt> be removed from the caches.
     *  @details This method removes from the caches all pages that match the <tt>params</tt> argument
     *  for every supported language.
     */
    protected function expire_cached_page($params)
    {
        $controller = isset($params['controller']) ? $params['controller'] : $this->name;
        $action = isset($params['action']) ? $params['action'] : $this->action;
        $id = isset($params['id']) ? "@{$params['id']}" : '';

        $langs = Localization::$languages;

        foreach ($langs as $lang) {
            $cachefile = __DIR__ . "/../caches/{$controller}/{$action}{$id}-{$lang}.cached";
            @unlink($cachefile);
        }
    }

    /**
     *  Response manipulation
     */

    /**
     *  @fn redirect_to($params)
     *  @short Redirects the response to another action, or URL.
     *  @details This method can either redirect the response to
     *  an URL, or another action, whose parameters are contained in
     *  the <tt>params</tt> argument.
     *  @param params An URL, or an array with details of the action
     *  for response redirection.
     */
    protected function redirect_to($params)
    {
        if (is_array($params)) {
            $URL = sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $this->url_to($params));
            if (!isset($params['after'])) {
                $this->response->add_header('Location', $URL);
                $this->response->flush();
            } else {
                $this->response->add_header('Refresh', "{$params['after']};url={$URL}");
            }
        } else {
            $this->response->add_header('Location', $params);
            $this->response->flush();
        }
    }

    /**
     *  @fn refresh($after)
     *  @short Refreshes current page after a given amount of time.
     *  @details Same as <tt>redirect_to</tt>, except that the destination is the same page.
     *  @param after Number of seconds after which perform a refresh.
     */
    protected function refresh($amount)
    {
        $this->redirect_to(array('after' => $amount));
    }

    /**
     *  @fn redirect_to_referrer($or_else)
     *  @short Redirects the response to the URL which is contained
     *  in the <tt>HTTP_REFERER</tt> server variable (i.e., performs a
     *  back redirection). If no <tt>HTTP_REFERER</tt> is set, and if passed
     *  the optional parameter <tt>or_else</tt>, the latter is used for an
     *  explicit redirection.
     *  @param or_else An URL, or an array with details of the action
     *  for response redirection if no <tt>HTTP_REFERER</tt> is set.
     */
    protected function redirect_to_referrer($or_else = null)
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->redirect_to($_SERVER['HTTP_REFERER']);
        } elseif (!empty($or_else)) {
            $this->redirect_to($or_else);
        }
    }

    public function set_base_path($path = __DIR__)
    {
        $this->base_path = $path;
    }

    /**
     *  Hyperlinks to actions
     */

    /**
     *  @fn link_to($text, $params)
     *  @short Links to another action.
     *  @details This method creates a hyperlink to another action, as
     *  specified in the <tt>params</tt> argument. This <tt>params</tt> array
     *  has the same semantics used in the <tt>make_relative_url</tt> method.
     *  You can also provide an explicit URL for the hyperlink by passing the
     *  <tt>href</tt> key. This will override other parameters.
     *  @see link_to_remote($text, $params);
     *  @param text A text label for the hyperlink.
     *  @param params Array of key value pairs defining the hyperlink.
     */
    public function link_to($text, $params = array())
    {
        $params['href'] = isset($params['href']) ? $params['href'] : $this->make_relative_url($params);

        unset($params['action']);
        unset($params['controller']);
        unset($params['id']);
        unset($params['query_string']);

        print a($text, $params);
    }

    /**
     *  @fn link_to_remote($text, $params)
     *  @short Links to another action, with AJAX support.
     *  @details This method behaves like <tt>link_to</tt>, except that
     *  it also adds an AJAX handler to replace the contents of the element with id
     *  <tt>target</tt>.
     *  You can also provide different URLs for the static and AJAX hyperlink, by
     *  explicitly setting the keys in <tt>params</tt>: <tt>href</tt>, <tt>remote_url</tt>,
     *  or both.
     *  @see link_to($text, $params);
     *  @param text A text label for the hyperlink.
     *  @param params Array of key value pairs defining the hyperlink.
     */
    public function link_to_remote($text, $params = array())
    {
        $params['href'] = !empty($params['href']) ? $params['href'] : $this->make_relative_url($params);
        $params['remote_url'] = !empty($params['remote_url'])
            ? $params['remote_url']
            : $this->make_relative_url($params);
        // Ajax magic
        $params['onclick'] =
            "$('#{$params['target']}').html('<div class=\"loading\"><span>" .
            l('Loading...') .
            "</span></div>').load('{$params['remote_url']}'); return false";

        unset($params['action']);
        unset($params['controller']);
        unset($params['id']);
        unset($params['target']);
        unset($params['remote_url']);

        print a($text, $params);
    }

    /**
     *  @fn button_to($text, $params)
     *  @short Creates a button that links to another action.
     *  @details This method creates a button that links to another action, as
     *  specified in the <tt>params</tt> argument. This <tt>params</tt> array
     *  has the same semantics used in the <tt>make_relative_url</tt> method.
     *  You can also provide an explicit URL for the hyperlink by passing the
     *  <tt>href</tt> key. This will override other parameters.
     *  @param text A text label for the button.
     *  @param params Array of key value pairs defining the hyperlink.
     */
    public function button_to($text, $params = array())
    {
        $params['href'] = isset($params['href']) ? $params['href'] : $this->make_relative_url($params);

        $params['onclick'] = "location.href='{$params['href']}'";

        unset($params['action']);
        unset($params['controller']);
        unset($params['id']);
        unset($params['query_string']);
        unset($params['href']);

        print button(joined_lower($text), $text, $params);
    }

    /**
     *  @fn unknown_action()
     *  @short Fallback handler for unknown action.
     *  @details This method implements the default action handler which
     *  sends a 404 Not Found error back to the client.
     */
    protected function unknown_action()
    {
        $this->send_error(404);
    }

    /**
     *  @fn send_error($status)
     *  @short Sends an HTTP error to the client.
     *  @details This method sends an HTTP error to the client with the
     *  status code of choice.
     *  @param status Status code.
     */
    protected function send_error($status)
    {
        HTTP::error($status);
    }

    /**
     *  @fn url_to($params)
     *  @short Creates a URL to an action.
     *  @details This method builds a URL to an action, as
     *  specified in the <tt>params</tt> argument. The <tt>params</tt> array
     *  has the same semantics used in the <tt>make_relative_url</tt> method.
     *  @param params Array of key value pairs defining the URL.
     */
    public function url_to($params)
    {
        return $this->make_relative_url($params);
    }

    /**
     *  @fn url_to_myself($relative)
     *  @short Creates a URL to the current action.
     *  @details This method builds a URL to the current action.
     *  @param relative Whether the URL should contain only the path,
     *  or be a qualified URI.
     */
    function url_to_myself($relative = true)
    {
        $url = $this->url_to(array(
            'action' => $this->action,
            'type' => $this->type,
            'id' => @$_REQUEST['id']
        ));
        return $relative ? $url : sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $url);
    }

    /**
     *  @fn make_relative_url($params)
     *  @short Creates a URL to an action.
     *  @details This method builds a URL to an action, as
     *  specified in the <tt>params</tt> argument. The <tt>params</tt> array
     *  has the following semantics:
     *
     *  <tt>controller</tt>: the name of the desired controller (optional, defaults to self).
     *  <tt>action</tt>: the name of the desired action (optional, defaults to 'index').
     *  <tt>id</tt>: the id of the argument of the action (optional).
     *  <tt>type</tt>: the type (extension) of the resource (optional, defaults to 'html').
     *  <tt>query_string</tt>: the query string part of the URL (optional).
     *  <tt>hash</tt>: the target HTML anchor of the URL (optional).
     *
     *  @param params Array of key value pairs defining the URL.
     */
    public function make_relative_url($params)
    {
        $controller =
            !isset($params['controller']) || empty($params['controller'])
                ? (isset($this->_name)
                    ? $this->_name
                    : $this->name)
                : $params['controller'];
        $type = isset($params['type']) && !empty($params['type']) ? $params['type'] : $this->type;
        if (isset($params['action']) && !empty($params['action'])) {
            if (isset($params['id']) && !empty($params['id'])) {
                $href = sprintf(
                    '%s%s/%s/%s',
                    Config::get('APPLICATION_ROOT'),
                    $controller,
                    $params['action'],
                    $params['id']
                );
            } else {
                $href = sprintf('%s%s/%s.%s', Config::get('APPLICATION_ROOT'), $controller, $params['action'], $type);
            }
        } else {
            $href = sprintf('%s%s/index.%s', Config::get('APPLICATION_ROOT'), $controller, $type);
        }
        if (isset($params['query_string'])) {
            $href .= "?{$params['query_string']}";
        }
        if (isset($params['hash'])) {
            $href .= "#{$params['hash']}";
        }
        return $href;
    }

    /**
     *  Response rendering
     */

    /**
     *  @fn render($params)
     *  @short Requests the rendering of the response.
     *  @details This method is responsible of loading the view and layout
     *  relative to the current controller and action, parsing their content,
     *  and returning the result to be appended to the response.
     *
     *  The <tt>params</tt> array has the following semantics:
     *
     *  @li <tt>action</tt>: the name of the action for which render a view (defaults to current action).
     *  @li <tt>layout</tt>: the layout used to render the view (optional, defaults to current action).
     *  It can be set to <tt>NULL</tt> to render the view with no layout (the same result can be obtained
     *  using the <tt>nolayout</tt> request parameter).
     *  @li <tt>return</tt>: return the rendered view as a string, rather than flushing it to the output buffer.
     *  @li <tt>partial</tt>: triggers the partial rendering mode (optional).
     *  @li <tt>object</tt>: passes an argument to be used by the partial view (optional).
     *
     *  @param params Parameters defining how the rendering should be realized.
     */
    public function render($params)
    {
        if ($this->rendered) {
            return;
        }
        // Initialize rendered content
        $rendered = null;
        if ($params === null) {
            $this->rendered = true;
        } elseif (isset($params['partial'])) {
            $partial = basename($params['partial']);

            // Get part file
            $partfile = sprintf('%s/views/%s/_%s.php', $this->base_path, $this->name, $partial);

            // Connect main object
            if (isset($params['object'])) {
                eval("\${$partial} = \$params['object'];");
            } elseif (isset($this->$partial)) {
                eval("\${$partial} = \$this->{$partial};");
            }

            // Start output buffering
            ob_start();

            // Evaluate and send to buffer
            print eval($this->load_part_contents($partfile));

            // Get buffer contents, clean output buffer
            $rendered = ob_get_clean();
        } else {
            // Get action to render
            $action =
                isset($params['action']) && !empty($params['action']) ? basename($params['action']) : $this->action;

            // Get part file
            $partfile = sprintf('%s/views/%s/%s.php', $this->base_path, $this->name, $action);

            // Start output buffering
            ob_start();

            // Evaluate and send to buffer
            print eval($this->load_part_contents($partfile));

            // Get buffer contents
            $this->content_for_layout = ob_get_contents();
            // Backwards compatibility
            $this->page_content = $this->content_for_layout;

            // Clean output buffer
            ob_end_clean();

            if ((isset($params['layout']) && $params['layout'] == false) || isset($_REQUEST['nolayout'])) {
                // Do not print layout
                $rendered = $this->content_for_layout;
            } else {
                $rendered = $this->render_layout($params);
            }

            $this->rendered = true;
        }

        if (isset($params['return'])) {
            return $rendered;
        } else {
            print $rendered;
        }
    }

    /**
     *  @fn render_as_string($params)
     *  @short Requests the rendering of the response as a string.
     *  @details This method calls <tt>render</tt> by passing the <tt>return</tt>
     *  parameter to <tt>TRUE</tt>, and returns the rendered response as a string.
     *  The <tt>params</tt> array has the same semantics discussed in the
     *  <tt>render</tt> method.
     *  @param params Parameters defining how the rendering should be realized.
     */
    public function render_as_string($params)
    {
        $params['return'] = true;
        return $this->render($params);
    }

    /**
     *  @fn render_layout($params)
     *  @short Renders the layout for the current view.
     *  @details This method is responsible of loading the layout
     *  as defined in the <tt>params</tt> argument, parsing it and
     *  returning the result.
     *  The <tt>params</tt> array has the same semantics of the <tt>render</tt>
     *  method, although only the <tt>layout</tt> key is relevant.
     *  @param params Parameters defining how the rendering should be realized.
     */
    protected function render_layout($params)
    {
        $layout = isset($params['layout']) && !empty($params['layout']) ? basename($params['layout']) : $this->name;

        // Initialize rendered content
        $rendered = null;

        // Start output buffering
        ob_start();

        // Get part file
        $partfile = sprintf('%s/views/layouts/%s_layout.php', $this->base_path, $layout);

        if (!file_exists($partfile)) {
            // Fall back to default layout
            $partfile = sprintf('%s/views/layouts/default_layout.php', $this->base_path);
        }

        // Evaluate and send to buffer
        print eval($this->load_part_contents($partfile));

        // Get buffer contents, clean output buffer
        $rendered = ob_get_clean();

        return $rendered;
    }

    /**
     *  @fn render_page
     *  @short Renders the page as a response for the current request.
     *  @details This method is responsible for building the response
     *  for the current request.
     *  @li All before filters that are registered for the current action are executed.
     *  @li If the current page is cacheable, and a cached version already exists in
     *  the caches, it is returned; otherwise it is rendered and eventually stored in
     *  the cache folder.
     *  @li All after filters that are registered for the current action are executed.
     *  @li Finally, the response is flushed to the client.
     */
    public function render_page()
    {
        // Process before filters queue
        foreach ($this->before_filters as $before_filter) {
            if ($this->filter_applicable($before_filter[1])) {
                $filter = $before_filter[0];
                $this->$filter();
            }
        }

        // If the page should be cached, verify if it exists in the cache
        if (in_array($this->action, $this->pages_cached) && $this->cached_page_exists()) {
            $cached_file = $this->cached_page_filename();
            if (isset($this->mimetype)) {
                $this->response->add_header('Content-Type', $this->mimetype);
            }
            $this->response->add_header('Content-Length', filesize($cached_file));
            $this->response->body .= file_get_contents($cached_file);
        } else {
            // Start buffering
            ob_start();

            // Call eventual controller action
            if (is_callable(array($this, $this->action))) {
                $this->{$this->action}();
            } else {
                $this->send_error(500);
            }
            // Call render on the controller
            // This won't have effect if the controller has already rendered
            $this->render($_REQUEST);

            // Get response body, clean output buffer
            $this->response->body .= ob_get_clean();

            // Conversely, if the page should be cached, store it in cache
            if (in_array($this->action, $this->pages_cached)) {
                $caches_dir = dirname($this->cached_page_filename());
                if (!file_exists($caches_dir)) {
                    mkdir($caches_dir, 0700, true);
                }
                file_put_contents($this->cached_page_filename(), $this->response->body);
            }
        }

        // Process after filters queue
        foreach ($this->after_filters as $after_filter) {
            if ($this->filter_applicable($after_filter[1])) {
                $filter = $after_filter[0];
                $this->$filter();
            }
        }

        if (isset($this->mimetype)) {
            $this->response->add_header('Content-Type', $this->mimetype);
        }

        // Finally, flush responses
        $this->response->flush($this->request->is_head());
    }

    /**
     *  @fn render_component($params)
     *  @short Renders a view from a controller other than self.
     *  @details This method renders a view from a controller other than self,
     *  for example when embedding a view into another view.
     *
     *  The <tt>params</tt> array has the same semantics of the <tt>render</tt> method,
     *  with the following additions:
     *
     *  <tt>controller</tt>: the name of the controller responsible for the view (defaults to self).
     *  @param params Parameters defining how the rendering should be realized.
     */
    public function render_component($params)
    {
        // Merge request and user parameters
        $_GET = array_merge($_GET, $params);

        // If a controller is not set, use current controller
        if (!isset($params['controller'])) {
            $controller_name = $this->name;
        } else {
            // Use the value stored in params as controller name
            $controller_name = basename($params['controller']);

            // Include the controller class file
            require_once sprintf('%s/controllers/%s_controller.php', $this->base_path, $controller_name);

            // Load localization table
            Localization::add_strings_table($controller_name);
        }

        // Create class name
        $classname = joined_lower_to_camel_case($controller_name) . 'Controller';

        // Instantiate controller
        $controller = new $classname();
        $controller->_name = $this->name;
        $controller->_action = $this->action;

        // Propagate base path
        $controller->set_base_path($this->base_path);

        // Unset controller key from params (why?)
        unset($params['controller']);

        // Set requested action
        if (isset($params['action'])) {
            $action = basename($params['action']);

            $controller->action = $action;
        }

        if (isset($params['props'])) {
            foreach ($params['props'] as $key => $val) {
                $controller->$key = $val;
            }
        }

        // Invoke action method
        $controller->$action();

        // Request rendering with no layout
        $params['layout'] = false;

        // Note that this could already have been called
        $controller->render($params);
    }

    /**
     *  @fn load_part_contents($filename)
     *  @short Loads the contents of the desired view file.
     *  @details This method returns the contents of the requested view file
     *  without parsing.
     *  @param filename The name of the view file to load.
     */
    protected function load_part_contents($filename)
    {
        if (!file_exists($filename)) {
            $this->send_error(500);
        }
        $contents = file_get_contents($filename);
        return $this->strip_external_php_tags($contents);
    }

    /**
     *  @fn strip_external_php_tags($php_code)
     *  @short Strips beginning and ending delimiters from the given PHP code.
     *  @details This method removes the beginning and ending PHP code delimiters
     *  to enable subsequent parsing with <tt>eval</tt>.
     *  @param php_code The code to be stripped.
     */
    protected function strip_external_php_tags($php_code)
    {
        if (($start = strpos($php_code, '<?php')) === 0) {
            $php_code = substr($php_code, $start + strlen('<?php'));
        } else {
            $php_code = "?>\n" . $php_code;
        }
        if (($end = strrpos($php_code, '?>')) === strlen($php_code) - strlen('?>')) {
            $php_code = substr($php_code, 0, strrpos($php_code, '?>'));
        } else {
            $php_code = $php_code . "\n<?php\n";
        }
        return $php_code;
    }

    /**
     *  Miscellaneous
     */

    /**
     *  @fn set_title($title)
     *  @short Sets the title for the current page.
     *  @param title A title for the current page.
     */
    public function set_title($title)
    {
        $this->title = $title;
    }

    /**
     *  @fn abort_and_flush
     *  @short Interrupts the processing of the request.
     */
    private function abort_and_flush()
    {
        ob_end_flush();
        exit();
    }

    /**
     *  Messages handling
     */

    /**
     *  @fn flash($message, $type)
     *  @short Shows a message to the user, with an optional type qualifier.
     *  @details The flash is a facility to store messages that should be visualized
     *  as a result of some event, or as a response to the user's previous request.
     *  @param message A message to show to the user.
     *  @param type The type of the message (e.g. 'warning').
     */
    public function flash($message, $type = 'error')
    {
        $_SESSION['flash'] = array('message' => $message, 'type' => $type);
    }

    /**
     *  Action methods
     */

    /**
     *  @fn index
     *  @short This is the default action method.
     */
    public function index()
    {
        $this->render(array('layout' => 'default'));
    }

    /**
     *  Filters
     */

    /**
     *  @fn compress
     *  @short Compresses the response with gzip encoding.
     *  @details This after-filter is capable of compressing the response with gzip encoding,
     *  in order to save bandwidth. If the client does not support gzip encoding, the response
     *  is not altered.
     */
    protected function compress()
    {
        if (
            isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
            preg_match('/(x-gzip|gzip)/', $_SERVER['HTTP_ACCEPT_ENCODING'], $matches)
        ) {
            $this->response->body = gzencode($this->response->body, 9);
            $this->response->add_header('Content-Encoding', $matches[1]);
        }
    }
}
?>
