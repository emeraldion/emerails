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

require_once __DIR__ . '/../include/common.inc.php';
require_once __DIR__ . '/../include/tag_support.inc.php';

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Helpers\HTTP;
use Emeraldion\EmeRails\Helpers\Localization;
use Emeraldion\EmeRails\Helpers\Request;
use Emeraldion\EmeRails\Helpers\Response;

/**
 *  @class BaseController
 *  @short Abstract base class for action controllers.
 *  @details A subclass of BaseController is in charge of handling a set of actions. It creates the network of model objects
 *  that will be rendered by the views, handles and validates postback data, defines and applies before and after filters.
 */
class BaseController
{
    /**
     * @attr name
     * @short The name of the controller.
     */
    public $name;

    /**
     * @attr action
     * @short The name of the action.
     */
    public $action;

    /**
     * @attr title
     * @short A title for the page.
     */
    public $title;

    /**
     * @attr type
     * @short An extension for action pages.
     */
    protected $type = 'html';

    /**
     * @attr mimetype
     * @short A MIME type for the response.
     */
    protected $mimetype = 'text/html';

    /**
     * @attr base_path
     * @short Base path for the application. Override via <code>set_base_path($path)</code> to run as a composer dependency.
     */
    protected $base_path = __DIR__ . '/..';

    /**
     * @attr headers
     * @short An array of headers for the response.
     */
    private $headers;

    /**
     * @attr rendered
     * @short Flag to tell if the response has been already rendered.
     */
    private $rendered = false;

    /**
     * @attr allowed_methods
     * @short Array of allowed request methods accepted by actions.
     * @details Contains a list of allow rules for methods accepted by actions. These are checked before anything else.
     * When the request method is <tt>GET</tt>, <tt>HEAD</tt> or <tt>OPTIONS</tt>, the request is allowed unless explicitly
     * blocked. For other methods, the request is blocked by default unless explicitly allowed.
     */
    private $allowed_methods = array();

    /**
     * @attr accepted_parameters
     * @short Map of accepted request parameters expected by actions.
     * @details Contains a map of maps of accepted request parameters keyed by action and parameter name. The value is a
     * dictionary with details about the type and acceptable values.
     */
    private $accepted_parameters = array();

    /**
     * @attr parameters
     * @short Hub for accepted request parameters
     * @details All parameters declared with <tt>accept_parameter</tt> will be hoisted as properties of this object, e.g.
     *
     * // GET /controller/action/1
     * protected function init() {
     *  $this->accept_parameter('index', 'id', array('required' => true, 'type' => 'int'));
     * }
     *
     * public function action() {
     *  var_dump($this->parameters->id);
     *  // => int(1)
     * }
     */
    protected $parameters;

    const PARAM_TYPE_BOOL = 'bool';
    const PARAM_TYPE_BOOL_ARRAY = 'bool[]';
    const PARAM_TYPE_ENUM = 'enum';
    const PARAM_TYPE_ENUM_ARRAY = 'enum[]';
    const PARAM_TYPE_FLOAT = 'float';
    const PARAM_TYPE_FLOAT_ARRAY = 'float[]';
    const PARAM_TYPE_INT = 'int';
    const PARAM_TYPE_INT_ARRAY = 'int[]';
    const PARAM_TYPE_STRING = 'string';
    const PARAM_TYPE_STRING_ARRAY = 'string[]';
    const PARAM_TYPE_TINYINT = 'tinyint';
    const PARAM_TYPE_TINYINT_ARRAY = 'tinyint[]';

    const PARAM_TYPES = array(
        self::PARAM_TYPE_BOOL,
        self::PARAM_TYPE_BOOL_ARRAY,
        self::PARAM_TYPE_ENUM,
        self::PARAM_TYPE_ENUM_ARRAY,
        self::PARAM_TYPE_FLOAT,
        self::PARAM_TYPE_FLOAT_ARRAY,
        self::PARAM_TYPE_INT,
        self::PARAM_TYPE_INT_ARRAY,
        self::PARAM_TYPE_STRING,
        self::PARAM_TYPE_STRING_ARRAY,
        self::PARAM_TYPE_TINYINT,
        self::PARAM_TYPE_TINYINT_ARRAY
    );

    /**
     * @attr before_filters
     * @short Array of filters that should be called before the response has been rendered.
     */
    private $before_filters = array();

    /**
     * @attr after_filters
     * @short Array of filters that should be called after the response has been rendered.
     */
    private $after_filters = array();

    /**
     * @attr pages_cached
     * @short Array of pages that should be cached.
     */
    private $pages_cached = array();

    /**
     * @attr actions_cached
     * @short Array of actions that should be cached.
     */
    private $actions_cached = array();

    /**
     * @fn __construct
     * @short Default constructor for controller objects.
     * @details Subclassers should not override this method. Do your specialized
     * initialization in the <tt>init</tt> method.
     */
    public function __construct()
    {
        $this->response = new Response();
        $this->request = new Request();
        $this->initialize();
        $this->init();
    }

    /**
     * @fn initialize
     * @short Initializes the controller object.
     * @details Subclassers should not override this method. Do your specialized
     * initialization in the <tt>init</tt> method.
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
     * @fn init
     * @short Custom initialization for the controller object.
     * @details Subclassers should override this method to perform specialized
     * initialization. The default implementation does nothing.
     */
    protected function init()
    {
        // Default implementation does nothing.
    }

    /**
     * Action filtering
     */

    /**
     * @fn before_filter($filter, $params)
     * @short Registers a function as a filter to be executed before the action is invoked.
     * @details EmeRails allows the developer to call one or more functions before the action is actually invoked.
     *
     * This may be useful to execute common code which is required before different action methods, to perform
     * authentication checks, etc.
     *
     * The controller keeps a list (FIFO) of filters that are invoked before the action method.
     * Everywhere in the filters chain, you can redirect the request or simply return silently to resign
     * control to the next filter, or the action itself.
     * You can customize the way the filter is invoked by passing the optional parameter <tt>params</tt>.
     *
     * Examples:
     *
     * <tt>$this->before_filter(array('foo', 'bar'));</tt>
     *
     * Executes the <tt>foo</tt> and <tt>bar</tt> filters (in this order) whatever action is requested.
     *
     * <tt>$this->before_filter('foo', array('only' => 'index'));</tt>
     *
     * Executes the <tt>foo</tt> filter only when the <tt>index</tt> action is requested.
     *
     * <tt>$this->before_filter('foo', array('except' => array('index', 'list')));</tt>
     *
     * Executes the <tt>foo</tt> filter unless the <tt>index</tt> or <tt>list</tt> actions are requested.
     *
     * @param filter The name (or an array of names) of the method to be executed as filter.
     * Currently, it must be a method of the controller.
     * @param params Parameters that define when the filter has to be invoked.
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
     * @fn after_filter($filter, $params)
     * @short Registers a function as a filter to be executed after the action is invoked.
     * @details EmeRails allows the developer to call one or more functions after the action has been invoked.
     *
     * This allows the developer to perform manipulation of the response, to enable markup expansion, etc.
     *
     * The controller keeps a list (FIFO) of filters that are invoked after the action method has returned.
     * Everywhere in the filters chain, you can redirect the request or simply return silently to resign
     * control to the next filter, or the response be flushed to the client.
     * You can customize the way the filter is invoked by passing the optional parameter <tt>params</tt>.
     *
     * Examples:
     *
     * <tt>$this->after_filter(array('foo', 'bar'));</tt>
     *
     * Executes the <tt>foo</tt> and <tt>bar</tt> filters (in this order) whatever action is requested.
     *
     * <tt>$this->after_filter('foo', array('only' => 'index'));</tt>
     *
     * Executes the <tt>foo</tt> filter only when the <tt>index</tt> action is requested.
     *
     * <tt>$this->after_filter('foo', array('except' => array('index', 'list')));</tt>
     *
     * Executes the <tt>foo</tt> filter unless the <tt>index</tt> or <tt>list</tt> actions are requested.
     *
     * @param filter The name (or an array of names) of the method to be executed as filter.
     * Currently, it must be a method of the controller.
     * @param params Parameters that define when the filter has to be invoked.
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
     * @fn filter_applicable($conditions)
     * @short Determines if a filter is applicable for the current action.
     * @details This method checks the <tt>conditions</tt> argument in order to determine if
     * the filter (whatever it is) can be applied for the current action.
     * @param conditions An array containing directives for the application of filters.
     * It has the same format of the second parameter of methods <tt>before_filter</tt> and
     * <tt>after_filter</tt>.
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
            // Allow a flat list of actions per filter as an alias for the 'only' form
            return in_array($this->action, $conditions);
        }
        return true;
    }

    /**
     * @fn accept_parameter($action, $name, $params)
     * @short Declares an accepted parameter
     */
    protected function accept_parameter($action, string $name, array $params = array())
    {
        if (is_array($action)) {
            foreach ($action as $a) {
                $this->accept_parameter($a, $name, $params);
            }
        } else {
            $this->accepted_parameters[$action][$name] = $params;
        }
    }

    /**
     * @fn populate_accepted_parameters()
     * @short Populates and validates accepted parameters
     */
    private function populate_accepted_parameters()
    {
        $action = $this->action;
        if (array_key_exists($action, $this->accepted_parameters)) {
            foreach ($this->accepted_parameters[$action] as $name => $params) {
                $value = null;
                switch ($this->request->method) {
                    case Request::METHOD_GET:
                    case Request::METHOD_HEAD:
                    case Request::METHOD_OPTIONS:
                        if ($this->request->get_parameter($name)) {
                            $value = $this->request->get_parameter($name);
                        }
                        break;
                    case Request::METHOD_POST:
                    case Request::METHOD_PUT:
                    case Request::METHOD_DELETE:
                        if ($this->request->get_parameter($name)) {
                            $value = $this->request->get_parameter($name);
                        }
                        if (isset($_POST[$name])) {
                            $value = $_POST[$name];
                        }
                        break;
                }
                if (!$this->parameters) {
                    $this->parameters = new stdClass();
                }
                $this->parameters->$name = $this->validate_parameter($name, $value, $params);
            }
        }
    }

    /**
     * @fn validate_parameter($name, $value, $params)
     * @short Validates an accepted parameter
     */
    protected function validate_parameter(string $name, $value, array $params = array())
    {
        if (!array_key_exists('type', $params)) {
            trigger_error(
                sprintf("[%s::%s] Missing type annotation for parameter '%s'", get_called_class(), __FUNCTION__, $name),
                E_USER_NOTICE
            );
        } else {
            preg_match('/([^\[\]]+)(\[\])?$/', $params['type'], $matches);
            @list(, $type, $multi) = $matches;

            $is_required = array_key_exists('required', $params);
            if ($has_default = array_key_exists('default', $params)) {
                $default_value = $params['default'];
            }

            if ($is_required) {
                if (empty($value)) {
                    // TODO: delegate the subclass to present this error
                    trigger_error(
                        sprintf(
                            "[%s::%s] Missing required %s parameter '%s'",
                            get_called_class(),
                            __FUNCTION__,
                            $type . $multi,
                            $name
                        ),
                        E_USER_ERROR
                    );
                }
            }

            // Check type
            switch ($type) {
                case self::PARAM_TYPE_STRING:
                    if ($multi) {
                        $outval = array();
                        $valid = true;
                        if ($value) {
                            foreach ($value as $val) {
                                if ($v = is_string($val) || empty($val)) {
                                    $outval[] = (string) $val;
                                }
                                $valid = $valid && $v;
                            }
                            if ($valid) {
                                $value = $outval;
                            }
                        } elseif ($has_default) {
                            $value = $default_value;
                        }
                    } else {
                        if ($valid = is_string($value) || empty($value)) {
                            $value = (string) $value;
                        }
                        if (empty($value) && $has_default) {
                            $value = $default_value;
                        }
                    }
                    break;
                case self::PARAM_TYPE_BOOL:
                    if ($multi) {
                        $outval = array();
                        $valid = true;
                        if ($value) {
                            foreach ($value as $val) {
                                if ($v = !is_null(filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))) {
                                    $outval[] = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                                }
                                $valid = $valid && $v;
                            }
                            if ($valid) {
                                $value = $outval;
                            }
                        } elseif ($has_default) {
                            $value = $default_value;
                        }
                    } else {
                        if ($valid = !is_null(filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))) {
                            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        } elseif ($valid = empty($value) && ($has_default || !$is_required)) {
                            if ($has_default) {
                                $value = (bool) $default_value;
                            }
                        }
                    }
                    break;
                case self::PARAM_TYPE_INT:
                case self::PARAM_TYPE_TINYINT:
                    if ($multi) {
                        $outval = array();
                        $valid = true;
                        if ($value) {
                            foreach ($value as $val) {
                                if ($v = is_numeric($val) && ((int) $val) == $val) {
                                    $outval[] = (int) $val;
                                } elseif ($v = empty($val) && $has_default) {
                                    $outval[] = (int) $default_value;
                                }
                                $valid = $valid && $v;
                            }
                            if ($valid) {
                                $value = $outval;
                            }
                        } elseif ($has_default) {
                            $value = $default_value;
                        }
                    } else {
                        if ($valid = is_numeric($value) && ((int) $value) == $value) {
                            $value = (int) $value;
                        } elseif ($valid = empty($value) && ($has_default || !$is_required)) {
                            if ($has_default) {
                                $value = (int) $default_value;
                            }
                        }
                    }
                    break;
                case self::PARAM_TYPE_FLOAT:
                    if ($multi) {
                        $outval = array();
                        $valid = true;
                        if ($value) {
                            foreach ($value as $val) {
                                if ($v = is_numeric($val) && ((float) $val) == $val) {
                                    $outval[] = (float) $val;
                                } elseif ($v = empty($val) && $has_default) {
                                    $outval[] = (float) $default_value;
                                }
                                $valid = $valid && $v;
                            }
                            if ($valid) {
                                $value = $outval;
                            }
                        } elseif ($has_default) {
                            $value = $default_value;
                        }
                    } else {
                        if ($valid = is_numeric($value) && ((float) $value) == $value) {
                            $value = (float) $value;
                        } elseif ($valid = empty($value) && ($has_default || !$is_required)) {
                            if ($has_default) {
                                $value = (float) $default_value;
                            }
                        }
                    }
                    break;
                case self::PARAM_TYPE_ENUM:
                    if (!array_key_exists('values', $params)) {
                        $valid = false;
                    } else {
                        $possible_values = $params['values'];
                        if ($multi) {
                            $outval = array();
                            $valid = true;
                            if ($value) {
                                foreach ($value as $val) {
                                    if (!($v = in_array($val, $possible_values))) {
                                        if (empty($val) && $has_default) {
                                            $outval[] = $default_value;
                                        }
                                    } else {
                                        $outval[] = $v;
                                    }
                                    $valid = $valid && $v;
                                }
                                if ($valid) {
                                    $value = $outval;
                                }
                            } elseif ($has_default) {
                                $value = $default_value;
                            }
                        } else {
                            if (!($valid = in_array($value, $possible_values))) {
                                if ($valid = empty($value) && ($has_default || !$is_required)) {
                                    if ($has_default) {
                                        $value = $default_value;
                                    }
                                }
                            }
                        }
                    }
                    break;
                default:
                    $valid = true;
            }
            if (!$valid) {
                // TODO: delegate the subclass to present this error
                trigger_error(
                    sprintf(
                        "[%s::%s] Type mismatch for parameter '%s'. Expected '%s', but found: %s",
                        get_called_class(),
                        __FUNCTION__,
                        $name,
                        $type . $multi,
                        var_export($value, true)
                    ),
                    E_USER_ERROR
                );
            }
        }
        return $value;
    }

    /**
     * @fn allow_method($method, $params)
     * @short Adds a rule to control allowed methods for action methods
     * @details EmeRails allows the developer to customize what methods are allowed for an action. By default,
     * all read-only methods (GET, HEAD, OPTIONS) are allowed, while the others are blocked. By invoking
     * this method, the default behavior can be altered.
     *
     * The controller keeps a map of allow rules that are looked up before the action method is invoked.
     * You can customize the way the rule is applied by passing the optional parameter <tt>params</tt>.
     *
     * - If <tt>params</tt> is not provided, the method or methods are allowed on all actions of the controller.
     * - If set to a string, the method or methods are allowed on the action of that name.
     * - If passing an array:
     *     - If the <tt>'only'</tt> key is set, the method or methods will be allowed only on the listed action name,
     *       or names if the value is an array.
     *     - If the <tt>'except'</tt> key is passed, the method or methods will be allowed on all actions except the
     *       listed action name, or names if the value is an array.
     *     - If <tt>params</tt> is an array of strings, the method behaves the same as when setting the <tt>'only'</tt> key.
     *
     * Examples:
     *
     * <tt>$this->allow_method('POST', 'create');</tt>
     *
     * Allows the <tt>POST</tt> method on the <tt>create</tt> action.
     *
     * <tt>$this->allow_method(array('PUT', 'DELETE'), 'widget');</tt>
     *
     * Allows the <tt>PUT</tt> and <tt>DELETE</tt> methods on the <tt>widget</tt> action.
     *
     * <tt>$this->allow_method('GET', array('except' => array('delete')));</tt>
     *
     * Blocks the <tt>GET</tt> method on the <tt>delete</tt> action.
     *
     * @param method The name (or an array of names) of the method
     * Check the <tt>Request</tt> object for a list of supported methods.
     * @param params Optional parameters that define how to handle the method
     */
    protected function allow_method($method, $params = null)
    {
        if (is_array($method)) {
            foreach ($method as $m) {
                $this->allow_method($m, $params);
            }
            return;
        }
        if (is_array($params)) {
            if (array_key_exists('except', $params)) {
                $except = $params['except'];
                if (is_array($except)) {
                    // Block this method for all actions in the 'except' clause
                    foreach ($except as $action) {
                        $this->allowed_methods[$action][$method] = false;
                    }
                } else {
                    // Block this method for the single action in the 'except' clause
                    $this->allowed_methods[$except][$method] = false;
                }
                // Allow this method for all other actions
                $this->allowed_methods['*'][$method] = true;
            } elseif (array_key_exists('only', $params)) {
                $only = $params['only'];
                if (is_array($only)) {
                    // Allow this method for all actions in the 'only' clause
                    foreach ($only as $action) {
                        $this->allowed_methods[$action][$method] = true;
                    }
                } else {
                    // Allow this method for the single action in the 'only' clause
                    $this->allowed_methods[$only][$method] = true;
                }
                // Block this method for all other actions
                $this->allowed_methods['*'][$method] = false;
            } else {
                // Allow this method for all listed actions
                foreach ($params as $action) {
                    $this->allowed_methods[$action][$method] = true;
                }
            }
        } elseif (!is_null($params)) {
            // Allow this method for the named action
            $this->allowed_methods[$params][$method] = true;
        } else {
            // Blanket allow
            $this->allowed_methods['*'][$method] = true;
        }
    }

    /**
     * @fn is_method_allowed
     * @short Checks if the request method is allowed for the requested action
     * @details Since the introduction of method allow rules, you can specify which HTTP methods are allowed by controllers.
     * By default, "dangerous" methods (<tt>PUT</tt>, <tt>POST</tt>, <tt>DELETE</tt>) are blocked by controllers. This is
     * controlled by the <tt>DEFAULT_ALLOWED_METHODS</tt> config setting, which accepts a list of method names accepted by default.
     *
     * You can customize this behavior by editing the config setting <tt>DEFAULT_ALLOWED_METHODS</tt> to tweak the default
     * list of allowed methods, or set its value to <tt>'*'</tt> to restore the legacy behavior of allowing all methods unless
     * explicitly blocked.
     *
     */
    private function is_method_allowed()
    {
        $default_allowed = in_array($this->request->method, Config::get('DEFAULT_ALLOWED_METHODS'));

        // If a key with the name of the action exists, check if the method is mentioned
        if (array_key_exists($this->action, $this->allowed_methods)) {
            $rules = $this->allowed_methods[$this->action];
            if (array_key_exists($this->request->method, $rules)) {
                return $rules[$this->request->method];
            }
        } elseif (array_key_exists('*', $this->allowed_methods)) {
            // If there's a blanket rule, check that instead
            $rules = $this->allowed_methods['*'];
            if (array_key_exists($this->request->method, $rules)) {
                return $rules[$this->request->method];
            }
        }

        // Fall back to default behavior
        return $default_allowed;
    }

    /**
     * Page caching
     */

    /**
     * @fn caches_page($page)
     * @short Requests that a page be cached.
     * @details This method registers the page <tt>page</tt> to be cached after it has been
     * executed and rendered.
     * @param page The name (or an array of names) of a page that should be cached.
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
     * @fn conditionally_caches_page($page, $condition)
     * @short Requests that a page be cached when a condition is verified.
     * @details Convenience method to cache the requested page only if the
     * <tt>condition</tt> argument evaluates as <tt>TRUE</tt>.
     * @param page The name (or an array of names) of a page that should be cached.
     * @param condition Flag to decide if the page should be cached.
     */
    protected function conditionally_caches_page($page, $condition = true)
    {
        if ($condition) {
            $this->caches_page($page);
        }
    }

    /**
     * @fn caches_action($page)
     * @short Requests that an action be cached.
     * @details This method is currently unused.
     * @param page The name of a page that should be cached.
     */
    protected function caches_action($page)
    {
        $this->actions_cached[] = $page;
    }

    /**
     * @fn cached_page_exists
     * @short Checks if a cached version of the current action exists.
     * @details This method checks the existence of the file whose name is returned by <tt>cached_page_filename</tt>.
     */
    protected function cached_page_exists()
    {
        return file_exists($this->cached_page_filename());
    }

    /**
     * @fn cached_page_filename
     * @short Returns a name for the cached page of current action.
     * @details This method creates a filename that is uniquely associated with the current controller, action,
     * argument identifier and language.
     */
    protected function cached_page_filename()
    {
        $lang = isset($_COOKIE[Config::get('LANGUAGE_COOKIE')]) ? $_COOKIE[Config::get('LANGUAGE_COOKIE')] : 'en';
        $id = isset($_REQUEST['id']) ? "@{$_REQUEST['id']}" : '';
        $cachefile = sprintf('%s/caches/%s/%s%s-%s.cached', $this->base_path, $this->name, $this->action, $id, $lang);

        return $cachefile;
    }

    /**
     * @fn expire_cached_page($params)
     * @short Requests that the page(s) defined by <tt>params</tt> be removed from the caches.
     * @details This method removes from the caches all pages that match the <tt>params</tt> argument
     * for every supported language.
     */
    protected function expire_cached_page($params)
    {
        $controller = isset($params['controller']) ? $params['controller'] : $this->name;
        $action = isset($params['action']) ? $params['action'] : $this->action;
        $id = isset($params['id']) ? "@{$params['id']}" : '';

        $langs = Localization::$languages;

        foreach ($langs as $lang) {
            $cachefile = sprintf('%s/caches/%s/%s%s-%s.cached', $this->base_path, $controller, $action, $id, $lang);
            if (file_exists($cachefile)) {
                unlink($cachefile);
            }
        }
    }

    /**
     * Response manipulation
     */

    /**
     * @fn redirect_to($params)
     * @short Redirects the response to another action, or URL.
     * @details This method can either redirect the response to
     * an URL, or another action, whose parameters are contained in
     * the <tt>params</tt> argument.
     * @param params An URL, or an array with details of the action
     * for response redirection.
     */
    protected function redirect_to($params)
    {
        if (is_array($params)) {
            // Redirect to the current action if not specified
            if (!array_key_exists('action', $params)) {
                $params['action'] = $this->action;
            }
            $URL = sprintf(
                '%s://%s%s',
                isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http',
                $_SERVER['HTTP_HOST'],
                $this->url_to($params)
            );
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
     * @fn refresh($after)
     * @short Refreshes current page after a given amount of time.
     * @details Same as <tt>redirect_to</tt>, except that the destination is the same page.
     * @param after Number of seconds after which perform a refresh.
     */
    protected function refresh($amount)
    {
        $this->redirect_to(array('after' => $amount));
    }

    /**
     * @fn redirect_to_referrer($or_else)
     * @short Redirects the response to the URL which is contained
     * in the <tt>HTTP_REFERER</tt> server variable (i.e., performs a
     * back redirection). If no <tt>HTTP_REFERER</tt> is set, and if passed
     * the optional parameter <tt>or_else</tt>, the latter is used for an
     * explicit redirection.
     * @param or_else An URL, or an array with details of the action
     * for response redirection if no <tt>HTTP_REFERER</tt> is set.
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
     * Hyperlinks to actions
     */

    /**
     * @fn link_to($text, $params)
     * @short Links to another action.
     * @details This method creates a hyperlink to another action, as
     * specified in the <tt>params</tt> argument. This <tt>params</tt> array
     * has the same semantics used in the <tt>make_relative_url</tt> method.
     * You can also provide an explicit URL for the hyperlink by passing the
     * <tt>href</tt> key. This will override other parameters.
     * @see link_to_remote($text, $params);
     * @param text A text label for the hyperlink.
     * @param params Array of key value pairs defining the hyperlink.
     */
    public function link_to($text, $params = array())
    {
        $params['href'] = isset($params['href']) ? $params['href'] : $this->make_relative_url($params);

        unset($params['action']);
        unset($params['controller']);
        unset($params['id']);
        unset($params['query_string']);

        $ret = a($text, $params);
        if (isset($params['return']) && $params['return']) {
            return $ret;
        } else {
            print $ret;
        }
    }

    /**
     * @fn link_to_remote($text, $params)
     * @short Links to another action, with AJAX support.
     * @details This method behaves like <tt>link_to</tt>, except that
     * it also adds an AJAX handler to replace the contents of the element with id
     * <tt>target</tt>.
     * You can also provide different URLs for the static and AJAX hyperlink, by
     * explicitly setting the keys in <tt>params</tt>: <tt>href</tt>, <tt>remote_url</tt>,
     * or both.
     * @see link_to($text, $params);
     * @param text A text label for the hyperlink.
     * @param params Array of key value pairs defining the hyperlink.
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
            l('loading') .
            "</span></div>').load('{$params['remote_url']}'); return false";

        unset($params['action']);
        unset($params['controller']);
        unset($params['id']);
        unset($params['target']);
        unset($params['remote_url']);

        $ret = a($text, $params);
        if (isset($params['return']) && $params['return']) {
            return $ret;
        } else {
            print $ret;
        }
    }

    /**
     * @fn button_to($text, $params)
     * @short Creates a button that links to another action.
     * @details This method creates a button that links to another action, as
     * specified in the <tt>params</tt> argument. This <tt>params</tt> array
     * has the same semantics used in the <tt>make_relative_url</tt> method.
     * You can also provide an explicit URL for the hyperlink by passing the
     * <tt>href</tt> key. This will override other parameters.
     * @param text A text label for the button.
     * @param params Array of key value pairs defining the hyperlink.
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

        $ret = button(joined_lower($text), $text, $params);
        if (isset($params['return']) && $params['return']) {
            return $ret;
        } else {
            print $ret;
        }
    }

    /**
     * @fn unknown_action()
     * @short Fallback handler for unknown action.
     * @details This method implements the default action handler which
     * sends a 404 Not Found error back to the client.
     */
    protected function unknown_action()
    {
        $this->send_error(404);
    }

    /**
     * @fn send_error($status)
     * @short Sends an HTTP error to the client.
     * @details This method sends an HTTP error to the client with the
     * status code of choice.
     * @param status Status code.
     */
    protected function send_error($status)
    {
        HTTP::error($status);
    }

    /**
     * @fn url_to($params)
     * @short Creates a URL to an action.
     * @details This method builds a URL to an action, as
     * specified in the <tt>params</tt> argument. The <tt>params</tt> array
     * has the same semantics used in the <tt>make_relative_url</tt> method.
     * @param params Array of key value pairs defining the URL.
     */
    public function url_to($params)
    {
        return $this->make_relative_url($params);
    }

    /**
     * @fn url_to_myself($relative)
     * @short Creates a URL to the current action.
     * @details This method builds a URL to the current action.
     * @param relative Whether the URL should contain only the path,
     * or be a qualified URI.
     */
    function url_to_myself($relative = true)
    {
        $url = $this->url_to(array(
            'action' => $this->action,
            'type' => $this->type,
            'id' => @$_REQUEST['id']
        ));
        return $relative
            ? $url
            : sprintf(
                '%s://%s%s',
                isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http',
                $_SERVER['HTTP_HOST'],
                $url
            );
    }

    /**
     * @fn make_relative_url($params)
     * @short Creates a URL to an action.
     * @details This method builds a URL to an action, as
     * specified in the <tt>params</tt> argument. The <tt>params</tt> array
     * has the following semantics:
     *
     * <tt>controller</tt>: the name of the desired controller (optional, defaults to self).
     * <tt>action</tt>: the name of the desired action (optional, defaults to 'index').
     * <tt>id</tt>: the id of the argument of the action (optional).
     * <tt>type</tt>: the type (extension) of the resource (optional, defaults to 'html').
     * <tt>query_string</tt>: the query string part of the URL (optional).
     * <tt>hash</tt>: the target HTML anchor of the URL (optional).
     *
     * @param params Array of key value pairs defining the URL.
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
     * Response rendering
     */

    /**
     * @fn render($params)
     * @short Requests the rendering of the response.
     * @details This method is responsible of loading the view and layout
     * relative to the current controller and action, parsing their content,
     * and returning the result to be appended to the response.
     *
     * The <tt>params</tt> array has the following semantics:
     *
     * @li <tt>action</tt>: the name of the action for which render a view (defaults to current action).
     * @li <tt>layout</tt>: the layout used to render the view (optional, defaults to current action).
     * It can be set to <tt>NULL</tt> to render the view with no layout (the same result can be obtained
     * using the <tt>nolayout</tt> request parameter).
     * @li <tt>return</tt>: return the rendered view as a string, rather than flushing it to the output buffer.
     * @li <tt>partial</tt>: triggers the partial rendering mode (optional).
     * @li <tt>object</tt>: passes an argument to be used by the partial view (optional).
     *
     * @param params Parameters defining how the rendering should be realized.
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
            $params['part'] = $partial;

            // Get part file
            $partfile = sprintf('%s/views/%s/_%s.php', $this->base_path, $this->name, $partial);

            // Start output buffering
            ob_start();

            // Evaluate and send to buffer
            print $this->evaluate_part($partfile, $params);

            // Get buffer contents, clean output buffer
            $rendered = ob_get_clean();
        } else {
            // Get action to render
            $action =
                isset($params['action']) && !empty($params['action']) ? basename($params['action']) : $this->action;
            $params['part'] = $action;

            // Get part file
            $partfile = sprintf('%s/views/%s/%s.php', $this->base_path, $this->name, $action);

            // Start output buffering
            ob_start();

            // Evaluate and send to buffer
            print $this->evaluate_part($partfile, $params);

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
     * @fn render_as_string($params)
     * @short Requests the rendering of the response as a string.
     * @details This method calls <tt>render</tt> by passing the <tt>return</tt>
     * parameter to <tt>TRUE</tt>, and returns the rendered response as a string.
     * The <tt>params</tt> array has the same semantics discussed in the
     * <tt>render</tt> method.
     * @param params Parameters defining how the rendering should be realized.
     */
    public function render_as_string($params)
    {
        $params['return'] = true;
        return $this->render($params);
    }

    /**
     * @fn render_layout($params)
     * @short Renders the layout for the current view.
     * @details This method is responsible of loading the layout
     * as defined in the <tt>params</tt> argument, parsing it and
     * returning the result.
     * The <tt>params</tt> array has the same semantics of the <tt>render</tt>
     * method, although only the <tt>layout</tt> key is relevant.
     * @param params Parameters defining how the rendering should be realized.
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
        print $this->evaluate_part($partfile);

        // Get buffer contents, clean output buffer
        $rendered = ob_get_clean();

        return $rendered;
    }

    /**
     * @fn render_page
     * @short Renders the page as a response for the current request.
     * @details This method is responsible for building the response
     * for the current request.
     * @li All before filters that are registered for the current action are executed.
     * @li If the current page is cacheable, and a cached version already exists in
     * the caches, it is returned; otherwise it is rendered and eventually stored in
     * the cache folder.
     * @li All after filters that are registered for the current action are executed.
     * @li Finally, the response is flushed to the client.
     */
    public function render_page()
    {
        // Check if method is allowed for this action
        if (!$this->is_method_allowed()) {
            $this->send_error(405);
        }

        // Populate accepted parameters
        $this->populate_accepted_parameters();

        // Process before filters queue
        foreach ($this->before_filters as $before_filter) {
            list($filter, $conditions) = $before_filter;
            if ($this->filter_applicable($conditions)) {
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
                $this->invoke_action();
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
            list($filter, $conditions) = $after_filter;
            if ($this->filter_applicable($conditions)) {
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
     * @fn invoke_action()
     * @short Thin wrapper around invocation of the controller's action method
     * @details This method is provided as a convenience to subclassers. You can override it to add
     * side effects or instrumentation, e.g. performance measurements.
     */
    protected function invoke_action()
    {
        $this->{$this->action}();
    }

    /**
     * @fn render_component($params)
     * @short Renders a view from a controller other than self.
     * @details This method renders a view from a controller other than self,
     * for example when embedding a view into another view.
     *
     * The <tt>params</tt> array has the same semantics of the <tt>render</tt> method,
     * with the following additions:
     *
     * <tt>controller</tt>: the name of the controller responsible for the view (defaults to self).
     * @param params Parameters defining how the rendering should be realized.
     */
    public function render_component($params)
    {
        // Merge request and user parameters
        $_GET = array_merge($_GET, $params);
        $_POST = array_merge($_POST, $params);
        $_REQUEST = array_merge($_REQUEST, $params);

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
     * @fn load_part_contents($filename)
     * @short Loads the contents of the desired view file.
     * @details This method returns the contents of the requested view file
     * without parsing.
     * @param partfile The name of the view file to load.
     */
    protected function load_part_contents($partfile)
    {
        if (!file_exists($partfile)) {
            if (Config::get('DEV_MODE')) {
                $file = preg_replace(
                    '/' . addcslashes($this->base_path, '/') . '/',
                    htmlentities('<PROJECT_ROOT>'),
                    $partfile
                );
                return $this->strip_external_php_tags(
                    block_tag(
                        'div',
                        implode("\n", array(
                            h3(l('error'), null),
                            block_tag('p', sprintf('Missing part file: %s', $file), null)
                        )),
                        array('class' => 'msg error')
                    )
                );
            } else {
                $this->send_error(500);
            }
        }
        $contents = file_get_contents($partfile);
        return $this->strip_external_php_tags($contents);
    }

    /**
     * @fn evaluate_part($partfile, $params)
     * @short Evaluates a part file and returns it as a string.
     * @details This method loads and evaluates a part file, setting the corresponding
     * implicit variables, handling symbolication for errors.
     * @param partfile The name of the part file to load and evaluate.
     * @param params Parameters defining how the rendering should be realized.
     */
    protected function evaluate_part($partfile, $params = array())
    {
        $GLOBALS['__PART__'] = $partfile;
        try {
            if (isset($params['partial'])) {
                $partial = basename($params['partial']);

                // Connect main object
                if (isset($params['object'])) {
                    eval("\${$partial} = \$params['object'];");
                } elseif (isset($this->$partial)) {
                    eval("\${$partial} = \$this->{$partial};");
                }
            }

            $ret = eval($this->load_part_contents($partfile));
        } catch (Throwable $t) {
            if (Config::get('DEV_MODE')) {
                $file = preg_replace(
                    '/' . addcslashes($this->base_path, '/') . '/',
                    htmlentities('<PROJECT_ROOT>'),
                    $partfile
                );
                $ret = block_tag(
                    'div',
                    implode("\n", array(
                        h3(l('error'), null),
                        block_tag(
                            'p',
                            sprintf('[%d] %s, at: %s:%d', $t->getCode(), $t->getMessage(), $file, $t->getLine()),
                            null
                        )
                    )),
                    array('class' => 'msg error')
                );
            } else {
                throw $t;
            }
        }
        unset($GLOBALS['__PART__']);

        return $ret;
    }

    /**
     * @fn strip_external_php_tags($php_code)
     * @short Strips beginning and ending delimiters from the given PHP code.
     * @details This method removes the beginning and ending PHP code delimiters
     * to enable subsequent parsing with <tt>eval</tt>.
     * The algorithm is as follows:
     *   - If a <tt>&lt;?php</tt> opening tag appears at the beginning of <tt>$php_code</tt>, it is stripped,
     *     otherwise a closing tag <tt>?&gt;</tt> is added to the beginning.
     *   - If a <tt>?&gt;</tt> closing tag appears at the end of <tt>$php_code</tt>, it is stripped, otherwise
     *
     * @param php_code The code to be stripped.
     */
    protected function strip_external_php_tags($php_code)
    {
        $first_opening_tag = strpos($php_code, '<?php');
        $last_opening_tag = strrpos($php_code, '<?php');
        $first_closing_tag = strpos($php_code, '?>');
        $last_closing_tag = strrpos($php_code, '?>');

        if ($first_opening_tag === 0) {
            // Trivial case, opening PHP tag at the beginning of content
            $php_code = substr($php_code, strlen('<?php'));
        } elseif (
            // No opening or closing PHP tags
            ($first_opening_tag === false && $first_closing_tag === false) ||
            // First opening PHP tag appearing before the first closing PHP tag
            ($first_closing_tag === false || $first_opening_tag < $first_closing_tag)
        ) {
            $php_code = "?>\n" . $php_code;
        }
        if (strrpos($php_code, "?>\n") === strlen($php_code) - strlen("?>\n")) {
            // Trivial case, closing PHP tag at the end of content
            $php_code = substr($php_code, 0, strrpos($php_code, "?>\n"));
        } elseif (
            // Last closing PHP tag appearing after the last opening PHP tag
            $last_opening_tag === false ||
            $last_closing_tag > $last_opening_tag
        ) {
            $php_code .= "\n<?php\n";
        }
        return $php_code;
    }

    /**
     * Miscellaneous
     */

    /**
     * @fn set_title($title)
     * @short Sets the title for the current page.
     * @param title A title for the current page.
     */
    public function set_title($title)
    {
        $this->title = $title;
    }

    /**
     * @fn abort_and_flush
     * @short Interrupts the processing of the request.
     */
    private function abort_and_flush()
    {
        ob_end_flush();
        exit();
    }

    /**
     * Messages handling
     */

    /**
     * @fn flash($message, $type)
     * @short Shows a message to the user, with an optional type qualifier.
     * @details The flash is a facility to store messages that should be visualized
     * as a result of some event, or as a response to the user's previous request.
     * @param message A message to show to the user.
     * @param type The type of the message (e.g. 'warning').
     */
    public function flash($message, $type = 'error')
    {
        $_SESSION['flash'] = array('message' => $message, 'type' => $type);
    }

    /**
     * Action methods
     */

    /**
     * @fn index
     * @short This is the default action method.
     */
    public function index()
    {
        $this->render(array('layout' => 'default'));
    }

    /**
     * Filters
     */

    /**
     * @fn compress
     * @short Compresses the response with gzip encoding.
     * @details This after-filter is capable of compressing the response with gzip encoding,
     * in order to save bandwidth. If the client does not support gzip encoding, the response
     * is not altered.
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
