import React from 'react';

// 5/15/2026: Now the interest dropdown values are coming from the Admin config page (/admin/config/visit-revamp).
// The DEFAULT_INTERESTS object is just a fallback in case the config values are missing or incomplete, and also serves as the source of truth for the full set of possible interests and their labels (since the config allows for a subset of these to be enabled).
const DEFAULT_INTERESTS = {
  grad: {
    architecture_construction: 'Architecture and Construction',
    arts: 'Arts',
    business: 'Business',
    communication_media: 'Communication and Media',
    computing_mathematics: 'Computing and Mathematics',
    education_teaching: 'Education and Teaching',
    engineering_technology: 'Engineering and Technology',
    entrepreneurship: 'Entrepreneurship',
    health_wellness: 'Health and Wellness',
    humanities: 'Humanities',
    interdisciplinary_studies: 'Interdisciplinary Studies',
    law_justice_public_service: 'Law, Justice and Public Service',
    science: 'Science',
    social_behavioral_sciences: 'Social and Behavioral Sciences',
    sustainability: 'Sustainability',
    stem: 'STEM',
  },
  ugrad: {
    25: 'Anthropology, Sociology and Cultural Studies',
    26: 'Architecture, Construction and Design',
    27: 'Business',
    28: 'Communication and Languages',
    29: 'Computer Science, Software Engineering and Mathematics',
    30: 'Criminology and Forensics',
    31: 'Earth, Space and Flight',
    32: 'Education and Teaching',
    33: 'Engineering',
    72: 'Fashion',
    73: 'Film, Media and Gaming',
    35: 'Fine Arts and Performance',
    76: 'Global Management and Leadership',
    36: 'Health and Wellness',
    37: 'History, Philosophy and Humanities',
    38: 'Journalism',
    39: 'Nursing',
    40: 'Pre-health',
    41: 'Pre-law',
    42: 'Psychology',
    43: 'Public Service and Political Science',
    44: 'Science',
    45: 'Sports, Tourism and Recreation',
    46: 'Sustainability',
    34: 'Undecided/Exploratory/Many Interests',
  },
};

export const interestLabels = {
  ...DEFAULT_INTERESTS.grad,
  ...DEFAULT_INTERESTS.ugrad,
};

const InterestDropdown = ({
  selectedStudentType,
  onSelectInterest,
  masterInterests = DEFAULT_INTERESTS,
  allowedInterests = { grad: [], ugrad: [] },
}) => {
  const handleSelect = (event) => {
    const selectedOption = event.target.value;

    // Save selection to JS session variable
    sessionStorage.setItem('interest', selectedOption);

    // Call the parent function to update selectedAreaOfInterest in CalendarPage
    onSelectInterest(selectedOption);
  };

  const level = selectedStudentType === 'Graduate student' ? 'grad' : 'ugrad';
  const optionsForLevel = masterInterests[level] || {};
  const allowedKeys = allowedInterests[level] || Object.keys(optionsForLevel);
  const filteredOptions = Object.entries(optionsForLevel)
    .filter(([key]) => allowedKeys.includes(key))
    .sort((a, b) => a[1].localeCompare(b[1]));

  return (
    <div>
      <fieldset className="mb-2">
        {selectedStudentType === '' ? (
          <select
            className="colselect custom-select form-select form-control col-12"
            id="area-of-interest"
            onChange={handleSelect}
          >
            <option value="">Select a student type first</option>
          </select>
        ) : selectedStudentType === 'Graduate student' ? (
          <select
            className="colselect custom-select form-select form-control col-12"
            id="interest-grad"
            name="interest-grad"
            onChange={handleSelect}
            defaultValue={sessionStorage.getItem('interest') || '0'}
          >
            <option value="0">I want to study...</option>
            {filteredOptions.map(([key, label]) => (
              <option key={key} value={level === 'grad' ? label : key}>
                {label}
              </option>
            ))}
          </select>
        ) : selectedStudentType !== 'Other' ? (
          <select
            className="colselect custom-select form-select form-control col-12"
            id="interest-ugrad"
            name="interest-ugrad"
            onChange={handleSelect}
            defaultValue={sessionStorage.getItem('interest') || '0'}
          >
            <option value="0">I want to study...</option>
            {filteredOptions.map(([key, label]) => (
              <option key={key} value={level === 'grad' ? label : key}>
                {label}
              </option>
            ))}
          </select>
        ) : null}
      </fieldset>
    </div>
  );
};

export default InterestDropdown;
