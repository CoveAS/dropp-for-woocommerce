<?php

namespace Dropp\Actions;

class Get_Plugin_Data_From_File {
	public function __invoke(string $filename): array
	{
		$pluginDir = realpath(WP_PLUGIN_DIR);
		$dirName    = str_replace($pluginDir, '', $filename);
		$parts      = array_filter(explode('/', $dirName));
		$dirName    = reset($parts);
		$plugins = get_plugins();
		foreach ($plugins as $path => $plugin) {
			$parts = explode('/', $path);
			if ($parts[0] !== $dirName) {
				continue;
			}
			return $plugin;
		}

		return [
			'Name' => ucfirst($dirName),
		];
	}
}
