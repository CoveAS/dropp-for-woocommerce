<?php

namespace Dropp\Deploy;

use Exception;

class Svn
{
	public function __construct(
		public readonly string $url,
		public readonly string $dir
	)
	{
	}

	/**
	 * @throws Exception
	 */
	public function list()
	{
		exec("svn list {$this->url}/tags", $output, $result);
		if ($result) {
			throw new Exception("Could not get tags from svn repo");
		}
		return array_map('trim', $output);
	}

	/**
	 * @return SvnFile[]
	 * @throws Exception
	 */
	public function status(): array
	{
		$lines = $this->runCommand('svn st');
		$newLines = [];
		foreach ($lines as $line) {
			$line = trim($line);
			if (!$line) {
				continue;
			}
			if (!preg_match('/^([ACDIMRX\?~\!])\s+(\S.*)/', $line, $parts)) {
				throw new Exception("Unidentified SVN modifier, \"$line\"");
			}
			$modifier    = $parts[1];
			$file        = $parts[2];
			$newLines [] = new SvnFile($file, $modifier);
		}
		return $newLines;
	}

	/**
	 * @throws Exception
	 */
	public function checkout(): array
	{
		return $this->runCommand("svn co --depth immediates $this->url .");
	}

	/**
	 * @throws Exception
	 */
	public function update($args = ''): array
	{
		return $this->runCommand(trim('svn update ' . $args));
	}

	/**
	 * @throws Exception
	 */
	public function commit(string $username, string $message): array
	{
		return $this->runCommand(
			sprintf(
				'svn commit --username %s -m "%s"',
				$username,
				$message
			)
		);
	}

	/**
	 * @throws Exception
	 */
	public function cp(string $from, string $to): array
	{
		return $this->runCommand(
			sprintf('svn cp %s %s', $from, $to),
		);
	}
	/**
	 * @throws Exception
	 */
	public function add(string $args): array
	{
		return $this->runCommand( 'svn --force --depth infinity add '. $args);
	}

	/**
	 * @throws Exception
	 */
	private function runCommand(string $command): array
	{
		exec(trim($command), $output, $result);

		if ($result !== 0) {
			throw new Exception("Command failed: $command");
		}

		return $output;
	}
}
