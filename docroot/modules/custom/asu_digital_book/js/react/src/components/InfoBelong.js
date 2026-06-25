import React, { useState, useEffect, useRef } from 'react';
import { useFormContext, Controller, useController } from 'react-hook-form';
import { Button, OverlayTrigger, Popover } from 'react-bootstrap';
import axios from 'axios';
import Select from 'react-select';
import vampireImg from './images/vampire.jpg';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'; 
import { useData } from './DrupalSettings';
//import 'bootstrap/dist/css/bootstrap.min.css';
import WhatsWhyField from './WhatswhyOptions';
import VideoPlayer from './VideoPlayer';

function InfoBelong({ localWebUrl }) {
  const { register, formState: { errors } } = useFormContext();
  const infoContent = '<h2><span class="highlight-gold">Where <span class="highlight-black">do you</span> belong?</h2><p class="ptext">Deciding on a college often comes down to which one fits best.</span></p>';
  const [isOpen, setIsOpen] = useState(false);
  const [isVisible, setIsVisible] = useState(false);
  const [isElementVisible, setIsElementVisible] = useState(true);
  const [fontIcon, setFontIcon] = useState('<span class="fontIconSymbol">+</span>');
  const popupRef = useRef();
  const data = useData();
  const [show, setShow] = useState(false);
  const handleClose = () => setShow(false);
  
  const popover = (
    <Popover id="popover-basic">
      <Popover.Header as="h3"> <Button variant="link" size="sm" className="float-end" onClick={handleClose}>
         X
        </Button></Popover.Header>
      <Popover.Body>
      <div> 
      <p>The Vampire Problem is a thought experiment that asks if you could become a vampire and have the powers and immortality that go with it, but couldn't go back to your former life once you became a vampire, would you do it? It's intended to reveal the dilemma of not knowing what an experience you can't undo will be like until you actually have the experience.
      </p>
      
      </div>
      </Popover.Body>
    </Popover>
  );



  const handleClickOutside = (event) => {
    console.log(isOpen);
    (isOpen && setIsOpen(false));
  };

  const handleClickInsideDiv = (event) => {
    // Handle click inside the specific div
    console.log('Clicked inside the specific div');
    togglePopup();
  };

  useEffect(() => {
    // Get the specific div element using the ref
    const specificDiv = popupRef.current;
    // Check if the specific div exists
    if (specificDiv) {
      // Add click event listener to the specific div
      specificDiv.addEventListener('click', handleClickOutside);
    }

    // Remove event listener when the component unmounts
    return () => {
      if (specificDiv) {
        specificDiv.removeEventListener('click', handleClickOutside);
      }
    };
  }, []);



  return (
  <div className='infoBelong bookArea col-12'>
    {/* <div className='bookArea col-12'> */}
      {/* Premise 2 hero section */}
      <div className="imageBlock" dangerouslySetInnerHTML={{ __html: data.belong_hero_block || '' }}></div>
      <div className="belongHero" dangerouslySetInnerHTML={{ __html: data.belong_hero || '' }} />

       {/* Premise 2 decide section */}
       <div className="anchorDiv" dangerouslySetInnerHTML={{ __html: data.belong_anchor_link || '' }}></div>
       <div className="decidingOnCollege" dangerouslySetInnerHTML={{ __html: data.deciding_on_college || '' }} />
      
      {/* Premise 2 vampire section */}
      <div className="bg network-white bg-top bg-percent-100 max-size-container center-container">
          <div className="container custom-container">
            <div className = "row">
              <div className = "col-12">
                <div className="pt-10 pb-10 block block-layout-builder block-inline-blocktext-content clearfix default">
                
                 <div class="quote-text font-weight-bold" data-v-5e16b0cf="">
                  <p>
                    <img className="img-fluid aos-init aos-animate" src="https://dev-asu-myfuture.ws.asu.edu/sites/default/files/inline-images/vamprie_0.png" data-entity-uuid="e85cf165-1665-413b-a072-7af123ad7a76" data-entity-type="file" width="110" height="110" data-aos="fade-in" data-aos-delay="500" data-aos-duration="1000" loading="lazy" />
                  </p>
                  <div ref={popupRef}><span className="custom-background aos-init aos-animate" data-aos="custom-animation" data-aos-duration="1000" data-aos-easing="ease">But it's like the vampire problem</span>
                  <OverlayTrigger 
                      trigger="click" 
                      placement="left" 
                      overlay={popover}
                      show={show}
                      onToggle={() => setShow(!show)}
                  >
                    <Button className="vampireButton" variant="link"><div dangerouslySetInnerHTML={{ __html: fontIcon }} /></Button>
                  </OverlayTrigger>(you may learn about that in an ASU philosophy class). <strong>How do you really know if a college fits you until you actually go?</strong></div>
                  </div>
                  {/* {isOpen && (
                   <div className="popupContent">
                      <p>The Vampire Problem is a thought experiment that asks if you could become a vampire and have the powers and immortality that go with it, but couldn't go back to your former life once you became a vampire, would you do it? It's intended to reveal the dilemma of not knowing what an experience you can't undo will be like until you actually have the experience.
                      </p>
                    </div>
                  )} */}
              </div>
            </div>
          </div>
        </div>
        </div>

        {/* Premise 2 video section */}
       {/*  <div className="decidingOnCollege" dangerouslySetInnerHTML={{ __html: data.belong_video || '' }} /> */}
       <div className="row"> 
             <div className='decidingOnCollege'><VideoPlayer videoSrc={data.belong_video} poster={data.belong_poster} /></div>
        </div> 

        <div className="whyGoingToCollege" dangerouslySetInnerHTML={{ __html: data.why_going_to_college || '' }} />

        {/* Premise 2 question section */}
        <div className='bg gray-1-bg bg-top bg-percent-100 max-size-container center-container'>
          <WhatsWhyField />
        </div>
        


   {/*  </div> */}
  </div>

  );

}

export default InfoBelong;
