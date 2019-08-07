<?php

namespace Legalweb\CosmicCalendarClient;

use Legalweb\CosmicCalendarClient\Exceptions\AccessForbiddenException;
use Legalweb\CosmicCalendarClient\Exceptions\APIRequestException;
use Legalweb\CosmicCalendarClient\Exceptions\APIUnavailableException;
use Legalweb\CosmicCalendarClient\Exceptions\ClientTokenDecodingException;
use Legalweb\CosmicCalendarClient\Exceptions\EventItemsNotFoundException;
use Legalweb\CosmicCalendarClient\Exceptions\EventNotCreatedException;
use Legalweb\CosmicCalendarClient\Exceptions\EventsNotFoundException;
use Legalweb\CosmicCalendarClient\Exceptions\InvalidJSONResponseException;
use Legalweb\CosmicCalendarClient\Exceptions\NotConfiguredException;
use Legalweb\CosmicCalendarClient\Exceptions\TaskItemsNotFoundException;
use Legalweb\CosmicCalendarClient\Exceptions\TaskNotCreatedException;
use Legalweb\CosmicCalendarClient\Exceptions\TasksNotFoundException;
use Legalweb\CosmicCalendarClient\Exceptions\TokenNotFoundException;
use Legalweb\CosmicCalendarClient\Exceptions\URLsNotFoundException;
use Legalweb\CosmicCalendarClient\Exceptions\UserNotConfiguredException;
use Legalweb\CosmicCalendarClient\Models\EventRequest;
use Legalweb\CosmicCalendarClient\Models\SetCalendlyLinkRequest;
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
     * @throws NotConfiguredException
     */
    public static function GetInstance(string $name = '')
    {
        if (strlen($name) > 0) {
            if (isset(self::$instances[$name])) {
                return self::$instances[$name];
            } else {
                throw new NotConfiguredException("Calendar Service " . $name . " not configured");
            }
        }

        if (isset(self::$defaultInstance)) {
            return self::$defaultInstance;
        }

        throw new NotConfiguredException("Calendar Service not configured");

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
     * @throws TokenNotFoundException
     */
    public function GetClientToken() {
        $r = $this->curlRequest("/token/");

        if ($r === null) {
            return null;
        }

        if (!isset($r->Token)) {
            throw new TokenNotFoundException("Token not found in JSON response");
        }

        return ClientToken::FromStdClass($r->Token);
    }

    /**
     * @return |null
     * @throws APIRequestException
     * @throws APIUnavailableException
     * @throws AccessForbiddenException
     * @throws ClientTokenDecodingException
     * @throws InvalidJSONResponseException
     * @throws UserNotConfiguredException
     */
    public function GetCalendlyLink() {
        $this->mustHaveUser();

        $url = "/calendly/link";

        $r = $this->curlRequest($url);

        if ($r === null) {
            return null;
        }

        if (!isset($r->Url)) {
            throw new CalendlyUrlNotSetException("Calendly link not set");
        }

        return $r->Url;
    }

    /**
     * @param string $url
     *
     * @return |null
     * @throws APIRequestException
     * @throws APIUnavailableException
     * @throws AccessForbiddenException
     * @throws ClientTokenDecodingException
     * @throws InvalidJSONResponseException
     * @throws UserNotConfiguredException
     */
    public function SetCalendlyLink(string $url) {
        $this->mustHaveUser();

        $setRequest = new SetCalendlyLinkRequest($url);

        $data = json_encode($setRequest);

        $url = "/calendly/link";

        $r = $this->curlRequest($url, $data);

        if ($r === null) {
            return null;
        }

        if (!isset($r->Url)) {
            throw new CalendlyUrlNotSetException("Calendly link not set");
        }

        return $r->Url;
    }

    /**
     * @param string         $summary
     * @param \DateTime      $start
     * @param \DateTime|null $end
     *
     * @return |null
     * @throws APIRequestException
     * @throws APIUnavailableException
     * @throws AccessForbiddenException
     * @throws ClientTokenDecodingException
     * @throws EventNotCreatedException
     * @throws InvalidJSONResponseException
     * @throws UserNotConfiguredException
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
            throw new EventNotCreatedException("Event not created");
        }

        return $r->Event;
    }

    /**
     * @param string    $title
     * @param \DateTime $due
     *
     * @return |null
     * @throws APIRequestException
     * @throws APIUnavailableException
     * @throws AccessForbiddenException
     * @throws ClientTokenDecodingException
     * @throws InvalidJSONResponseException
     * @throws TaskNotCreatedException
     * @throws UserNotConfiguredException
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
            throw new TaskNotCreatedException("Task not created");
        }

        return $r->Task;
    }

    /**
     * @param int $days
     *
     * @return |null
     * @throws APIRequestException
     * @throws APIUnavailableException
     * @throws AccessForbiddenException
     * @throws ClientTokenDecodingException
     * @throws EventItemsNotFoundException
     * @throws EventsNotFoundException
     * @throws InvalidJSONResponseException
     * @throws UserNotConfiguredException
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
            throw new EventsNotFoundException("Events not found in JSON response");
        }

        if (!isset($r->Events->items)) {
            throw new EventItemsNotFoundException("Events items not found in JSON response");
        }

        return $r->Events->items;
    }

    /**
     * @return |null
     * @throws APIRequestException
     * @throws APIUnavailableException
     * @throws AccessForbiddenException
     * @throws ClientTokenDecodingException
     * @throws InvalidJSONResponseException
     * @throws TaskItemsNotFoundException
     * @throws TasksNotFoundException
     * @throws UserNotConfiguredException
     */
    public function GetTasks() {
        $this->mustHaveUser();

        $r = $this->curlRequest("/calendar/tasks");

        if ($r === null) {
            return null;
        }

        if (!isset($r->Tasks)) {
            throw new TasksNotFoundException("Tasks not found in JSON response");
        }

        if (!isset($r->Tasks->items)) {
            throw new TaskItemsNotFoundException("Tasks items not found in JSON response");
        }

        return $r->Tasks->items;
    }

    /**
     * @return |null
     * @throws APIRequestException
     * @throws APIUnavailableException
     * @throws AccessForbiddenException
     * @throws ClientTokenDecodingException
     * @throws InvalidJSONResponseException
     * @throws URLsNotFoundException
     */
    public function GetOAuthURLs() {
        $r = $this->curlRequest("/login/oauth/urls");

        if ($r === null) {
            return null;
        }

        if (!isset($r->URLS)) {
            throw new URLsNotFoundException("URLs not found in JSON response");
        }

        return $r->URLS;
    }

    /**
     * @return bool
     * @throws UserNotConfiguredException
     */
    protected function mustHaveUser()
    {
        if (strlen($this->user) > 0) {
            return true;
        }

        throw new UserNotConfiguredException("User not configured for request");
    }

    /**
     * @param string $r
     *
     * @return object|null
     * @throws APIRequestException
     * @throws ClientTokenDecodingException
     * @throws InvalidJSONResponseException
     */
    protected function decodeResponse(string $r) {
        if ($r === null) {
            return null;
        }

        $o = json_decode($r);

        if ($o === null) {
            throw new ClientTokenDecodingException("Error decoding JSON response for client token");
        }

        if (!isset($o->ResponseCode) || !isset($o->Response)) {
            throw new InvalidJSONResponseException("Invalid JSON response");
        }

        if ($o->ResponseCode !== 200) {
            throw new APIRequestException("API request failed", $o->ResponseCode);
        }

        return (object) $o->Response;
    }

    /**
     * @param string $url
     * @param string $json
     *
     * @return object|null
     * @throws APIRequestException
     * @throws APIUnavailableException
     * @throws AccessForbiddenException
     * @throws ClientTokenDecodingException
     * @throws InvalidJSONResponseException
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

        if (!$this->config->VerifySSL) {
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch,CURLOPT_SSL_VERIFYSTATUS, false);
        }

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $r = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if (is_bool($r)) {
            if ($r === false) {
                throw new APIRequestException("Error making API request");
            } else {
                throw new APIRequestException("No response data for API request");
            }
        }

        switch ($httpcode) {
            case 503:
                throw new APIUnavailableException("API Service Unavailable");
            case 403:
                throw new AccessForbiddenException("Access Forbidden");
            case 400:
                throw new APIRequestException("Bad API request");
            case 200:
                return $this->decodeResponse((string) $r);
            default:
                throw new APIRequestException("Unhandled API response", $httpcode);
        }
    }
}