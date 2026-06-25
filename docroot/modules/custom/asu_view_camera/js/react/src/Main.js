import React, {Component} from 'react';
import ComingSoon from './components/ComingSoon/ComingSoon.js';
import Heading from './components/Heading/Heading.js';
import data from './config/config.json';
import VideoCamera from './components/Video/VideoCamera.js';
import {
 useParams,
  
} from "react-router-dom";

const Main = (props) => {
  const videoRefElement = useRef(null);
  const { campus, id } = useParams();
  let heading = "";
  let streamCam = "";

  if (campus === undefined && id === undefined) {
    heading = Object.keys(props.camOptions)[0];
    streamCam = data['cam_mapping'][data['cam_options'][Object.keys(data['cam_options'])[0]][0]];
  } else if (campus !== undefined && id === undefined) {
    heading = campus;
    streamCam = data['cam_mapping'][data['cam_options'][heading][0]];
  } else {
    heading = campus;
    streamCam = id;
  }

  const callbackFunction = (childData) => {
    const callBackstream = data['cam_mapping'][data['cam_options'][childData][0]];
    videoRefElement.current.changeCampus(callBackstream, childData);
  }

  return (
    <div>
      <div id="vidContainer">
        <VideoCamera
          ref={videoRefElement}
          source={props.source}
          stream={streamCam}
          resolution={props.resolution}
          camOptions={props.camOptions}
          heading={heading}
        />
      </div>
      <ComingSoon hide={campus} parentCallBack={callbackFunction} />
    </div>
  );
}

export default Main;