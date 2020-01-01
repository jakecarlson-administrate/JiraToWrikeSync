<?php
namespace Administrate\JiraToWrikeSync;

class JiraToWrikeSync extends CLIRoutine
{

    const WRIKE_API_BASE = "https://www.wrike.com/api/v4/";

    private
        $api,
        $jiraScheduleFile,
        $jiraFields,
        $wrikeSettings,
        $jira,
        $wrike
    ;

    // Constructor
    public function __construct($jiraScheduleFile, $jiraFields, $wrikeSettings) {

        // Initialize output formatter
        parent::__construct();
        $this->_init_cli();

        // Set passed args
        $this->jiraScheduleFile = $jiraScheduleFile;
        $this->jiraFields = $jiraFields;
        $this->wrikeSettings = $wrikeSettings;

    }

    // Run
    public function run() {

        // Load Jira issues
        $this->jira = new JiraIssueLoader(
            $this->_get_schedule_file($this->jiraScheduleFile),
            $this->jiraFields
        );

        // Load Wrike tasks
        $this->_init_api_client($this->_get_api_access_token($this->wrikeSettings['access_token']));
        $this->wrike = new WrikeTaskLoader(
            $this->api,
            $this->_get_wrike_parent_folder($this->wrikeSettings['parent_folder']),
            $this->wrikeSettings['jira_key_field'],
            $this->wrikeSettings['team_field']
        );

        // List orphaned items on both sides
        $this->jira->list_orphaned_issues(array_keys($this->wrike->get_jira_map()));
        $this->wrike->list_orphaned_tasks();

        // Run the Wrike task updaters
        $updater = new WrikeTaskUpdater(
            $this->api, $this->jira->get_issues(),
            $this->wrike->get_tasks(),
            $this->wrike->get_jira_map(),
            $this->wrikeSettings['team_field']
        );
        $updater->list_changes();
        $updater->update_tasks($this->cli->arguments->get('push'));

        // We're done!
        $this->cli->br()->br()->green()->flank('COMPLETE', '!', 4)->br()->br();

    }

    // Initialize the output formatter
    private function _init_cli() {

        $this->cli->arguments->add([
            'csv' => [
                'prefix'      => 'c',
                'longPrefix'  => 'csv',
                'description' => 'Jira schedule CSV',
            ],
            'token' => [
                'prefix'      => 't',
                'longPrefix'  => 'token',
                'description' => 'Wrike API access token',
            ],
            'folder' => [
                'prefix'      => 'f',
                'longPrefix'  => 'folder',
                'description' => 'Wrike parent folder',
            ],
            'verbose' => [
                'prefix'      => 'v',
                'longPrefix'  => 'verbose',
                'description' => 'Verbose output',
                'noValue'     => true,
            ],
            'push' => [
                'prefix'      => 'p',
                'longPrefix'  => 'push',
                'description' => 'Push schedule to Wrike',
                'noValue'     => true,
            ],
            'help' => [
                'prefix'      => 'h',
                'longPrefix'  => 'help',
                'description' => 'Prints a usage statement',
                'noValue'     => true,
            ],
        ]);
        $this->cli->arguments->parse();
        $this->_br();

        // Output the usage info if 'help' flag set
        if ($this->cli->arguments->get('help')) {
            $this->cli->usage();
            $this->_br();
            exit;
        }

    }

    // Initialize Wrike API client
    private function _init_api_client($token) {
        if ($this->_debug()) $this->_inline("Configuring Wrike API client");
        $this->api = new \GuzzleHttp\Client([
            'base_uri' => self::WRIKE_API_BASE,
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);
        if ($this->_debug()) $this->_success();
    }

    // Get CLI arguments with fallback defaults
    private function _get_schedule_file($default) { return $this->_get_cli_argument('csv', $default); }
    private function _get_api_access_token($default) { return $this->_get_cli_argument('token', $default); }
    private function _get_wrike_parent_folder($default) { return $this->_get_cli_argument('folder', $default); }

}