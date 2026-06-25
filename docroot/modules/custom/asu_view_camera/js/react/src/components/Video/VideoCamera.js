import React, {Component} from 'react';
import Heading from '../../components/Heading/Heading.js';
import './video.css';
import ReactDOM from 'react-dom';
import ReactPlayer from 'react-player'
import screenfull from 'screenfull'
import {isMobile, isMobileSafari} from 'react-device-detect';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faExpandArrowsAlt, faTimes } from '@fortawesome/free-solid-svg-icons'
import data from '../../config/config.json';
import axios from 'axios';
import Loader from 'react-loader-spinner';
import { Link } from 'react-router-dom';

//import { BallTriangle } from 'react-loader-spinner';
//import 'react-loader-spinner/dist/loader/css/react-spinner-loader.css'; 

class VideoCamera extends Component {
    constructor(props) {
        super(props);
        this.playerRef = React.createRef();
        // initial state
        this.state = {
            loading: false, 
            stream: props.stream, 
            resolution: props.resolution, 
            url: props.camURL, 
            heading: props.heading,
            
        };
        //this.detectSpeed();
    }

   

    detectSpeed(){
        var xhr = new XMLHttpRequest();
        var startTime, endTime, fileSize;
        var self = this;
        console.log(xhr.readyState);
        // Rig the call-back... THE important part
         xhr.onreadystatechange = function () {

            // we only need to know when the request has completed
            if (xhr.readyState === 4 && xhr.status === 200) {
  
                // Here we stop the timer & register end time
                endTime = (new Date()).getTime();
  
                // Also, calculate the file-size which has transferred
                fileSize = xhr.responseText.length;
                // N.B: fileSize reports number of Bytes
  
                // Calculate the connection-speed
                var speed = (fileSize * 8) / ((endTime - startTime)/1000) / 1024;
                // Use (fileSize * 8) instead of fileSize for kbps instead of kBps
  
                // Report the result, or have fries with it...
                console.log(speed + " kbps\n");
                var newResolution = "";

               
                if(speed > 0 && speed < 500)
                    newResolution = "360p";
                else if(speed >= 500 && speed < 2000)
                    newResolution = "480p";     
                else if(speed >= 2000 && speed < 3000)
                    newResolution = "720p";
                else 
                    newResolution = "4k";

                if(self.state.resolution !== newResolution){
                    //console.log("Need to call new stream")
                    self.setState({ 
                        resolution: newResolution 
                    }, () => {
                        self.setupVideoFunction(self.props.source, self.state.stream);
                    });
                }else{
                    //console.log("Continue")
                }
            }
        }

        // Snap back; here's where we start the timer
        startTime = (new Date()).getTime();
        xhr.open("GET", data['bandwidth_src'] , true);
        xhr.send(); 
    }

    changeCampus(newStream, newHeading){
        this.setupVideoFunction(this.props.source, newStream);
        this.setState({ heading: newHeading, stream: newStream },() => {
            this.highlightLink(newStream)
        });
    }

    setupVideoFunction(source, stream) {
        var self = this;
        if(!isMobile){
            self.setState({loading: false});
        }
        var resol = this.state.resolution;
        const query = "?".concat("stream=",stream,"&resolution=",resol)
        //console.log("Query ",source.concat(query));
        source = source.concat(query);
        //console.log(source);
        axios({
            url: source,
            method: 'get'
          })
          .then(response => {
            const camURL = response.data
            //console.log("Getting url: ",camURL);
            this.setState({url: camURL}, () => {
                //console.log("Setting url: ",this.state.url);
            });
            if(isMobileSafari){
                var video = document.getElementById('my-video');
                var source = document.getElementById('video-src');
               
                 setTimeout(function() {  
                    video.pause();
                    source.setAttribute('src', camURL);
                    video.load();
                    self.setState({loading: true}); 
                    video.play(); 
                }, 2000); 
                  
            }
          })
        

      }

      highlightLink(stream) {
        if(document.getElementById('options') !== null){
            var childDivs = document.getElementById('options').getElementsByTagName('p');
            for(var i=0; i<childDivs.length; i++ ){
                if(data['cam_mapping'][childDivs[i].innerHTML] === stream){
                    // activate link
                    childDivs[i].style.color = '#8c1d40';
                }
            }
        }
    }

    

    componentDidMount() {
            this.setupVideoFunction(this.props.source, this.state.stream);
    
            if(isMobile){
                this.setState({ 
                    controls: true
                })
            }
    
            if (screenfull.isEnabled) {
                screenfull.on('change', () => {
                    if(screenfull.isFullscreen){
                        this.setState({ showCloseButton: true });
                    } else {
                        this.setState({ showCloseButton: false });
                    }
                });
            }
    
            this.videoResetInterval = setInterval(() => {
               //"Calling to reset with current stream ",this.state.stream);
                this.setupVideoFunction(this.props.source, this.state.stream);
            }, 240000);
    
            this.detectSpeedInterval = setInterval(() => {
                //this.detectSpeed()
            }, 10000);
    
            this.highlightLink(this.state.stream);
    }

   

    componentWillUnmount() {
        clearInterval(this.videoResetInterval);
        clearInterval(this.detectSpeedInterval);
    }

    handleError = () => {
        console.log("Error in hls stream");
    }

    playerRef = player => {
        this.player = player
    }

    /*handleClickFullscreen = () => {
        if(!isMobile)
            //screenfull.toggle(myref(this.player));
        screenfull.toggle(this.playerRef.current); 
    }*/

    handleClickFullscreen = () => {
        const playerElement = this.playerRef.current;
       // console.log(playerElement);
        if (screenfull.isEnabled && playerElement) {
          if (!screenfull.isFullscreen) {
            playerElement.requestFullscreen();
          } else {
            screenfull.exit();
          }
        }
    };


    onReady = () => {
        if(!isMobile){
            this.setState({ playing: true, showExpanded: true, loading: false });
        } else {
            this.setState({ playing: true });
        }
    }

   
    changeLink = (e) => {
        var childDivs = document.getElementById('options').getElementsByTagName('p');
        
        for(var i=0; i< childDivs.length; i++ ){
            
            var childDiv = childDivs[i];
            
            if(childDiv.innerHTML !== e.target.innerHTML){
                // deactivate link
                childDiv.style.color = '#000000';
            }else{
                // activate link
                childDiv.style.color = '#8c1d40';
            }
        }
        this.setState({ stream: data['cam_mapping'][e.target.innerHTML] }, () => {
            console.log("Changed stream: ",this.state.stream)
            this.setupVideoFunction(this.props.source, this.state.stream)
        });
    }
    
    
    

    render () {
        const { url, controls, playing, light, loading, volume, showExpanded, showCloseButton, heading } = this.state

        const videoStyle = {
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
            paddingTop: 25
        };

        const spinnerStyle = {
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center'
        }

        const optionContainer = {
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
            paddingBottom: 20
        };

        const optionStyle = {
            textDecorationLine: 'underline',
            marginLeft: '0.5vw',
            marginRight: '.5vw',
            fontWeight: 'bold',
            cursor: 'pointer'
        };
            
        const containerStyle = {
            backgroundColor: '#f1f1f1'
        }

        const inlineStyle = {
            display: 'inline-block'
        }
    

        return(
       
            <div id="container" style={containerStyle}>
                <div style={videoStyle}>
                    <div id="video" className='video'>
                        <Heading text={heading}/> 
                        <section className='section'>
                       
                        {
                            isMobileSafari && 
                            <div id="my-video" 
                            width="100%" 
                            height="auto"
                            muted={true} 
                            playsInline={true}
                            controls>
                            <source id="video-src" src={url} type="application/x-mpegURL"/>
                            Your browser does not support HTML video.
                            </div>
                        }
                        {
                            loading &&
                            <Loader style={spinnerStyle}
                                type="ThreeDots"
                                color="#8c1d40"
                                height={50}
                                width={50}
                            />
                        }
                        {
                            !isMobileSafari && 
                            <div className='player-wrapper' ref={this.playerRef}>
                            { 
                                !isMobile && !playing && showExpanded && !showCloseButton &&
                                <div className='expandContainer'>
                                  <FontAwesomeIcon icon={faExpandArrowsAlt} className='expand' onClick={this.handleClickFullscreen}/>
                                   <span className='expand' onClick={this.handleClickFullscreen}>X</span> 
                                </div>
                            }
                            { 
                                !isMobile && showCloseButton &&
                                <div className='closeContainer'>
                                    <FontAwesomeIcon icon={faTimes} className='close' onClick={this.handleClickFullscreen}/>
                                </div>
                            }
                            
                            <ReactPlayer
                                className="react-player-style"
                                width='100%'
                                height='100%'
                                url={url}
                                playing={playing}
                                playsinline={true}
                                controls={controls}
                                light={light}
                                volume={volume}
                                muted={true}
                                onClick={this.handleClickFullscreen}
                                onReady={this.onReady}
                                onError={e => console.log('Error:',e)}
                            />
                            </div>
                        }
                     </section>
                        
                    </div>
                </div>
                {
                    heading === "tempe" && 
                    <div id='options' style={optionContainer}>
                    
                    {Object.keys(this.props.camOptions[heading]).map((key, i) => {
                            const path = `/${heading}/${data['cam_mapping'][this.props.camOptions[heading][key]]}`;
                            //console.log('path', path);
                            return (
                            <React.Fragment key={i}>
                                <Link to={path} style={{ textDecoration: 'none', color: 'black' }}>
                                <div style={inlineStyle}>
                                    <p className="text" style={optionStyle} onClick={e => this.changeLink(e)}>
                                    {this.props.camOptions[heading][key]}
                                    </p>
                                </div>
                                </Link>
                                {i !== Object.keys(this.props.camOptions[heading]).length - 1 && <div style={inlineStyle}>|</div>}
                            </React.Fragment>
                            );
                            })
                    }
                </div>
                } 
            </div>
        )
    }
  
}

export default VideoCamera;