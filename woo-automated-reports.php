<?php

/*
  Plugin Name: WooCommerce Automated Reports
  Plugin URI: https://stepqueue.com/plugins/woocommerce-automated-reports/
  Description: Get your automated order reports to your email daily or on specific time.
  Version: 1.0.2
  Author: StepQueue
  Author URI: https://stepqueue.com
  License: GPL 3.0
  WC requires at least: 3.0.0
  WC tested up to: 3.2.0
 */

if (!defined('ABSPATH'))
{
    exit;
}
require_once(ABSPATH . "wp-admin/includes/plugin.php");
if (in_array('woocommerce/woocommerce.php', get_option('active_plugins')))
{
    if (!in_array('woocommerce-automated-reports-pro/woocommerce-automated-reports-pro.php', get_option('active_plugins')))
    {
        if (!defined('SQ_AUTO_REPORT_VERSION'))
        {
            define('SQ_AUTO_REPORT_VERSION', '1.0.2');
        }
        if (!defined('SQ_AUTO_REPORT_SLUG'))
        {
            define('SQ_AUTO_REPORT_SLUG', 'sq_auto_reports');
        }
        if (!defined('SQ_AUTO_REPORT_URL'))
        {
            define('SQ_AUTO_REPORT_URL', plugin_dir_url(__FILE__));
        }
        if (!defined('SQ_AUTO_REPORT_PATH'))
        {
            define('SQ_AUTO_REPORT_PATH', plugin_dir_path(__FILE__));
        }
        if (!defined('SQ_AUTO_REPORT_IMG'))
        {
            define('SQ_AUTO_REPORT_IMG', SQ_AUTO_REPORT_URL . "assets/img/");
        }
        if (!defined('SQ_AUTO_REPORT_CSS'))
        {
            define('SQ_AUTO_REPORT_CSS', SQ_AUTO_REPORT_URL . "assets/css/");
        }
        if (!defined('SQ_AUTO_REPORT_JS'))
        {
            define('SQ_AUTO_REPORT_JS', SQ_AUTO_REPORT_URL . "assets/js/");
        }
        if (!defined('SQ_AUTO_REPORT_INC'))
        {
            define('SQ_AUTO_REPORT_INC', SQ_AUTO_REPORT_PATH . "includes/");
        }
        if (!defined('SQ_AUTO_REPORT_VIEWS'))
        {
            define('SQ_AUTO_REPORT_VIEWS', SQ_AUTO_REPORT_PATH . "views/");
        }
        if (!defined('SQ_AUTO_REPORT_VENDOR'))
        {
            define('SQ_AUTO_REPORT_VENDOR', SQ_AUTO_REPORT_PATH . "vendor/");
        }

        add_action('init', 'sq_auto_report_run', 99);

        function sq_auto_report_run()
        {
            require_once (SQ_AUTO_REPORT_INC . "sq-auto-report-init.php");
            require_once (SQ_AUTO_REPORT_INC . "sq-auto-report-public.php");
            new SQueue_Auto_Report();
            if (!class_exists('StepQueue_Uninstall_feedback_Listener')) {
                require_once (SQ_AUTO_REPORT_INC . "class-stepqueue-uninstall.php");
            }
            $qvar = array(
                'name' => 'WooCommerce Automated Reports',
                'version' => SQ_AUTO_REPORT_VERSION,
                'slug' => 'woo-automated-reports',
                'lang' => SQ_AUTO_REPORT_SLUG,
            );
            new StepQueue_Uninstall_feedback_Listener($qvar);
        }

        if (!class_exists('SQ_Auto_Report_Cron'))
        {
            require_once (SQ_AUTO_REPORT_INC . "sq-auto-report-cron.php");
            require_once (SQ_AUTO_REPORT_INC . "sq-auto-report-generator.php");
            new SQueue_Auto_Report_Cron();
        }
        
        add_filter('plugin_row_meta', 'sq_auto_report_plugin_row_meta', 10, 2);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'sq_auto_report_plugin_action_link');

        function sq_auto_report_plugin_action_link($links)
        {
            $plugin_links = array(
                '<a href="' . admin_url('admin.php?page='.SQ_AUTO_REPORT_SLUG) . '">' . __('Schedule a Report', SQ_AUTO_REPORT_SLUG) . '</a>'
            );
            if (array_key_exists('deactivate', $links))
            {
                $links['deactivate'] = str_replace('<a', '<a class="woo-automated-reports-deactivate-link"', $links['deactivate']);
            }
            return array_merge($plugin_links, $links);
        }

        function sq_auto_report_plugin_row_meta($links, $file)
        {
            if ($file == plugin_basename(__FILE__))
            {
                $row_meta = array(
                    '<a href="https://stepqueue.com/documentation/woocommerce-automated-reports-setup/" target="_blank">' . __('Documentation', SQ_AUTO_REPORT_SLUG) . '</a>',
                    '<a href="https://stepqueue.com/plugins/woocommerce-automated-reports/" target="_blank">' . __('Buy Pro', SQ_AUTO_REPORT_SLUG) . '</a>',
                    '<a href="https://wordpress.org/support/plugin/woo-automated-reports/" target="_blank">' . __('Support', SQ_AUTO_REPORT_SLUG) . '</a>'
                );
                return array_merge($links, $row_meta);
            }
            return (array) $links;
        }
    } 
    else
    {
        add_action('admin_notices', 'sq_auto_report_admin_notices', 99);
        deactivate_plugins(plugin_basename(__FILE__));

        function sq_auto_report_admin_notices()
        {
            is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain)
                    {
                        $old = array(
                            "Plugin <strong>activated</strong>.",
                            "Selected plugins <strong>activated</strong>."
                        );
                        $new = "<span style='color:red'>WooCommerce Automated Reports - Pro Version is currently installed and active</span>";
                        if (in_array($untranslated_text, $old, true))
                        {
                            $translated_text = $new;
                        }
                        return $translated_text;
                    }, 99, 3);
        }

        return;
    }
} 
else
{
    add_action('admin_notices', 'sq_auto_report_wc_basic_admin_notices', 99);
    deactivate_plugins(plugin_basename(__FILE__));

    function sq_auto_report_wc_basic_admin_notices()
    {
        is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain)
                {
                    $old = array(
                        "Plugin <strong>activated</strong>.",
                        "Selected plugins <strong>activated</strong>."
                    );
                    $new = "<span style='color:red'>WooCommerce Automated Reports - WooCommerce is not Installed</span>";
                    if (in_array($untranslated_text, $old, true))
                    {
                        $translated_text = $new;
                    }
                    return $translated_text;
                }, 99, 3);
    }

    return;
}
