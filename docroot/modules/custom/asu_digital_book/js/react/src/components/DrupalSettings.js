// DataContext.js
import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

const DataContext = createContext();

export const useData = () => {
  return useContext(DataContext);
};

export const DataProvider = ({ children }) => {
  const [data, setData] = useState({});
  const baseUrl = window.location.origin;
  let localWebUrl = '';
  if (baseUrl === "http://localhost:8080") {
    localWebUrl = 'http://localhost:8080/archanavtest/web';
  } else {
    localWebUrl = baseUrl;
  }

  useEffect(() => {
    const apiUrl = `${localWebUrl}/api/settings`;
    axios.get(apiUrl)
      .then(response => {
        setData(response.data);
      })
      .catch(error => {
        console.error('There was an error fetching the settings!', error);
      });
  }, [localWebUrl]);

  return (
    <DataContext.Provider value={data}>
      {children}
    </DataContext.Provider>
  );
};
