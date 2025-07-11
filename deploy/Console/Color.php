<?php

namespace Dropp\Deploy\Console;

enum Color
{
    case RESET;
    case BLACK;
    case RED;
    case GREEN;
    case YELLOW;
    case BLUE;
    case MAGENTA;
    case CYAN;
    case WHITE;
    case BOLD;
    case UNDERLINE;
    case REVERSED;

    public function getCode(): string
    {
        return match ($this) {
            self::RESET => "\033[0m",
            self::BLACK => "\033[0;30m",
            self::RED => "\033[0;31m",
            self::GREEN => "\033[0;32m",
            self::YELLOW => "\033[0;33m",
            self::BLUE => "\033[0;34m",
            self::MAGENTA => "\033[0;35m",
            self::CYAN => "\033[0;36m",
            self::WHITE => "\033[0;37m",
            self::BOLD => "\033[1m",
            self::UNDERLINE => "\033[4m",
            self::REVERSED => "\033[7m",
        };
    }

	public function wrap(string $label): string
	{
		return $this->getCode() . $label . self::RESET->getCode();
	}
}
