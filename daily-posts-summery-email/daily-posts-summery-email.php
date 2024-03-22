<?php
/*
Plugin Name: Daily Posts Summary Email
Plugin URI: #
Description: Sends a daily summary email to the admin with the number of posts created and their titles for that day.
Version: 1.0
Author: Your Name
Author URI: #
License: GPL2
*/

function dps_schedule_daily_cron()
{
    if (!wp_next_scheduled('dps_daily_summary_email')) {
        // Schedule the event if it's not already scheduled
        wp_schedule_event(time(), 'daily', 'dps_daily_summary_email');
    }
}
// Schedule the cron job when WordPress initializes
add_action('wp', 'dps_schedule_daily_cron');

// Hook for the scheduled email
function dps_send_daily_summary_email()
{
    $today = date('Y-m-d');
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'date_query'     => array(
            array(
                'after'     => $today . ' 00:00:00',
                'before'    => $today . ' 23:59:59',
                'inclusive' => true,
            ),
        ),
    );

    // Query posts created today
    $posts_query = new WP_Query($args);

    // Output for debugging
    echo "<pre>";
    print_r($posts_query);
    echo "</pre>";

    $post_count = $posts_query->found_posts;
    $post_titles = array();

    // Loop through posts and collect titles
    if ($posts_query->have_posts()) {
        while ($posts_query->have_posts()) {
            $posts_query->the_post();
            $post_titles[] = get_the_title();
        }
    }

    // Reset post data
    wp_reset_postdata();

    // Prepare email subject and message
    $email_subject = 'Daily Posts Summary: ' . $today;
    $email_message = "Number of posts created today: $post_count\n\n";
    $email_message .= "Titles of posts created today:\n";
    $email_message .= implode("\n", $post_titles);

    // Send email
    wp_mail(get_option('admin_email'), $email_subject, $email_message);
}
// Hook the function to the scheduled event
add_action('dps_daily_summary_email', 'dps_send_daily_summary_email');


function do_this_in_an_hour()
{
    // do something
    echo "Hello Farid, your cron job has started.";
}
add_action('my_new_event', 'do_this_in_an_hour');

// You need to put this line inside a function hook, 
// such as 'init', 'wp_loaded', or 'after_setup_theme', 
// to ensure it doesn't run on every page visit.
// Otherwise, it will schedule a new event on every page load.
function schedule_my_event()
{
    wp_schedule_single_event(time() + 120, 'my_new_event'); // 3600 seconds = 1 hour
}
add_action('init', 'schedule_my_event');

function schedule_my_event2()
{
    $to = 'faridcse7800@gmail.com';
    $subject = 'Test email';
    $body = 'This is a test email sent using wp_mail() function.';
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Send email
    $sent = wp_mail($to, $subject, $body, $headers);

    if ($sent) {
        echo 'Email sent successfully.';
    } else {
        echo 'Failed to send email.';
    }
}

add_action('init', 'schedule_my_event2');
