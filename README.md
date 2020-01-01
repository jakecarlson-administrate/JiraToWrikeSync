# JiraToWrikeSync

This command-line tool takes a schedule CSV generated from Jira Portfolio and updates all corresponding Wrike tasks under a given parent folder.

## Setup

1. Download and unzip or clone this repository to your local machine.

2. Open the "Terminal" application and navigate to the repository directory.

3. Install Composer by following the directions [here](https://getcomposer.org/download/).

4. Install dependencies by running `php composer.phar install`.

5. Create a `.env` file by copying the example and editing it: `mv .env .env.example`.
    - `WRIKE_ACCESS_TOKEN`: your user's permanent Wrike API access token; instructions to create one are [here](https://help.wrike.com/hc/en-us/community/posts/211849065-Get-Started-with-Wrike-s-API).
    - `WRIKE_PARENT_FOLDER`: the ID of the folder whose tasks you want to sync with Jira. You can get the ID of the folder by running the following command: `curl -g -X GET -H 'Authorization: bearer [your_access_token]' 'https://www.wrike.com/api/v4/folders'`. Search the output (Cmd+F) for the "title" attribute of the folder. The "id" attribute of the same node is what you want.
    - You can optionally configure numerous other settings if desired:
        - `JIRA_SCHEDULE_FILE`: path to the Jira Portfolio schedule CSV; default: `./schedule.csv`
        - `JIRA_KEY_FIELD`: Schedule CSV key column header; default: `Issue key`
        - `JIRA_TITLE_FIELD`: Schedule CSV title column header; default: `Title`
        - `JIRA_TYPE_FIELD`: Schedule CSV type column header; default: `Hierarchy` *
        - `JIRA_PARENT_FIELD`: Schedule CSV parent column header; default: `Parent`
        - `JIRA_START_FIELD`: Schedule CSV scheduled start date column header; default: `Scheduled start`
        - `JIRA_END_FIELD`: Schedule CSV scheduled end date column header; default: `Scheduled end`
        - `JIRA_TEAM_FIELD`: Schedule CSV team column header; default: `Teams`
        - `WRIKE_JIRA_KEY_FIELD`: Wrike Jira key custom field ID; default: `IEAAXCISJUABFJSX` (Jira Key)
        - `WRIKE_TEAM_FIELD`: Wrike Jira team custom field ID; default: `IEAAXCISJUABFGZA` (Assigned Team)
        
6. Make the script executable by running `chmod +x JiraToWrikeSync`.
        
## Usage

Run the script from anywhere: `path/to/repository/JiraToWrikeSync`. It should work without adding any arguments so long as the `WRIKE_ACCESS_TOKEN` and `WRIKE_PARENT_FOLDER` options are configured in `.env`. However, you may pass in the following arguments at runtime:
    - token (t): your Wrike access token
    - folder (f): the parent folder ID
    - csv (c): the path to the schedule CSV