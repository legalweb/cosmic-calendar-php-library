<?php

namespace Legalweb\CosmicCalendarClientExample\Lib\Commands;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Option;
use Legalweb\CosmicCalendarClient\CalendarService;
use Legalweb\CosmicCalendarClient\Traits\Castable;
use Legalweb\CosmicCalendarClientExample\Lib\Traits\Configurable;

class AddEvent extends Command {

    use Castable;
    use Configurable;

    public function __construct()
    {
        $options = [
            Option::create("u", "user", Getopt::REQUIRED_ARGUMENT)->setDescription("Specify user to act on behalf of"),
            Option::create("t", "title", Getopt::REQUIRED_ARGUMENT)->setDescription("Specify title of event"),
            Option::create(null, "start", Getopt::REQUIRED_ARGUMENT)->setDescription("Specify start date time of event"),
            Option::create(null, "end", Getopt::OPTIONAL_ARGUMENT)->setDescription("Specify end date time of event"),
        ];
        parent::__construct('addevent', [$this, 'handle'], $options);
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

            $summary = $opt->getOption("title");
            $start = new \DateTime($opt->getOption("start"));
            $end = null;

            var_dump($summary);
            var_dump($start);
            var_dump($end);

            if ($end = $opt->getOption("end")) {
                $end = new \DateTime($opt->getOption("end"));
            }

            $r = $cs->AddEvent($summary, $start, $end);

            if ($r) {
                var_dump($r);
            } else {
                echo "\nNo event created.\n";
            }
        } catch (\Exception $exception) {
            trigger_error("Unexpected error occurred: " . $exception->getMessage());
        }
    }
}