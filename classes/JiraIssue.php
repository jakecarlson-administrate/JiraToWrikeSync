<?php
namespace Administrate\JiraToWrikeSync;

class JiraIssue extends WorkItem
{

    const DATE_FORMAT = 'd/m/Y';

    private $parentTitle;

    // Constructor
    public function __construct($fields, $fieldMap) {
        $this->parentTitle = $fields[$fieldMap['parent']];
        parent::__construct(
            $fields[$fieldMap['key']],
            $fields[$fieldMap['title']],
            $fields[$fieldMap['team']],
            null,
            $fields[$fieldMap['start']],
            $fields[$fieldMap['end']],
            $this->_parse_type($fields),
            $fields['Rank']
        );
    }

    // Set the parent key based on passed initiatives map
    public function set_parent($initiativesMap) {
        $this->parent = $initiativesMap[$this->parentTitle];
    }

    // Parse out the issue type
    private function _parse_type($fields) {
        return $fields[array_keys($fields)[0]]; // Something weird going on here -- should be able to be referenced by key name but it's not working ...
//        return $fields[$fieldMaps['type']];
    }

    // Simple status methods
    public function is_initiative() { return ($this->get_type() == 'Initiative'); }
    public function is_epic() { return ($this->get_type() == 'Epic'); }

    // Standard getters
    public function get_id() { return $this->id; }
    public function get_parent_title() { return $this->parentTitle; }

}