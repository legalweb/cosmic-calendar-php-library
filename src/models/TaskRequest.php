<?php

namespace Legalweb\CosmicCalendarClient\Models;

class TaskRequest implements \JsonSerializable
{
    /**
     * @var string
     */
    var $title;

    /**
     * @var \DateTime
     */
    var $due;

    /**
     * TaskRequest constructor.
     *
     * @param string    $title
     * @param \DateTime $due
     */
    public function __construct(string $title, \DateTime $due)
    {
        $this->title = $title;
        $this->due = $due;
    }

    public function jsonSerialize()
    {
        return [
            'title' => $this->title,
            'due' => $this->due->format("Y-m-d\TH:i:s\Z"),
        ];
    }
}