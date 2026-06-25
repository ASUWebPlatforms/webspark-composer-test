import React, {Component} from 'react';
import {Link} from "react-router-dom";
import tempeJPG from '../../assets/images/tempe/tempe-campus.jpg';
import downtownJPG from '../../assets/images/downtown/downtown-campus.jpg';
import polytechnicJPG from '../../assets/images/polytechnic/polytechnic-campus.jpg';
import westJPG from '../../assets/images/west-campus/west-campus.jpg';
import Heading from '../../components/Heading/Heading.js';

class ComingSoon extends Component {

  constructor(props) {
    super(props);
    // initial state
    this.state = {
        showPoly: true,
        showTempe: false, 
        showDowntown: true, 
        showWest: true
    };
  }

  componentDidMount(){
    this.hideCampus(this.props.hide)
  }

  hideCampus(childData)  {
    if(childData === "tempe"){
      this.setState({ 
        showDowntown: true,
        showPoly: true,
        showTempe: false, 
        showWest: true
      })
      this.props.parentCallBack(childData)

    }else if(childData === "polytechnic"){
      this.setState({ 
        showDowntown: true,
        showPoly: false,
        showTempe: true, 
        showWest: true
      })
      this.props.parentCallBack(childData)
    }else if(childData === "downtown"){
      this.setState({ 
        showDowntown: false,
        showPoly: true,
        showTempe: true, 
        showWest: true
      })
      this.props.parentCallBack(childData)
    }else if(childData === "west"){
      this.setState({ 
        showDowntown: true,
        showPoly: true,
        showTempe: true, 
        showWest: false
      })
      this.props.parentCallBack(childData)
    }
  }

  callbackFunction = (childData) => {
    this.hideCampus(childData)
  }

  render(){

    const { showPoly, showTempe, showWest, showDowntown } = this.state

    const row = {
      display: "flex",
      justifyContent: "center",
      alignItems: "center",
    }
  
    const column = {
      flex: '33.33%',
      marginTop: 15,
      marginBottom: 0,
      textAlign: 'center'
    }
  
    const imgStyle = {
      marginTop: 20,
      width: '90%',
      cursor: 'pointer'
    }
    /*
    const header = {
      display: 'flex',
      justifyContent: 'center',
      alignItems: 'center'
    }
    */
    return (
    <div>
      <div style={row}>
      {
        showPoly && 
        <div style={column}>
          <Link to="/polytechnic" style={{ textDecoration: 'none', color: 'black' }}>
            <Heading text="polytechnic" parentCallBack={this.callbackFunction} />
          </Link>
          <Link to="/polytechnic">
          <picture>
            <img src={polytechnicJPG} onClick={() => this.hideCampus("polytechnic")} style={imgStyle} loading="lazy"/>
          </picture>
          </Link>

        </div>
      }
      {
        showTempe && 
        <div style={column}>
          <Link to="/tempe" style={{ textDecoration: 'none' , color: 'black'}}>
            <Heading text="tempe" parentCallBack={this.callbackFunction}/>
          </Link>
          <Link to="/tempe">
          <picture>
            <img src={tempeJPG} onClick={() => this.hideCampus("tempe")} style={imgStyle} loading="lazy"/>
          </picture>
          </Link>
        </div>
      }
      {
        showDowntown && 
        <div style={column}>
          <Link to="/downtown" style={{ textDecoration: 'none', color: 'black' }}>
            <Heading text="downtown" parentCallBack={this.callbackFunction} />
          </Link>
          <Link to="/downtown">
          <picture>
             <img src={downtownJPG} onClick={() => this.hideCampus("downtown")} style={imgStyle} loading="lazy"/>
          </picture>
          </Link>
        </div>
      }
      {
        showWest && 
        <div style={column}>
          <Link to="/west" style={{ textDecoration: 'none', color: 'black' }}>
            <Heading text="west" parentCallBack={this.callbackFunction} />
          </Link>
          <Link to="/west">
          <picture>
             <img src={westJPG} onClick={() => this.hideCampus("west")} style={imgStyle} loading="lazy"/>
          </picture>
          </Link>
        </div>
      }
      </div>
      <div style={column}>
         
      </div>
    </div>
    );
  }
}

export default ComingSoon;