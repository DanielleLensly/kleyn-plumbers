<?php

class AI_Scheduler_Appointment_Manager
{

    public static function register_cpt()
    {
        $args = array(
            'public' => true,
            'label' => 'Appointments',
            'supports' => array('title', 'editor', 'custom-fields'),
            'menu_icon' => 'dashicons-calendar-alt',
            'show_in_rest' => true,
        );
        register_post_type('appointment', $args);
    }

    public static function create_appointment($data)
    {
        $title = sprintf('Appointment: %s - %s', $data['name'] ?? 'Guest', $data['date'] ?? date('Y-m-d'));

        $content = "Problem: " . ($data['problem'] ?? 'N/A') . "\n";
        $content .= "Phone: " . ($data['phone'] ?? 'N/A') . "\n";
        $content .= "Address: " . ($data['address'] ?? 'N/A') . "\n";
        $content .= "Time: " . ($data['time'] ?? 'N/A') . "\n";

        $post_id = wp_insert_post(array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'appointment',
        ));

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, '_appointment_data', $data);

            // Send Email with Calendar Invite
            self::send_notification_email($post_id, $data);

            return $post_id;
        }
        return false;
    }

    private static function send_notification_email($post_id, $data)
    {
        $admin_email = 'daniella.lensly@gmail.com';
        $customer_email = $data['email'] ?? '';

        $subject = 'New Appointment Booking: ' . ($data['name'] ?? 'Guest');

        $message_body = "<strong>Name:</strong> " . ($data['name'] ?? 'N/A') . "<br>";
        $message_body .= "<strong>Phone:</strong> " . ($data['phone'] ?? 'N/A') . "<br>";
        $message_body .= "<strong>Email:</strong> " . ($data['email'] ?? 'N/A') . "<br>";
        $message_body .= "<strong>Address:</strong> " . ($data['address'] ?? 'N/A') . "<br>";
        $message_body .= "<strong>Preferred Date:</strong> " . ($data['date'] ?? 'N/A') . "<br>";
        $message_body .= "<strong>Problem:</strong> " . ($data['problem'] ?? 'N/A') . "<br>";
        $message_body .= "<strong>Date/Time:</strong> " . ($data['date'] ?? 'N/A') . " " . ($data['time'] ?? '') . "<br>";

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Kleyn Plumbers <admin@kleynplumbers.co.za>'
        );
        $attachments = array();

        // Generate ICS File
        $ics_content = self::generate_ics($data);
        if ($ics_content) {
            $upload_dir = wp_upload_dir();
            $ics_path = $upload_dir['basedir'] . '/appointment.ics';
            file_put_contents($ics_path, $ics_content);
            $attachments[] = $ics_path;
        }

        // 1. Send to Admin
        $admin_message = "New appointment booking details:<br><br>" . $message_body;
        self::log_email($admin_email, 'Admin: ' . $subject, $admin_message, $attachments);
        wp_mail($admin_email, $subject, $admin_message, $headers, $attachments);

        // 2. Send to Customer
        if (!empty($customer_email) && is_email($customer_email)) {
            $customer_subject = 'Appointment Confirmation';
            $customer_message = "Dear " . ($data['name'] ?? 'Customer') . ",<br><br>";
            $customer_message .= "Thank you for booking with Kleyn Plumbers. We have received your request for the following details:<br><br>";
            $customer_message .= $message_body;
            $customer_message .= "<br>We will be in touch shortly to confirm your appointment.<br><br>Kind Regards,<br>Kleyn Plumbers Team";

            self::log_email($customer_email, $customer_subject, $customer_message, $attachments);
            wp_mail($customer_email, $customer_subject, $customer_message, $headers, $attachments);
        }

        // Cleanup
        if (isset($ics_path) && file_exists($ics_path)) {
            unlink($ics_path);
        }
    }

    private static function log_email($to, $subject, $message, $attachments)
    {
        $log_content = "Time: " . date('Y-m-d H:i:s') . "\n";
        $log_content .= "To: $to\n";
        $log_content .= "From: Kleyn Plumbers <admin@kleynplumbers.co.za>\n";
        $log_content .= "Subject: $subject\n";
        $log_content .= "Message Body: \n" . str_replace('<br>', "\n", $message) . "\n";
        $log_content .= "Attachments: " . implode(', ', $attachments) . "\n";
        $log_content .= "----------------------------------------\n\n";
        file_put_contents(wp_upload_dir()['basedir'] . '/email_debug_log.txt', $log_content, FILE_APPEND);
    }

    private static function generate_ics($data)
    {
        $date_str = ($data['date'] ?? date('Y-m-d')) . ' ' . ($data['time'] ?? '09:00');
        $start_time = strtotime($date_str);

        if (!$start_time) {
            $start_time = time();
        }

        $end_time = $start_time + 3600; // 1 hour duration

        $dtstart = date('Ymd\THis', $start_time);
        $dtend = date('Ymd\THis', $end_time);
        $now = date('Ymd\THis');

        $summary = 'Plumbing Appt: ' . ($data['name'] ?? 'Guest');
        $description = 'Problem: ' . ($data['problem'] ?? 'N/A') . '\nPhone: ' . ($data['phone'] ?? 'N/A');
        $location = ($data['address'] ?? 'N/A');

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Rocky Plumbers//AI Scheduler//EN\r\n";
        $ics .= "METHOD:REQUEST\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:" . md5(uniqid(mt_rand(), true)) . "@rockyplumbers.local\r\n";
        $ics .= "DTSTAMP:" . $now . "\r\n";
        $ics .= "DTSTART:" . $dtstart . "\r\n";
        $ics .= "DTEND:" . $dtend . "\r\n";
        $ics .= "SUMMARY:" . $summary . "\r\n";
        $ics .= "DESCRIPTION:" . $description . "\r\n";
        $ics .= "LOCATION:" . $location . "\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR";

        return $ics;
    }
}
