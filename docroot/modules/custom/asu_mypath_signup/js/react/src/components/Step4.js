import React, {useEffect, useState} from 'react';
import { useFormContext, useController } from 'react-hook-form';
import { PhoneInput } from 'react-international-phone';
import axios from 'axios';
import Select from 'react-select';

const Step4 = () => {
    const { register, formState: { errors } } = useFormContext();
    const htmlData = '<i class="fa-custom fa-exclamation-triangle fas"></i>';
    
  return (
    <div className="step4">
      <div className='warning_code' dangerouslySetInnerHTML={{ __html: htmlData }} />
      <h4>You're almost there!</h4> <p>You will need to return to Transfer Guide site and click the <strong>"Create an account"</strong> button to finish the signup process. Please refere to the below example to seee where yo can find the button.</p>
    </div>
  );
};

export default Step4;