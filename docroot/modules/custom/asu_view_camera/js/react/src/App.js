import React, {
  Component
} from 'react';

import ComingSoon from './components/ComingSoon/ComingSoon.js';
import VideoCamera from './components/Video/VideoCamera.js';
import data from './config/config.json';
//import Main from './Main.js';
import {
  Route,
  useParams,
  Routes
} from "react-router-dom";

import './App.css';

const Main = (props) => {
  
  var videoRefElement = React.createRef()
  var streamCam, heading = ""
  let { campus,id } = useParams();
  
  if(campus === undefined && id === undefined){
    heading = Object.keys(props.camOptions)[0]
    streamCam = data['cam_mapping'][data['cam_options'][Object.keys(data['cam_options'])[0]][0]]
  }else if(campus !== undefined && id === undefined){
    heading = campus;
    streamCam = data['cam_mapping'][data['cam_options'][heading][0]];
  }else{
    heading = campus; 
    streamCam = id;
  }
  
  const callbackFunction = (childData) => {
    const callBackstream = data['cam_mapping'][data['cam_options'][childData][0]]
    videoRefElement.current.changeCampus(callBackstream,childData);
  }

  return ( 
    <div>
      <div id = "vidContainer">
          <VideoCamera  ref={videoRefElement}
                source = {props.source}
                stream = {streamCam}
                resolution = {props.resolution}
                camOptions = {props.camOptions}
                heading = {heading} />   
      </div>
      <ComingSoon hide={campus} parentCallBack={callbackFunction}/>
    </div>
  );
}

class App extends Component {

  componentDidMount() {
    console.log("App component mounted");
  }

  render() {
    return ( 
      <div style = {{overflow: 'hidden'}}>
       <Routes>
          <Route
              path="/:campus?/:id?"
              element={
                <Main
                  source={data['source']}
                  stream={data['cam_mapping'][data['cam_options'][Object.keys(data['cam_options'])[0]][0]]}
                  resolution='4k'
                  camOptions={data['cam_options']}
                />
              }
            />
        </Routes>
      
      </div>
    );
  }
}

export default App;