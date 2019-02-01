<?php

namespace Legalweb\CosmicCalendarClientExample\Lib\Commands;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Option;
use Legalweb\CosmicCalendarClient\CalendarService;
use Legalweb\CosmicCalendarClient\Traits\Castable;
use Legalweb\CosmicCalendarClientExample\Lib\Traits\Configurable;

class GetOAuthURLs extends Command {

    use Castable;
    use Configurable;

    public function __construct()
    {
        parent::__construct('getoauthurls', [$this, 'handle']);
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
            $cs = CalendarService::NewCalendarService($c, false);
            $r = $cs->GetOAuthURLs();

            if ($r) {
                echo "\nURLs";
                array_walk($r, function($v, $k) {
                    echo "\n", strtoupper($k), ": ", $v;
                });
                echo "\n";
            } else {
                echo "\nNo URLs retrieved.\n";
            }
        } catch (\Exception $exception) {
            trigger_error("Unexpected error occurred: " . $exception->getMessage());
        }
    }
}