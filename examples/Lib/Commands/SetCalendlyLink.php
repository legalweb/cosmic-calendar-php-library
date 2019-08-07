<?php

namespace Legalweb\CosmicCalendarClientExample\Lib\Commands;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Option;
use Legalweb\CosmicCalendarClient\CalendarService;
use Legalweb\CosmicCalendarClient\Traits\Castable;
use Legalweb\CosmicCalendarClientExample\Lib\Traits\Configurable;

class SetCalendlyLink extends Command {

    use Castable;
    use Configurable;

    public function __construct()
    {
        $options = [
            Option::create("u", "user", Getopt::REQUIRED_ARGUMENT)->setDescription("Specify user to act on behalf of"),
            Option::create("l", "url", Getopt::REQUIRED_ARGUMENT)->setDescription("Specify URL to calendly account"),
        ];
        parent::__construct('setcalendlylink', [$this, 'handle'], $options);
    }

    /**
     * @param GetOpt $opt
     */
    public function handle(GetOpt $opt)
    {
        try {
            $c = $this->getCalendarConfig($opt);
        } catch (\Exception $exception) {
            trigger_error("Invalid configuration: " . $exception->getMessage());
            return;
        }

        try {
            $cs = CalendarService::NewCalendarService($c, false, $opt->getOption("user"));

            $r = $cs->SetCalendlyLink($opt->getOption("url"));

            if ($r) {
                var_dump($r);
            } else {
                echo "\nNo link set.\n";
            }
        } catch (\Exception $exception) {
            trigger_error("Unexpected error occurred: " . $exception->getMessage());
        }
    }
}