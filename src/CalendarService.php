<?php
/**
 * Created by PhpStorm.
 * User: aaron
 * Date: 19/12/2018
 * Time: 13:11
 */

namespace Legalweb\CosmicCalendarClient;

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

    /**
     * @param Config $config
     * @param bool   $isDefault
     *
     * @return CalendarService
     */
    public static function NewCalendarService(Config $config, bool $isDefault = false)
    {
        $cs = new CalendarService();
        $cs->SetConfig($config);

        if (is_null(self::$defaultInstance) || $isDefault) {
            self::$defaultInstance = $cs;
        }

        if (strlen($config->Name) > 0) {
            self::$instances[$config->Name] = $cs;
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

    protected function SetConfig(Config $config)
    {
        $this->config = $config;
    }

    public function GetClientToken() {
        $r = $this->curlRequest("/token/");

        if ($r === null) {
            return null;
        }

        if (!isset($r->Token)) {
            trigger_error("Token not found in JSON response");
            return null;
        }

        return $r->Token;
    }

    public function AddEvent() {

    }

    public function AddTask() {

    }

    public function GetEvents() {

    }

    public function GetTasks() {

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
    protected function curlRequest(string $url, string $data = "") {
        $ch = curl_init($this->config->EndPoint . $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config->Client . ":" . $this->config->Secret);

        if (strlen($data) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data)
            ]);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $r = curl_exec($ch);

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

        return $this->decodeResponse((string) $r);
    }
}