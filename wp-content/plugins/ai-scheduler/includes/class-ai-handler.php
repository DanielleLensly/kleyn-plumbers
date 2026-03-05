<?php

class AI_Scheduler_AI_Handler
{

    private $api_key;

    public function __construct()
    {
        $this->api_key = get_option('ai_scheduler_api_key');
    }

    public function process_message($message, $history = [])
    {
        if (!$this->api_key) {
            return array('reply' => 'Error: API Key is missing. Please contact the administrator.', 'action' => 'error');
        }

        $services_list = "Emergency Plumbing, Drainage, Pipe/Leak Detection, Pressure Release Valves, Burst Water Pipes, Burst Geysers, Geyser Replacements, Plumbing Repairs, Residential Plumbing, New Bathroom Builds, Bathroom Renovations, Blocked Drains, Sewer Lines, Plumbing COCs";

        $system_prompt = "You are Rocky, a highly skilled and helpful plumbing expert for Kleyn Plumbers.
        Your goal is to assist users by answering their plumbing questions with practical advice AND helping them book appointments if needed.
        Current Date: " . date('Y-m-d l') . ".
        
        Services Offered: $services_list.
        
        BEHAVIOR GUIDELINES:
        1. If the user asks a plumbing question, PROVIDE A HELPFUL, CONCISE ANSWER.
        
        IMPORTANT FORMATTING RULES:
        - ALWAYS format step-by-step instructions as a numbered list.
        - EACH STEP MUST BE ON A NEW LINE. Do not group steps together.
        - Use clear line breaks between paragraphs.

        2. After answering, suggest they book a professional appointment if the issue is complex.
        3. If the user provides booking details (Name, Phone, Address, Problem), extract them for a booking.
        
        BOOKING EXTRACTION:
        When the user wants to book, you MUST extract: 
        - Name
        - Phone Number
        - Address
        - Problem Description
        - Date and Time

        If information is missing for a booking, ask for it politely.
        If all booking information is present, output a JSON object ONLY with the key 'booking_complete': true, and the fields above.
        
        Keep responses professional, friendly, and helpful.";

        $messages = array(
            array('role' => 'system', 'content' => $system_prompt)
        );

        // Append conversation history
        foreach ($history as $msg) {
            $messages[] = $msg;
        }

        $messages[] = array('role' => 'user', 'content' => $message);

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'gpt-4o-mini',
                'messages' => $messages,
                'temperature' => 0.7,
            )),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            file_put_contents(wp_upload_dir()['basedir'] . '/ai_debug_log.txt', "WP Error: " . $response->get_error_message() . "\n", FILE_APPEND);
            return array('reply' => 'Sorry, I am having trouble connecting to the brain.', 'action' => 'error');
        }

        $body = wp_remote_retrieve_body($response);
        file_put_contents(wp_upload_dir()['basedir'] . '/ai_debug_log.txt', "Raw Response: " . $body . "\n", FILE_APPEND);

        $data = json_decode($body, true);

        $reply_content = $data['choices'][0]['message']['content'] ?? 'I did not understand that.';

        // Check if response is JSON (Booking Complete)
        if (preg_match('/\{.*\}/s', $reply_content, $matches)) {
            $json_str = $matches[0];
            $booking_data = json_decode($json_str, true);

            if (isset($booking_data['booking_complete']) && $booking_data['booking_complete']) {
                return array(
                    'reply' => "Thank you! I have booked your appointment for " . ($booking_data['date'] ?? 'the requested time') . ".",
                    'action' => 'book',
                    'data' => $booking_data
                );
            }
        }

        return array('reply' => $reply_content, 'action' => 'chat');
    }
}
