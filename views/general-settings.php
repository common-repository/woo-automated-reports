<?php
if (!defined('ABSPATH'))
{
    exit;
}
$time_based_rules = get_option(SQ_AUTO_REPORT_SLUG.'_time_based_rules',array());
$status_based_rules = get_option(SQ_AUTO_REPORT_SLUG.'_status_based_rules',array());
$merged = array_merge($time_based_rules, $status_based_rules);
if(!empty($merged))
{
    foreach ($merged as $report => $data) {
        ?>
        <div class="postbox" style="padding: 10px;">
            <?php
            if (strpos($report, 'time_based_') !== false) {
                $type='time_based';
                $args = $data;
                ?>
                <table class="widefat" style="border: none;">
                    <thead>
                        <tr>
                            <th class="type"><?php _e('Type', SQ_AUTO_REPORT_SLUG); ?></th>
                            <th class="data"><?php _e('Data', SQ_AUTO_REPORT_SLUG); ?></th>
                            <th class="time"><?php _e('Time', SQ_AUTO_REPORT_SLUG); ?></th>
                            <th class="format"><?php _e('Format', SQ_AUTO_REPORT_SLUG); ?></th>
                            <th class="emails"><?php _e('Emails', SQ_AUTO_REPORT_SLUG); ?></th>
                            <th class="actions"><?php _e('Actions', SQ_AUTO_REPORT_SLUG); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="type">
                                <?php echo ucfirst($data['type']); ?>
                            </td>
                            <td class="data">
                                <?php echo ucwords(str_replace('_',' ',$data['includes'])); ?>
                            </td>
                            <td class="time">
                                <?php
                                if(isset($data['date']))
                                {
                                    echo $data['date'];
                                    echo ' ';
                                }
                                echo $data['hour'];
                                echo ':';
                                echo $data['min'];
                                echo ':00';
                                ?>
                            </td>
                            <td class="fomat">
                                <?php echo ucfirst($data['format']); ?>
                            </td>
                            <td class="emails">
                                <?php echo implode(', ',$data['emails']); ?>
                            </td>
                            <td class="actions">
                                <a href="<?php echo admin_url("admin.php?page=" . SQ_AUTO_REPORT_SLUG . "&tab=time_based&action=delete&id=" . $report); ?>" title="Delete Report" onclick="return confirm('Are you sure to delete?')">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php
            }
            if (strpos($report, 'status_based_') !== false) {
                $type='status_based';
                $args = $data;
                ?>
                <table class="widefat" style="border: none;">
                    <thead>
                        <tr>
                            <th class="type"><?php _e('Type', SQ_AUTO_REPORT_SLUG); ?></th>
                            <th class="data"><?php _e('Data', SQ_AUTO_REPORT_SLUG); ?></th>
                            <th class="time"><?php _e('Time', SQ_AUTO_REPORT_SLUG); ?></th>
                            <th class="format"><?php _e('Format', SQ_AUTO_REPORT_SLUG); ?></th>
                            <th class="emails"><?php _e('Emails', SQ_AUTO_REPORT_SLUG); ?></th>
                            <th class="statuses"><?php _e('Statuses', SQ_AUTO_REPORT_SLUG); ?></th>
                            <th class="actions"><?php _e('Actions', SQ_AUTO_REPORT_SLUG); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="type">
                                <?php echo ucfirst($data['type']); ?>
                            </td>
                            <td class="data">
                                <?php echo ucwords(str_replace('_',' ',$data['includes'])); ?>
                            </td>
                            <td class="time">
                                <?php
                                if(isset($data['date']))
                                {
                                    echo $data['date'];
                                    echo ' ';
                                }
                                echo $data['hour'];
                                echo ':';
                                echo $data['min'];
                                echo ':00';
                                ?>
                            </td>
                            <td class="fomat">
                                <?php echo ucfirst($data['format']); ?>
                            </td>
                            <td class="emails">
                                <?php echo implode(', ',$data['emails']); ?>
                            </td>
                            <td class="statuses">
                                <?php echo ucwords(str_replace('wc-',' ',implode(', ',$data['status']))); ?>
                            </td>
                            <td class="actions">
                                <a href="<?php echo admin_url("admin.php?page=" . SQ_AUTO_REPORT_SLUG . "&tab=status_based&action=delete&id=" . $report); ?>" title="Delete API" onclick="return confirm('Are you sure to delete?')">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php
            }
            $generator = new SQueue_Auto_Report_Generator($type,$args);
            echo $generator->generate_table();
        echo '</div>';
    }
}
else
{
    ?>
    <div class="woocommerce-BlankState">
        <h2 class="woocommerce-BlankState-message">
            <?php _e('Create and automate to send your orders report to Email', SQ_AUTO_REPORT_SLUG); ?>.</h2>
        <a class="woocommerce-BlankState-cta button-primary button" href="<?php echo admin_url("admin.php?page=" . SQ_AUTO_REPORT_SLUG . "&tab=time_based") ?>">
            <?php _e('Create Time Based Report', SQ_AUTO_REPORT_SLUG); ?>
        </a>
        <a class="woocommerce-BlankState-cta button-primary button" href="<?php echo admin_url("admin.php?page=" . SQ_AUTO_REPORT_SLUG . "&tab=status_based") ?>">
            <?php _e('Create Order Status Based Report', SQ_AUTO_REPORT_SLUG); ?>
        </a>
    </div>
    <?php
}