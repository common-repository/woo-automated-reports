<?php

if (!defined('ABSPATH')) {
    exit;
}

class SQueue_Auto_Report{

    function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts',array($this,'admin_scripts'));
    }
    
    function admin_menu()
    {
        add_submenu_page('woocommerce', __('Automated Reports',SQ_AUTO_REPORT_SLUG), __('Automated Reports',SQ_AUTO_REPORT_SLUG), 'manage_woocommerce',SQ_AUTO_REPORT_SLUG, array($this, 'render_tab'));
    }
    
    function render_tab()
    {
        $page = (!empty($_GET['page']))? esc_attr($_GET['page']) : '';
        if($page == SQ_AUTO_REPORT_SLUG)
        {
            $tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'general';
            if($tab == 'time_based' && isset($_POST['time_based_add_rule']))
            {
                unset($_POST['time_based_add_rule']);
                $time_based_rules = get_option(SQ_AUTO_REPORT_SLUG.'_time_based_rules',array());
                $time_based_rule = $_POST;
                $slug_check = true;
                $key = '';
                do 
                {
                    $key = 'time_based_'.sq_auto_report_slug_generate(4);
                    if (!isset($time_based_rules[$key])) 
                    {
                        $slug_check = false;
                    }
                } while ($slug_check);
                $time_based_rules[$key]= $time_based_rule['time_based'];
                update_option(SQ_AUTO_REPORT_SLUG.'_time_based_rules', $time_based_rules);
            }
            if($tab == 'time_based' && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']))
            {
                $id = $_GET['id'];
                $time_based_rules = get_option(SQ_AUTO_REPORT_SLUG.'_time_based_rules',array());
                unset($time_based_rules[$id]);
                update_option(SQ_AUTO_REPORT_SLUG.'_time_based_rules', $time_based_rules);
                wp_clear_scheduled_hook($id);
                wp_redirect(admin_url("admin.php?page=" . SQ_AUTO_REPORT_SLUG . "&tab=time_based"));
            }
            if($tab == 'status_based' && isset($_POST['status_based_add_rule']))
            {
                $status_based_rules = get_option(SQ_AUTO_REPORT_SLUG.'_status_based_rules',array());
                $status_based_rule = $_POST;
                $slug_check = true;
                $key = '';
                do 
                {
                    $key = 'status_based_'.sq_auto_report_slug_generate(4);
                    if (!isset($status_based_rules[$key])) 
                    {
                        $slug_check = false;
                    }
                } while ($slug_check);
                $status_based_rules[$key]= $status_based_rule['status_based'];
                update_option(SQ_AUTO_REPORT_SLUG.'_status_based_rules', $status_based_rules);
            }
            if($tab == 'status_based' && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']))
            {
                $id = $_GET['id'];
                $status_based_rules = get_option(SQ_AUTO_REPORT_SLUG.'_status_based_rules',array());
                unset($status_based_rules[$id]);
                update_option(SQ_AUTO_REPORT_SLUG.'_status_based_rules', $status_based_rules);
                wp_clear_scheduled_hook($id);
                wp_redirect(admin_url("admin.php?page=" . SQ_AUTO_REPORT_SLUG . "&tab=status_based"));
            }
            echo '
                    <div class="wrap">
                        <h1 class="wp-heading-inline">'.__('WooCommerce Automated Report',SQ_AUTO_REPORT_SLUG).'</h1>
                        <hr class="wp-header-end">';
                    $this->admin_page_tabs($tab);
                    switch($tab)
                    {
                        case "general":
                            echo '<div class="table-box table-box-main" id="general_section" style="margin-top: 10px;">';
                               include(SQ_AUTO_REPORT_VIEWS . "general-settings.php");
                            echo '</div>';
                            break;
                        case "time_based":
                            echo '<div class="table-box table-box-main" id="time_based_section" style="margin-top: 10px;">';
                               include(SQ_AUTO_REPORT_VIEWS . "time-based-settings.php");
                            echo '</div>';
                            break;
                        case "status_based":
                            echo '<div class="table-box table-box-main" id="status_based_section" style="margin-top: 10px;">';
                               include(SQ_AUTO_REPORT_VIEWS . "status-based-settings.php");
                            echo '</div>';
                            break;
                        case "premium":
                            echo '<div class="table-box table-box-main" id="premium_section" style="margin-top: 10px;">';
                               include(SQ_AUTO_REPORT_VIEWS . "upgrade_premium.php");
                            echo '</div>';
                            break;
                    }
            echo '</div>';
        }
    }

    function admin_page_tabs($current = 'general') {
        $tabs = array(
            'general'   => __("General", SQ_AUTO_REPORT_SLUG),
            'time_based'   => __("Time Based", SQ_AUTO_REPORT_SLUG),
            'status_based'   => __("Status Based", SQ_AUTO_REPORT_SLUG),
            'premium'   => __("Premium Features", SQ_AUTO_REPORT_SLUG),
        );
        $html =  '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $name ){
            $class = ($tab == $current) ? 'nav-tab-active' : '';
            $style = ($tab == $current) ? 'border-bottom: 1px solid transparent !important;' : '';
            $html .=  '<a style="text-decoration:none !important;'.$style.'" class="nav-tab ' . $class . '" href="?page='.SQ_AUTO_REPORT_SLUG.'&tab=' . $tab . '">' . $name . '</a>';
        }
        $html .= '</h2>';
        echo $html;
    }
    
    function admin_scripts()
    {
        $page = (!empty($_GET['page']))? esc_attr($_GET['page']) : '';
        $tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'general';
        if($page == SQ_AUTO_REPORT_SLUG)
        {
            wp_enqueue_script("jquery");
            wp_enqueue_style( 'wp-color-picker');
            wp_enqueue_script( 'wp-color-picker');
            wp_enqueue_script(SQ_AUTO_REPORT_SLUG.'_admin_scripts',SQ_AUTO_REPORT_JS.'admin-scripts.js');
            wp_enqueue_script('wc-enhanced-select');
            wp_enqueue_style( 'woocommerce_admin_styles');
        }
        if($page == SQ_AUTO_REPORT_SLUG && $tab == 'premium')
        {
            wp_enqueue_style('bootstrap', SQ_AUTO_REPORT_CSS . 'bootstrap.css');
        }
    }
}