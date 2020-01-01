<?php
namespace Administrate\JiraToWrikeSync;

class JiraIssueLoader extends CLIRoutine
{
    
    private $issues;
    private $initiatives = [];

    // Constructor
    public function __construct($file, $fields) {
        parent::__construct();
        if ($this->_debug()) $this->_header("LOAD JIRA ISSUES FROM SCHEDULE CSV");
        $this->_load_issues($file, $fields);
        $this->_map_initiative_titles();
        $this->_set_epic_parents();
        if ($this->_debug()) $this->cli->br()->br();
    }

    // List orphaned issues
    public function list_orphaned_issues($wrikeTaskJiraKeys) {
        $orphanKeys = array_diff(array_keys($this->issues), $wrikeTaskJiraKeys);
        if (count($orphanKeys) > 0) {
            $this->_header("JIRA ISSUES NOT IN WRIKE");
            foreach ($orphanKeys as $key) {
                $issue = $this->get_issue($key);
                $this->cli->bold()->red()->inline($issue->to_str())
                    ->darkGray()->out("(Parent: {$this->_get_parent_str($issue, $this->issues)})")
                ;
            }
            $this->cli->br()->br();
        }
    }

    // Parse the Jira schedule file
    private function _load_issues($file, $fields) {
        if ($this->_debug()) $this->_inline("Parsing schedule file");
        $rank = 1;
        if (($h = fopen($file, "r")) !== FALSE) {
            $headers = false;
            while (($row = fgetcsv($h, 1000, ",")) !== FALSE) {
                if (!$headers) {
                    $headers = str_replace('"', '', $row);
                } else {
                    $values = array_combine($headers, $row);
                    $values['Rank'] = $rank;
                    $this->issues[$values[$fields['key']]] = new JiraIssue($values, $fields);
                }
                ++$rank;
            }
            fclose($h);
            if ($this->_debug()) $this->_success();
        } else {
            $this->_error("FAILED TO OPEN SCHEDULE FILE: {$file}", true);
        }
    }

    // Create a mapping from Initiative name to Initiative issue key
    private function _map_initiative_titles() {
        if ($this->_debug()) $this->_inline("Mapping initiative names");
        foreach ($this->issues as $issue) {
            if ($issue->is_initiative()) {
                $this->initiatives[$issue->get_title()] = $issue->get_key();
            }
        }
        if ($this->_debug()) $this->_success();
    }

    // Loop through issues and set parent issue where appropriate
    private function _set_epic_parents() {
        if ($this->_debug()) $this->_inline("Setting issue parents");
        foreach ($this->issues as $key=>$issue) {
            if ($issue->is_epic()) {
                $this->issues[$key]->set_parent($this->initiatives);
            }
        }
        if ($this->_debug()) $this->_success();
    }
    
    // Simple getters
    public function get_issues() { return $this->issues; }
    public function get_issue($key) { return $this->issues[$key]; }

}