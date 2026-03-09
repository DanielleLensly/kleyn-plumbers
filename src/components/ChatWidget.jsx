import React, { useState, useRef, useEffect } from 'react';
import { FaWhatsapp, FaCalendarAlt, FaExpandAlt, FaCompressAlt, FaTimes, FaCommentDots } from 'react-icons/fa';

const WHATSAPP_URL = 'https://wa.me/27767264010';
import './ChatWidget.css';

const SYSTEM_PROMPT = `You are Rocky, a friendly and knowledgeable assistant for Kleyn Plumbers. 
Help customers with plumbing questions, advice, and appointment bookings. 
Keep responses concise and helpful. If someone wants to book, let them know they can use the "Book Appointment" button.`;

// Cloudflare Worker proxy URL — keeps the OpenAI API key server-side
const WORKER_URL = 'https://rocky-chat.danielle-lensly93.workers.dev';

const ChatWidget = ({ onOpenBooking }) => {
  const [isOpen, setIsOpen] = useState(false);
  const [isExpanded, setIsExpanded] = useState(false);
  const [view, setView] = useState('selection'); // 'selection' | 'chat'
  const [messages, setMessages] = useState([
    { role: 'bot', text: 'Hello! I can assist you with booking a plumbing appointment. How can I help you today?' }
  ]);
  const [inputValue, setInputValue] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const messagesEndRef = useRef(null);

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages, isLoading]);

  const toggleChat = () => setIsOpen(!isOpen);
  const closeChat = () => { setIsOpen(false); setIsExpanded(false); };
  const goBack = () => setView('selection');
  const toggleExpand = () => setIsExpanded(prev => !prev);

  const sendMessage = async (e) => {
    e.preventDefault();
    if (!inputValue.trim() || isLoading) return;

    const userText = inputValue.trim();
    const updatedMessages = [...messages, { role: 'user', text: userText }];
    setMessages(updatedMessages);
    setInputValue('');
    setIsLoading(true);

    try {
      const apiMessages = [
        { role: 'system', content: SYSTEM_PROMPT },
        ...updatedMessages.map(m => ({
          role: m.role === 'bot' ? 'assistant' : 'user',
          content: m.text,
        })),
      ];

      const response = await fetch(WORKER_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ messages: apiMessages }),
      });

      const data = await response.json();
      console.log('Worker response:', data); // debug
      const botReply = data.choices?.[0]?.message?.content;
      if (botReply) {
        setMessages(prev => [...prev, { role: 'bot', text: botReply }]);
      } else {
        console.error('Unexpected response:', JSON.stringify(data));
        setMessages(prev => [...prev, { role: 'bot', text: `Error: ${data.error?.message || 'Unknown error from API'}` }]);
      }
    } catch (err) {
      console.error('Fetch error:', err);
      setMessages(prev => [...prev, { role: 'bot', text: 'Sorry, something went wrong. Please try again.' }]);

    } finally {
      setIsLoading(false);
    }
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
        <div className={`ai-chat-widget${isExpanded ? ' expanded' : ''}`}>
          <div className="chat-header">
            {view === 'chat' && (
              <button className="back-btn" onClick={goBack}>&larr;</button>
            )}
            <h3 className="chat-title">How can we help?</h3>
            <div className="header-actions">
              <button className="header-btn" onClick={toggleExpand} title={isExpanded ? 'Shrink Chat' : 'Expand Chat'}>
                {isExpanded ? <FaCompressAlt /> : <FaExpandAlt />}
              </button>
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
                {isLoading && (
                  <div className="message bot-message typing-indicator">
                    Rocky is typing<span>.</span><span>.</span><span>.</span>
                  </div>
                )}
                <div ref={messagesEndRef} />
              </div>
              <div className="chat-quick-actions">
                <button className="quick-action-btn" onClick={() => { onOpenBooking(); setIsOpen(false); }}>
                  <FaCalendarAlt /> Book Appointment
                </button>
                <a className="quick-action-btn whatsapp" href={WHATSAPP_URL} target="_blank" rel="noopener noreferrer">
                  <FaWhatsapp /> Contact Rocky
                </a>
              </div>
              <form className="chat-input-area" onSubmit={sendMessage}>
                <input 
                  type="text" 
                  value={inputValue}
                  onChange={(e) => setInputValue(e.target.value)}
                  placeholder="Type your message..."
                  disabled={isLoading}
                />
                <button type="submit" className="send-btn" disabled={isLoading}>Send</button>
              </form>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default ChatWidget;
