<?php

namespace Dropp\Deploy;

use Exception;

class Git
{
    public function __construct(public string $dir)
    {
        // Ensure the directory exists
        if (!is_dir($this->dir)) {
            throw new Exception("Directory {$this->dir} does not exist");
        }
    }

    /**
     * Executes a Git command in the specified directory.
     *
     * @param string $command The Git command to execute (e.g., 'git status -s').
     * @return array The output of the command.
     * @throws Exception If the command fails.
     */
    private function runCommand(string $command): array
    {
        // Prepend `cd` to ensure the command runs in the correct directory
        $fullCommand = "cd " . escapeshellarg($this->dir) . " && " . $command;

        exec($fullCommand, $output, $result);

        if ($result !== 0) {
            throw new Exception("Command failed: $command");
        }

        return $output;
    }

    /**
     * Gets the current Git tag.
     *
     * @return string The current Git tag.
     * @throws Exception If no tag is found or the command fails.
     */
    public function getTag(): string
    {
        $output = $this->runCommand('git tag --contains');

        if (empty($output)) {
            throw new Exception("No version tag found for the current git commit");
        }

        return trim($output[0]);
    }

    /**
     * Gets the Git status.
     *
     * @return array The Git status output.
     * @throws Exception If the command fails.
     */
    public function status(): array
    {
        return $this->runCommand('git status -s');
    }
}
