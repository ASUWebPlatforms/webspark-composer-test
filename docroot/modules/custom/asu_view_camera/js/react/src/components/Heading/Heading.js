import React from 'react'

const Heading = (props) => {

    const headerStyle = {
        backgroundColor: "#ffc627",
        width: "fit-content",
        padding: '0.2vw'
    }

    const test = {
        position: 'relative',
        display: 'flex',
        justifyContent: 'center',
        fontWeight: 'bold',
        cursor: 'pointer'
    }

    const changeStream = (e) => {
        props.parentCallBack(e.target.innerHTML)
    }

    return (
        <div className="text headingText" style={test}>
            <div style={headerStyle}  onClick={e => changeStream(e)}>{props.text}</div>
        </div>  
    )
}

export default Heading;