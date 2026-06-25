import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App'; // Use App for routing and main logic
import './index.css'; // Import global styles if needed

// Find the root element and render the App
const root = ReactDOM.createRoot(document.getElementById('visit-react-root'));
root.render(<App />);