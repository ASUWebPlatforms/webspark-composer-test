import { useState, useEffect } from 'react';

const ds =
  typeof drupalSettings !== 'undefined' &&
  drupalSettings.asu_loan_proration_tool
    ? drupalSettings.asu_loan_proration_tool
    : {};

// Full-time credit thresholds for proration (per federal definition).
const UNDERGRAD_FULL_TIME = ds.undergradMaxCreditLimit || 12;
const GRAD_FULL_TIME = ds.graduateMaxCreditLimit || 9;

// Minimum credits to receive any Direct Loan.
const UNDERGRAD_MIN_CREDITS = ds.undergradLeastCreditLimit || 6;
const GRAD_MIN_CREDITS = ds.graduateLeastCreditLimit || 5;

// Parse pipe-separated options "key|Label" per line into [{value, label}].
function parsePipeOptions(str) {
  if (!str) return [];
  return str
    .replace(/\r\n|\r/g, '\n')
    .split('\n')
    .map((s) => s.trim())
    .filter(Boolean)
    .map((pair) => {
      const [value, label] = pair.split('|');
      return {
        value: (value || '').trim(),
        label: (label || value || '').trim(),
      };
    });
}

// Strip commas and parse to number.
function parseFormatted(str) {
  if (str === null || str === undefined || str === '') return 0;
  return Number(String(str).replace(/,/g, '')) || 0;
}

// Format as "$X,XXX" rounded to nearest dollar.
function formatCurrency(num) {
  return '$' + Math.round(num).toLocaleString('en-US');
}

// Determine current ASU academic year (named after the fall calendar year).
// Fall 2026 + Spring 2027 = AY 2026. June onward shows the upcoming fall year.
function getDefaultAcadYear() {
  const now = new Date();
  return now.getMonth() + 1 >= 6 ? now.getFullYear() : now.getFullYear() + 1;
}

// Return true when the student type value/label indicates undergraduate.
function isUndergradStudentType(studentType) {
  if (!studentType) return false;
  const t = studentType.toLowerCase();
  return t.includes('ugrd') || t.includes('undergrad') || t.includes('ug');
}

// Get annual federal loan cap from drupalSettings.
// Credits options are passed so we can map the selected value to a year tier by index.
function getFederalAnnualCap(formValues, creditsOptions) {
  const { studentType, dependency, credits } = formValues;

  if (!isUndergradStudentType(studentType)) {
    return parseFormatted(ds.graduateMax);
  }

  // Admin configures credits options in ascending order (1st year, 2nd year, 3rd year+).
  const idx = creditsOptions.findIndex((o) => o.value === credits);
  const tier = idx <= 0 ? 'One' : idx === 1 ? 'Two' : 'Three';

  const isIndependent =
    dependency && dependency.toLowerCase().includes('indep');

  if (isIndependent) {
    if (tier === 'One') return parseFormatted(ds.undergradIndependentMaxOne);
    if (tier === 'Two') return parseFormatted(ds.undergradIndependentMaxTwo);
    return parseFormatted(ds.undergradIndependentMaxThree);
  }

  // Dependent (default for undergrad)
  if (tier === 'One') return parseFormatted(ds.undergradDependentMaxOne);
  if (tier === 'Two') return parseFormatted(ds.undergradDependentMaxTwo);
  return parseFormatted(ds.undegradDependentMaxThree);
}

// Keep only the semester entries whose term title matches any selected semester value.
// semesterValues is an array of pill values (e.g. ["fall", "spring"]).
// Term titles from the API look like "Fall 2026", "Spring 2027", "Summer 2027".
function filterSemesterEntries(entries, semesterValues) {
  if (!semesterValues || semesterValues.length === 0) return entries;
  const svs = semesterValues.map((s) => s.toLowerCase());
  return entries.filter(([termTitle]) => {
    const tt = termTitle.toLowerCase();
    if (tt.includes('summer')) return svs.some((sv) => sv.includes('summer'));
    if (tt.includes('spring')) return svs.some((sv) => sv.includes('spring'));
    if (tt.includes('fall')) return svs.some((sv) => sv.includes('fall'));
    return true;
  });
}

const LoanResultsTable = ({ formValues, onLoaded }) => {
  const [loading, setLoading] = useState(true);
  const [fetchError, setFetchError] = useState('');
  const [semesterTotals, setSemesterTotals] = useState({});

  const creditsOptions = parsePipeOptions(ds.creditsCompleted);

  useEffect(() => {
    if (!formValues) return;

    const {
      studentType,
      residency,
      campus,
      college,
      program,
      creditsPerSemester,
      semester,
    } = formValues;

    const acadYear = ds.currentAcadYear || getDefaultAcadYear();
    const includeSummer =
      Array.isArray(semester) &&
      semester.some((s) => s.toLowerCase().includes('summer'))
        ? 1
        : 0;
    //console.log('acadYear:', acadYear);
    const params = new URLSearchParams({
      acad_year: acadYear,
      include_summer: includeSummer ? '1' : '0',
      residency: residency || '',
      acad_career: studentType || '',
      campus: campus || '',
      acad_prog: college || '',
      admit_term: '',
      admit_level: '',
      honors: 0,
      program_fee: program || '',
      credit_hr: creditsPerSemester || 0,
    });

    setLoading(true);
    setFetchError('');
    const fetchUrl = `/tuition/results/json?${params.toString()}`;
    fetch(fetchUrl)
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then((data) => {
        setSemesterTotals(data.semesterTotals || {});
      })

      .catch((err) => {
        console.error('Failed to fetch tuition results:', err);
        setFetchError('Could not load tuition results. Please try again.');
      })
      .finally(() => {
        setLoading(false);
        if (onLoaded) onLoaded();
      });
  }, [formValues]);

  if (loading) {
    return (
      <div className="loan-results_loading" aria-live="polite">
        Loading results…
      </div>
    );
  }

  if (fetchError) {
    return (
      <div className="loan-results_error" role="alert">
        {fetchError}
      </div>
    );
  }

  const { studentType, creditsPerSemester, semester } = formValues;
  const isUndergrad = isUndergradStudentType(studentType);
  const fullTime = isUndergrad ? UNDERGRAD_FULL_TIME : GRAD_FULL_TIME;
  const minCredits = isUndergrad ? UNDERGRAD_MIN_CREDITS : GRAD_MIN_CREDITS;

  // Below minimum enrollment → no Direct Loan disbursed.
  const belowMinEnrollment = creditsPerSemester < minCredits;

  const allEntries = Object.entries(semesterTotals);
  //console.log(semesterTotals, 'received tuition results data');
  const filteredEntries = filterSemesterEntries(allEntries, semester);

  if (filteredEntries.length === 0) {
    return (
      <div className="loan-results_empty">
        No tuition data found for the selected options.
      </div>
    );
  }

  const annualCap = getFederalAnnualCap(formValues, creditsOptions);

  // Proration: clamp at 100%, then round to two decimal places per federal formula.
  // e.g. 8 credits / 9 full-time = 0.8888… → 0.89 × $20,500 = $18,245 annual.
  const prorationPct =
    Math.round(Math.min(1, creditsPerSemester / fullTime) * 100) / 100;
  const annualLoanAmount = belowMinEnrollment ? 0 : annualCap * prorationPct;

  // Each regular semester gets half the prorated annual amount (annualLoanAmount / 2).
  // When fall + spring are both enrolled, summer is limited to the annual-cap remainder
  // (annualCap − annualLoanAmount) rather than another full half-share.
  const basePerSemester = annualLoanAmount / 2;
  const hasBothRegularSems =
    filteredEntries.some(([t]) => t.toLowerCase().includes('fall')) &&
    filteredEntries.some(([t]) => t.toLowerCase().includes('spring'));

  // Round annual to a whole dollar first so fall + spring always sum exactly.
  const roundedAnnual = Math.round(annualLoanAmount);

  const acadYearNum = Number(ds.currentAcadYear || getDefaultAcadYear());
  const acadYearDisplay = `${acadYearNum}-${String(acadYearNum + 1).slice(-2)}`;
  // Build display names from acadYearNum so Fall/Spring always use the correct years
  // regardless of what term titles the tuition API returns.
  const semLabelMap = {
    fall: `Fall ${acadYearNum}`,
    spring: `Spring ${acadYearNum + 1}`,
    summer: `Summer ${acadYearNum + 1}`,
  };

  const rows = filteredEntries.map(([semTitle, tuitionFormatted]) => {
    const tuition = parseFormatted(tuitionFormatted);
    const isSummer = semTitle.toLowerCase().includes('summer');
    const isFall = semTitle.toLowerCase().includes('fall');

    let federalLoan;
    if (isSummer) {
      const summerCap = hasBothRegularSems
        ? Math.max(0, annualCap - annualLoanAmount)
        : basePerSemester;
      federalLoan = Math.round(Math.min(basePerSemester, summerCap));
    } else {
      // Fall rounds up at .5+; spring rounds down — ceil(n/2)+floor(n/2) = n exactly.
      federalLoan = isFall
        ? Math.ceil(roundedAnnual / 2)
        : Math.floor(roundedAnnual / 2);
    }

    const remaining = Math.max(0, tuition - federalLoan);
    const semKey = isFall ? 'fall' : isSummer ? 'summer' : 'spring';
    const semDisplayTitle = semLabelMap[semKey];
    return { semTitle, semDisplayTitle, tuition, federalLoan, remaining };
  });

  const totalTuition = rows.reduce((s, r) => s + r.tuition, 0);
  const totalFederal = rows.reduce((s, r) => s + r.federalLoan, 0);
  const totalRemaining = rows.reduce((s, r) => s + r.remaining, 0);
  const totalCredits = creditsPerSemester * rows.length;
  const semDisplayTitles = (semester || []).map((s) => {
    const key = s.toLowerCase().includes('fall')
      ? 'fall'
      : s.toLowerCase().includes('spring')
        ? 'spring'
        : 'summer';
    return semLabelMap[key];
  });
  const semesterDisplay =
    semDisplayTitles.length <= 1
      ? semDisplayTitles[0] || ''
      : semDisplayTitles.length === 2
        ? `${semDisplayTitles[0]} and ${semDisplayTitles[1]}`
        : `${semDisplayTitles.slice(0, -1).join(', ')}, and ${semDisplayTitles[semDisplayTitles.length - 1]}`;

  return (
    <div>
      <div>
        <hr className="loan-results_divider" />
      </div>
      <div className="loan-results">
        <div className="results-heading">
          <div className="loan-results_title">
            <span className="heading-highlight-gold">Cost Summary</span>
          </div>
          <div>
            <strong>Your annual student loan estimate and tuition costs</strong>
          </div>
          <div>
            The estimate is based on the academic year {acadYearDisplay} and
            your enrollment for {semesterDisplay}.
          </div>
          <br />
        </div>
        <div className="loan-results_table-wrap">
          <table className="loan-results_table">
            <thead>
              <tr>
                <th scope="col">Semester</th>
                <th scope="col">Credits</th>
                <th scope="col">Tuition &amp; Fees</th>
                <th scope="col">Federal Loan</th>
                <th scope="col">Your Remaining Cost</th>
              </tr>
            </thead>
            <tbody>
              {rows.map(
                ({ semDisplayTitle, tuition, federalLoan, remaining }) => (
                  <tr key={semDisplayTitle}>
                    <td>{semDisplayTitle}</td>
                    <td>{creditsPerSemester}</td>
                    <td>{formatCurrency(tuition)}</td>
                    <td>({formatCurrency(federalLoan)})</td>
                    <td>{formatCurrency(remaining)}</td>
                  </tr>
                ),
              )}
            </tbody>
            <tfoot>
              <tr className="loan-results_total-row">
                <th scope="row">Total</th>
                <td>{totalCredits}</td>
                <td>{formatCurrency(totalTuition)}</td>
                <td>{formatCurrency(totalFederal)}</td>
                <td>{formatCurrency(totalRemaining)}</td>
              </tr>
            </tfoot>
          </table>
        </div>
        {belowMinEnrollment && (
          <p className="loan-results_enrollment-note" role="note">
            Students enrolled in fewer than {minCredits} credits per semester
            are not eligible for a federal Direct Loan.
          </p>
        )}
        <div
          className="loan-results_about"
          dangerouslySetInnerHTML={{ __html: ds.resultsAbout }}
        />
        <div
          className="loan-results_disclaimer"
          dangerouslySetInnerHTML={{ __html: ds.resultsDisclaimer }}
        />
      </div>
    </div>
  );
};

export default LoanResultsTable;
