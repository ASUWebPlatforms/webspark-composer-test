import React from 'react';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import CalendarPage from './components/CalendarPage'; // Form component

function App() {
  // const calendarPageUrl = "react-calendar";
  // const calendarPageUrls = ['react-calendar', 'node/279144/layout', 'test-calendar-page'];
  const drupalCalendarPaths =
    window.drupalSettings?.visitRevamp?.calendarPaths;

  // Fallback to a default if config is empty
  const calendarPageUrls = Array.isArray(drupalCalendarPaths) && drupalCalendarPaths.length
    ? drupalCalendarPaths
    : ['react-calendar'];

  return (
    <Router>
      <Routes>
        {calendarPageUrls.map((url) => (
          <Route
            key={url}
            path={`/${url}`}
            element={<CalendarPage />}
          />
        ))}

        <Route path="*" element={<h2>Page not found!</h2>} /> {/* Catch-all route */}
      </Routes>
    </Router>
  );
}

export default App;