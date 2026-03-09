import React from 'react';
import './Header.css';

const Header = ({ onOpenBooking }) => {
  return (
    <header className="site-header">
      <div className="container header-content">
        <div className="logo-area">
          <h1>Kleyn Plumbers</h1>
        </div>
        <nav className="main-nav">
          <a href="#services">Services</a>
          <a href="#about">About</a>
          <button onClick={onOpenBooking} className="btn btn-primary" style={{cursor: 'pointer'}}>Book Now</button>
        </nav>
      </div>
    </header>
  );
};

export default Header;
