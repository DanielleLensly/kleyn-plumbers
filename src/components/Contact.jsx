import React from 'react';
import './Contact.css';

const Contact = ({ onOpenBooking }) => {
  return (
    <section id="contact" className="contact-section">
      <div className="container">
        <div className="contact-container">
          <div className="contact-info">
              <h2 className="contact-heading">Contact Us</h2>
              <p className="contact-detail">Phone: 076 726 4010</p>
              <p className="contact-detail">Email: rocky@kleynplumbers.co.za</p>
              <p className="contact-detail">Available 24/7 for Emergencies</p>
              <button 
                onClick={onOpenBooking} 
                className="btn btn-primary" 
                style={{marginTop: '2rem', background: 'white', color: 'var(--primary)'}}
              >
                Book an Appointment
              </button>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Contact;
