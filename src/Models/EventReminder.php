<?php


namespace Legalweb\CosmicCalendarClient\Models;


class EventReminder
{
    /**
     * @var string
     */
    var $method;

    /**
     * @var int
     */
    var $minutes;

    /**
     * EventReminder constructor.
     *
     * @param string $method
     * @param int    $minutes
     */
    public function __construct(string $method, int $minutes)
    {
        $this->method = $method;
        $this->minutes = $minutes;
    }
}