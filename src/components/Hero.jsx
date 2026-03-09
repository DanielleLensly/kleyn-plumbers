import React from 'react';
import './Hero.css';

const Hero = () => {
  return (
    <section className="hero-section">
      <div className="container">
        <div className="hero-content">
          <h2>Expert Plumbing Services</h2>
          <p>Available 24/7 for Emergencies in your area.</p>
          <a href="#services" className="btn btn-primary" style={{ background: 'white', color: 'var(--primary)' }}>Our Services</a>
        </div>
      </div>
    </section>
  );
};

export default Hero;
