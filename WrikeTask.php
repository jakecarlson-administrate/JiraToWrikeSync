<?php
namespace Administrate\JiraToWrikeSync;

class WrikeTask
{

    use Schedulable;

    const DATE_FORMAT = "Y-m-d";

    private
        $id,
        $title,
        $jiraKey,
        $parent,
        $oldParents,
        $team
    ;

    // Constructor
    public function __construct($obj, $fields) {
        $this->id = $obj->id;
        $this->title = $obj->title;
        $this->jiraKey = $this->_parse_custom_field($obj, $fields['jira_key']);
        $this->team = $this->_parse_custom_field($obj, $fields['team']);
        if ($obj->dates->type == 'Planned') {
            $this->set_schedule_dates($obj->dates->start, $obj->dates->due);
        }
        $this->oldParents = $obj->superTaskIds;
        if (!empty($obj->oldParents)) {
            $this->parent = $obj->oldParents[0];
        }
    }

    // Parse out the value of a custom field
    private function _parse_custom_field($obj, $fieldID) {
        foreach ($obj->customFields as $field) {
            if ($field->id == $fieldID) {
                return $field->value;
            }
        }
        return null;
    }

    // Return a string representation of this object
    public function to_str() {
        return "[{$this->get_id()}] {$this->get_title()}";
    }

    // Utility functions
    public function has_jira_key() { return !empty($this->jiraKey); }
    public function has_parent() { return !empty($this->parent); }
    public function has_team() { return !empty($this->team); }

    // Standard setters
    public function set_parent($id) { $this->parent = $id; }
    public function set_team($team) { $this->team = $team; }

    // Standard getters
    public function get_id() { return $this->id; }
    public function get_title() { return $this->title; }
    public function get_jira_key() { return $this->jiraKey; }
    public function get_parent() { return $this->parent; }
    public function get_old_parents() { return $this->oldParents; }
    public function get_team() { return $this->team; }

}