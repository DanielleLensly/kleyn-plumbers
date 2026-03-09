import React, { useState } from 'react';
import { FaWhatsapp, FaCalendarAlt, FaExpandAlt, FaTimes, FaCommentDots } from 'react-icons/fa';
import './ChatWidget.css';

const ChatWidget = ({ onOpenBooking }) => {
  const [isOpen, setIsOpen] = useState(false);
  const [view, setView] = useState('selection'); // 'selection' | 'chat'
  const [messages, setMessages] = useState([
    { role: 'bot', text: 'Hello! I can assist you with booking a plumbing appointment. How can I help you today?' }
  ]);
  const [inputValue, setInputValue] = useState('');

  const toggleChat = () => setIsOpen(!isOpen);
  const closeChat = () => setIsOpen(false);
  const goBack = () => setView('selection');

  const sendMessage = (e) => {
    e.preventDefault();
    if (!inputValue.trim()) return;

    setMessages([...messages, { role: 'user', text: inputValue }]);
    setInputValue('');
    
    // Simulate AI response for now
    setTimeout(() => {
      setMessages(prev => [...prev, { role: 'bot', text: 'I am a demo AI. In a real environment, I would connect to the backend API now.' }]);
    }, 1000);
  };

  return (
    <div className="ai-scheduler-container">
      {!isOpen && (
        <>
          <div className="ai-help-message" onClick={toggleChat}>
            Need to make a booking? 👋
          </div>
          <div className="ai-chat-toggle" onClick={toggleChat}>
            <FaCommentDots />
          </div>
        </>
      )}

      {isOpen && (
        <div className="ai-chat-widget">
          <div className="chat-header">
            {view === 'chat' && (
              <button className="back-btn" onClick={goBack}>&larr;</button>
            )}
            <h3 className="chat-title">How can we help?</h3>
            <div className="header-actions">
              <button className="header-btn" title="Expand Chat"><FaExpandAlt /></button>
              <button className="header-btn" onClick={closeChat} title="Close"><FaTimes /></button>
            </div>
          </div>

          {view === 'selection' && (
            <div className="selection-view">
              <div className="selection-options">
                <button className="selection-btn" onClick={() => setView('chat')}>
                  <FaWhatsapp className="icon" />
                  Ask Rocky a Question
                </button>
                <button className="selection-btn" onClick={() => { onOpenBooking(); setIsOpen(false); }}>
                  <FaCalendarAlt className="icon" />
                  Book Appointment
                </button>
              </div>
            </div>
          )}

          {view === 'chat' && (
            <div className="chat-view">
              <div className="chat-messages">
                {messages.map((msg, idx) => (
                  <div key={idx} className={`message ${msg.role}-message`}>
                    {msg.text}
                  </div>
                ))}
              </div>
              <form className="chat-input-area" onSubmit={sendMessage}>
                <input 
                  type="text" 
                  value={inputValue}
                  onChange={(e) => setInputValue(e.target.value)}
                  placeholder="Type your message..." 
                />
                <button type="submit" className="send-btn">Send</button>
              </form>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default ChatWidget;
