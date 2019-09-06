<?php

namespace Legalweb\CosmicCalendarClientExample\Lib\Commands;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Option;
use Legalweb\CosmicCalendarClient\CalendarService;
use Legalweb\CosmicCalendarClient\Models\EventReminder;
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
            Option::create("d", "details", Getopt::REQUIRED_ARGUMENT)->setDescription("Specify details of event"),
            Option::create(null, "start", Getopt::REQUIRED_ARGUMENT)->setDescription("Specify start date time of event"),
            Option::create(null, "end", Getopt::OPTIONAL_ARGUMENT)->setDescription("Specify end date time of event"),
            Option::create(null, 'email-reminder', Getopt::OPTIONAL_ARGUMENT)->setDescription("Minutes before event to send email reminder"),
            Option::create(null, 'popup-reminder', Getopt::OPTIONAL_ARGUMENT)->setDescription("Minutes before event to popup reminder"),
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
            $description = $opt->getOption("details");
            $start = new \DateTime($opt->getOption("start"));
            $end = null;

            if ($end = $opt->getOption("end")) {
                $end = new \DateTime($opt->getOption("end"));
            }

            $reminders = [];

            if ($emailReminder = $opt->getOption("email-reminder")) {
                $emailReminder = new EventReminder("email", $emailReminder);
                $reminders[] = $emailReminder;
            }

            if ($popupReminder = $opt->getOption("popup-reminder")) {
                $popupReminder = new EventReminder("popup", $popupReminder);
                $reminders[] = $popupReminder;
            }

            $r = $cs->AddEvent($summary, $description, $start, $end, ...$reminders);

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