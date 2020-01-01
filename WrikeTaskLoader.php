<?php
namespace Administrate\JiraToWrikeSync;

class WrikeTaskLoader extends CLIRoutine
{

    use WrikeInterfaceable;
    
    private
        $api,
        $tasks,
        $orphanedTasks
    ;

    // Constructor
    public function __construct($apiClient, $parentFolderId, $jiraKeyField, $teamField) {
        parent::__construct();
        if ($this->_debug()) $this->_header("LOAD WRIKE TASKS FROM API");
        $this->api = $apiClient;
        $this->_load_tasks($parentFolderId, $jiraKeyField, $teamField);
        $this->_map_jira_to_wrike();
        if ($this->_debug()) $this->cli->br()->br();
    }

    // List orphaned tasks
    public function list_orphaned_tasks() {
        if (count($this->orphanedTasks) > 0) {
            $this->_header("WRIKE TASKS NOT IN JIRA");
            foreach ($this->orphanedTasks as $taskId) {
                $task = $this->get_task($taskId);
                $this->cli->bold()->red()->inline($task->to_str())
                    ->darkGray()->out(" (Parent: {$this->_get_parent_str($task, $this->tasks)})")
                ;
            }
            $this->cli->br()->br();
        }
    }

    // Load Wrike tasks from the API
    private function _load_tasks($parentFolderId, $jiraKeyField, $teamField) {
        if ($this->_debug()) $this->_inline("Getting Wrike tasks from the API");
        $response = $this->api->get(
            "folders/{$parentFolderId}/tasks",
            [
                'query' =>  [
                    'descendants'   =>  true,
                    'subTasks'      =>  true,
                    'fields'        =>  json_encode([
                        'metadata',
                        'customFields',
                        'superTaskIds',
                        'dependencyIds'
                    ]),
                ]
            ]
        );
        if ($response->getStatusCode() == 200) {
            foreach ($this->_parse_api_response($response) as $task) {
                $this->tasks[$task->id] = new WrikeTask(
                    $task,
                    [
                        'jira_key'  =>  $jiraKeyField,
                        'team'      =>  $teamField,
                    ]
                );
            }
            if ($this->_debug()) $this->_success();
        } else {
            $this->_error("API call failed: {$response->getStatusCode()}");
        }
    }

    // Create a mapping from Jira key to Wrike ID
    private function _map_jira_to_wrike() {
        if ($this->_debug()) $this->_inline("Mapping Jira issues to Wrike tasks");
        foreach ($this->tasks as $task) {
            if (!empty($task->get_jira_key())) {
                $this->jiraWrikeMap[$task->get_jira_key()] = $task->get_id();
            } else {
                $this->orphanedTasks[] = $task->get_id();
            }
        }
        if ($this->_debug()) $this->_success();
    }

    // Simple getters
    public function get_tasks() { return $this->tasks; }
    public function get_task($id) { return $this->tasks[$id]; }
    public function get_orphaned_tasks() { return $this->orphanedTasks; }
    public function get_jira_map() { return $this->jiraWrikeMap; }
    public function get_task_by_jira_key($key) { return $this->jiraWrikeMap[$key]; }
    public function get_jira_issue_by_wrike_id($id) { return $this->jiraWrikeMap[array_search($id, $this->jiraWrikeMap)]; }

}