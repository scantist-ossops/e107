<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class pluginsTest extends \Codeception\Test\Unit
	{

	//	protected $_plg;

		protected function _before()
		{
			/*try
			{
				$this->_plg = $this->make('e107plugin');
			}
			catch (Exception $e)
			{
				$this->assertTrue(false, "Couldn't load e107plugin object");
			}*/
		}

		private function makePluginReport($pluginDir, $debug=false)
		{
			$debug_text = "\n\n---- Log \n\n";
			$log = e107::getPlugin()->getLog();

			foreach($log as $line)
			{
				$debug_text .= " - ".$line."\n";
			}

			$debug_text .= "----------------------------------------\n\n";

			$debug_text .= "---- Pref: plug_installed (version)\n\n";
			$pref = e107::getConfig('core',true,true)->get('plug_installed');

			$debug_text .= print_r($pref[$pluginDir],true);

			$installedPref = isset($pref[$pluginDir]) ? $pref[$pluginDir] : false;

			$debug_text .= "\n\n---- Plugin Prefs: \n\n";
			$pluginPref = e107::pref($pluginDir);
			$debug_text .= print_r($pluginPref,true);


			$debug_text .= "\n---- Plugin Table: ".$pluginDir."\n\n";
			$pluginTable = e107::getDb()->retrieve('plugin','*', "plugin_path='".$pluginDir."' LIMIT 1", true);
			$debug_text .= print_r($pluginTable,true);

			$debug_text .= "\n---- Menu Table: ".$pluginDir."\n\n";
			$menuTable = e107::getDb()->retrieve('menus','*', "menu_location = 0 AND menu_path='".$pluginDir."/' LIMIT 10", true);
			$debug_text .= print_r($menuTable, true);

			$debug_text .= "\n---- Site Links Table: ".$pluginDir."\n\n";
			$linksTable = e107::getDb()->retrieve('links','*', "link_owner='".$pluginDir."' ", true);
			$debug_text .= print_r($linksTable, true);

			$files_in_plugin_directory = scandir(e_PLUGIN.$pluginDir);
			$corePref = e107::getConfig('core',true,true)->getPref();


			$debug_text .= "\n---- Addons\n\n";
			$debug_text .= "-------------------------------------------------------------------\n";
			$debug_text .= "Addon file                  In Core pref e_xxxx_list  \n";
			$debug_text .= "-------------------------------------------------------------------\n";

			$addonPref = array();

			$plugin_addon_names = $this->pluginFileListToPluginAddonNames($files_in_plugin_directory);

			foreach($plugin_addon_names as $plugin_addon_name)
			{
				$key = $plugin_addon_name."_list";
				$addon_pref_is_present = !empty($corePref[$key][$pluginDir]);
				$debug_addon_pref_is_present = ($addon_pref_is_present) ? 'YES' : 'NO';

				if($key === 'e_help_list')
				{
					$debug_addon_pref_is_present = "DEPRECATED by Admin-UI renderHelp()";
				}
				else
				{
					$addonPref[$plugin_addon_name] = $addon_pref_is_present;
				}

				$debug_text .= str_pad("$plugin_addon_name.php",20)
					."\t\t$debug_addon_pref_is_present\n";
			}

			$debug_text .= "-------------------------------------------------------------------\n";

			if($debug === true)
			{
				codecept_debug($debug_text);
			}

			return array(
				'log'           => $log,
				'installedPref' => $installedPref,
				'pluginPref'    => $pluginPref,
				'pluginTable'   => $pluginTable,
				'menuTable'     => $menuTable,
				'linksTable'    => $linksTable,
				'addonPref'     => $addonPref
			);
		}

		public function testBanner()
		{
			$this->pluginInstall('banner');

			$tp = e107::getParser();

			$result = $tp->parseTemplate("{BANNER=e107promo}",true);
			$this->assertContains("<img class='e-banner img-responsive img-fluid'",$result);

			$this->pluginUninstall('banner');
			$result2 = $tp->parseTemplate("{BANNER=e107promo}",true);

			// The expected value below was the actual observed output when the assertion was written:
			$this->assertEquals('&nbsp;', $result2);
		}

		public function testChatbox_Menu()
		{
			$this->pluginInstall('chatbox_menu');

			$this->pluginUninstall('chatbox_menu');
		}

		public function testDownload()
		{
			$this->pluginInstall('download');

			$this->pluginUninstall('download');
		}

		public function testFaqs()
		{
			$this->pluginInstall('faqs');
			$this->pluginUninstall('faqs');
		}

		public function testFeaturebox()
		{
			$this->pluginInstall('featurebox');
			$this->pluginUninstall('featurebox');
		}

		public function testForum()
		{
			$this->pluginInstall('forum');
			$this->pluginUninstall('forum');
		}

		public function testGallery()
		{
			$this->pluginInstall('gallery');
			$this->pluginUninstall('gallery');
		}

		public function testGsitemap()
		{
			$this->pluginInstall('gsitemap');
			$this->pluginUninstall('gsitemap');
		}

		public function testImport()
		{
			$this->pluginInstall('import');
			$this->pluginUninstall('import');
		}

		public function testLinkwords()
		{
			$this->pluginInstall('linkwords');
			$this->pluginUninstall('linkwords');
		}

		public function testPm()
		{
			$this->pluginInstall('pm');
			$this->pluginUninstall('pm');
		}

		public function testPoll()
		{
			$this->pluginInstall('poll');
			$this->pluginUninstall('poll');
		}

		public function testRss_menu()
		{
			$this->pluginInstall('rss_menu');
			$this->pluginUninstall('rss_menu');
		}

		public function testSocial()
		{
			$this->pluginUninstall('social');
			$this->pluginInstall('social');
		}

		public function testTagcloud()
		{
			$this->pluginInstall('tagcloud');
			$this->pluginUninstall('tagcloud');
		}

		private function pluginInstall($pluginDir)
		{

			e107::getPlugin()->install($pluginDir);

			$install = $this->makePluginReport($pluginDir, false); // set to true to see more info

			//todo additional checks

			foreach($install['addonPref'] as $key=>$val)
			{
				$this->assertTrue($val, $key." list pref is missing for ".$pluginDir);
			}
		}

		private function pluginUninstall($pluginDir)
		{
			$opts = array(
				'delete_tables' => 1,
				'delete_files'   => 0
			);

			e107::getPlugin()->uninstall($pluginDir, $opts);

			$uninstall = $this->makePluginReport($pluginDir, false); // set to true to see more info

			//todo additional checks

			$this->assertEmpty($uninstall['linksTable'], $pluginDir." link still exists in the links table");

			foreach($uninstall['addonPref'] as $key=>$val)
			{
				$message = $key." list pref still contains '".$pluginDir."' after uninstall of ".$pluginDir.". ";
				$this->assertEmpty($val, $message);
			}
		}

		/**
		 * @param $plugin_file_list
		 * @return array
		 */
		private function pluginFileListToPluginAddonNames($plugin_file_list)
		{
			$plugin_addon_names = array_map(function ($addon_path) {
				return basename($addon_path, '.php');
			}, $plugin_file_list);

			$class_name_that_has_plugin_addons_array = 'e107plugin';
			try {
				$reflectionClass = new ReflectionClass($class_name_that_has_plugin_addons_array);
			} catch (ReflectionException $e) {
				$this->fail("Could not instantiate $class_name_that_has_plugin_addons_array to get \$plugin_addons");
			}
			$reflectionProperty = $reflectionClass->getProperty('plugin_addons');
			$reflectionProperty->setAccessible(true);
			$valid_plugin_addon_names = $reflectionProperty->getValue(new $class_name_that_has_plugin_addons_array());

			$plugin_addon_names = array_filter($plugin_addon_names, function ($plugin_addon_name) use ($valid_plugin_addon_names) {
				return in_array($plugin_addon_name, $valid_plugin_addon_names);
			});

			return $plugin_addon_names;
		}
	}
