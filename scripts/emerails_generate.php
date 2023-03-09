#!/usr/bin/php
<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2023
 *
 * @format
 */

require_once __DIR__ . '/base.php';

use splitbrain\phpcli\Options;

use Emeraldion\EmeRails\Helpers\ANSIColorWriter;

class EmerailsGenerate extends ScriptCommand
{
    protected $name = 'EmeRails Generator Utility';
    protected $version = 'v1.0';

    protected function setup(Options $options)
    {
        $options->setHelp('EmeRails script to generate controllers, models and views');

        // General options
        $options->registerOption('verbose', 'Print additional messages');
        $options->registerOption('dry-run', 'Do not commit changes');

        // Controller command
        $options->registerCommand('controller', 'Manage controllers');
        // Controller options
        $options->registerArgument('controller', 'Name of the controller', true, 'controller');
        $options->registerOption(
            'actions',
            'Action methods, comma separated',
            'a',
            'action1,action2,...',
            'controller'
        );

        // Model options
        $options->registerCommand('model', 'Manage models');
        // Model options
        $options->registerArgument('model', 'Name of the model', true, 'model');
        $options->registerOption(
            'fields',
            'Fields of the model, with types',
            'f',
            'field1:type1,field2:type2,...',
            'model'
        );

        // View command
        $options->registerCommand('view', 'Manage views');
    }

    protected function main(Options $options)
    {
        $dry_run = $options->getOpt('dry-run');
        $verbose = $options->getOpt('verbose');

        switch ($options->getCmd()) {
            case 'controller':
                $controller = strtolower(first($options->getArgs()));

                print "\tcreating controllers/{$controller}_controller.php\n";

                $controller_class = table_name_to_class_name("{$controller}_controller");

                $controller_code = <<<EOT
<?php
/**
 * @format
 */

use Emeraldion\EmeRails\Controllers\BaseController;

/**
 * @class {$controller_class}
 * @short Edit this controller's short description
 * @details Edit this controller's detailed description
 */
class {$controller_class} extends BaseController
{
    /**
     * @fn init
     * @short Performs specialized initialization
     * @details You should use this method to do your custom initialization.
     */
    protected function init()
    {
        parent::init();

        // TODO: add your initialization code here
    }

    /**
     * @fn index
     * @short This is the default action
     * @details This is the default action when the controller is invoked without an action
     */
    public function index()
    {
        // TODO: add your code here
    }

EOT;
                $this->create_view($controller, 'index');

                $actions = explode(',', $options->getOpt('actions'));

                for ($i = 0; $i < count($actions); $i += 1) {
                    $action = strtolower($actions[$i]);

                    if ($action === 'index') {
                        continue;
                    }

                    $this->create_view($controller, $action);

                    $controller_code .= <<<EOT

    /**
     * @fn {$action}
     * @short Edit this actions's short description
     * @details Edit this actions's detailed description
     */
    public function {$action}()
    {
        // TODO: add your code here
    }

EOT;
                }
                $controller_code .= <<<EOT
}

?>

EOT;

                $dir = sprintf('%s/../../controllers', $GLOBALS['_composer_bin_dir'] ?? __DIR__ . '/../vendor/bin');

                if (!(file_exists($dir) && is_dir($dir))) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents(sprintf('%s/%s_controller.php', $dir, $controller), $controller_code);
                break;
            case 'model':
                $model_class = joined_lower_to_camel_case(first($options->getArgs()));
                $model_table = class_name_to_table_name($model_class);
                $model = singularize($model_table);

                print "\tcreating models/$model.php\n";

                $model_code = <<<EOT
<?php
/**
 * @format
 */

use Emeraldion\EmeRails\Models\ActiveRecord;

/**
 * @class {$model_class}
 * @short Edit this model's short description
 * @details Edit this model's detailed description
 */
class {$model_class} extends ActiveRecord
{
    // TODO: add your code here
}
?>

EOT;

                $dir = sprintf('%s/../../models', $GLOBALS['_composer_bin_dir'] ?? __DIR__ . '/../vendor/bin');

                if (!(file_exists($dir) && is_dir($dir))) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents(sprintf('%s/%s.php', $dir, $model), $model_code);

                $fields = array('id' => 'int(11)');
                $fields_opt = explode(',', $options->getOpt('fields'));
                for ($i = 0; $i < count($fields_opt); $i += 1) {
                    list($name, $type) = explode(':', $fields_opt[$i]);
                    $fields[$name] = $type;
                }

                $this->create_model($model_table, $fields);
                break;
            default:
                $options->useCompactHelp();
                print $options->help();
        }
    }

    protected function create_model($tablename, $fields)
    {
        $conn = Db::get_connection();

        $query = "SHOW TABLES LIKE '{$conn->escape($tablename)}'";
        $conn->prepare($query);
        $conn->exec();

        if (!$conn->result()) {
            $query = "CREATE TABLE `{$conn->escape($tablename)}` (\n";

            $i = 0;
            foreach ($fields as $name => $type) {
                $comma = $i > 0 ? ',' : '';
                $query .= "{$comma}`{$name}` {$type}\n";
                $i += 1;
            }

            $query .= ');';

            $conn->prepare($query);
            $conn->exec();
        }

        Db::close_connection($conn);
    }

    protected function create_view($controller, $action)
    {
        print "\tcreating views/$controller/$action.php\n";

        $dir = sprintf('%s/../../views/%s', $GLOBALS['_composer_bin_dir'] ?? __DIR__ . '/../vendor/bin', $controller);

        if (!(file_exists($dir) && is_dir($dir))) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(sprintf('%s/%s.php', $dir, $action), '<!-- TODO: add your code here -->');
    }
}

$cli = new EmerailsGenerate();
$cli->run();


?>
