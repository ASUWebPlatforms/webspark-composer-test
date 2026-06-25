import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';

const element = document.getElementById('react-cost-comparison');
if (element) {
  createRoot(element).render(<App />);
}
