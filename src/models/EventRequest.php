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
     * EventRequest constructor.
     *
     * @param string         $summary
     * @param \DateTime      $start
     * @param \DateTime|null $end
     */
    public function __construct(string $summary, \DateTime $start, \DateTime $end = null)
    {
        $this->summary = $summary;
        $this->start = $start;
        $this->end = $end;
    }

    public function jsonSerialize()
    {
        return [
            'summary' => $this->summary,
            'start' => $this->start->format("Y-m-d\TH:i:s\Z"),
            'end' => $this->end ?
                $this->end->format("Y-m-d\TH:i:s\Z") :
                (new \DateTime($this->start->format("Y-m-d\TH:i:s\Z")))
                    ->add(new \DateInterval("P1D"))->format("Y-m-d\TH:i:s\Z"),
        ];
    }
}