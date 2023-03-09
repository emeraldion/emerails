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
        $options->registerOption('action', 'Action method', 'a', 'action', 'controller');

        // Model options
        $options->registerCommand('model', 'Manage models');
        // Model options
        $options->registerArgument('model', 'Name of the model', true, 'model');
        $options->registerOption('field', 'Model field', 'f', 'field', 'model');

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

                echo "\tcreating controllers/{$controller}_controller.php\n";

                $controller_class = table_name_to_class_name("{$controller}_controller");

                $controller_code = <<<EOT
<?php
	require_once(__DIR__ . "/base_controller.php");

	/**
	 *	@class {$controller_class}
	 *	@short Edit this controller's short description
	 *	@details Edit this controller's detailed description
	 */
	class {$controller_class} extends BaseController
	{
		/**
		 *	@fn init
		 *	@short Performs specialized initialization
		 *	@details You should use this method to do your custom initialization.
		 */
		protected function init()
		{
            parent::init();

            // TODO: add your initialization code here
		}

		/**
		 *	@fn index
		 *	@short This is the default action
		 *	@details This is the default action when the controller is invoked without an action
		 */
		public function index()
		{
			// TODO: add your code here
		}

EOT;
                $this->create_view($controller, 'index');

                $actions = explode(',', $options->getOpt('action'));

                for ($i = 0; $i < count($actions); $i++) {
                    $action = strtolower($actions[$i]);

                    $this->create_view($controller, $action);

                    $controller_code .= <<<EOT

		/**
		 *	@fn {$action}
		 *	@short Edit this actions's short description
		 *	@details Edit this actions's detailed description
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

                file_put_contents(__DIR__ . "/../controllers/{$controller}_controller.php", $controller_code);
                break;
            case 'model':
                $model_class = joined_lower_to_camel_case(first($options->getArgs()));
                $model_table = class_name_to_table_name($model_class);
                $model = singularize($model_table);

                echo "\tcreating models/$model.php\n";

                $model_code = <<<EOT
<?php
	require_once(__DIR__ . "/base.php");

	/**
	 *	@class {$model_class}
	 *	@short Edit this model's short description
	 *	@details Edit this model's detailed description
	 */
	class {$model_class} extends ActiveRecord
	{
		// TODO: add your code here
	}
?>
EOT;

                file_put_contents(__DIR__ . "/../models/$model.php", $model_code);

                $fields = array('id' => 'int(11)');
                if ($argc > 2) {
                    for ($i = 3; $i < $argc; $i += 2) {
                        $fields[$argv[$i]] = $argv[$i + 1];
                    }
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

        $query = "CREATE TABLE `{$conn->escape($tablename)}` (\n";

        $i = 0;
        foreach ($fields as $name => $type) {
            $comma = $i > 0 ? ',' : '';
            $query .= "{$comma}`{$name}` {$type}\n";
            $i++;
        }

        $query .= ');';

        $conn->prepare($query);
        $conn->exec();

        Db::close_connection($conn);
    }

    protected function create_view($controller, $action)
    {
        echo "\tcreating views/$controller/$action.php\n";

        $dir = __DIR__ . "/../views/$controller";
        if (!is_dir($dir)) {
            mkdir($dir, 0755);
        }

        file_put_contents(__DIR__ . "/../views/$controller/$action.php", '<!-- TODO: add your code here -->');
    }

    protected function get_string_file($dir)
    {
        if (strpos($dir, self::DEFAULT_BASE_DIR) === 0) {
            $dir = substr($dir, strlen(self::DEFAULT_BASE_DIR) + 1);
        }
        return $dir;
    }
}

$cli = new EmerailsGenerate();
$cli->run();


?>
