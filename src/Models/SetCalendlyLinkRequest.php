<?php

namespace Legalweb\CosmicCalendarClient\Models;

class SetCalendlyLinkRequest implements \JsonSerializable
{
    /**
     * @var string
     */
    var $url;

    /**
     * EventRequest constructor.
     *
     * @param string         $summary
     * @param \DateTime      $start
     * @param \DateTime|null $end
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function jsonSerialize()
    {
        return [
            'url' => $this->url,
        ];
    }
}