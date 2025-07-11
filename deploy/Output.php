<?php

namespace Dropp\Deploy;

use Dropp\Deploy\Console\Color;

class Output
{
	public function fatal(string $string): void
	{
        $this->timestamp();
		echo Color::RED->wrap("Error: $string");
		if (! str_ends_with($string, "\n")) {
			echo "\n";
		}
		die;
	}

	public function info(string $string): void
    {
        $this->timestamp();
		echo $string;
		if (! str_ends_with($string, "\n")) {
			echo "\n";
		}
	}

    private function timestamp(): void
    {
        echo sprintf('[%s] ', date('H:i:s'));
    }
}
