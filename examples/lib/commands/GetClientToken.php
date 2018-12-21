<?php

namespace Legalweb\CosmicCalendarClientExample\Lib\Commands;

use GetOpt\Command;
use GetOpt\GetOpt;
use Legalweb\CosmicCalendarClient\CalendarService;
use Legalweb\CosmicCalendarClientExample\Lib\Traits\UsesConfig;

class GetClientToken extends Command {

    use UsesConfig;

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
        } catch (Exception $exception) {
            trigger_error("Invalid configuration: " . $exception->getMessage());
            return;
        }

        try {
            $cs = CalendarService::NewCalendarService($c);
            $r = $cs->GetClientToken();

            echo "\nToken: ", $r->Token, "\nVendor: ", $r->Vendor, "\nExpires: ", $r->Expires, "\n";
        } catch (Exception $exception) {
            trigger_error("Unexpected error occurred: " . $exception->getMessage());
        }
    }
}