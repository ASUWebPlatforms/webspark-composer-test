import React,{ useState} from 'react'
//import Form from "./components/Form";
import MainForm from './components/MainForm';
import "./App.css";
import { DataProvider } from './components/DrupalSettings';

function App() {
  return (
    <div>
      <DataProvider>
        <MainForm />
      </DataProvider>
      
    </div>
  )
}

export default App
