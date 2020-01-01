<?php
namespace Administrate\JiraToWrikeSync;

class WrikeTaskUpdater extends CLIRoutine
{

    use WrikeInterfaceable;

    private
        $api,
        $jiraIssues,
        $wrikeTasks,
        $map,
        $teamField
    ;

    // Constructor
    public function __construct($apiClient, $jiraIssues, $wrikeTasks, $map, $teamField) {
        parent::__construct();
        $this->api = $apiClient;
        $this->jiraIssues = $jiraIssues;
        $this->wrikeTasks = $wrikeTasks;
        $this->map = $map;
        $this->teamField = $teamField;
    }

    // List changes before committing
    public function list_changes() {

        // Show header
        $this->_header("CHANGES TO BE PUSHED");

        // Loop through the Jira issues
        foreach ($this->jiraIssues as $issue) {

            // Get a reference to the issue data
            if ($issue->has_parent()) {
//                $this->cli->tab()->out('|');
                $this->cli->tab();
            }
            $this->cli->bold()->out($issue->to_str(true));

            // If we can't find the referenced key, skip this one
            if (isset($this->map[$issue->get_id()])) {

                // Get a reference to the Wrike task
                $task = $this->wrikeTasks[$this->map[$issue->get_id()]];

                // Show update for parent
                $parentTask = null;
                if (isset($this->wrikeTasks[$task->get_parent()])) {
                    $parentTask = $this->wrikeTasks[$task->get_parent()]->get_jira_id();
                }
                $this->_show_field_update(
                    'Parent',
                    $this->_get_parent_str($parentTask, $this->jiraIssues),
                    $this->_get_parent_str($issue, $this->jiraIssues),
                    $issue->has_parent()
                );

                // Show update for start date
                $this->_show_field_update(
                    'Start Date',
                    $this->_format_date($task->get_start_time()),
                    $this->_format_date($issue->get_start_time()),
                    $issue->has_parent()
                );

                // Show update for end date
                $this->_show_field_update(
                    'End Date',
                    $this->_format_date($task->get_end_time()),
                    $this->_format_date($issue->get_end_time()),
                    $issue->has_parent()
                );

                // Show update for team
                $this->_show_field_update(
                    'Team',
                    $this->_get_team_str($task->get_team()),
                    $this->_get_team_str($issue->get_team()),
                    $issue->has_parent()
                );

            } else {
                $this->_warning("No Wrike task linked!");
            }

            $this->_br();

        }

        $this->_br();

    }

    // Normalize team string
    private function _get_team_str($team) {
        if (empty($team)) {
            return 'none';
        } else {
            return $team;
        }
    }

    // Show a field's updates
    private function _show_field_update($label, $old, $new, $nested = false) {
        if ($this->_debug() || ($old != $new)) {
            if ($nested) {
                $this->cli->tab();
            }
            $this->cli->inline("{$label}: ");
            if ($old == $new) {
                $this->cli->darkGray()->out($old);
            } else {
                $this->cli->yellow()->inline($old)
                    ->inline(" --> ")
                    ->green()->out($new)
                ;
            }
        }
    }

    // Format a date
    private function _format_date($timestamp) {
        return date(self::DATE_FORMAT, $timestamp);
    }

    // Update the Wrike tasks
    public function update_tasks($autoPush) {

        $input = $this->cli->confirm('Would you like to push the above changes to Wrike?');
        if ($autoPush || $input->confirmed()) {

            // Show header
            $this->_br()->_header("UPDATING WRIKE TASKS");

            // Loop through the Jira issues
            foreach ($this->jiraIssues as $issue) {

                // If we can't find the referenced key, skip this one
                if (isset($this->map[$issue->get_id()])) {

                    // Get a reference to the Wrike task
                    $task = $this->wrikeTasks[$this->map[$issue->get_id()]];

                    $this->_inline("{$issue->to_str()} --> {$task->to_str()}");

                    // Initialize update JSON
                    $json = [];

                    // Add any modified parent
                    if (!in_array($this->map[$issue->get_id()], $task->get_old_parents())) {

                        // Figure out what parent to add
                        if ($issue->has_parent()) {
                            $newParent = $this->map[$issue->get_parent()];
                            $json['addSuperTasks'] = [$newParent];
                        }

                        // Figure out what parents to remove
                        $parentsToRemove = $task->get_old_parents();
                        if ($issue->has_parent()) {
                            $parentsToRemove = array_diff($parentsToRemove, [$newParent]);
                        }
                        if (!empty($parentsToRemove)) {
                            $json['removeSuperTasks'] = $parentsToRemove;
                        }

                    }

                    // Add any modified dates to the update fields
                    if (
                        $issue->has_schedule() &&
                        (
                            $this->_format_date($issue->get_start_time()) != $this->_format_date($task->get_start_time()) ||
                            $this->_format_date($issue->get_end_time()) != $this->_format_date($task->get_end_time())
                        )
                    ) {
                        $task->set_schedule_times($issue->get_start_time(), $issue->get_end_time());
                        $json['dates'] = [
                            'type'      => 'Planned',
                            'start'     => $task->get_start_date(),
                            'duration'  => $task->get_duration(),
                            'due'       => $task->get_end_date(),
                        ];
                    }

                    // Add the modified team to the update fields
                    if ($issue->get_team() != $task->get_team()) {
                        $json['customFields'] = [
                            [
                                'id'    =>  $this->teamField,
                                'value' =>  $issue->get_team(),
                            ],
                        ];
                    }

                    // Update the Wrike task
                    $response = $this->api->put(
                        "tasks/{$task->get_id()}",
                        [
                            'json'      => $json,
                            'exceptions'=> false,
                        ]
                    );
                    if ($response->getStatusCode() == 200) {
                        $updatedTask = $this->_parse_api_response($response)[0];
                        $this->_success();
                    } else {
                        $this->_error("FAILED");
                    }

                    // Show the payload and response
                    if ($this->_debug()) {
                        $this->cli->br()->out('Request:');
                        $this->cli->dump($json);
                        $this->cli->out('Response:');
                        if ($response->getStatusCode() == 200) {
                            $this->cli->dump($updatedTask);
                        } else {
                            $this->cli->dump($response->getBody()->getContents());
                        }
                        $this->_br();
                    }

                } else {
                    $this->_warning("No Wrike task linked!");
                }

            }

        }

    }

}