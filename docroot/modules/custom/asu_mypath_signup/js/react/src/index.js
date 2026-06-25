import React, { useState } from 'react';
import ReactDOM from 'react-dom';
//import App from './App';
import { createRoot } from 'react-dom/client';

const App = React.lazy(() => import('./App'));

const rootElement = document.getElementById("my-app-target");
const root = createRoot(rootElement);
//ReactDOM.render(<App />, rootElement);
root.render(<App />);