import React, { useEffect, useRef } from 'react';

function YoutubePlayer({ videoId }) {
  const playerRef = useRef(null);

  useEffect(() => {
    // Load the YouTube IFrame API script
    const tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    const firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    // Function called by YouTube IFrame API once it's ready
    window.onYouTubeIframeAPIReady = () => {
      playerRef.current = new window.YT.Player('youtube-player', {
        videoId: videoId,
        playerVars: {
          autoplay: 1,
          mute: 1,
        },
        events: {
          'onReady': onPlayerReady,
        }
      });
    };

    // Cleanup: remove the script if the component is unmounted
    return () => {
      if (playerRef.current) {
        playerRef.current.destroy();
      }
      delete window.onYouTubeIframeAPIReady;
    };
  }, [videoId]);

  const onPlayerReady = (event) => {
    const iFrame = event.target.getIframe();

    // Add click and touch event listeners to the iframe
    iFrame.addEventListener('click', () => requestFullscreen(iFrame));
    iFrame.addEventListener('touchstart', () => requestFullscreen(iFrame));
  };

  const requestFullscreen = (element) => {
    if (element.requestFullscreen) {
      element.requestFullscreen();
    } else if (element.mozRequestFullScreen) { // Firefox
      element.mozRequestFullScreen();
    } else if (element.webkitRequestFullscreen) { // Chrome, Safari, Opera
      element.webkitRequestFullscreen();
    } else if (element.msRequestFullscreen) { // IE/Edge
      element.msRequestFullscreen();
    } else if (typeof element.webkitEnterFullscreen === 'function') {
      // For iOS Safari
      element.webkitEnterFullscreen();
    }
  };

  return (
    <div className="youtube-video-container">
      <div id="youtube-player"></div>
    </div>
  );
}

export default YoutubePlayer;
