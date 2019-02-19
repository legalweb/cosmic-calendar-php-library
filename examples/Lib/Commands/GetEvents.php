<?php

namespace Legalweb\CosmicCalendarClientExample\Lib\Commands;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Option;
use Legalweb\CosmicCalendarClient\CalendarService;
use Legalweb\CosmicCalendarClient\Traits\Castable;
use Legalweb\CosmicCalendarClientExample\Lib\Traits\Configurable;

class GetEvents extends Command {

    use Castable;
    use Configurable;

    public function __construct()
    {
        $options = [
            Option::create("u", "user", Getopt::REQUIRED_ARGUMENT)->setDescription("Specify user to act on behalf of"),
            Option::create("d", "days", Getopt::OPTIONAL_ARGUMENT)->setDescription("Specify number of days ahead to obtain events for"),
        ];
        parent::__construct('getevents', [$this, 'handle'], $options);
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

            if ($days = $opt->getOption("days")) {
                $r = $cs->GetEvents($opt->getOption("days"));
            } else {
                $r = $cs->GetEvents();
            }

            if ($r) {
                var_dump($r);
            } else {
                echo "\nNo events retrieved.\n";
            }
        } catch (\Exception $exception) {
            trigger_error("Unexpected error occurred: " . $exception->getMessage());
        }
    }
}