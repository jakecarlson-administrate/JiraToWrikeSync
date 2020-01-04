<?php

return [

    'jira'  =>  [
        'schedule_file' =>  $_ENV['JIRA_SCHEDULE_FILE'] ?? './schedule.csv',
        'key_field'     =>  $_ENV['JIRA_KEY_FIELD'] ?? 'Issue key',
        'title_field'   =>  $_ENV['JIRA_TITLE_FIELD'] ?? 'Title',
        'type_field'    =>  $_ENV['JIRA_TYPE_FIELD'] ?? 'Hierarchy',
        'parent_field'  =>  $_ENV['JIRA_PARENT_FIELD'] ?? 'Parent',
        'start_field'   =>  $_ENV['JIRA_START_FIELD'] ?? 'Scheduled start',
        'end_field'     =>  $_ENV['JIRA_END_FIELD'] ?? 'Scheduled end',
        'team_field'    =>  $_ENV['JIRA_TEAM_FIELD'] ?? 'Teams',
    ],

    'wrike' =>  [
        'access_token'  =>  $_ENV['WRIKE_ACCESS_TOKEN'] ?? null,
        'parent_folder' =>  $_ENV['WRIKE_PARENT_FOLDER'] ?? null,
        'jira_key_field'=>  $_ENV['WRIKE_JIRA_KEY_FIELD'] ?? 'IEAAXCISJUABFJSX',
        'team_field'    =>  $_ENV['WRIKE_TEAM_FIELD'] ?? 'IEAAXCISJUABFGZA',
    ]

];