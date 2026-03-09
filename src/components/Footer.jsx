import React from 'react';
import './Footer.css';

const Footer = () => {
  return (
    <footer className="site-footer">
      <div className="container">
        <p>&copy; {new Date().getFullYear()} Kleyn Plumbers. All rights reserved.</p>
      </div>
    </footer>
  );
};

export default Footer;
