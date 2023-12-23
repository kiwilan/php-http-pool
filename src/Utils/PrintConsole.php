<?php

namespace Kiwilan\HttpPool\Utils;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

class PrintConsole
{
    protected function __construct(
        protected ConsoleOutput $output,
        protected bool $print = true,
    ) {
    }

    public static function make(bool $print = true): self
    {
        $service = new self(
            output: new ConsoleOutput(),
            print: $print,
        );

        return $service;
    }

    /**
     * @param  string  $color Color can be black, red, green, yellow, blue, magenta, cyan, white, default, gray, bright-red, bright-green, bright-yellow, bright-blue, bright-magenta, bright-cyan, bright-white
     */
    public function print(string $message, string $color = 'default', Throwable $th = null): void
    {
        if (! $this->print) {
            return;
        }

        syslog(LOG_INFO, $message);

        $style = new OutputFormatterStyle($color, '', []);
        $this->output->getFormatter()
            ->setStyle('info', $style);

        if ($th) {
            $this->output->writeln("<info>Error about {$message}</info>\n");
            $this->output->writeln($th->getMessage());
        } else {
            $this->output->writeln("<info>{$message}</info>");
        }
    }

    public function newLine(): void
    {
        if (! $this->print) {
            return;
        }

        $style = new OutputFormatterStyle('red', '', ['bold']);
        $this->output->getFormatter()
            ->setStyle('info', $style);
        $this->output->writeln('');
    }
}
