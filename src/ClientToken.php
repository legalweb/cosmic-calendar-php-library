<?php

namespace Legalweb\CosmicCalendarClient;

use Legalweb\CosmicCalendarClient\Traits\Castable;

class ClientToken
{
    use Castable;

    var $Expires;
    var $Token;
    var $Vendor;

    /**
     * @param \stdClass $s
     *
     * @return ClientToken
     */
    public static function FromStdClass(\stdClass $s)
    {
        $d = new ClientToken();

        $d = self::Cast($s, $d);

        return $d;
    }
}