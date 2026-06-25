import './index.css';

import { BrowserRouter } from "react-router-dom";

import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';
import { createRoot } from 'react-dom/client';
import * as serviceWorker from './serviceWorker';

const rootElement = document.getElementById("camera-div");
const root = createRoot(rootElement);
root.render(
  <BrowserRouter>
  <App />
  </BrowserRouter>
);



// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: https://bit.ly/CRA-PWA
serviceWorker.unregister();
