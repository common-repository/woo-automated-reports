<?php

if (!defined('ABSPATH')) {
    exit;
}

class SQueue_Auto_Report_Cron {
    
    protected $daily = array();
    protected $specific = array();
    protected $schedules = array();
    
    function __construct() {
        add_action('init', array($this, 'report_schedule_init'));
        add_filter('cron_schedules', array($this, 'every_schedule_time'));
        $time_based_rules = get_option(SQ_AUTO_REPORT_SLUG.'_time_based_rules',array());
        $status_based_rules = get_option(SQ_AUTO_REPORT_SLUG.'_status_based_rules',array());
        $merged = array_merge($time_based_rules, $status_based_rules);
        foreach ($merged as $id => $rule)
        {
            switch ($rule['type'])
            {
                case 'specific':
                    $timezone_format = _x('Y-m-d H:i:s', 'timezone date format');
                    $current = strtotime(date_i18n($timezone_format));
                    $time = strtotime(get_gmt_from_date($rule['date'].' '.$rule['hour'].':'.$rule['min'].":00"));
                    if($current<$time)
                    {
                        add_action($id, array($this, 'start_report_action'));
                        $this->specific[$id] = $time;
                    }
                    break;
                case 'daily':
                    $timezone_format = _x('Y-m-d', 'timezone date format');
                    $current = date_i18n($timezone_format);
                    $time = strtotime(get_gmt_from_date($current.' '.$rule['hour'].':'.$rule['min'].":00"));
                    add_action($id, array($this, 'start_report_action'));
                    $this->daily[$id] = $time;
                    break;
                case 'every':
                    $time = ($this->get_unixtime('hour', $rule['hour'])+$this->get_unixtime('min', $rule['min']));
                    add_action($id, array($this, 'start_report_action'));
                    $this->schedules[$id] = $time;
                    break;
                default:
                    break;
            }
        }
    }
    
    function get_unixtime($type,$period) {
        $val = 0;
        $min = 60;
        $hour = 3600;
        $day = 86400;
        $week = 604800;
        $month = 4.34524;
        $year = 52.1429;
        switch ($type) {
            case "min":
                $val = $min;
                break;
            case "hour":
                $val = $hour;
                break;
            case "day":
                $val = $day;
                break;
            case "week":
                $val = $week;
                break;
            case "month":
                $val = $month * $week;
                break;
            case "year":
                $val = $year * $week;
                break;
            default:
                break;
        }
        return ($val*$period);
    }
    
    function every_schedule_time($schedules)
    {
        foreach ($this->schedules as $key => $value) {
            $schedules[$key] = array(
                'interval' => $value,
                'display' => "Every ".($value/60)." Minutes"
            );
        }
        return $schedules;
    }

    function report_schedule_init() {
        foreach ($this->daily as $key => $value) {
            //wp_clear_scheduled_hook($key);
            if (!wp_next_scheduled($key)) {
                wp_schedule_event($value,'daily',$key);
            }
        }
        foreach ($this->specific as $key => $value) {
            //wp_clear_scheduled_hook($key);
            wp_schedule_single_event($value,$key);
        }
        foreach ($this->schedules as $key => $value) {
            //wp_clear_scheduled_hook($key);
            if (!wp_next_scheduled($key)) {
                wp_schedule_event(time(),$key,$key);
            }
        }
    }
    
    function start_report_action() {
        $report = current_action();
        $type = '';
        $args = array();
        if (strpos($report, 'time_based_') !== false) {
            $type='time_based';
            $data = get_option(SQ_AUTO_REPORT_SLUG.'_time_based_rules');
            $args = $data[$report];
        }
        if (strpos($report, 'status_based_') !== false) {
            $type='status_based';
            $data = get_option(SQ_AUTO_REPORT_SLUG.'_status_based_rules');
            $args = $data[$report];
        }
        $generator = new SQueue_Auto_Report_Generator($type,$args);
        $generator->start();
    }
}
