<?php
if (!defined('ABSPATH'))
{
    exit;
}
$time_based_rules = get_option(SQ_AUTO_REPORT_SLUG . '_time_based_rules', array());
?>
<style>
    p.description
    {
        font-style: normal;
    }
    table.time_based_tables th {
        padding: 9px 7px!important;
        vertical-align: middle;
    }
    table.time_based_tables td,
    table.time_based_tables th{
        vertical-align: middle;
    }
    table.time_based_tables tr:nth-child(odd) td{
        background: #eeeeee;
    }
</style>
<form method="post" action="<?php echo admin_url("admin.php?page=" . SQ_AUTO_REPORT_SLUG . "&tab=time_based"); ?>">
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="time_based[emails][]">
                    <?php _e('Email To', SQ_AUTO_REPORT_SLUG); ?>
                </label>
            </th>
            <td>
                <input name="time_based[emails][]" size="43" type="email" required id="time_based_emails" placeholder="sample@email.com">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="time_based[type]">
                    <?php _e('Report Type', SQ_AUTO_REPORT_SLUG); ?>
                </label>
            </th>
            <td>
                <select name="time_based[type]" required id="time_based_type" style="width:25%">
                    <option value=""><?php _e('Select Type', SQ_AUTO_REPORT_SLUG); ?></option>
                    <option value="every"><?php _e('Every', SQ_AUTO_REPORT_SLUG); ?></option>
                    <option value="daily"><?php _e('Daily', SQ_AUTO_REPORT_SLUG); ?></option>
                    <option value="specific"><?php _e('Specific', SQ_AUTO_REPORT_SLUG); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="time_based[hour]">
                    <?php _e('Report Time', SQ_AUTO_REPORT_SLUG); ?>
                </label>
            </th>
            <td>
                <input type="number" name="time_based[hour]" required id="time_based_hour" placeholder="<?php _e('24 Hours', SQ_AUTO_REPORT_SLUG); ?>" min="0" max="23" style="width:10%">
                <input type="number" name="time_based[min]" required placeholder="Minutes" min="0" max="59" style="width:10%">
                <p class="description">
                    <?php 
                        _e('Provide the time based on your timezone.', SQ_AUTO_REPORT_SLUG);
                        $timezone_format = _x('Y-m-d H:i:s', 'timezone date format');
                        printf(__('Local time is %s.'), '<code>' . date_i18n($timezone_format) . '</code>');
                    ?>
                </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="time_based[format]">
                    <?php _e('Report Format', SQ_AUTO_REPORT_SLUG); ?>
                </label>
            </th>
            <td>
                <label>
                    <input name="time_based[format]" type="radio" value="text" required checked>
                    <?php _e("Text", SQ_AUTO_REPORT_SLUG); ?>
                </label>
                <br>
                <label>
                    <?php _e("PDF (Pro Feature)", SQ_AUTO_REPORT_SLUG); ?>
                </label>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="time_based[includes][]">
                    <?php _e('Report Data', SQ_AUTO_REPORT_SLUG); ?>
                </label>
            </th>
            <td>
                <label>
                    <input name="time_based[includes]" type="radio" value="time_between" checked>
                    <?php _e("Time Between Orders", SQ_AUTO_REPORT_SLUG); ?>  <small>( <?php _e("Will send orders between last sending time and specified time", SQ_AUTO_REPORT_SLUG); ?> )</small>
                </label>
                <br>
                <label>
                    <input name="time_based[includes]" type="radio" value="today_orders">
                    <?php _e("Today's Orders", SQ_AUTO_REPORT_SLUG); ?>
                </label>
                <br>
                <label>
                    <input name="time_based[includes]" type="radio" value="yesterday_orders">
                    <?php _e("Yesterday's Orders", SQ_AUTO_REPORT_SLUG); ?>
                </label>
            </td>
        </tr>
    </table>
    <input type="submit" class="button button-primary" name="time_based_add_rule" id="time_based_add_rule" value="<?php _e("Add Rule", SQ_AUTO_REPORT_SLUG); ?>">
</form>
<hr style="margin-top: 1.5em;" >
<table class="mjnow_clients widefat" cellspacing="0" style="margin-top: 10px;">
    <thead>
        <tr>
            <th class="type"><?php _e('Type', SQ_AUTO_REPORT_SLUG); ?></th>
            <th class="time"><?php _e('Time', SQ_AUTO_REPORT_SLUG); ?></th>
            <th class="format"><?php _e('Format', SQ_AUTO_REPORT_SLUG); ?></th>
            <th class="emails"><?php _e('Emails', SQ_AUTO_REPORT_SLUG); ?></th>
            <th class="actions"><?php _e('Actions', SQ_AUTO_REPORT_SLUG); ?></th>
        </tr>
    </thead>
    <tbody class="ui-sortable">
        <?php
        if (!empty($time_based_rules))
        {
            foreach ($time_based_rules as $id => $rule)
            {
                ?>
                <tr>
                    <td class="type">
                        <b>Type : </b><?php echo ucfirst($rule['type']); ?>
                        <hr>
                        <b>Data : </b><?php echo ucwords(str_replace('_',' ',$rule['includes'])); ?>
                    </td>
                    <td class="time">
                        <?php
                        if(isset($rule['date']))
                        {
                            echo $rule['date'];
                            echo ' ';
                        }
                        echo $rule['hour'];
                        echo ':';
                        echo $rule['min'];
                        echo ':00';
                        ?>
                    </td>
                    <td class="fomat">
                        <?php echo ucfirst($rule['format']); ?>
                    </td>
                    <td class="emails">
                        <?php echo implode(', ',$rule['emails']); ?>
                    </td>
                    <td class="actions">
                        <a href="<?php echo admin_url("admin.php?page=" . SQ_AUTO_REPORT_SLUG . "&tab=time_based&action=delete&id=" . $id); ?>" title="Delete Report" onclick="return confirm('Are you sure to delete?')">
                            <span class="dashicons dashicons-trash"></span>
                        </a>
                    </td>
                </tr>
                <?php
            }
        } else
        {
            ?>
            <tr>
                <td colspan="12"><?php _e('No Rules Found', SQ_AUTO_REPORT_SLUG); ?></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>