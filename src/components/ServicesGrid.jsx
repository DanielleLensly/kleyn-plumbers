import React from 'react';
import './ServicesGrid.css';

const services = [
  { title: 'Emergency Plumbing', desc: '24/7 fast response for critical issues.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Emergency' },
  { title: 'Drainage', desc: 'Professional drain and sewer cleaning.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Drainage' },
  { title: 'Pipe/Leak Detection', desc: 'Expert leak detection and pipe repair.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Leak+Detection' },
  { title: 'Pressure Release Valves', desc: 'Installation and maintenance of PRVs.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Valves' },
  { title: 'Burst Water Pipes', desc: 'Quick repairs for burst pipes to prevent damage.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Burst+Pipes' },
  { title: 'Burst Geysers', desc: 'Geyser replacements and component fixes.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Geysers' },
  { title: 'Geyser Installation', desc: 'New geyser installations and upgrades.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Installation' },
  { title: 'Plumbing Repairs', desc: 'General maintenance and tap washers.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Repairs' },
  { title: 'Residential Plumbing', desc: 'Home plumbing solutions for your family.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Residential' },
  { title: 'New Bathroom Builds', desc: 'Complete plumbing for new bathrooms.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=New+Bathroom' },
  { title: 'Bathroom Renovations', desc: 'Modernize your bathroom with our help.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Renovations' },
  { title: 'Blocked Drains', desc: 'Unclogging drains quickly and efficiently.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Blocked+Drains' },
  { title: 'Sewer Lines', desc: 'Replacement of collapsed sewer lines.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=Sewer+Lines' },
  { title: "Plumbing COC's", desc: 'Certificates of Compliance for plumbing.', img: 'https://placehold.co/400x300/0073aa/ffffff?text=COC' }
];

const ServicesGrid = () => {
  return (
    <div className="kleyn-services-grid">
      {services.map((service, index) => (
        <div key={index} className="service-card">
          <div 
            className="service-image" 
            style={{ backgroundImage: `url(${service.img})` }}
            aria-label={service.title}
          ></div>
          <div className="service-content">
            <h4>{service.title}</h4>
            <p>{service.desc}</p>
          </div>
        </div>
      ))}
    </div>
  );
};

export default ServicesGrid;
