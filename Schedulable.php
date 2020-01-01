<?php
namespace Administrate\JiraToWrikeSync;

trait Schedulable
{

    private $timeFormat = ' H:i:s';
    private $timeSuffix = ' 12:00:00';

    // Basic instance properties
    private
        $startTime,
        $endTime
    ;

    // Set schedule dates
    public function set_schedule_dates($startDate, $endDate) {
        $this->startTime = $this->_parse_time($startDate);
        $this->endTime = $this->_parse_time($endDate);
    }

    // Set schedule times
    public function set_schedule_times($startTime, $endTime) {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    // Parse a date into a timestamp
    private function _parse_time($date, $format = false) {
        if (!$format) {
            $format = static::DATE_FORMAT;
        }
        $date = str_replace('T', ' ', $date);
        if ($spacePos = strpos($date, ' ')) {
            $date = substr($date, 0, $spacePos);
        }
        return date_timestamp_get(date_create_from_format($format . $this->timeFormat, $date . $this->timeSuffix));
    }

    // Format a date
    private function _format_date($timestamp, $format = false) {
        if (!$format) {
            $format = static::DATE_FORMAT;
        }
        return date($format, $timestamp);
    }

    // Format a datetime
    private function _format_datetime($timestamp, $format = false) {
        if (!$format) {
            $format = static::DATE_FORMAT;
        }
        return date($format, $timestamp) . $this->timeSuffix;
    }

    // Whether the item has a schedule
    public function has_schedule() {
        return (!empty($this->startTime) && !empty($this->endTime));
    }

    // Standard getters
    public function get_start_time() { return $this->startTime; }
    public function get_end_time() { return $this->endTime; }
    public function get_start_date($format = false) { return $this->_format_date($this->startTime, $format); }
    public function get_end_date($format = false) { return $this->_format_date($this->endTime, $format); }
    public function get_start_datetime($format = false) { return $this->_format_datetime($this->startTime, $format); }
    public function get_end_datetime($format = false) { return $this->_format_datetime($this->endTime, $format); }
    public function get_duration() { return (($this->endTime - $this->startTime) / 60); }

}