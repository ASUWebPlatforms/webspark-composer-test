import React, { useState, useEffect, useRef } from 'react';
import { useFormContext, Controller, useController } from 'react-hook-form';
import axios from 'axios';
import Select from 'react-select';
import astronaught from './images/personaasset1.jpeg';
import queen from './images/personaasset2.jpeg';
import fireFighter from './images/personaasset3.jpeg';
import doctor from './images/personaasset4.jpeg';
import police from './images/personaasset5.jpeg';
import infoImage from './images/infoimage.jpg';
/* import Lottie from 'lottie-react';
import doctorData from './assets/doctor.json';
import astroData from './assets/astronaut.json';*/
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faPlay, faStop } from '@fortawesome/free-solid-svg-icons';
import AOS from 'aos';
import 'aos/dist/aos.css'; 
import DrupalSettings from './DrupalSettings';

//const doctorData = React.lazy(() => import('./assets/doctor.json'));
//const astroData = React.lazy(() => import('./assets/astronaut.json'));

const InfoPassion = ({localWebUrl}) => {
    
    const { register, formState: { errors } } = useFormContext();
    const infoContent = "<p>Hello view book</p>";
    const [isPlaying, setIsPlaying] = useState(true);  

    const professionAnimateRef = useRef(null);
    const astroAnimateRef = useRef(null); 
    const [q1leftsubmitted, setq1leftSubmitted] = useState(false);
    const [q1rightsubmitted, setq1rightSubmitted] = useState(false);

    //AOS animation
    useEffect(() => {
        AOS.init({
          duration: 1000, // duration of the animation in milliseconds
          once: false, // whether animation should happen only once - while scrolling down
          mirror: true, // whether elements should animate out while scrolling past them
        });
    }, []);

    const {
        field: { onApChange, apValue },
        fieldState: { invalid: apinvalid },
      } = useController({
        name: 'aspiringProfession',
        rules: { required: 'Last name is required' },
      });


   

    // Function to handle play action
    const handlePlay = () => {
      if (!isPlaying) {
      // Start both animations if not already playing
      if (professionAnimateRef.current) professionAnimateRef.current.play();
      if (astroAnimateRef.current) astroAnimateRef.current.play();
      setIsPlaying(true); // Update playing state
      }
    };

    // Function to handle stop action
    const handleStop = () => {
        if (isPlaying) {
        // Stop both animations if playing
        if (professionAnimateRef.current) professionAnimateRef.current.stop();
            if (astroAnimateRef.current) astroAnimateRef.current.stop();
            setIsPlaying(false); // Update playing state
        }
    };

    const q1LeftSubmitHandler = (event) => {
        event.preventDefault();
        setq1leftSubmitted(true);
        onApChange(event.target.value);
        console.log(event.target.value);
    }

    const q1RightSubmitHandler = (event) => {
        event.preventDefault();
        setq1rightSubmitted(true);
        console.log(event.target.value);
    }
   
    return (
        <div className='infoPassion bookArea'>
            <DrupalSettings />
            <div className="animated-bg">
           
                <h2>What do you want to do in life?</h2>
                
               {/*  <img src={infoImage} alt="What do you want to do"/> */}
            </div>
            <div className="bg gray-7-bg bg-top bg-percent-100 max-size-container center-container">
                <div data-aos="slide-right" className="text-white pt-10 pb-10 block block-layout-builder block-inline-blocktext-content clearfix" ><h2><strong>You were told you could be anything when you grow up.</strong></h2>
                </div>
            </div>
            <div className="imagesPersona">
                <img src={astronaught} alt="Astronaught" data-aos="fade-in" data-aos-delay="50" data-aos-duration="1000" />
                <img src={queen} alt="Astronaught" data-aos="fade-in" data-aos-delay="250" data-aos-duration="1000" />
                <img src={fireFighter} alt="Astronaught" data-aos="fade-in" data-aos-delay="500" data-aos-duration="1000" />
                <img src={doctor} alt="Astronaught" data-aos="fade-in" data-aos-delay="750" data-aos-duration="1000" />
                <img src={police} alt="Astronaught" data-aos="fade-in" data-aos-delay="1000" data-aos-duration="1000" />
            </div>
            <div>
                <div className = "slideBackgroundText quote-text font-weight-bold">
                    So do you know what you want to do?
                </div>
            </div>
          
            {/* <div className='animateButtonsDiv'>
                 {!isPlaying ? (
                    <button className='animateButton' onClick={handlePlay}>
                    <FontAwesomeIcon icon={faPlay} />
                    </button>
                 ) : (
                    <button className='animateButton' onClick={handleStop}>
                    <FontAwesomeIcon icon={faStop} />
                    </button>
                 )}
            </div> */}

            <div className='infoQues1'>
                <h3><span className="custom-background aos-init header-content" data-aos="bg-highlight"><strong>So do you know what you want to do?</strong></span></h3>
                <div className='q1Section'>
                    <div className="goldSection col-md-4 q1LeftSide">
                        {!q1leftsubmitted && (
                            <div className='q1inputField'>
                                <div className='q1innerinputField'>
                                    <label htmlFor='aspiringProfession'>
                                        <h3>I want to be a</h3></label>
                                    <input 
                                        id="aspiring_profession_field" 
                                        type="text" 
                                        {...register('aspiringProfession', { 
                                            required: 'Field is required',
                                            pattern: { value: /^[a-zA-Z0-9\s\.,!?]*$/, message: 'Only plain text allowed' } 
                                        })} 
                                        name='aspiringProfession'
                                        value={apValue}
                                        onChange={onApChange}
                                        className={apinvalid ? 'error-field' : ''}
                                    />
                                    {errors.aspiringProfession && <span className="error">{errors.aspiringProfession.message}</span>}
                                </div>
                                <button className='btn btn-primary' onClick={q1LeftSubmitHandler} type="submit">Submit</button>
                            </div>
                        )}
                        {/* Conditionally render message if form is submitted */}
                        {q1leftsubmitted && <div className='q1Results'>That's great! We can help you get there.</div>}
                    </div> 
                    <div className='middleOr'>Or</div>
                    <div className='blackSection col-md-4 q1RightSide'>
                       
                            {!q1rightsubmitted && (
                                <div className='q1inputField'>
                                    <div className='q1innerinputField'>
                                        <h3><p>&nbsp;</p>I still don't know.</h3>
                                    </div>
                                
                                    <button className='btn btn-primary' onClick={q1RightSubmitHandler} type="submit">Submit</button>
                                </div>
                            )}
                            {q1rightsubmitted && <div className='q1Results'>That's OK. You're not the only student who's unsure.</div>}
                    </div>
                </div>
            </div>
        </div>
        
    );
    
};

export default InfoPassion;
