import React, { useState } from 'react';
import './App.css';
import Header from './components/Header';
import Hero from './components/Hero';
import About from './components/About';
import ServicesGrid from './components/ServicesGrid';
import Contact from './components/Contact';
import Footer from './components/Footer';
import ChatWidget from './components/ChatWidget';
import ContactModal from './components/ContactModal';

function App() {
  const [isBookingOpen, setIsBookingOpen] = useState(false);

  const openBooking = () => setIsBookingOpen(true);
  const closeBooking = () => setIsBookingOpen(false);

  return (
    <div className="app-container">
      <Header onOpenBooking={openBooking} />

      <main>
        <Hero />
        <About />
        
        <section id="services" className="services-section">
          <div className="container">
            <h2 className="section-title">Our Services</h2>
            <ServicesGrid />
          </div>
        </section>
        
        <Contact onOpenBooking={openBooking} />
      </main>

      <Footer />
      
      {/* Floating Chat Widget */}
      <ChatWidget onOpenBooking={openBooking} />

      {/* Booking Modal */}
      <ContactModal isOpen={isBookingOpen} onClose={closeBooking} />
    </div>
  );
}

export default App;
