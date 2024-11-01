<?php

if (!defined('ABSPATH'))
{
    exit;
}

class SQueue_Auto_Report_Generator
{

    protected $type;
    protected $data;
    protected $orders;
    function __construct($type, $args)
    {
        $this->type = $type;
        $this->data = $args;
        $this->orders = $this->query_orders();
    }

    function start()
    {
        $this->fire_email();
    }

    function query_orders()
    {
        global $wpdb;
        $status_query = "";
        $timezone_full = _x('Y-m-d H:i:s', 'timezone date format');
        $timezone_date = _x('Y-m-d', 'timezone date format');
        $data = $this->data;
        switch ($data['type'])
        {
            case 'every':
                switch ($data['includes'])
                {
                    case 'time_between':
                        $query_to = date($timezone_full, current_time('timestamp'));
                        $query_from = date($timezone_full, strtotime('-'.$data['hour'].' hour -'.$data['min'].' minutes', strtotime(current_time($timezone_full))));
                        break;
                    case 'today_orders':
                        $query_from = date($timezone_full, strtotime(current_time($timezone_date)));
                        $query_to = date($timezone_full, strtotime('+1 days', strtotime(current_time($timezone_date))));
                        break;
                    case 'yesterday_orders':
                        $query_to = date($timezone_full, strtotime(current_time($timezone_date)));
                        $query_from = date($timezone_full, strtotime('-1 days', strtotime(current_time($timezone_date))));
                        break;
                }
                break;
            case 'daily':
            case 'specific':
                switch ($data['includes'])
                {
                    case 'time_between':
                        $query_to = date($timezone_full, current_time('timestamp'));
                        $query_from = date($timezone_full, strtotime('-1 days', strtotime(current_time($timezone_full))));
                        break;
                    case 'today_orders':
                        $query_from = date($timezone_full, strtotime(current_time($timezone_date)));
                        $query_to = date($timezone_full, strtotime('+1 days', strtotime(current_time($timezone_date))));
                        break;
                    case 'yesterday_orders':
                        $query_to = date($timezone_full, strtotime(current_time($timezone_date)));
                        $query_from = date($timezone_full, strtotime('-1 days', strtotime(current_time($timezone_date))));
                        break;
                }
                break;
        }
        if($this->type == 'status_based')
        {
            $status = array();
            if(count($data['status']) >1)
            {
                $status_query = '(';
            }
            foreach ($data['status'] as $stat)
            {
                array_push($status,"p.post_status = '".$stat."'");
            }
            $status_query .= implode(' OR ', $status);
            if(count($data['status']) >1)
            {
                $status_query .= ')';
            }
            
        }
        $query = "select
            p.ID as order_id,
            p.post_date order_date,
            p.post_status as order_status,
            max( CASE WHEN pm.meta_key = '_billing_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as billing_first_name,
            max( CASE WHEN pm.meta_key = '_billing_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as billing_last_name,
            max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as order_total,
            max( CASE WHEN pm.meta_key = '_order_currency' and p.ID = pm.post_id THEN pm.meta_value END ) as order_currency,
            (select GROUP_CONCAT(concat(oi.order_item_name,' x ',CASE WHEN im.meta_key = '_qty' and im.order_item_id = oi.order_item_id THEN im.meta_value END) SEPARATOR '<br>') from ".$wpdb->prefix."woocommerce_order_items as oi JOIN ".$wpdb->prefix."woocommerce_order_itemmeta as im WHERE im.order_item_id = oi.order_item_id and oi.order_id = p.ID and oi.order_item_type='line_item') as order_products
        from
            ".$wpdb->prefix."posts p 
            join ".$wpdb->prefix."postmeta pm on p.ID = pm.post_id
            join ".$wpdb->prefix."woocommerce_order_items oi on p.ID = oi.order_id
        where
            p.post_type = 'shop_order' AND
            p.ID = pm.post_id AND
            p.post_date BETWEEN '".$query_from."' AND '".$query_to."'
            ".(($status_query != "")?" AND ".$status_query:"")."
        group by
            p.ID";
        $orders = $wpdb->get_results($query,ARRAY_A);
        return $orders;
    }
    
    function generate_table()
    {
        $table_column = 'ID|Status|Name|Products|Total';
        $table_column_value = 'order_id|order_status|customer_name|order_products|order_total';
        $td_th = 'border: 1px solid #ddd;padding: 8px;';
        $th = 'padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: #4CAF50;color: white;';
        $tables = '<table class="orders" style="border-collapse: collapse;width: 100%;">';
        $column_name = explode('|', $table_column);
        $column_value = explode('|', $table_column_value);
        $tables.='<tr>';
        foreach ($column_name as $name)
        {
            $tables.= '<th style="'.$td_th.$th.'">'.$name.'</th>';
        }
        $tables.='</tr>';
        if(!empty($this->orders))
        {
            $count = 1;
            foreach ($this->orders as $order)
            {
                if(($count%2)==0)
                {
                    $even = "background-color: #f2f2f2;";
                }
                else
                {
                    $even = '';
                }
                $total = $order['order_total'];
                $order['order_total'] = $this->total_formatter($total,$order['order_currency']);
                $tables .='<tr style="'.$even.'">';
                foreach ($column_value as $value)
                {
                    switch ($value)
                    {
                        case 'customer_name':
                            $name = $order['billing_first_name'].' '.$order['billing_last_name'];
                            $tables.='<td style="'.$td_th.'">'.$name.'</td>';
                            break;
                        case 'order_id':
                            $tables.='<td style="'.$td_th.'"><a href="'.admin_url('post.php?post='.$order['order_id'].'&action=edit').'" target="_blank" rel="nofollow">'.$order['order_id'].'</a></td>';
                            break;
                        case 'order_status':
                            $tables.='<td style="'.$td_th.'">'.(isset($order['order_status'])?ucwords(str_replace('wc-',' ',$order['order_status'])):'').'</td>';
                            break;
                        default:
                            $tables.='<td style="'.$td_th.'">'.(isset($order[$value])?$order[$value]:'').'</td>';
                            break;
                    }
                }
                $tables.='</tr>';
            }
        }
        else
        {
            $tables .= '<tr><td colspan="12" style="'.$td_th.'width:100%;">No Orders placed</td></tr>';
        }
        $tables .= '</table>';
        return $tables;
    }
    
    function generate_styles()
    {
        return '<style>
            </style>
        ';
    }
    
    function generate_html()
    {
        return '
            <html>
            <head>
                '.$this->generate_styles().'
            </head>
            <body>
                '.$this->generate_body().'
                <br></br>
                '.$this->generate_table().'
                '.$this->poweredby_text().'
            </body>
            </html>
        ';
    }
    
    function string_replacer($data)
    {
        $data = str_replace('[{store_name}]', get_bloginfo('name'), $data);
        $data = str_replace('[{type}]', ucfirst($this->data['type']), $data);
        $data = str_replace('[{date_time}]',((isset($this->data['date'])?$this->data['date'].' ':'').$this->data['hour'].':'.$this->data['min'].':00'), $data);
        $data = str_replace('[{orders_placed}]', $this->orders_placed(), $data);
        $data = str_replace('[{orders_total}]', $this->orders_total(TRUE), $data);
        $data = str_replace('[{reports_data}]', ucfirst(str_replace('_', ' ', $this->data['includes'])), $data);
        return $data;
    }
    
    function total_formatter($total,$currency='')
    {
        switch (get_option('woocommerce_currency_pos','left'))
        {
            case 'left':
                $amount = get_woocommerce_currency_symbol($currency).$total;
                break;
            case 'left_space':
                $amount = get_woocommerce_currency_symbol($currency).' '.$total;
                break;
            case 'right':
                $amount = $total. get_woocommerce_currency_symbol($currency);
                break;
            case 'right_space':
                $amount = $total.' '.get_woocommerce_currency_symbol($currency);
                break;
        }
        return $amount;
    }
    
    function orders_placed()
    {
        return count($this->orders);
    }
    
    function orders_total($format = false)
    {
        $total = 0;
        foreach ($this->orders as $order)
        {
            $total += $order['order_total'];
        }
        if($format)
        {
            return $this->total_formatter($total);
        }
        else
        {
            return $total;
        }
    }
    
    function generate_subject()
    {
        $subject = '[{store_name}] Order Report: [{type}] at [{date_time}]';
        $subject = $this->string_replacer($subject);
        return $subject;
    }
    
    function generate_body()
    {
        $body = 'Orders Placed: [{orders_placed}]<br>Orders Total: [{orders_total}]<br>Reports Data: [{reports_data}]';
        $body = $this->string_replacer($body);
        return $body;
    }
    
    function poweredby_text()
    {
        return' <br>
                <div style="text-align:center;"><span style="opacity: 0.4;font-size: 10px;color: black;">Email is a service from '. get_bloginfo('name').'.</span><hr><span> Powered by </span><a href="https://stepqueue.com" target="_blank" rel="nofollow" style="opacity: 0.4;font-size: 10px;color: black !important;">StepQueue</a></div>
                ';
    }
    
    function fire_email()
    {
        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $subject = $this->generate_subject();
        $html = $this->generate_html();
        wp_mail($this->data['emails'], $subject, $html,$headers);
    }
}