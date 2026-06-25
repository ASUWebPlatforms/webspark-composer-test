import React from 'react';

const ProgressBar = ({ currentStep, totalSteps }) => {
    const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
  
    return (
      <div style={{ width: '100%', backgroundColor: '#ccc', marginBottom: '20px' }}>
        <div
          style={{
            width: `${progressPercentage}%`,
            height: '10px',
            backgroundColor: '#4caf50',
          }}
        />
      </div>
    );
  };

  export default ProgressBar;