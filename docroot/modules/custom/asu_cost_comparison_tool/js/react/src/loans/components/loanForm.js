import { useState, useEffect } from 'react';

// Normalize any line ending style (\r\n, \r, \n) then split into non-empty lines.
function splitLines(str) {
  if (!str) return [];
  //console.log('Parsing lines from string:', str);
  return str
    .replace(/\r\n|\r/g, '\n')
    .split('\n')
    .map((s) => s.trim())
    .filter(Boolean);
}

// Parse one entry per line "key|Label" or "key|Label|Subtitle" → [{ value, label, subtitle }, ...]
function parsePipeOptions(str) {
  //console.log('Parsing pipe options from string:', str);
  return splitLines(str).map((pair) => {
    const [value, label, subtitle] = pair.split('|');
    return {
      value: (value || '').trim(),
      label: (label || value || '').trim(),
      subtitle: (subtitle || '').trim(),
    };
  });
}

// Parse options that may be comma or newline-separated, with optional "key|Label" pipe.
function parseCreditsOptions(str) {
  if (!str) return [];
  return str
    .replace(/\r\n|\r/g, '\n')
    .split(/[\n,]/)
    .map((s) => s.trim())
    .filter(Boolean)
    .map((v) => {
      const [value, label] = v.split('|');
      return {
        value: (value || '').trim(),
        label: (label || value || '').trim(),
      };
    });
}

// Parse one entry per line "key|Label" into a map: { key: "Label", ... }
function parseFieldLabels(str) {
  //console.log('Parsing field labels from string:', str);
  const map = {};
  splitLines(str).forEach((pair) => {
    const [key, label] = pair.split('|');
    if (key) map[key.trim()] = (label || key).trim();
  });
  return map;
}

const ds =
  typeof drupalSettings !== 'undefined' &&
  drupalSettings.asu_loan_proration_tool
    ? drupalSettings.asu_loan_proration_tool
    : {};
//console.log('Loan proration tool settings:', ds);
const fieldLabels = parseFieldLabels(ds.fieldLabels);
const studentTypeOptions = parsePipeOptions(ds.studentType);
const residencyOptions = parsePipeOptions(ds.loanResidency);
const campusOptions = parsePipeOptions(ds.campusList);
//console.log('cp', campusOptions);
const semesterOptions = parsePipeOptions(ds.semester);
const dependencyOptions = parsePipeOptions(ds.loanDependency);
const creditsOptions = parsePipeOptions(ds.creditsCompleted);
//console.log(creditsOptions);
const creditsHelpTextUndergrad = parseFieldLabels(ds.creditsHelpTextUndergrad);
const creditsHelpTextGraduate = parseFieldLabels(ds.creditsHelpTextGraduate);
const creditsHelpRawUndergrad = ds.creditsHelpTextUndergrad || '';
const creditsHelpRawGraduate = ds.creditsHelpTextGraduate || '';
const importantNote = ds.importantNotes || '';
//console.log('Parsed important note:', importantNote);
const STUDENT_TYPE_LABEL = fieldLabels.studentType || 'Student Type';
const RESIDENCY_LABEL =
  fieldLabels.loanResidency || fieldLabels.residency || 'Residency';
const DEPENDENCY_LABEL = fieldLabels.loanDependency || 'Dependency';
const CREDITS_LABEL = fieldLabels.creditsCompleted || 'Credits Completed';
const CAMPUS_LABEL = fieldLabels.campusList || 'Campus';
const COLLEGE_LABEL = fieldLabels.collegeList || 'College';
const PROGRAM_LABEL = fieldLabels.programList || 'Program';
const SEMESTER_LABEL = fieldLabels.semester || 'Semester';
const CREDIT_SEMESTER_MAX = Number(ds.creditSemesterLimit) || 21;
const UNDERGRAD_FULL_TIME = Number(ds.undergradMaxCreditLimit) || 12;
const GRAD_FULL_TIME = Number(ds.graduateMaxCreditLimit) || 9;
const submitText = ds.submitButtonText || 'Estimate my costs';
const currentAcadYear = ds.currentAcadYear;

// Returns true when the selected student type value or label includes "undergrad".
function checkIsUndergraduate(value) {
  const opt = studentTypeOptions.find((o) => o.value === value);
  if (!opt) return false;
  const text = (opt.value + ' ' + opt.label).toLowerCase();
  return text.includes('undergrad') || text.includes('ug');
}

const LoanForm = ({ onSubmit }) => {
  const [studentType, setStudentType] = useState('');
  const [residency, setResidency] = useState('');
  const [campus, setCampus] = useState('');
  const [college, setCollege] = useState('');
  const [collegeOptions, setCollegeOptions] = useState([]);
  const [collegeFetching, setCollegeFetching] = useState(false);
  const [collegeFetchError, setCollegeFetchError] = useState('');

  const [program, setProgram] = useState('');
  const [programOptions, setProgramOptions] = useState([]);
  const [programFetching, setProgramFetching] = useState(false);
  const [programFetchError, setProgramFetchError] = useState('');

  const [dependency, setDependency] = useState('');
  const [credits, setCredits] = useState('');
  const [creditsPerSemester, setCreditsPerSemester] = useState(1);
  const [creditsSliderTouched, setCreditsSliderTouched] = useState(false);
  const [semester, setSemester] = useState([]);

  const isUndergraduate = checkIsUndergraduate(studentType);

  // Reset undergrad-only fields when student type changes away from undergrad.
  useEffect(() => {
    if (!isUndergraduate) {
      setDependency('');
      setCredits('');
      setSemester([]);
    }
  }, [isUndergraduate]);

  // Reset campus, college, and program whenever student type changes.
  useEffect(() => {
    setCampus('');
    setCollege('');
    setCollegeOptions([]);
    setProgram('');
    setProgramOptions([]);
    setDependency('');
  }, [studentType]);

  // Fetch college options whenever campus changes.
  useEffect(() => {
    if (!campus) {
      setCollegeOptions([]);
      setCollege('');
      return;
    }
    setCollegeFetching(true);
    setCollegeFetchError('');
    setCollege('');
    setCollegeOptions([]);
    const url = `/api/loan-proration/college-list/${encodeURIComponent(campus)}/${encodeURIComponent(studentType)}`;
    //console.log('Fetching college options from URL:', url);
    fetch(url)
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then((data) => {
        const options = Object.entries(data.programs || {})
          .filter(([value]) => value !== '')
          .map(([value, label]) => ({ value, label }));
        setCollegeOptions(options);
      })
      .catch((err) => {
        //console.error('Failed to fetch college list:', err);
        setCollegeFetchError('Could not load colleges. Please try again.');
      })
      .finally(() => setCollegeFetching(false));
  }, [campus]);

  // Fetch program options whenever residency, campus, studentType, or college changes.
  useEffect(() => {
    if (!residency || !campus || !studentType || !college) {
      setProgramOptions([]);
      setProgram('');
      return;
    }
    setProgramFetching(true);
    setProgramFetchError('');
    setProgram('');
    setProgramOptions([]);

    const url = `/api/loan-proration/program-list/${encodeURIComponent(residency)}/${encodeURIComponent(campus)}/${encodeURIComponent(studentType)}/${encodeURIComponent(college)}/${encodeURIComponent(currentAcadYear)}`;
    //console.log('Fetching program options from URL:', url);

    fetch(url)
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then((data) => {
        const options = Object.entries(data.programs || {})
          .filter(([value]) => value !== '')
          .map(([value, label]) => ({ value, label }));
        setProgramOptions(options);
        //console.log('Received program options:', options);
        //console.log('Program options length:', options.length);
        if (options.length === 1) {
          setProgram(options[0].value);
        }
      })
      .catch((err) => {
        //console.error('Failed to fetch program list:', err);
        setProgramFetchError('Could not load programs. Please try again.');
      })
      .finally(() => setProgramFetching(false));
  }, [residency, campus, studentType, college]);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (onSubmit) {
      onSubmit({
        studentType,
        residency,
        dependency,
        credits,
        creditsPerSemester,
        campus,
        college,
        program,
        semester,
      });
    }
  };

  const residencyLocked = !studentType;
  // Undergrad: dependency locked until residency, credits locked until dependency
  const dependencyLocked = !residency;
  // Credits: for undergrad wait for dependency
  const creditsLocked = !dependency;
  // Campus: for undergrad wait for credits, for grad wait for residency
  const campusLocked = isUndergraduate ? !credits : !residency;
  const collegeLocked = !campus;
  const programLocked = !college;
  // Semester: locked until program is chosen, or until college is set when no programs exist.
  const programSatisfied =
    !!program || (!!college && programOptions.length === 0);
  const semesterLocked = !programSatisfied;
  const creditsPerSemesterLocked = semester.length === 0;
  // Submit requires all visible required fields
  const canSubmit =
    !!(
      studentType &&
      residency &&
      campus &&
      college &&
      programSatisfied &&
      semester.length > 0
    ) &&
    (!isUndergraduate || (!!dependency && !!credits));

  return (
    <form
      className="loan-form"
      onSubmit={handleSubmit}
      noValidate
      aria-label="Loan proration calculator"
    >
      <div className="loan-form_sections">
        {/* Student Type */}
        <fieldset className="loan-form_section" aria-required="true">
          <legend className="loan-form_section-header">
            <span className="loan-form_bullet" aria-hidden="true">
              •
            </span>
            {STUDENT_TYPE_LABEL}
          </legend>
          <div className="loan-form_cards" role="radiogroup">
            {studentTypeOptions.map(({ value, label, subtitle }) => {
              const inputId = `student-type-${value}`;
              const subtitleId = subtitle ? `${inputId}-desc` : undefined;
              return (
                <label
                  key={value}
                  htmlFor={inputId}
                  tabIndex={0}
                  className={`loan-form_card${studentType === value ? ' loan-form_card--selected' : ''}`}
                  onKeyDown={(e) => {
                    if (e.key === ' ' || e.key === 'Enter') {
                      e.preventDefault();
                      setStudentType(value);
                    }
                  }}
                >
                  <input
                    type="radio"
                    id={inputId}
                    name="studentType"
                    value={value}
                    checked={studentType === value}
                    onChange={() => setStudentType(value)}
                    className="loan-form_card-input"
                    aria-describedby={subtitleId}
                    tabIndex={-1}
                    required
                  />
                  <span className="loan-form_card-title">{label}</span>
                  {subtitle && (
                    <span id={subtitleId} className="text-muted">
                      {subtitle}
                    </span>
                  )}
                </label>
              );
            })}
          </div>
        </fieldset>

        {/* Residency – locked until Student Type is chosen */}
        <fieldset
          className={`loan-form_section${residencyLocked ? ' loan-form_section--disabled' : ''}`}
          disabled={residencyLocked}
          aria-disabled={residencyLocked}
          aria-label={
            residencyLocked
              ? `${RESIDENCY_LABEL} – select a student type first`
              : RESIDENCY_LABEL
          }
        >
          <legend className="loan-form_section-header">
            <span className="loan-form_bullet" aria-hidden="true">
              •
            </span>
            {RESIDENCY_LABEL}
          </legend>

          <div className="loan-form_cards" role="radiogroup">
            {residencyOptions.map(({ value, label, subtitle }) => {
              const inputId = `residency-${value}`;
              const subtitleId = subtitle ? `${inputId}-desc` : undefined;
              return (
                <label
                  key={value}
                  htmlFor={inputId}
                  tabIndex={residencyLocked ? -1 : 0}
                  className={`loan-form_card${residency === value ? ' loan-form_card--selected' : ''}${residencyLocked ? ' loan-form_card--locked' : ''}`}
                  onKeyDown={(e) => {
                    if (
                      !residencyLocked &&
                      (e.key === ' ' || e.key === 'Enter')
                    ) {
                      e.preventDefault();
                      setResidency(value);
                    }
                  }}
                >
                  <input
                    type="radio"
                    id={inputId}
                    name="residency"
                    value={value}
                    checked={residency === value}
                    onChange={() => setResidency(value)}
                    className="loan-form_card-input"
                    aria-describedby={subtitleId}
                    tabIndex={-1}
                    disabled={residencyLocked}
                    required
                  />
                  <span className="loan-form_card-title">{label}</span>
                  {subtitle && (
                    <span id={subtitleId} className="text-muted">
                      {subtitle}
                    </span>
                  )}
                </label>
              );
            })}
          </div>
        </fieldset>

        {/* Dependency – undergrad only, locked until Residency is chosen */}
        {isUndergraduate && (
          <fieldset
            className={`loan-form_section${dependencyLocked ? ' loan-form_section--disabled' : ''}`}
            disabled={dependencyLocked}
            aria-disabled={dependencyLocked}
            aria-label={
              dependencyLocked
                ? `${DEPENDENCY_LABEL} – select a residency first`
                : DEPENDENCY_LABEL
            }
          >
            <legend className="loan-form_section-header">
              <span className="loan-form_bullet" aria-hidden="true">
                •
              </span>
              {DEPENDENCY_LABEL}
            </legend>
            <div className="loan-form_cards" role="radiogroup">
              {dependencyOptions.map(({ value, label, subtitle }) => {
                const inputId = `dependency-${value}`;
                const subtitleId = subtitle ? `${inputId}-desc` : undefined;
                return (
                  <label
                    key={value}
                    htmlFor={inputId}
                    tabIndex={dependencyLocked ? -1 : 0}
                    className={`loan-form_card${dependency === value ? ' loan-form_card--selected' : ''}${dependencyLocked ? ' loan-form_card--locked' : ''}`}
                    onKeyDown={(e) => {
                      if (
                        !dependencyLocked &&
                        (e.key === ' ' || e.key === 'Enter')
                      ) {
                        e.preventDefault();
                        setDependency(value);
                      }
                    }}
                  >
                    <input
                      type="radio"
                      id={inputId}
                      name="dependency"
                      value={value}
                      checked={dependency === value}
                      onChange={() => setDependency(value)}
                      className="loan-form_card-input"
                      aria-describedby={subtitleId}
                      tabIndex={-1}
                      disabled={dependencyLocked}
                      required
                    />
                    <span className="loan-form_card-title">{label}</span>
                    {subtitle && (
                      <span id={subtitleId} className="text-muted">
                        {subtitle}
                      </span>
                    )}
                  </label>
                );
              })}
            </div>
          </fieldset>
        )}

        {/* Credits Completed – undergrad only, locked until Dependency is chosen */}
        {isUndergraduate && (
          <fieldset
            className={`loan-form_section${creditsLocked ? ' loan-form_section--disabled' : ''}`}
            disabled={creditsLocked}
            aria-disabled={creditsLocked}
            aria-label={
              creditsLocked
                ? `${CREDITS_LABEL} – complete the previous step first`
                : CREDITS_LABEL
            }
          >
            <legend className="loan-form_section-header">
              <span className="loan-form_bullet" aria-hidden="true">
                •
              </span>
              {CREDITS_LABEL}
            </legend>
            <div
              className="loan-form_cards loan-form_cards--compact credits-completed-field"
              role="radiogroup"
            >
              {creditsOptions.map(({ value, label, subtitle }) => {
                const inputId = `credits-${value}`;
                const subtitleId = subtitle ? `${inputId}-desc` : undefined;
                return (
                  <label
                    key={value}
                    htmlFor={inputId}
                    tabIndex={creditsLocked ? -1 : 0}
                    className={`loan-form_card${credits === value ? ' loan-form_card--selected' : ''}${creditsLocked ? ' loan-form_card--locked' : ''}`}
                    onKeyDown={(e) => {
                      if (
                        !creditsLocked &&
                        (e.key === ' ' || e.key === 'Enter')
                      ) {
                        e.preventDefault();
                        setCredits(value);
                      }
                    }}
                  >
                    <input
                      type="radio"
                      id={inputId}
                      name="credits"
                      value={value}
                      checked={credits === value}
                      onChange={() => setCredits(value)}
                      className="loan-form_card-input"
                      tabIndex={-1}
                      disabled={creditsLocked}
                      required
                    />
                    <span className="loan-form_card-title">{label}</span>
                    {subtitle && (
                      <span id={subtitleId} className="text-muted">
                        {subtitle}
                      </span>
                    )}
                  </label>
                );
              })}
            </div>
          </fieldset>
        )}

        {/* Campus – locked until Residency (grad) or Credits Completed (undergrad) */}
        <fieldset
          className={`loan-form_section${campusLocked ? ' loan-form_section--disabled' : ''}`}
        >
          <legend className="loan-form_section-header">
            <span className="loan-form_bullet" aria-hidden="true">
              •
            </span>
            {CAMPUS_LABEL}
          </legend>
          <select
            id="campus-select"
            className="loan-form_select"
            value={campus}
            onChange={(e) => setCampus(e.target.value)}
            disabled={campusLocked}
            aria-disabled={campusLocked}
            aria-required="true"
            required
          >
            <option value="" disabled>
              -- Select a campus --
            </option>
            {campusOptions.map(({ value, label }) => (
              <option key={value} value={value}>
                {label}
              </option>
            ))}
          </select>
        </fieldset>

        {/* College – locked until Campus is chosen, options fetched from API */}
        <fieldset
          className={`loan-form_section${collegeLocked ? ' loan-form_section--disabled' : ''}`}
        >
          <div
            role="status"
            aria-live="polite"
            aria-atomic="true"
            className="loan-form_sr-only"
          >
            {collegeFetching ? 'Loading colleges…' : ''}
          </div>
          <legend className="loan-form_section-header">
            <span className="loan-form_bullet" aria-hidden="true">
              •
            </span>
            {COLLEGE_LABEL}
          </legend>
          <select
            id="college-select"
            className="loan-form_select"
            value={college}
            onChange={(e) => setCollege(e.target.value)}
            disabled={collegeLocked || collegeFetching}
            aria-disabled={collegeLocked || collegeFetching}
            aria-required="true"
            aria-busy={collegeFetching}
            required
          >
            <option value="" disabled>
              {collegeFetching
                ? 'Loading…'
                : collegeOptions.length === 0 && campus
                  ? 'No colleges found'
                  : '-- Select a college --'}
            </option>
            {collegeOptions.map(({ value, label }) => (
              <option key={value} value={value}>
                {label}
              </option>
            ))}
          </select>
          {collegeFetchError && (
            <p className="loan-form_field-error" role="alert">
              {collegeFetchError}
            </p>
          )}
        </fieldset>

        {/* Program – hidden for undergrads until multiple options are available.
            Auto-selects the only option when exactly one exists. */}
        <fieldset
          className={`loan-form_section${programLocked ? ' loan-form_section--disabled' : ''}`}
          hidden={programOptions.length <= 1}
        >
          <div
            role="status"
            aria-live="polite"
            aria-atomic="true"
            className="loan-form_sr-only"
          >
            {programFetching ? 'Loading programs…' : ''}
            {!programLocked && !programFetching
              ? `${PROGRAM_LABEL} is now available`
              : ''}
          </div>
          <legend className="loan-form_section-header">
            <span className="loan-form_bullet" aria-hidden="true">
              •
            </span>
            {PROGRAM_LABEL}
          </legend>

          <select
            id="program-select"
            className="loan-form_select"
            value={program}
            onChange={(e) => setProgram(e.target.value)}
            disabled={programLocked || programFetching}
            aria-disabled={programLocked || programFetching}
            aria-required={programOptions.length > 1 ? 'true' : 'false'}
            aria-busy={programFetching}
            required={programOptions.length > 1}
          >
            <option value="" disabled>
              {programFetching
                ? 'Loading…'
                : programOptions.length === 0 && college
                  ? 'No programs found'
                  : '-- Select a program --'}
            </option>
            {programOptions.map(({ value, label }) => (
              <option key={value} value={value}>
                {label}
              </option>
            ))}
          </select>
          {programFetchError && (
            <p className="loan-form_field-error" role="alert">
              {programFetchError}
            </p>
          )}
        </fieldset>

        {/* Semester – locked until Program is chosen */}
        <fieldset
          className={`loan-form_section${semesterLocked ? ' loan-form_section--disabled' : ''}`}
          disabled={semesterLocked}
          aria-disabled={semesterLocked}
          aria-label={
            semesterLocked
              ? `${SEMESTER_LABEL} – select a program first`
              : SEMESTER_LABEL
          }
        >
          <legend className="loan-form_section-header">
            <span className="loan-form_bullet" aria-hidden="true">
              •
            </span>
            {SEMESTER_LABEL}
            <span className="loan-form_section-description">
              Choose one, two, or three semesters terms.
            </span>
          </legend>
          <div className="loan-form_pills" role="group">
            {semesterOptions.map(({ value, label }) => {
              const inputId = `semester-${value}`;
              const isChecked = semester.includes(value);
              return (
                <label
                  key={value}
                  htmlFor={inputId}
                  tabIndex={semesterLocked ? -1 : 0}
                  className={`loan-form_pill${isChecked ? ' loan-form_pill--selected' : ''}${semesterLocked ? ' loan-form_pill--locked' : ''}`}
                  onKeyDown={(e) => {
                    if (
                      !semesterLocked &&
                      (e.key === ' ' || e.key === 'Enter')
                    ) {
                      e.preventDefault();
                      setSemester((prev) =>
                        prev.includes(value)
                          ? prev.filter((v) => v !== value)
                          : [...prev, value],
                      );
                    }
                  }}
                >
                  <input
                    type="checkbox"
                    id={inputId}
                    name="semester"
                    value={value}
                    checked={isChecked}
                    onChange={() =>
                      setSemester((prev) =>
                        prev.includes(value)
                          ? prev.filter((v) => v !== value)
                          : [...prev, value],
                      )
                    }
                    className="loan-form_card-input"
                    tabIndex={-1}
                    disabled={semesterLocked}
                  />
                  {label}
                </label>
              );
            })}
          </div>
        </fieldset>

        {/* Credits per Semester – horizontal slider, available after semester is chosen */}
        <fieldset
          className={`loan-form_section${creditsPerSemesterLocked ? ' loan-form_section--disabled' : ''}`}
          disabled={creditsPerSemesterLocked}
          aria-disabled={creditsPerSemesterLocked}
        >
          <legend className="loan-form_section-header">
            <span className="loan-form_bullet" aria-hidden="true">
              •
            </span>
            Credits per Semester
          </legend>
          <div className="loan-form_slider-wrap">
            <output
              className="loan-form_slider-value"
              htmlFor="credits-per-semester"
            >
              <span className="hr-num">{creditsPerSemester}</span> credit
              {creditsPerSemester !== 1 ? 's' : ''}
            </output>
            <input
              type="range"
              id="credits-per-semester"
              className="loan-form_slider"
              min={1}
              max={CREDIT_SEMESTER_MAX}
              step={1}
              value={creditsPerSemester}
              onChange={(e) => {
                setCreditsPerSemester(Number(e.target.value));
                setCreditsSliderTouched(true);
              }}
              aria-valuemin={1}
              aria-valuemax={CREDIT_SEMESTER_MAX}
              aria-valuenow={creditsPerSemester}
              aria-label="Credits per semester"
              disabled={creditsPerSemesterLocked}
            />
            <div className="loan-form_slider-labels">
              {Array.from({ length: CREDIT_SEMESTER_MAX }, (_, i) => {
                const val = i + 1;
                const fullTime = isUndergraduate
                  ? UNDERGRAD_FULL_TIME
                  : GRAD_FULL_TIME;
                const label =
                  val === fullTime
                    ? `${fullTime} (Full-time)`
                    : val === 1 || val % 3 === 0
                      ? String(val)
                      : null;
                if (!label) return null;
                const ratio = (val - 1) / (CREDIT_SEMESTER_MAX - 1);
                return (
                  <span
                    key={val}
                    className="loan-form_slider-tick"
                    style={{ left: `calc(8px + ${ratio} * (100% - 16px))` }}
                  >
                    {label}
                  </span>
                );
              })}
            </div>
          </div>

          {studentType === 'UGRD' &&
            creditsSliderTouched &&
            creditsPerSemester < UNDERGRAD_FULL_TIME &&
            creditsHelpRawUndergrad && (
              <div
                className="loan-form_slider-warning"
                dangerouslySetInnerHTML={{ __html: creditsHelpRawUndergrad }}
              />
            )}
          {studentType === 'GRAD' &&
            creditsSliderTouched &&
            creditsPerSemester < GRAD_FULL_TIME &&
            creditsHelpRawGraduate && (
              <div
                className="loan-form_slider-warning"
                dangerouslySetInnerHTML={{ __html: creditsHelpRawGraduate }}
              />
            )}
        </fieldset>
      </div>
      <div
        className="important-note"
        dangerouslySetInnerHTML={{ __html: importantNote }}
      />

      <button
        type="submit"
        className="loan-form_submit"
        disabled={!canSubmit}
        aria-disabled={!canSubmit}
      >
        {submitText}
      </button>
    </form>
  );
};

export default LoanForm;
