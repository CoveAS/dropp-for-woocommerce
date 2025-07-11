<?php

namespace Dropp\Deploy;

use Dropp\Deploy\Console\Color;
use Exception;

class SvnFile
{
	public string $label;

	/**
	 * @throws Exception
	 */
	public function __construct(
		public string $path,
		public string $modifier,
	)
	{
		$map = [
			' ' => 'No changes',
			'A' => 'Added',
			'C' => 'Conflicted',
			'D' => 'Deleted',
			'I' => 'Ignored',
			'M' => 'Modified',
			'R' => 'Replaced',
			'X' => 'Unversioned dir',
			'?' => 'Unstaged',
			'~' => 'Error',
			'!' => 'Missing',
		];
		if (! isset($map[$modifier])) {
			throw new Exception("Unknown SVN modifier '$modifier'");
		}
		$this->label = $map[$this->modifier];
	}

	public function remove(): mixed
	{
		return `svn rm --force $this->path`;
	}

	public function getColorLabel()
	{
		return sprintf(
			'[%s] ',
			match ($this->modifier) {
				'A' => Color::GREEN->wrap($this->label),       // Added
				'C', '?' => Color::YELLOW->wrap($this->label),     // Conflicted
				'D', '~', '!' => Color::RED->wrap($this->label),        // Deleted
				'I' => Color::CYAN->wrap($this->label),       // Ignored
				'M' => Color::BLUE->wrap($this->label),       // Modified
				'R' => Color::MAGENTA->wrap($this->label),    // Replaced
				'X' => Color::WHITE->wrap($this->label),      // Unversioned dir
				' ' => Color::RESET->wrap($this->label),      // No changes
				default => $this->label,                      // Default (no color)
			}
		);
	}

}
