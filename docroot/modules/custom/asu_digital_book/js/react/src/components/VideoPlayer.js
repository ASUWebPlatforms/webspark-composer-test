import React, { useRef, useEffect } from 'react';

const VideoPlayer = ({ videoSrc ,poster}) => {
  const videoRef = useRef(null);

  /* const handleFullscreen = () => {
    const video = videoRef.current;

    if (video.requestFullscreen) {
      video.requestFullscreen();
    } else if (video.mozRequestFullScreen) { // Firefox
      video.mozRequestFullScreen();
    } else if (video.webkitRequestFullscreen) { // Chrome, Safari, Opera
      video.webkitRequestFullscreen();
    } else if (video.msRequestFullscreen) { // IE/Edge
      video.msRequestFullscreen();
    } else if (typeof video.webkitEnterFullscreen === 'function') {
      // For iOS Safari
      video.webkitEnterFullscreen();
    }
  }; */

   useEffect(() => {
    // Callback function to handle the intersection change
    const handleIntersection = (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          // Play the video if it is in view
          videoRef.current.play();
        } else {
          // Pause the video if it is out of view
          videoRef.current.pause();
        }
      });
    };

    // Create the Intersection Observer
    const observer = new IntersectionObserver(handleIntersection, {
      threshold: 0.1, // Play the video when 50% of it is visible
    });

    // Observe the video element
    if (videoRef.current) {
      observer.observe(videoRef.current);
    }

    // Cleanup observer on component unmount
    return () => {
      if (videoRef.current) {
        observer.unobserve(videoRef.current);
      }
    };
  }, []); 

  return (
    <div className="video-container" data-aos="fade-up" data-aos-once="true">
      {!videoSrc && <div>Loading video...</div>}
      {videoSrc && (
      <video
        ref={videoRef}
        src={videoSrc}
        controls
        muted
        autoPlay
        preload='none'
        poster={poster}
        //onClick={handleFullscreen}
       //onTouchStart={handleFullscreen}
        playsInline
        style={{ width: '100%', height: 'auto' }}
        type="video/mp4"  // Ensuring the correct type
        className='video'
      >
        Your browser does not support the video tag.
      </video>
      )}
    </div>
  );
};

export default VideoPlayer;
