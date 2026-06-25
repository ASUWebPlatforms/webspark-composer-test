import React, { useState, useEffect, useRef } from 'react';
import { useFormContext, Controller, useController } from 'react-hook-form';
import axios from 'axios';
import Select from 'react-select';
import astronaught from './images/personaasset1.jpeg';
import queen from './images/personaasset2.jpeg';
import fireFighter from './images/personaasset3.jpeg';
import doctor from './images/personaasset4.jpeg';
import police from './images/personaasset5.jpeg';
import AOS from 'aos';
import 'aos/dist/aos.css'; 
import { useData } from './DrupalSettings';
import WhatsWhyField from './WhatswhyOptions';
import YoutubePlayer from './YoutubePlayer';
import VideoPlayer from './VideoPlayer';


const InfoPassion = ({localWebUrl}) => {
    const data = useData();
    console.log(data);
    //const methods = useForm();
    const { control, register, formState: { errors } } = useFormContext();
    const infoContent = "<p>Hello view book</p>";
    const [isPlaying, setIsPlaying] = useState(true);  

    const professionAnimateRef = useRef(null);
    const astroAnimateRef = useRef(null); 
    const [q1leftsubmitted, setq1leftSubmitted] = useState(false);
    const [q1rightsubmitted, setq1rightSubmitted] = useState(false);
    const whatYouKnowOptions = [
       { "I know exactly what I want to do" : "I know exactly what I want to do." },
       { "I have a couple ideas, but Im not sure" : "I have a couple ideas, but I\’m not sure." },
       { "I have no idea" : "I have no idea." }
   ];

   const whatsWhyOptions = [
       {'focused_futurist' : 'So I can get a good job and have a successful career.'},
       {'deep_diver' : 'To gain skills and knowledge about something I’m really interested in.'},
       {'trailblazer' : ' To help make an impact in the world.'},
       {'superfan' : 'To experience the classic college experience.'},
       {'networker' : 'To meet new people and network.'}
   ]

    const {
        field: { onChange: ChangesetKnowValue, value: knowValue },
        fieldState: { invalid:knowInvalid }
        } = useController({
        name: 'knowValueField',
        control,
        defaultValue: '',
        rules: { required: 'Please select grade' }
    });

    const {
        field: { onChange: ChangesetWhatsWhy, value: whatsWhy },
        fieldState: { invalid:whyInvalid }
        } = useController({
        name: 'whatsWhyField',
        control,
        defaultValue: '',
        rules: { required: 'Please select value' }
    });

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


    //change handler for multi select check box whats why field
    const handleWhatsWhyCheckboxChange = (optionValue) => {
        const newValue = whatsWhy.includes(optionValue)? whatsWhy.filter(item => item !== optionValue) : [...whatsWhy, optionValue];
        ChangesetWhatsWhy(newValue);
       
    };

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
        <div className='infoPassion bookArea col-12'>
            <div className="layout__fixed-width">
                <div className="imageBlock" dangerouslySetInnerHTML={{ __html: data.passion_hero_block || '' }}></div>
                <div className="bg topo-white bg-top bg-percent-100 max-size-container center-container">
                    <div className="container">
                        <div className="headerImage row">
                            <div className="col-12 pb-10 pt-10" dangerouslySetInnerHTML={{ __html: data.what_to_do_with_life || '' }}></div>
                        </div>
                    </div>
                </div>
            </div>
            
            
            {/* <div className="max-size-container center-container">
                <div className="container">
                    <div className="row">
                        //   <div className="headerVideo col-12 pb-10 pt-10" dangerouslySetInnerHTML={{ __html: data.asu_tour_video || '' }} /> 
                         <div className="headerVideo col-12 pb-10 pt-10">
                        <YoutubePlayer videoId='7Rw5_5_P06k' />
                        </div>
                    </div>
                </div>
            </div>  */}

            <div className="row"> 
                 <div className='passionVideoReact'><div className="anchorDiv" dangerouslySetInnerHTML={{ __html: data.passion_anchor_link || '' }}></div><VideoPlayer videoSrc={data.asu_tour_video} poster={data.asu_tour_poster} /></div>
             </div>

            <div className="bg gray-7-bg  bg-top bg-percent-100 max-size-container center-container">
                <div className="container">
                    <div className="row">
                        <div className="slideText pb-3 pt-3">
                            <div data-aos="slide-right" className="text-white pt-10 pb-10 block block-layout-builder block-inline-blocktext-content clearfix">
                                <h2><strong>You were told you could be anything when you grow up.</strong></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>  

            <div className="bg network-white bg-top bg-percent-100 max-size-container center-container"> 
                <div className="container">
                    <div className="row">
                        <div className="imagesPersona pt-10 pb-10">
                            <img src={astronaught} alt="Astronaught" data-aos="fade-in" data-aos-delay="50" data-aos-duration="1000" />
                            <img src={queen} alt="Astronaught" data-aos="fade-in" data-aos-delay="250" data-aos-duration="1000" />
                            <img src={fireFighter} alt="Astronaught" data-aos="fade-in" data-aos-delay="500" data-aos-duration="1000" />
                            <img src={doctor} alt="Astronaught" data-aos="fade-in" data-aos-delay="750" data-aos-duration="1000" />
                            <img src={police} alt="Astronaught" data-aos="fade-in" data-aos-delay="1000" data-aos-duration="1000" />
                        </div>
                    </div>
                </div>
            </div>

            <div className="bg gray-2-bg bg-top  pb-8 pt-10 bg-percent-100 max-size-container center-container"> 
                <div className="container">
                    <div className="row">
                        <div className="question2 col-12" dangerouslySetInnerHTML={{ __html: data.do_you_know_what_you_want_to_do || '' }} />
                    </div>
                   
                    <div className="row">
                        <div className="js-form-item form-item js-form-type-radios form-item-hs-value js-form-item-know-value form-group" data-aos="slide-left">
                    
                        {whatYouKnowOptions.map((knowoption, index) => {
                        
                            const knowkey = Object.keys(knowoption)[0]; 
                            const knowlabel = knowoption[knowkey]; 
                            return (
                                <div className="js-form-item form-item js-form-type-radio form-check" key={index}>
                                    <input 
                                        type="radio"
                                        id={`knowOptions${knowkey}`}
                                        value={knowkey}
                                        checked={knowValue === knowkey}
                                        onChange={() => ChangesetKnowValue(knowkey)}
                                        name="knowValueField"
                                        className={`form-radio form-check-input ${knowInvalid ?  "error-field" : ''} ` }
                                        
                                    />    
                                    <label className='form-check-label' htmlFor={`knowOptions${knowkey}`} >
                                        {knowlabel}
                                    </label><br />
                                    
                                </div>
                            );
                        })}
                        {knowInvalid && <span className="error error-message">{errors.knowValueField?.message}</span>}
                        </div>
                        
                    </div>
                </div>
            </div>
           {knowValue === 'I know exactly what I want to do' &&  (  
            <div className="question3" dangerouslySetInnerHTML={{ __html: data.i_know || '' }} />         
            )}

            {(knowValue === 'I have a couple ideas, but Im not sure' || knowValue === 'I have no idea') &&  (  
            <div>
                <div className="question3-1" dangerouslySetInnerHTML={{ __html: data.have_ideas_but_not_sure || '' }} /> 

                <div className="question3-2" dangerouslySetInnerHTML={{ __html: data.have_ideas_but_one || '' }} />
                
                <div className="question3-3" dangerouslySetInnerHTML={{ __html: data.have_ideas_but_two || '' }} /> 

                <div className="question3-4" dangerouslySetInnerHTML={{ __html: data.have_ideas_but_three || '' }} /> 

                <div className="question3-5" dangerouslySetInnerHTML={{ __html: data.have_ideas_but_four || '' }} />  

                <div className="question3-6" dangerouslySetInnerHTML={{ __html: data.have_ideas_but_five || '' }} /> 
            </div>
            )}
            {knowValue && (
            <div className="afteKnow">
            <WhatsWhyField />
            
            
            </div>
            )}
            
        </div>
         
    );
    
};

export default InfoPassion;
