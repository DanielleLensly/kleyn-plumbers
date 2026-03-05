<?php

class AI_Scheduler_API_Endpoint
{

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        register_rest_route('ai-scheduler/v1', '/message', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_message'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('ai-scheduler/v1', '/booking', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_booking'),
            'permission_callback' => '__return_true',
        ));
    }

    public function handle_booking($request)
    {
        $data = $request->get_params();

        // Basic validation
        if (empty($data['name']) || empty($data['phone'])) {
            return new WP_Error('missing_params', 'Name and Phone are required', array('status' => 400));
        }

        // Map 'message' field to 'problem' as expected by Appointment Manager
        if (isset($data['message']) && !isset($data['problem'])) {
            $data['problem'] = $data['message'];
        }

        $appt_id = AI_Scheduler_Appointment_Manager::create_appointment($data);

        if ($appt_id) {
            return rest_ensure_response(array('success' => true, 'appt_id' => $appt_id, 'reply' => 'Thank you! Your appointment request has been sent. We will contact you shortly to confirm.'));
        }

        return new WP_Error('booking_error', 'Could not save appointment', array('status' => 500));
    }

    public function handle_message($request)
    {
        $message = $request->get_param('message');
        $history = $request->get_param('history') ?? [];

        if (empty($message)) {
            return new WP_Error('no_message', 'Message is required', array('status' => 400));
        }

        $ai_handler = new AI_Scheduler_AI_Handler();
        $response = $ai_handler->process_message($message, $history);

        if ($response['action'] === 'book') {
            // Create Appointment
            $appt_id = AI_Scheduler_Appointment_Manager::create_appointment($response['data']);
            if ($appt_id) {
                $response['appt_id'] = $appt_id;
            } else {
                $response['reply'] = "There was an error saving your appointment, but I have noted the details.";
            }
        }

        return rest_ensure_response($response);
    }
}

new AI_Scheduler_API_Endpoint();
