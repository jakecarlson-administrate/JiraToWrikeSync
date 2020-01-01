<?php
namespace Administrate\JiraToWrikeSync;

class WrikeTask extends WorkItem
{

    const DATE_FORMAT = "Y-m-d";

    private
        $jiraId,
        $oldParents
    ;

    // Constructor
    public function __construct($obj, $fields) {
        $this->oldParents = $obj->superTaskIds;
        $this->jiraId = $this->_parse_custom_field($obj, $fields['jira_key']);
        parent::__construct(
            $obj->id,
            $obj->title,
            $this->_parse_custom_field($obj, $fields['team']),
            !empty($this->oldParents) ? $this->oldParents[0] : null,
            (property_exists($obj->dates, 'start')) ? $obj->dates->start : null,
            (property_exists($obj->dates, 'due')) ? $obj->dates->due : null
        );
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

    // Utility functions
    public function has_jira_key() { return !empty($this->jiraId); }

    // Standard getters
    public function get_jira_id() { return $this->jiraId; }
    public function get_old_parents() { return $this->oldParents; }

}