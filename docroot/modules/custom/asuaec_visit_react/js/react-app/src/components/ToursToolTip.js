import React from 'react';
import './ToursToolTip.css';

const TOURS_INFO = {
  'In-person walking tour without academic fair': {
    color: '#8C1D40',
    tooltip:
      '<p>Join an in-person admission presentation followed by a student-led guided tour of campus for you and up to two guests.</p>',
  },
  'In-person walking tour with academic fair': {
    color: '#FF7F32',
    tooltip:
      '<p>Join an in-person admission presentation followed by a student-led guided tour and academic fair. An academic fair gives you the chance to meet representatives from different academic programs, ask questions about majors and career paths, and explore your academic options.</p>',
  },
  'Barrett, The Honors College': {
    color: '#00A3E0',
    tooltip:
      "<p>Established in 1988, Barrett is the #1 ranked honors college in the nation. An undergraduate student can participate in the Barrett Honors College experience at any of ASU's four metropolitan Phoenix campuses.</p>",
  },
  'Academic facility tour': {
    color: '#E74973', // ASU Pink
    tooltip: '<p></p>',
  },
  'Self-guided tour': {
    color: '#78BE20',
    tooltip:
      '<p>Go on a self-guided tour of campus at your convenience with resources designed to help you explore a campus that fits your interests and academic goals</p>',
  },
  'Signature event': {
    color: '#FFC627',
    tooltip:
      '<p>Signature events like Sun Devil Day are ASU’s flagship visit experiences, offering a longer, more in-depth tour for students who are further along in their college search.</p>',
  },
  'Classroom visit': {
    color: '#4AB7C4', // ASU Turquoise
    tooltip:
      '<p>Experience ASU’s innovative learning environments firsthand with a visit to one of our classrooms. This is a great opportunity to see how ASU’s unique learning spaces foster collaboration and creativity among students.</p>',
  },
};

const ToursToolTip = ({ label }) => {
  const info = TOURS_INFO[label];
  if (!info) return null;

  return (
    <span className="tooltip-wrapper">
      <button
        className="tooltip-button"
        style={{ backgroundColor: info.color }}
        aria-label={`Info about ${label}`}
      >
        i
      </button>
      <span
        className="tooltip-content"
        dangerouslySetInnerHTML={{ __html: info.tooltip }}
      />
    </span>
  );
};

export default ToursToolTip;
export { TOURS_INFO };
