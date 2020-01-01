<?php
namespace Administrate\JiraToWrikeSync;

class JiraIssue
{

    use Schedulable;

    const DATE_FORMAT = 'd/m/Y';

    private
        $key,
        $title,
        $type,
        $parentTitle,
        $parentKey,
        $team
    ;

    // Constructor
    public function __construct($fields, $fieldMap) {
        $this->key = $fields[$fieldMap['key']];
        $this->title = $fields[$fieldMap['title']];
        $this->type = $this->_parse_type($fields);
        $this->parentTitle = $fields[$fieldMap['parent']];
        $this->team = $fields[$fieldMap['team']];
        $this->rank = $fields['Rank'];
        $this->set_schedule_dates($fields[$fieldMap['start']], $fields[$fieldMap['end']]);
    }

    // Set the parent key based on passed initiatives map
    public function set_parent($initiativesMap) {
        $this->parentKey = $initiativesMap[$this->parentTitle];
    }

    // Return a string representation of this object
    public function to_str($includeType = false) {
        $str = "[{$this->get_key()}] {$this->get_title()}";
        if ($includeType) {
            $str .= " ({$this->get_type()})";
        }
        return $str;
    }

    // Parse out the issue type
    private function _parse_type($fields) {
        return $fields[array_keys($fields)[0]]; // Something weird going on here -- should be able to be referenced by key name but it's not working ...
//        return $fields[$fieldMaps['type']];
    }

    // Simple status methods
    public function is_initiative() { return ($this->get_type() == 'Initiative'); }
    public function is_epic() { return ($this->get_type() == 'Epic'); }
    public function has_parent() { return !empty($this->parentKey); }
    public function has_team() { return !empty($this->team); }

    // Standard getters
    public function get_key() { return $this->key; }
    public function get_title() { return $this->title; }
    public function get_type() { return $this->type; }
    public function get_parent() { return $this->parentKey; }
    public function get_parent_title() { return $this->parentTitle; }
    public function get_team() { return $this->team; }
    public function get_rank() { return $this->rank; }

}