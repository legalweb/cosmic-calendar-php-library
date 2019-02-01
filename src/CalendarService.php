<?php

namespace Legalweb\CosmicCalendarClient;

use Legalweb\CosmicCalendarClient\Models\EventRequest;
use Legalweb\CosmicCalendarClient\Models\TaskRequest;

/**
 * Class CalendarService
 * @package Legalweb\CosmicCalendarClient
 */
class CalendarService
{
    /**
     * @var CalendarService
     */
    protected static $defaultInstance;

    /**
     * @var CalendarService[]
     */
    protected static $instances = [];

    /** @var Config */
    protected $config;

    /** @var string */
    protected $user = "";

    /**
     * @param Config $config
     * @param bool   $isDefault
     * @param string $user
     *
     * @return CalendarService
     */
    public static function NewCalendarService(Config $config, bool $isDefault = false, $user = "")
    {
        $cs = new CalendarService();
        $cs->SetConfig($config);

        if (is_null(self::$defaultInstance) || $isDefault) {
            self::$defaultInstance = $cs;
        }

        if (strlen($config->Name) > 0) {
            self::$instances[$config->Name] = $cs;
        }

        if (strlen($user) > 0) {
            $cs->SetUser($user);
        }

        return $cs;
    }

    /**
     * @param string $name
     *
     * @return CalendarService|null
     */
    public static function GetInstance(string $name = '')
    {
        if (strlen($name) > 0) {
            if (isset(self::$instances[$name])) {
                return self::$instances[$name];
            } else {
                trigger_error("Calendar Service " . $name . " not configured");
                return null;
            }
        }

        if (isset(self::$defaultInstance)) {
            return self::$defaultInstance;
        }

        trigger_error("Calendar Service not configured");

        return null;
    }

    /**
     * @param Config $config
     */
    protected function SetConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $user
     */
    public function SetUser(string $user)
    {
        $this->user = $user;
    }

    /**
     * @return ClientToken|null
     */
    public function GetClientToken() {
        $r = $this->curlRequest("/token/");

        if ($r === null) {
            return null;
        }

        if (!isset($r->Token)) {
            trigger_error("Token not found in JSON response");
            return null;
        }

        return ClientToken::FromStdClass($r->Token);
    }

    /**
     * @param string         $summary
     * @param \DateTime      $start
     * @param \DateTime|null $end
     *
     * @return |null
     * @throws \Exception
     */
    public function AddEvent(string $summary, \DateTime $start, \DateTime $end = null) {
        $this->mustHaveUser();

        $eventRequest = new EventRequest($summary, $start, $end);

        $data = json_encode($eventRequest);

        $url = "/calendar/events";

        $r = $this->curlRequest($url, $data);

        if ($r === null) {
            return null;
        }

        if (!isset($r->Event)) {
            trigger_error("Event not created");
            return null;
        }

        return $r->Event;
    }

    /**
     * @param string    $title
     * @param \DateTime $due
     *
     * @return |null
     * @throws \Exception
     */
    public function AddTask(string $title, \DateTime $due) {
        $this->mustHaveUser();

        $eventRequest = new TaskRequest($title, $due);

        $data = json_encode($eventRequest);

        $url = "/calendar/tasks";

        $r = $this->curlRequest($url, $data);

        if ($r === null) {
            return null;
        }

        if (!isset($r->Task)) {
            trigger_error("Task not created");
            return null;
        }

        return $r->Task;
    }

    /**
     * @param int $days
     *
     * @return |null
     * @throws \Exception
     */
    public function GetEvents(int $days = 0) {
        $this->mustHaveUser();

        $url = "/calendar/events";

        if ($days > 0) {
            $url .= "?days=" . $days;
        }

        $r = $this->curlRequest($url);

        if ($r === null) {
            return null;
        }

        if (!isset($r->Events)) {
            trigger_error("Events not found in JSON response");
            return null;
        }

        if (!isset($r->Events->items)) {
            trigger_error("Events items not found in JSON response");
            return null;
        }

        return $r->Events->items;
    }

    /**
     * @return |null
     * @throws \Exception
     */
    public function GetTasks() {
        $this->mustHaveUser();

        $r = $this->curlRequest("/calendar/tasks");

        if ($r === null) {
            return null;
        }

        if (!isset($r->Tasks)) {
            trigger_error("Tasks not found in JSON response");
            return null;
        }

        if (!isset($r->Tasks->items)) {
            trigger_error("Tasks items not found in JSON response");
            return null;
        }

        return $r->Tasks->items;
    }

    /**
     * @return |null
     */
    public function GetOAuthURLs() {
        $r = $this->curlRequest("/login/oauth/urls");

        if ($r === null) {
            return null;
        }

        if (!isset($r->URLS)) {
            trigger_error("URLs not found in JSON response");
            return null;
        }

        return $r->URLS;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function mustHaveUser()
    {
        if (strlen($this->user) > 0) {
            return true;
        }

        throw new \Exception("User not configured for request");
    }

    /**
     * @param string $r
     *
     * @return object|null
     */
    protected function decodeResponse(string $r) {
        if ($r === null) {
            return null;
        }

        $o = json_decode($r);

        if ($o === null) {
            trigger_error("Error decoding JSON response for client token");
            return null;
        }

        if (!isset($o->ResponseCode) || !isset($o->Response)) {
            trigger_error("Invalid JSON response");
            return null;
        }

        if ($o->ResponseCode !== 200) {
            trigger_error("API request failed");
            return $o->Response;
        }

        return (object) $o->Response;
    }

    /**
     * @param string $url
     * @param string $data
     *
     * @return object|null
     */
    protected function curlRequest(string $url, string $json = "") {
        $ch = curl_init($this->config->EndPoint . $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config->Client . ":" . $this->config->Secret);

        $headers = [];

        if (strlen($this->user)) {
            $headers[] = "X-Auth-User: " . $this->user;
        }

        if (strlen($json) > 0) {
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($json);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        } else if (count($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $r = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if (is_bool($r)) {
            if ($r === false) {
                trigger_error("Error making API request");
                return null;
            } else {
                trigger_error("No response data for API request");
                return null;
            }
        }

        switch ($httpcode) {
            case 503:
                trigger_error("API Service Unavailable");
                return null;
            case 403:
                trigger_error("Access Forbidden");
                return null;
            case 400:
                trigger_error("Bad API request");
                return null;
            case 200:
                return $this->decodeResponse((string) $r);
            default:
                trigger_error("Unhandled API response: " . $httpcode);
                return null;
        }
    }
}