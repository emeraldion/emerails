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

use Emeraldion\EmeRails\Config;

function strip_double_dots($str)
{
    return preg_replace('/\.\.\//', '', $str);
}

/**
 *	@class AssetController
 *	@short Controller for asset retrieval and inherent optimizations.
 *	@details The Asset controller tries to save bandwidth by compressing the plain text resources
 *	(e.g. CSS stylesheets, Javascript source code, etc.)
 */
class AssetController extends BaseController
{
    protected function init()
    {
        // Call parent's init method
        parent::init();

        switch ($this->request->get_parameter('ext')) {
            case 'js':
                $this->mimetype = 'application/x-javascript';
                break;
            case 'rss':
                $this->mimetype = 'application/rss+xml';
                break;
            case 'xml':
                $this->mimetype = 'text/xml';
                break;
            case 'css':
                $this->mimetype = 'text/css';
                break;
            default:
                $this->mimetype = 'text/plain';
        }

        $this->after_filter('send_cache_headers', 'compress');
    }

    /**
     * @fn index
     * @short Serves a single resources
     * @details This action method returns a single asset resource described by the <code>dir</code>, <code>file</code>, and <code>ext</code>.
     */
    public function index()
    {
        $asset_file = sprintf(
            '%s/assets/%s/%s.%s',
            $this->base_path,
            strip_double_dots($this->request->get_parameter('dir')),
            strip_double_dots($this->request->get_parameter('file')),
            strip_double_dots($this->request->get_parameter('ext'))
        );
        $asset_file = get_called_class()::resolve_asset_file($asset_file, $this->base_path);

        $this->response->body = $this->rewrite(file_get_contents($asset_file));

        $this->response->add_header('Last-Modified', gmstrftime('%a, %d %b %Y %H:%M:%S %Z', filemtime($asset_file)));

        $this->render(null);
    }

    /**
     * @fn aggregate
     * @short Aggregates resources
     * @details This action method returns aggregated resources from a list provided by <code>get_aggregate_files</code>.
     * Note that the base implementation returns nothing as the <code>get_aggregate_files</code> method returns an empty list, and must
     * be overridden by subclassers.
     * @param bundle The name of the bundle, defaults to null
     */
    public function aggregate($bundle = null)
    {
        $this->response->body = '';
        $mtime = 0;
        foreach (get_called_class()::get_aggregate_files($bundle) as $asset_file) {
            $asset_file = get_called_class()::resolve_asset_file($asset_file, $this->base_path);
            $mtime = max($mtime, filemtime($asset_file));
            $this->response->body .= $this->rewrite(file_get_contents($asset_file));
        }

        $this->response->add_header('Last-Modified', gmstrftime('%a, %d %b %Y %H:%M:%S %Z', $mtime));

        $this->render(null);
    }

    /**
     * @fn rewrite
     * @short Rewrites the contents of the asset file
     * @details The basic implementation replaces the occurrences of the path <code>/assets/</code> with the
     * actual path comprising of the <code>APPLICATION_ROOT</code> config value. It can be overridden to perform
     * more specialized rewriting e.g. customize CSS vars, etc.
     * @param body The body of the asset
     */
    protected function rewrite($body)
    {
        if (Config::get('APPLICATION_ROOT') !== '/') {
            return preg_replace('/\/assets\//i', Config::get('APPLICATION_ROOT') . 'assets/', $body);
        }
        return $body;
    }

    /**
     * @fn send_cache_headers
     * @short Sends basic cache headers
     * @details This method sends some useful cache headers to instruct proxy servers and the client to
     * cache the resource for at least 1 day
     */
    protected function send_cache_headers()
    {
        $this->response->add_header('Content-Type', $this->mimetype);
        $this->response->add_header('Cache-Control', 'max-age=86400');
        $this->response->add_header('Pragma', 'max-age=86400');
        $this->response->add_header('Date', gmstrftime('%a, %d %b %Y %H:%M:%S %Z'));
        $this->response->add_header('Expires', gmstrftime('%a, %d %b %Y %H:%M:%S %Z', time() + 86400));
    }

    /**
     * @fn resolve_asset_file
     * @short Resolves the path to the asset file
     * @details This method can be overridden to specify a different path for the requested file, e.g.
     * to return cached or minified resources.
     * @param file The path of the file
     * @param base_path The base path of the controller
     */
    protected static function resolve_asset_file($file, $base_path = __DIR__ . '/../')
    {
        // The base implementation returns the original file path
        return $file;
    }

    /**
     * @fn get_aggregate_files
     * @short Returns the files to aggregate
     * @details This method is abstract and must be overridden by subclassers to specify the list of files to aggregate.
     * @param bundle The name of the bundle to aggregate, defaults to null
     */
    protected static function get_aggregate_files($bundle = null)
    {
        return array();
    }
}
?>
