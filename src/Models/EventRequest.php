<?php

namespace Legalweb\CosmicCalendarClient\Models;

class EventRequest implements \JsonSerializable
{
    /**
     * @var string
     */
    var $summary;

    /**
     * @var \DateTime
     */
    var $start;

    /**
     * @var \DateTime
     */
    var $end;

    /**
     * @var EventReminder[]
     */
    var $reminders;

    /**
     * EventRequest constructor.
     *
     * @param string         $summary
     * @param \DateTime      $start
     * @param \DateTime|null $end
     * @param EventReminder  ...$reminders
     */
    public function __construct(string $summary, \DateTime $start, \DateTime $end = null, EventReminder ...$reminders)
    {
        $this->summary = $summary;
        $this->start = $start;
        $this->end = $end;
        $this->reminders = $reminders;
    }

    public function jsonSerialize()
    {
        return [
            'summary' => $this->summary,
            'start' => $this->start->format("Y-m-d\TH:i:s\Z"),
            'end' => $this->end ?
                $this->end->format("Y-m-d\TH:i:s\Z") :
                (new \DateTime($this->start->format("Y-m-d\TH:i:s\Z")))
                    ->add(new \DateInterval("PT900S"))->format("Y-m-d\TH:i:s\Z"),
            'reminders' => $this->reminders,
        ];
    }
}