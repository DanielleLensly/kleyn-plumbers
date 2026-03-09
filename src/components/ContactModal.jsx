import React, { useState } from 'react';
import './ContactModal.css';

const ContactModal = ({ isOpen, onClose }) => {
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    email: '',
    address: '',
    date: '',
    message: '',
    captcha: ''
  });

  if (!isOpen) return null;

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    alert('Booking request submitted!\nNote: In a real app, this would send an email or save to a database.');
    onClose();
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-content" onClick={e => e.stopPropagation()}>
        <button className="close-btn" onClick={onClose}>&times;</button>
        <h3 className="modal-title">Book an Appointment</h3>
        
        <form className="booking-form" onSubmit={handleSubmit}>
          <div className="form-group">
            <label>Name <span className="required">*</span></label>
            <input type="text" name="name" required placeholder="Your Name" value={formData.name} onChange={handleChange} />
          </div>
          
          <div className="form-group">
            <label>Cell Number <span className="required">*</span></label>
            <input type="tel" name="phone" required placeholder="082 123 4567" value={formData.phone} onChange={handleChange} />
          </div>
          
          <div className="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="email@example.com" value={formData.email} onChange={handleChange} />
          </div>
          
          <div className="form-group">
            <label>Preferred Date <span className="required">*</span></label>
            <input type="date" name="date" required value={formData.date} onChange={handleChange} min={new Date().toISOString().split('T')[0]} />
          </div>
          
          <div className="form-group full-width">
            <label>Address</label>
            <input type="text" name="address" placeholder="123 Street Name, Suburb" value={formData.address} onChange={handleChange} />
          </div>
          
          <div className="form-group full-width">
            <label>Message <span className="required">*</span></label>
            <textarea name="message" required placeholder="Describe your problem..." rows="4" value={formData.message} onChange={handleChange}></textarea>
          </div>
          
          <button type="submit" className="btn btn-primary submit-btn">Request Booking</button>
        </form>
      </div>
    </div>
  );
};

export default ContactModal;
