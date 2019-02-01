<?php

namespace Legalweb\CosmicCalendarClientExample\Lib\Commands;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Option;
use Legalweb\CosmicCalendarClient\CalendarService;
use Legalweb\CosmicCalendarClient\Traits\Castable;
use Legalweb\CosmicCalendarClientExample\Lib\Traits\Configurable;

class AddTask extends Command {

    use Castable;
    use Configurable;

    public function __construct()
    {
        $options = [
            Option::create("u", "user", Getopt::REQUIRED_ARGUMENT)->setDescription("Specify user to act on behalf of"),
            Option::create("t", "title", Getopt::REQUIRED_ARGUMENT)->setDescription("Specify title of task"),
            Option::create("d", "due", Getopt::REQUIRED_ARGUMENT)->setDescription("Specify due date time of task"),
        ];
        parent::__construct('addtask', [$this, 'handle'], $options);
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

            $title = $opt->getOption("title");
            $due = new \DateTime($opt->getOption("due"));

            $r = $cs->AddTask($title, $due);

            if ($r) {
                var_dump($r);
            } else {
                echo "\nNo task created.\n";
            }
        } catch (\Exception $exception) {
            trigger_error("Unexpected error occurred: " . $exception->getMessage());
        }
    }
}