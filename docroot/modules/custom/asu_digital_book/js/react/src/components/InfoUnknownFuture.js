import React, { useState, useEffect } from 'react';
import { useFormContext, Controller, useController } from 'react-hook-form';
import { useData } from './DrupalSettings';
import WhatsWhyField from './WhatswhyOptions';
import YoutubePlayer from './YoutubePlayer';
import VideoPlayer from './VideoPlayer';


const InfoUnkownFuture = ({localWebUrl}) => {
    const { register, formState: { errors } } = useFormContext();
    const data = useData();
    //console.log(data);
   
    return (
        <div className='infoUnknown bookArea col-12'>
            <div className="imageBlock" dangerouslySetInnerHTML={{ __html: data.unknown_hero_image_block || '' }}></div>
            <div className='premose3Hero' dangerouslySetInnerHTML={{ __html: data.unknown_hero || '' }} />
            <div className="anchorDiv" dangerouslySetInnerHTML={{ __html: data.unknown_anchor_link || '' }}></div>
            <div className='myLifeTurned' dangerouslySetInnerHTML={{ __html: data.unknown_my_life_turned || '' }} />
            <div className='tenYearsFromNow' dangerouslySetInnerHTML={{ __html: data.ten_years_from_now || '' }} />
            <div className='careeroptions' dangerouslySetInnerHTML={{ __html: data.career_options || '' }} />
            <div className='itMakesYouWonder' dangerouslySetInnerHTML={{ __html: data.it_makes_you_wonder || '' }} />
            <div className='parallaxBlock' dangerouslySetInnerHTML={{ __html: data.parallax_block || '' }} />
            <div className='radicallyDifferent' dangerouslySetInnerHTML={{ __html: data.radically_different || '' }} />
            <div className='inventFuture' dangerouslySetInnerHTML={{ __html: data.invent_future || '' }} />
           
             {/*  <div className='inventVideo' dangerouslySetInnerHTML={{ __html: data.invent_video || '' }} /> */}
             {/* <div className="row"> 
             <div className='inventVideoReact video-container'>React component<VideoPlayer videoSrc='https://avmyd73124-asu-myfuture.ws.asu.edu/sites/default/files/2024-08/Script%206%20%281%29.mp4' /></div>
             </div>  */}
             <div className="row"> 
             <div className='inventVideoReact video-container'><VideoPlayer videoSrc={data.invent_video} poster={data.invent_poster} /></div>
             </div> 
            {/* <div className='whyCollege' dangerouslySetInnerHTML={{ __html: data.why_college || '' }} /> 
            <div className='whatsWHy'>
            <div className='bg gray-1-bg bg-top bg-percent-100 max-size-container center-container'>
                <WhatsWhyField />
            </div>
            </div> */}
             <div className="whyGoingToCollege" dangerouslySetInnerHTML={{ __html: data.why_going_to_college || '' }} />

            {/* Premise 3 question section */}
            <div className='bg gray-1-bg bg-top bg-percent-100 max-size-container center-container'>
            <WhatsWhyField />
            </div>
        </div>
    );
    
};

export default InfoUnkownFuture;
