jQuery(document).ready(function ($) {
    console.log('AI Scheduler Chat JS loaded');

    const chatContainer = $('#chat-messages');
    const inputField = $('#user-input');
    const sendBtn = $('#send-btn');
    let messageHistory = [];

    function addMessage(text, isUser) {
        const className = isUser ? 'user-message' : 'bot-message';
        const msgDiv = $('<div class="message ' + className + '"></div>').text(text);

        // Append Buttons for Bot Messages
        if (!isUser) {
            // Book Appointment Button (Blue)
            const bookLink = $('<button class="book-rocky-btn">Book Appointment with Rocky</button>');
            bookLink.on('click', function () {
                window.aiSchedulerOpenBooking();
            });

            // WhatsApp Button (Green)
            const contactLink = $('<a href="https://wa.me/+27631510081" target="_blank" class="contact-rocky-btn">Contact Rocky on WhatsApp</a>');

            msgDiv.append('<br>').append(bookLink).append(contactLink);
        }

        chatContainer.append(msgDiv);
        chatContainer.scrollTop(chatContainer[0].scrollHeight);

        // Add to history (simplified for now)
        messageHistory.push({
            role: isUser ? 'user' : 'assistant',
            content: text
        });
    }

    // Toggle Logic (Moved outside sendMessage)
    const widget = $('#ai-scheduler-widget');
    const toggleBtn = $('#ai-chat-toggle');
    const helpMsg = $('#ai-help-message');
    const closeBtn = $('#close-chat-btn');
    const expandBtn = $('#expand-chat-btn');

    console.log('Widget found:', widget.length > 0);
    console.log('Toggle button found:', toggleBtn.length > 0);
    console.log('Help message found:', helpMsg.length > 0);

    function openChat() {
        console.log('openChat called');
        widget.fadeIn();
        toggleBtn.fadeOut();
        helpMsg.fadeOut();
        inputField.focus();
    }

    function closeChat() {
        console.log('closeChat called');
        widget.fadeOut();
        toggleBtn.fadeIn();
        helpMsg.fadeIn();
    }

    // Toggle Expand
    expandBtn.on('click', function () {
        widget.toggleClass('expanded');
        const icon = $(this).find('span');
        if (widget.hasClass('expanded')) {
            icon.removeClass('dashicons-editor-expand').addClass('dashicons-editor-contract');
        } else {
            icon.removeClass('dashicons-editor-contract').addClass('dashicons-editor-expand');
        }
    });

    toggleBtn.on('click', function () {
        console.log('Toggle button clicked');
        openChat();
    });
    helpMsg.on('click', function () {
        console.log('Help message clicked');
        openChat();
    });
    closeBtn.on('click', closeChat);

    function sendMessage() {
        const text = inputField.val().trim();
        if (!text) return;

        addMessage(text, true);
        inputField.val('');
        inputField.prop('disabled', true);
        sendBtn.prop('disabled', true);

        // Existing Chat Logic
        $.ajax({
            url: aiScheduler.apiUrl,
            method: 'POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', aiScheduler.nonce);
            },
            data: {
                message: text,
                history: messageHistory.slice(-5) // Send last 5 messages for context
            },
            success: function (response) {
                addMessage(response.reply, false);
            },
            error: function () {
                addMessage("Error connecting to server. Please try again.", false);
            },
            complete: function () {
                inputField.prop('disabled', false);
                sendBtn.prop('disabled', false);
                inputField.focus();
            }
        });
    }

    sendBtn.on('click', sendMessage);
    inputField.on('keypress', function (e) {
        if (e.which === 13) sendMessage();
    });

    // View Elements
    const selectionView = $('#selection-view');
    const chatView = $('#chat-view');
    const bookingView = $('#booking-view');
    const backBtn = $('#back-to-selection-btn');
    const chatTitle = $('#chat-title');

    // Button Elements
    const btnAskQuestion = $('#btn-ask-question');
    const btnBookAppointment = $('#btn-book-appointment');

    // Selection Logic
    btnAskQuestion.on('click', function () {
        // Show Chat View
        selectionView.hide();
        chatView.show();
        backBtn.show();
        chatTitle.text('Ask Rocky');
    });

    // Captcha Logic
    let captchaSum = 0;
    function generateCaptcha() {
        const num1 = Math.floor(Math.random() * 10) + 1;
        const num2 = Math.floor(Math.random() * 10) + 1;
        captchaSum = num1 + num2;
        $('#captcha-label').html(`Security Check: ${num1} + ${num2} = ? <span class="required-label">(Required)</span>`);
        $('#booking-captcha').val('');
    }

    // --- MODAL LOGIC ---
    const bookingModal = $('#ai-booking-modal');
    const closeModalBtn = $('.ai-close-modal');

    // Open Modal Internal Function
    function openModal() {
        console.log('Opening booking modal');
        bookingModal.show(); // Use show() to guarantee visibility immediately
        bookingModal.css('display', 'flex'); // Ensure flex for centering
        bookingModal.addClass('active');
        generateCaptcha();
    }

    // Close Modal Logic
    function closeModal() {
        bookingModal.hide();
        bookingModal.removeClass('active');
    }

    closeModalBtn.on('click', closeModal);

    $(window).on('click', function (event) {
        if ($(event.target).is(bookingModal)) {
            closeModal();
        }
    });

    // Update 'Book Appointment' button in widget to open modal
    btnBookAppointment.on('click', function () {
        openModal();
    });

    // Handle Form Submission (Updated for Modal)
    $('#booking-form').on('submit', function (e) {
        e.preventDefault();

        const submitBtn = $('.submit-booking-btn');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Processing...');

        // Verify Captcha
        const captchaInput = parseInt($('#booking-captcha').val());
        if (captchaInput !== captchaSum) {
            alert('Incorrect security answer. Please try again.');
            submitBtn.prop('disabled', false).text(originalBtnText);
            return;
        }

        const name = $('#booking-name').val();
        const phone = $('#booking-phone').val();
        const email = $('#booking-email').val();
        const address = $('#booking-address').val();
        const date = $('#booking-date').val();
        const message = $('#booking-message').val();

        $.ajax({
            url: aiScheduler.bookingUrl,
            method: 'POST',
            data: {
                name: name,
                phone: phone,
                message: message,
                date: date,
                email: email,
                address: address
            },
            success: function (response) {
                closeModal();
                $('#booking-form')[0].reset();

                // Open Chat and show success message
                widget.fadeIn();
                toggleBtn.fadeOut();
                helpMsg.fadeOut();

                // Switch to chat view
                selectionView.hide();
                chatView.show();
                chatTitle.text('Booking Confirmed');

                // Simulate Bot Response
                addMessage(response.reply || "Booking confirmed! Check your email.", false);
            },
            error: function () {
                alert("Error processing your booking. Please try again or contact us directly.");
            },
            complete: function () {
                submitBtn.prop('disabled', false).text(originalBtnText);
            }
        });
    });

    // Expose function to open booking directly
    window.aiSchedulerOpenBooking = function () {
        openModal();
    };



    // Move Services Grid to Elementor Section (if exists)
    const newGrid = document.getElementById('kleyn-services-grid');
    const oldSection = document.querySelector('.elementor-element-0386009'); // The 3-column section

    if (newGrid && oldSection) {
        // Clear old content and append new grid
        oldSection.innerHTML = '';
        oldSection.appendChild(newGrid);

        // Ensure container is visible and clean
        oldSection.style.display = 'block';
        oldSection.style.padding = '20px';
    }
    // Back Button Logic
    backBtn.on('click', function () {
        chatView.hide();
        bookingView.hide();
        selectionView.show();
        backBtn.hide();
        chatTitle.text('How can we help?');
    });

});
