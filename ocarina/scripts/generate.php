<?php
	/**
	 *	Project EmeRails - Codename Ocarina
	 *
	 *	© 2008 Claudio Procida
	 *	http://www.emeraldion.it
	 *
	 */

	require_once(dirname(__FILE__) . "/../include/common.inc.php");
	
	function usage()
	{
		echo <<<EOT
Usage: generate.php controller controller_name [action1 [action2 ...]]
	 generate.php model model_name

EOT;
	}

	if (count($argv) > 2)
	{
		if ($argv[1] == 'controller')
		{
			$controller = $argv[2];
			
			echo "\tcreating controllers/{$controller}_controller.php\n";
			
			$controller_class = table_name_to_class_name("{$controller}_controller");
			
			$controller_code = <<<EOT
<?php
	require_once(dirname(__FILE__) . "/base_controller.php");
	
	/**
	 *	@class {$controller_class}
	 *	@short
	 *	@details
	 */
	class {$controller_class} extends BaseController
	{
		/**
		 *	
		 *	@short Performs specialized initialization
		 *	@details You should use this method to do your custom initialization.
		 */
		protected function init()
		{
			// TODO: add your initialization code here
		}
EOT;

			for ($i = 3; $i < count($argv); $i++)
			{
				echo "\tcreating views/$controller/{$argv[$i]}.php\n";
				
				mkdir(dirname(__FILE__) . "/../views/$controller", 755);
				file_put_contents(dirname(__FILE__) . "/../views/$controller/{$argv[$i]}.php",
					"<!-- TODO: add your code here -->");
				
				$controller_code .= <<<EOT
		    
		/**
		 *	
		 *	@short
		 *	@details
		 */
		public function {$argv[$i]}()
		{
			// TODO: add your code here
		}

EOT;
			
			}
			$controller_code .= <<<EOT

	}
?>
EOT;
		
			file_put_contents(dirname(__FILE__) . "/../controllers/{$controller}_controller.php",
				$controller_code);
		}
		else if ($argv[1] == 'model')
		{
			$model_class = $argv[2];
			
			$model = singularize(class_name_to_table_name($model_class));

			echo "\tcreating models/$model.php\n";
			
			$model_code = <<<EOT
<?php
	require_once(dirname(__FILE__) . "/base.php");

	/**
	 *	@class {$model_class}
	 *	@short
	 *	@details
	 */
	class {$model_class} extends ActiveRecord
	{
		// TODO: add your code here
	}
?>
EOT;
		
			file_put_contents(dirname(__FILE__) . "/../models/$model.php",
				$model_code);
		}
		else
		{
			usage();
		}
	}
	else
	{
		usage();
	}
?>