// src/App.js
import { useState, useRef } from 'react';
import LoanForm from './components/loanForm';
import LoanResultsTable from './components/LoanResultsTable';

const LoanApp = () => {
  const [submittedValues, setSubmittedValues] = useState(null);
  const resultsRef = useRef(null);

  const handleFormSubmit = (values) => {
    setSubmittedValues(values);
  };

  const handleResultsLoaded = () => {
    if (resultsRef.current) {
      resultsRef.current.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  };

  return (
    <div className="app-container">
      <h1>Loan Proration Tool</h1>
      <LoanForm onSubmit={handleFormSubmit} />
      {submittedValues && (
        <div ref={resultsRef}>
          <LoanResultsTable
            formValues={submittedValues}
            onLoaded={handleResultsLoaded}
          />
        </div>
      )}
    </div>
  );
};

export default LoanApp;
