<?php
namespace Administrate\JiraToWrikeSync;

abstract class WorkItem
{

    use Schedulable;

    protected
        $id,
        $title,
        $team,
        $parent,
        $type,
        $rank
    ;

    // Constructor
    public function __construct($id, $title, $team = null, $parent = null, $startDate = null, $endDate = null, $type = null, $rank = null) {
        $this->id = $id;
        $this->title = $title;
        $this->team = $team;
        $this->parent = $parent;
        if (!empty($startDate) && !empty($endDate)) {
            $this->set_schedule_dates($startDate, $endDate);
        }
        $this->type = $type;
        $this->rank = $rank;
    }

    // Standard setters
    public function set_parent($parent) { $this->parent = $parent; }
    public function set_team($team) { $this->team = $team; }

    // Status methods
    public function has_parent() { return !empty($this->parent); }
    public function has_team() { return !empty($this->team); }

    // Standard getters
    public function get_id() { return $this->id; }
    public function get_title() { return $this->title; }
    public function get_parent() { return $this->parent; }
    public function get_team() { return $this->team; }
    public function get_type() { return $this->type; }
    public function get_rank() { return $this->rank; }

    // Return a string representation of this object
    public function to_str($includeType = false) {
        $str = "[{$this->get_id()}] {$this->get_title()}";
        if ($includeType) {
            $str .= " ({$this->get_type()})";
        }
        return $str;
    }

}