<?php
/**
 * Plugin Name: AI Appointment Scheduler
 * Description: AI-powered appointment booking and "Services" list display.
 * Version: 1.0.5
 * Author: Antigravity
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AI_SCHEDULER_PATH', plugin_dir_path(__FILE__));
define('AI_SCHEDULER_URL', plugin_dir_url(__FILE__));

require_once AI_SCHEDULER_PATH . 'includes/class-appointment-manager.php';
require_once AI_SCHEDULER_PATH . 'includes/class-ai-handler.php';
require_once AI_SCHEDULER_PATH . 'includes/class-api-endpoint.php';

class AI_Scheduler
{

    public function __construct()
    {
        add_action('init', array('AI_Scheduler_Appointment_Manager', 'register_cpt'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_shortcode('ai_scheduler_chat', array($this, 'render_chat_widget'));
        add_shortcode('kleyn_services_list', array($this, 'render_services_list'));
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));

        // Force injection for Elementor pages
        add_filter('the_content', array($this, 'inject_content_to_home'), 99);
    }

    public function inject_content_to_home($content)
    {
        if (is_front_page() && !is_admin()) {

            // New Contact Section HTML
            $contact_html = '
            <div class="ai-scheduler-contact-section">
                <div class="ai-contact-container">
                    <div class="ai-contact-left">
                        <h2 class="ai-contact-heading">Contact Us</h2>
                        <p class="ai-contact-detail">Phone: 076 726 4010</p>
                        <p class="ai-contact-detail">Email: rocky@kleynplumbers.co.za</p>
                        <p class="ai-contact-detail">Available 24/7 for Emergencies</p>
                    </div>
                    <div class="ai-contact-right">
                        <div class="ai-book-btn" onclick="aiSchedulerOpenBooking()">
                            Book an Appointment
                        </div>
                    </div>
                </div>
            </div>';

            $chat = do_shortcode('[ai_scheduler_chat]');
            return $content . $contact_html . $chat;
        }
        return $content;
    }

    public function enqueue_assets()
    {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('ai-scheduler-css', AI_SCHEDULER_URL . 'assets/css/style.css', array(), '1.1.8');
        wp_enqueue_script('ai-scheduler-js', AI_SCHEDULER_URL . 'assets/js/chat.js', array('jquery'), '1.1.8', true);
        wp_localize_script('ai-scheduler-js', 'aiScheduler', array(
            'apiUrl' => rest_url('ai-scheduler/v1/message'),
            'bookingUrl' => rest_url('ai-scheduler/v1/booking'),
            'nonce' => wp_create_nonce('wp_rest')
        ));
    }

    public function render_chat_widget()
    {
        ob_start();
        ?>
        <div id="ai-scheduler-container" class="ai-scheduler-container">
            <!-- Help Message Bubble -->
            <div id="ai-help-message" class="ai-help-message" onclick="aiSchedulerOpenChat()">
                Need to make a booking? 👋
            </div>

            <!-- Toggle Icon -->
            <div id="ai-chat-toggle" class="ai-chat-toggle" onclick="aiSchedulerOpenChat()">
                <span class="dashicons dashicons-format-chat"></span>
            </div>

            <script>
                function aiSchedulerOpenChat() {
                    console.log('Direct click handler called');
                    var widget = document.getElementById('ai-scheduler-widget');
                    var toggle = document.getElementById('ai-chat-toggle');
                    var helpMsg = document.getElementById('ai-help-message');
                    if (widget) {
                        widget.style.display = 'block';
                        toggle.style.display = 'none';
                        helpMsg.style.display = 'none';
                    }
                }
                function aiSchedulerCloseChat() {
                    var widget = document.getElementById('ai-scheduler-widget');
                    var toggle = document.getElementById('ai-chat-toggle');
                    var helpMsg = document.getElementById('ai-help-message');
                    if (widget) {
                        widget.style.display = 'none';
                        toggle.style.display = 'flex';
                        helpMsg.style.display = 'block';
                    }
                }
            </script>

            <!-- Chat Widget (Hidden by default) -->
            <div id="ai-scheduler-widget" class="ai-chat-widget" style="display: none;">
                <div class="chat-header">
                    <button id="back-to-selection-btn" class="back-btn" style="display: none;">&larr;</button>
                    <h3 id="chat-title">How can we help?</h3>
                    <div class="header-actions">
                        <button id="expand-chat-btn" class="header-btn" title="Expand Chat"><span
                                class="dashicons dashicons-editor-expand"></span></button>
                        <button id="close-chat-btn" class="header-btn" onclick="aiSchedulerCloseChat()"
                            title="Close">&times;</button>
                    </div>
                </div>

                <!-- Selection View (Default) -->
                <div id="selection-view">
                    <div class="selection-options">
                        <button id="btn-ask-question" class="selection-btn">
                            <span class="dashicons dashicons-whatsapp"></span>
                            Ask Rocky a Question
                        </button>
                        <button id="btn-book-appointment" class="selection-btn">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            Book Appointment
                        </button>
                    </div>
                </div>

                <!-- Chat View -->
                <div id="chat-view" style="display: none;">
                    <div class="chat-messages" id="chat-messages">
                        <div class="message bot-message">Hello! I can assist you with booking a plumbing appointment. How can I
                            help you today?</div>
                    </div>
                    <div class="chat-input-area">
                        <input type="text" id="user-input" placeholder="Type your message..." />
                        <button id="send-btn">Send</button>
                    </div>
                </div>

                <!-- Booking View REMOVED -->
            </div>

            <!-- Modal Booking Form -->
            <div id="ai-booking-modal" class="ai-modal" style="display:none;">
                <div class="ai-modal-content">
                    <span class="ai-close-modal">&times;</span>
                    <h3>Book an Appointment</h3>
                    <form id="booking-form" class="booking-form">
                        <div class="form-group">
                            <label for="booking-name">Name <span class="required-label">(Required)</span></label>
                            <input type="text" id="booking-name" required placeholder="Your Name">
                        </div>
                        <div class="form-group">
                            <label for="booking-phone">Cell Number <span class="required-label">(Required)</span></label>
                            <input type="tel" id="booking-phone" required placeholder="082 123 4567">
                        </div>
                        <div class="form-group">
                            <label for="booking-email">Email</label>
                            <input type="email" id="booking-email" placeholder="email@example.com">
                        </div>
                        <div class="form-group">
                            <label for="booking-address">Address</label>
                            <input type="text" id="booking-address" placeholder="123 Street Name, Suburb">
                        </div>
                        <div class="form-group">
                            <label for="booking-date">Preferred Date <span class="required-label">(Required)</span></label>
                            <input type="date" id="booking-date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="booking-message">Message <span class="required-label">(Required)</span></label>
                            <textarea id="booking-message" required placeholder="Describe your problem..."></textarea>
                        </div>
                        <div class="form-group">
                            <label id="captcha-label" for="booking-captcha">Security Question <span
                                    class="required-label">(Required)</span></label>
                            <input type="number" id="booking-captcha" required placeholder="Sum of two numbers">
                        </div>
                        <button type="submit" class="submit-booking-btn">Request Booking</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_services_list()
    {
        $services = array(
            array('title' => 'Emergency Plumbing', 'desc' => '24/7 fast response for critical issues.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Emergency'),
            array('title' => 'Drainage', 'desc' => 'Professional drain and sewer cleaning.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Drainage'),
            array('title' => 'Pipe/Leak Detection', 'desc' => 'Expert leak detection and pipe repair.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Leak+Detection'),
            array('title' => 'Pressure Release Valves', 'desc' => 'Installation and maintenance of PRVs.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Valves'),
            array('title' => 'Burst Water Pipes', 'desc' => 'Quick repairs for burst pipes to prevent damage.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Burst+Pipes'),
            array('title' => 'Burst Geysers', 'desc' => 'Geyser replacements and component fixes.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Geysers'),
            array('title' => 'Geyser Installation', 'desc' => 'New geyser installations and upgrades.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Installation'),
            array('title' => 'Plumbing Repairs', 'desc' => 'General maintenance and tap washers.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Repairs'),
            array('title' => 'Residential Plumbing', 'desc' => 'Home plumbing solutions for your family.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Residential'),
            array('title' => 'New Bathroom Builds', 'desc' => 'Complete plumbing for new bathrooms.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=New+Bathroom'),
            array('title' => 'Bathroom Renovations', 'desc' => 'Modernize your bathroom with our help.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Renovations'),
            array('title' => 'Blocked Drains', 'desc' => 'Unclogging drains quickly and efficiently.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Blocked+Drains'),
            array('title' => 'Sewer Lines', 'desc' => 'Replacement of collapsed sewer lines.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=Sewer+Lines'),
            array('title' => "Plumbing COC's", 'desc' => 'Certificates of Compliance for plumbing.', 'img' => 'https://placehold.co/400x300/0073aa/ffffff?text=COC')
        );

        $output = '<div class="kleyn-services-grid" id="kleyn-services-grid">';
        foreach ($services as $service) {
            $output .= '<div class="service-card">';
            $output .= '<div class="service-image" style="background-image: url(' . esc_url($service['img']) . ');"></div>';
            $output .= '<div class="service-content">';
            $output .= '<h4>' . esc_html($service['title']) . '</h4>';
            $output .= '<p>' . esc_html($service['desc']) . '</p>';
            $output .= '</div>'; // .service-content
            $output .= '</div>'; // .service-card
        }
        $output .= '</div>';
        return $output;
    }

    public function add_settings_page()
    {
        add_options_page('AI Scheduler Settings', 'AI Scheduler', 'manage_options', 'ai-scheduler-settings', array($this, 'render_settings_page'));
    }

    public function register_settings()
    {
        register_setting('ai-scheduler-settings-group', 'ai_scheduler_api_key');
    }

    public function render_settings_page()
    {
        ?>
        <div class="wrap">
            <h1>AI Scheduler Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('ai-scheduler-settings-group'); ?>
                <?php do_settings_sections('ai-scheduler-settings-group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">OpenAI API Key</th>
                        <td><input type="text" name="ai_scheduler_api_key"
                                value="<?php echo esc_attr(get_option('ai_scheduler_api_key')); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

new AI_Scheduler();
