import React from 'react';
import './Contact.css';

// Email split to prevent bot scraping
const emailUser = 'danielle.lensly93';
const emailDomain = 'gmail.com';

const Contact = ({ onOpenBooking }) => {
  const email = `${emailUser}@${emailDomain}`;
  return (
    <section id="contact" className="contact-section">
      <div className="container">
        <div className="contact-container">
          <div className="contact-info">
              <h2 className="contact-heading">Contact Us</h2>
              <p className="contact-detail">Phone: 063 151 0081</p>
              <p className="contact-detail">Email: <a href={`mailto:${email}`} style={{color:'inherit'}}>{email}</a></p>
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
