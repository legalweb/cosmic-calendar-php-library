<?php

namespace Legalweb\CosmicCalendarClientExample\Lib\Commands;

use GetOpt\Command;
use GetOpt\GetOpt;
use Legalweb\CosmicCalendarClient\CalendarService;
use Legalweb\CosmicCalendarClient\Traits\Castable;
use Legalweb\CosmicCalendarClientExample\Lib\Traits\Configurable;

class GetClientToken extends Command {

    use Castable;
    use Configurable;

    public function __construct()
    {
        parent::__construct('getclienttoken', [$this, 'handle']);
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
            $cs = CalendarService::NewCalendarService($c);
            $r = $cs->GetClientToken();

            if ($r) {
                echo "\nToken: ", $r->Token, "\nVendor: ", $r->Vendor, "\nExpires: ", $r->Expires, "\n";
            } else {
                echo "\nNo token retrieved.\n";
            }
        } catch (\Exception $exception) {
            trigger_error("Unexpected error occurred: " . $exception->getMessage());
        }
    }
}