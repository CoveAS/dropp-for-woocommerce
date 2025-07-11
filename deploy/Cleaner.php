<?php

namespace Dropp\Deploy;

use Exception;

class Cleaner
{

	public function removeDesktopServicesStore(): mixed
	{
		return `find . -name ".DS_Store" -type d -delete`;
	}

	/**
	 * @throws Exception
	 */
	public function removeFiles(array $files): void
	{
		foreach ($files as $file) {
			exec("rm -rf $file", $output, $result);
			if ($result === 0) {
				continue;
			}
			throw new Exception("Failed to remove $file");
		}
	}

	/**
	 * @throws Exception
	 */
	public function removeDirs(array $dirs): void
	{
		foreach ($dirs as $dir) {
			exec("rm -rf $dir", $output, $result);
			if ($result === 0) {
				continue;
			}
			throw new Exception("Failed to remove $dir");
		}
	}
}
