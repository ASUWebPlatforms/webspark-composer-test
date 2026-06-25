import React from 'react';
import isMobile from '../mobile'
function InfoText() {

    const containerStyle = {
      display: "flex",
      justifyContent: "center",
      alignItems: "center",
      fontSize: 20,
      height: 'auto',
      position: 'relative'
    }
  
    return (
      <div id="info" className={isMobile()}>
        <div style={containerStyle}>
          {/* <p className="text webcamtext" style={{margin: '2.5%' }}>Campus webcams are here to give you a real-time view of Arizona State University campuses.</p> */}
          <h2>Campus webcams are here to give you a real-time view of Arizona State University campuses.</h2>
        </div>
      </div>
    );
}

export default InfoText;