<?php
/**
 * Created by PhpStorm.
 * User: aaron
 * Date: 19/12/2018
 * Time: 13:11
 */

namespace CosmicCalendar;

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
        $ch = curl_init();
        
    }

    public function AddEvent() {

    }

    public function AddTask() {

    }

    public function GetEvents() {

    }

    public function GetTasks() {

    }
}