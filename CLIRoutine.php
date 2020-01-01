<?php
namespace Administrate\JiraToWrikeSync;

abstract class CLIRoutine
{

    const DATE_FORMAT = 'Y-m-d';

    protected $cli;

    public function __construct() {
        $this->cli = new \League\CLImate\CLImate;
    }

    protected function _get_cli_argument($name, $default) {
        $arg = $this->cli->arguments->get($name);
        if (empty($arg)) {
            $arg = $default;
        }
        if (empty($arg) || is_null($arg)) {
            $this->_error("NO {$name} DEFINED", true);
        }
        return $arg;
    }

    protected function _get_parent_str($obj, $objs) {
        if (!is_object($obj) && !empty($obj) && isset($objs[$obj])) {
            $obj = $objs[$obj];
        }
        if (is_object($obj) && $obj->has_parent()) {
            $parent = $objs[$obj->get_parent()];
            return $parent->to_str();
        }
        return 'none';
    }


    // CLI helpers
    protected function _header($str) { $this->cli->bold()->cyan()->out($str)->cyan()->border('-', 48)->br(); return $this; }
    protected function _inline($str) { $this->cli->inline($str . " ... "); return $this; }
    protected function _br() { $this->cli->br(); return $this; }
    protected function _error($str = "FAILED", $fatal = false) {
        $this->cli->red($str);
        if ($fatal) {
            $this->_br();
            exit;
        }
        return $this;
    }
    protected function _warning($str) { $this->cli->yellow($str); return $this; }
    protected function _success($str = "OK") { $this->cli->green($str); return $this; }
    protected function _debug() { return $this->cli->arguments->get('verbose'); }

}