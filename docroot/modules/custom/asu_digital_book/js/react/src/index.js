import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import App from './App';
import { createRoot } from 'react-dom/client';
import { BrowserRouter as Router } from 'react-router-dom';

const rootElement = document.getElementById("digital-viewbook-div");
const root = createRoot(rootElement);
//ReactDOM.render(<App />, rootElement);
root.render(  
    <Router>
        <App />
    </Router>
  );