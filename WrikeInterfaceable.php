<?php
namespace Administrate\JiraToWrikeSync;

trait WrikeInterfaceable
{

    // Parse a Wrike API response
    private function _parse_api_response($response) {
        return json_decode($response->getBody()->getContents())->data;
    }

}