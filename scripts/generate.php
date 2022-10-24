#!/usr/bin/php
<?php
/**
 *	Project EmeRails - Codename Ocarina
 *
 *	Copyright (c) 2008, 2017 Claudio Procida
 *	http://www.emeraldion.it
 *
 */

require_once dirname(__FILE__) . "/../include/common.inc.php";
require_once dirname(__FILE__) . "/../include/db.inc.php";
require_once dirname(__FILE__) . '/../include/' . DB_ADAPTER . '_adapter.php';

function usage()
{
    echo <<<EOT
Usage: generate.php controller controller_name [action1 [action2 ...]]
       generate.php model model_name [field1 [type1 [field2 [type2 ...]]]]

EOT;
}

function create_model($tablename, $fields)
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

function create_view($controller, $action)
{
    echo "\tcreating views/$controller/$action.php\n";

    $dir = dirname(__FILE__) . "/../views/$controller";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
    }

    file_put_contents(
        dirname(__FILE__) . "/../views/$controller/$action.php",
        "<!-- TODO: add your code here -->"
    );
}

if ($argc > 2) {
    if ($argv[1] == 'controller') {
        $controller = strtolower($argv[2]);

        echo "\tcreating controllers/{$controller}_controller.php\n";

        $controller_class = table_name_to_class_name(
            "{$controller}_controller"
        );

        $controller_code = <<<EOT
<?php
	require_once(dirname(__FILE__) . "/base_controller.php");

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
        create_view($controller, 'index');

        for ($i = 3; $i < $argc; $i++) {
            $action = strtolower($argv[$i]);

            create_view($controller, $action);

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

        file_put_contents(
            dirname(__FILE__) . "/../controllers/{$controller}_controller.php",
            $controller_code
        );
    } elseif ($argv[1] == 'model') {
        $model_class = joined_lower_to_camel_case($argv[2]);
        $model_table = class_name_to_table_name($model_class);
        $model = singularize($model_table);

        echo "\tcreating models/$model.php\n";

        $model_code = <<<EOT
<?php
	require_once(dirname(__FILE__) . "/base.php");

	/**
	 *	@class {$model_class}
	 *	@short Edit this model's short description
	 *	@details Edit this actions's detailed description
	 */
	class {$model_class} extends ActiveRecord
	{
		// TODO: add your code here
	}
?>
EOT;

        file_put_contents(
            dirname(__FILE__) . "/../models/$model.php",
            $model_code
        );

        $fields = array('id' => 'int(11)');
        if ($argc > 2) {
            for ($i = 3; $i < $argc; $i += 2) {
                $fields[$argv[$i]] = $argv[$i + 1];
            }
        }

        create_model($model_table, $fields);
    } else {
        usage();
    }
} else {
    usage();
}

?>
