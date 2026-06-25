// src/App.js
import React, {
  useState,
  useEffect,
  useMemo,
  useRef,
  useCallback,
  Suspense,
} from "react";
//import CostTable from './components/CostTable';
import "./styles.css";
//import FinancialAidTable from './components/FinancialAidTable';
//import html2pdf from "html2pdf.js";
//import { buildEmailHtml } from './utils/emailBuilder';
const FinancialAidTable = React.lazy(
  () => import("./components/FinancialAidTable"),
);
const CostTable = React.lazy(() => import("./components/CostTable"));

/**
 * Row / column setup
 * IDs correspond to your earlier rows:
 *  '01' = Tuition & fees
 *  '02' = Books & supplies
 *  '03' = Housing
 *  '04' = Transportation
 *
 *
 */

// Read drupalSettings if available; fallback maps used otherwise
const ds =
  typeof drupalSettings !== "undefined" &&
  drupalSettings.asu_cost_comparison_tool
    ? drupalSettings.asu_cost_comparison_tool
    : null;

//const webformId = ds && ds.cost_webform_id ? ds.cost_webform_id : null;
//const LocalStorageKey = webformId ? `asu_costs_draft_${webformId}_v1` : 'asu_costs_compare_tool_draft_v1';
const LocalStorageKey = "Drupal_cost_comp_tool";
const loginUrl = ds.cas_login_url;
const INITIAL_ROWS = [
  {
    id: "01",
    label: "Tuition & fees",
    help: ds.tuitionToolTip || "Tuition and fees",
  },
  {
    id: "02",
    label: "Books & supplies",
    help: ds.booksToolTip || "Books and supplies",
  },
  {
    id: "03",
    label: "Housing and food (on campus)",
    help: ds.housingToolTip || "Housing and food (on campus)",
  },
  {
    id: "04",
    label: "Transportation",
    help: ds.transportationToolTip || "Local transportation",
  },
];

// radio options
const resident_options = [
  { value: "AZ", label: "Arizona resident" },
  { value: "non-az", label: "Non resident" },
  { value: "intl", label: "International student" },
];

const campus_options = [
  { value: "oncampus", label: "On campus" },
  { value: "with_parents", label: "With my parents" },
  { value: "offcampus", label: "Off campus" },
];

// Row id constants (for clarity)
const TUITION_ROW_ID = "01";
const BOOKS_ROW_ID = "02";
const HOUSING_ROW_ID = "03";
const TRANSPORT_ROW_ID = "04";

// helper: numeric validation
const isNumeric = (v) => {
  if (v === "" || v === null || v === undefined) return true;
  const cleaned = String(v).replace(/[\s,$]/g, "");
  return /^[0-9]+(?:\.[0-9]{0,2})?$/.test(cleaned);
};

export default function App() {
  // initial inputs are empty — NO initial defaults
  const [values, setValues] = useState(() => {
    const v = {};
    INITIAL_ROWS.forEach((r) => {
      v[r.id] = { asu: "", school2: "", school3: "" };
    });
    return v;
  });

  //Errors state
  const [errors, setErrors] = useState({});
  const [fieldErrors, setFieldErrors] = useState({});
  const printRef = useRef(null);
  const [isHydrated, setIsHydrated] = useState(false);
  // make a field key
  const fieldKey = (rowId, colKey) => `${rowId}-${colKey}`;
  // set/clear single field error
  const setFieldError = (rowId, colKey, message) => {
    const k = fieldKey(rowId, colKey);
    setErrors((prev) => {
      if (!message) {
        const next = { ...prev };
        delete next[k];
        return next;
      }
      return { ...prev, [k]: message };
    });
  };

  // Labels state for columns
  const [labels, setLabels] = useState({
    your_costs: "Estimated annual costs",
    asu: "Arizona State University",
    school2: "",
    school3: "",
  });

  // update a label by key
  const setLabel = (key, value) =>
    setLabels((prev) => ({ ...prev, [key]: value }));

  // create columns derived from labels
  const COLUMNS = useMemo(
    () => [
      { key: "your_costs", label: labels.your_costs, editable: false },
      { key: "asu", label: labels.asu, editable: false },
      { key: "school2", label: labels.school2, editable: true },
      { key: "school3", label: labels.school3, editable: true },
    ],
    [labels],
  );

  const baseUrl = window.location.origin;

  let localWebUrl = "";
  localWebUrl = baseUrl;
  //console.log('Local web URL:', localWebUrl);
  const [totalCosts, setTotalCosts] = useState({
    tuition: 0,
    housing: 0,
    books: 0,
    transportation: 0,
    other: 0,
  });

  const [showTotals, setShowTotals] = useState(false);
  const [totalGiftAid, setTotalGiftAid] = useState(0);
  const [totalLoans, setTotalLoans] = useState(0);
  const [netPrice, setNetPrice] = useState(0);
  const [remainingCosts, setRemainingCosts] = useState(0);
  const [aidValues, setAidData] = useState({});
  const FALLBACK_TUITION = {
    AZ: ds.defaultTuitionAz,
    "non-az": ds.defaultTuitionNonAz,
    intl: ds.defaultTuitionIntl,
  };
  const FALLBACK_BOOKS = {
    AZ: ds.defaultBooksAndSupplies,
    "non-az": ds.defaultBooksAndSupplies,
    intl: ds.defaultBooksAndSupplies,
  };
  const FALLBACK_TRANSPORT = {
    AZ: ds.defaultTransportation,
    "non-az": ds.defaultTransportation,
    intl: ds.defaultTransportation,
  };
  const FALLBACK_HOUSING = {
    oncampus: ds.defaultOnCampusHousing,
    with_parents: ds.defaultWithParentsMealPlans,
    offcampus: ds.defaultOffCampusLiving,
  };
  const TUITION_DEFAULTS_BY_RESIDENCE = FALLBACK_TUITION;
  const BOOKS_DEFAULTS_BY_RESIDENCE = FALLBACK_BOOKS;
  const TRANSPORT_DEFAULTS_BY_RESIDENCE = FALLBACK_TRANSPORT;
  const HOUSING_DEFAULTS_BY_CAMPUS = FALLBACK_HOUSING;
  // radios: start empty so no defaults are applied on mount
  const [residentValue, setResidentValue] = useState("");
  const [campusValue, setCampusValue] = useState("");
  const [school2, setSchool2] = useState("");
  const [school3, setSchool3] = useState("");
  const [emailModalOpen, setEmailModalOpen] = useState(false);
  const [emailToSend, setEmailToSend] = useState("");
  const [emailError, setEmailError] = useState("");
  const [emailSaving, setEmailSaving] = useState(false);
  const [sid, setSid] = useState(null);
  const [emailSent, setEmailSent] = useState(false);
  const [emailMessage, setEmailMessage] = useState("");
  const [emailContent, setEmailContent] = useState("");
  const [reset, setReset] = useState(false);
  const [hasLabelErrors, setHasLabelErrors] = useState(false);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [drupalUser, setDrupalUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [calculateButton, setCalculateButton] = useState("Calculate");
  const [isCalculating, setIsCalculating] = useState(false);
  const [hasAidErrors, setHasAidErrors] = useState(false);
  const [hasErrors, setHasCostErrors] = useState(false);
  const [resetKey, setResetKey] = useState(0);

  //Create form to enter email address
  const [email, setEmail] = useState("");

  // small email regex (simple, sufficient for UI validation)
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  // submission meta kept in state so it survives re-renders and is easy to read
  const [submissionMeta, setSubmissionMeta] = useState(() => {
    const mirror = loadLocalMirror();
    return {
      wsid: mirror?.wsid ?? null,
      webTo: mirror?.webTo ?? null,
      savedAt: mirror?.savedAt ?? null,
    };
  });

  // ------ LocalStorage helpers & canonical mirror functions ------
  function loadLocalMirror() {
    try {
      const raw = localStorage.getItem(LocalStorageKey);
      if (!raw) return null;
      return JSON.parse(raw);
    } catch (e) {
      //console.warn('loadLocalMirror failed', e);
      return null;
    }
  }

  function saveLocalMirror({
    wsid = null,
    webTo = null,
    payload = null,
    savedAt = null,
  } = {}) {
    try {
      const obj = {
        wsid,
        webTo,
        payload,
        savedAt: savedAt ?? new Date().toISOString(),
      };
      localStorage.setItem(LocalStorageKey, JSON.stringify(obj));
    } catch (e) {
      //console.warn('saveLocalMirror failed', e);
    }
  }

  function loadFromLocalStorage() {
    try {
      const raw = localStorage.getItem(LocalStorageKey);
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      if (parsed && parsed.payload) return parsed.payload;
      return parsed;
    } catch (e) {
      //console.warn('Failed to parse localStorage draft', e);
      return null;
    }
  }

  useEffect(() => {
    const fetchUserData = async () => {
      try {
        const response = await fetch("/api/cost-comparison-tool/user-data", {
          credentials: "include",
        });
        if (response.ok) {
          const data = await response.json();
          setDrupalUser(data);
          setIsAuthenticated(data.authenticated);
        }
      } catch (error) {
        console.error("Error fetching user data:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchUserData();
  }, []);

  /**
   * When residency is selected (non-empty), apply defaults for:
   *  - Tuition (row 01)
   *  - Books   (row 02)
   *  - Transport (row 04)
   *
   * This will overwrite those ASU cells when the user picks residency.
   */
  useEffect(() => {
    //console.log(residentValue);
    if (!residentValue) {
      // Do not apply defaults on mount or when resident is cleared.
      return;
    }
    const tDefault = TUITION_DEFAULTS_BY_RESIDENCE[residentValue];
    const bDefault = BOOKS_DEFAULTS_BY_RESIDENCE[residentValue];
    const trDefault = TRANSPORT_DEFAULTS_BY_RESIDENCE[residentValue];
    setValues((prev) => {
      const next = { ...prev };

      if (next[TUITION_ROW_ID]) {
        next[TUITION_ROW_ID] = {
          ...next[TUITION_ROW_ID],
          asu:
            typeof tDefault !== "undefined"
              ? String(tDefault)
              : next[TUITION_ROW_ID].asu,
        };
      }
      if (next[BOOKS_ROW_ID]) {
        next[BOOKS_ROW_ID] = {
          ...next[BOOKS_ROW_ID],
          asu:
            typeof bDefault !== "undefined"
              ? String(bDefault)
              : next[BOOKS_ROW_ID].asu,
        };
      }
      if (next[TRANSPORT_ROW_ID]) {
        next[TRANSPORT_ROW_ID] = {
          ...next[TRANSPORT_ROW_ID],
          asu:
            typeof trDefault !== "undefined"
              ? String(trDefault)
              : next[TRANSPORT_ROW_ID].asu,
        };
      }
      return next;
    });
  }, [residentValue]); // only runs after user selects residency

  /**
   * When campus is selected (non-empty), apply housing defaults (row 03).
   */
  useEffect(() => {
    if (!campusValue) {
      return;
    }
    // console.log(campusValue);
    const housingDefault = HOUSING_DEFAULTS_BY_CAMPUS[campusValue];

    setValues((prev) => {
      const next = { ...prev };
      if (next[HOUSING_ROW_ID]) {
        next[HOUSING_ROW_ID] = {
          ...next[HOUSING_ROW_ID],
          asu:
            typeof housingDefault !== "undefined"
              ? String(housingDefault)
              : next[HOUSING_ROW_ID].asu,
        };
      }
      return next;
    });
  }, [campusValue]);

  const handleChange = (rowId, colKey, newValue) => {
    setValues((prev) => {
      const prevRow = prev[rowId] || {};
      const prevValue = prevRow[colKey] ?? "";
      if (prevValue === newValue) {
        // nothing changed — avoid triggering a re-render
        return prev;
      }
      return {
        ...prev,
        [rowId]: {
          ...prevRow,
          [colKey]: newValue,
        },
      };
    });

    const key = fieldKey(rowId, colKey);

    // instant validation (unchanged)
    if (!isNumeric(newValue)) {
      setErrors((prev) => ({
        ...prev,
        [key]: "Only numeric values are allowed (e.g. 12345 or 12345.67)",
      }));
    } else {
      setErrors((prev) => {
        if (!prev[key]) return prev;
        const next = { ...prev };
        delete next[key];
        return next;
      });
    }
    setShowTotals(false);
  };

  function closeEmailModal() {
    setEmailModalOpen(false);
    setEmailError("");
    setEmailSent(false);
    setEmailMessage("");
  }

  async function fetchWithTimeout(url, options = {}, timeoutMs = 15000) {
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), timeoutMs);
    try {
      const resp = await fetch(url, { ...options, signal: controller.signal });
      clearTimeout(id);
      return resp;
    } catch (err) {
      clearTimeout(id);
      throw err;
    }
  }

  async function saveToServer(payload) {
    const endpoint = `${localWebUrl}/api/cost-comparison-tool`;

    try {
      const resp = await fetch(endpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify(payload),
      });

      const text = await resp.text();
      let body = null;
      try {
        body = text ? JSON.parse(text) : null;
      } catch (_) {}

      return {
        ok: resp.ok,
        status: resp.status,
        body,
        text,
        rawResponse: resp,
      };
    } catch (err) {
      console.error("saveToServer error", err);
      return { ok: false, status: 0, error: err.message || err };
    }
  }

  // Apply saved payload (from server or local) to React state
  function applySavedPayload(payload) {
    if (!payload) return;
    //if(reset) return;
    if (payload.costs) {
      // mapping expects payload.costs to be same shape as `values` (rowId -> { asu, school2, school3 })
      setValues(payload.costs);
    }
    if (payload.aid) setAidData(payload.aid);
    if (payload.totals) setTotalCosts(payload.totals);
    //console.log(payload.resident, 'payload.resident');
    if (payload.resident) setResidentValue(payload.resident);
    if (payload.campus) setCampusValue(payload.campus);
    if (payload.labels) setLabels(payload.labels);
    // show totals if payload looks like a completed calculation
    setShowTotals(true);
  }

  // On mount: if authenticated, try load server draft then fallback to local; if not authenticated, hydrate from local
  useEffect(() => {
    let cancelled = false;
    (async () => {
      if (loading) return;
      const mirror = loadLocalMirror();
      const webToken = mirror?.webTo ?? null;

      if (isAuthenticated) {
        try {
          const resp = await fetch(
            `${localWebUrl}/api/cost-comparison-tool/get-user-submission`,
            {
              method: "GET",
              credentials: "include",
              headers: {
                Accept: "application/json",
                ...(webToken ? { "X-WEB-TOKEN": webToken } : {}),
              },
            },
          );
          if (resp.ok) {
            const json = await resp.json();
            setSid(json.sid || null);
            if (!cancelled && json?.data) {
              applySavedPayload(json.data);
              try {
                saveLocalMirror({
                  wsid: json.sid ?? null,
                  webTo: webToken ?? null,
                  payload: json.data,
                  savedAt: new Date().toISOString(),
                });
              } catch (e) {}
              return;
            }
          }
        } catch (e) {
          //console.warn('Failed to fetch server draft', e);
        }
      }

      // fallback to local
      const local = loadFromLocalStorage();
      if (!cancelled && local) {
        applySavedPayload(local);
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [loading, isAuthenticated]);

  // When user logs in, push local draft to server? For now we try claim flow in separate useEffect.
  useEffect(() => {
    if (!isAuthenticated) return;
    // previously we pushed local draft; keep behavior minimal here
    const local = loadFromLocalStorage();
    if (local) {
      applySavedPayload(local);
    }
  }, [isAuthenticated]);

  // --------- tryClaimSavedSubmission: will be called automatically after login (useEffect below) ----------
  async function tryClaimSavedSubmission() {
    let saved = null;
    try {
      const raw = localStorage.getItem(LocalStorageKey);
      if (!raw) return;
      saved = JSON.parse(raw);
    } catch (err) {
      //console.warn('No saved submission to claim or parse failed:', err);
      return;
    }

    if (!saved?.wsid || !saved?.webTo) {
      return;
    }

    // Ensure user is authenticated
    try {
      const meResp = await fetch(
        `${localWebUrl}/api/cost-comparison-tool/user-data`,
        { credentials: "include" },
      );
      if (!meResp.ok) {
        return;
      }
      const me = await meResp.json();
      if (!me?.uid) return;
    } catch (err) {
      //console.warn('User probe failed:', err);
      return;
    }

    // Attempt to claim
    try {
      const claimResp = await fetch(
        `${localWebUrl}/api/cost-comparison-tool/claim/${encodeURIComponent(saved.wsid)}`,
        {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json",
            "X-WEB-TOKEN": saved.webTo,
          },
          body: JSON.stringify({
            note: "Claiming anonymous submission after sign-in",
          }),
        },
      );

      const text = await claimResp.text();
      let body = null;
      try {
        body = text ? JSON.parse(text) : null;
      } catch (e) {
        body = text;
      }

      if (!claimResp.ok) {
        //console.warn('Claim failed', claimResp.status, body);
        return;
      }

      // success — UPDATE local mirror (do NOT delete)
      // Prefer server-returned values if present; otherwise keep the existing saved values.
      const newWsid = body?.wsid ?? saved.wsid ?? null;
      const newWebTo = body?.webTo ?? saved.webTo ?? null;
      const newPayload = body?.data ?? saved.payload ?? null;
      const newSavedAt = new Date().toISOString();

      // Merge and persist the canonical mirror shape
      const newMirror = {
        wsid: newWsid,
        webTo: newWebTo,
        payload: newPayload,
        savedAt: newSavedAt,
        // mark claimed for client-side UI if you want (optional)
        claimed: true,
      };
      try {
        localStorage.setItem(LocalStorageKey, JSON.stringify(newMirror));
      } catch (e) {
        //console.warn('Failed to update local mirror after claim', e);
      }

      // Update in-memory meta so UI can reflect the change
      setSubmissionMeta({
        wsid: newWsid,
        webTo: newWebTo,
        savedAt: newSavedAt,
      });

      // If server returned submission data, apply it to the UI
      if (body?.data) {
        applySavedPayload(body.data);
      }

      // Optionally update sid if server provided it
      if (body?.wsid) {
        setSid(body.wsid);
      }

      // console.log('Claim success — local mirror updated', newMirror);
    } catch (err) {
      console.error("Claim request failed", err);
    }
  }

  // Run tryClaimSavedSubmission automatically after login (whenever isAuthenticated becomes true)
  useEffect(() => {
    if (!isAuthenticated) return;

    tryClaimSavedSubmission();
  }, [isAuthenticated]);

  const handleEmailSubmit = async function (e) {
    e.preventDefault();

    // basic client-side validation first
    if (!emailToSend || !emailRegex.test(String(emailToSend).trim())) {
      setEmailError("Please enter a valid email address.");
      return;
    }

    // disable UI so user can't double-click
    if (emailSaving) {
      console.info("Already saving — ignoring duplicate submit");
      return;
    }

    setEmailSaving(true);
    setEmailError("");

    const mirror = loadLocalMirror();
    const webToken = mirror?.webTo ?? submissionMeta.webTo ?? null;
    let subSid = mirror?.wsid ?? submissionMeta.wsid ?? sid ?? null;
    //console.log(subSid);
    // If we don't have sid, try to fetch from server using token (if any)
    if (!subSid) {
      try {
        const resp = await fetch(
          `${localWebUrl}/api/cost-comparison-tool/get-user-submission`,
          {
            method: "GET",
            credentials: "include",
            headers: {
              Accept: "application/json",
              ...(webToken ? { "X-WEB-TOKEN": webToken } : {}),
            },
          },
        );
        if (resp.ok) {
          const json = await resp.json();
          subSid = json.sid ?? null;
        } else {
          //console.warn('Server returned non-OK when fetching submission:', resp.status);
        }
      } catch (err) {
        //console.warn('Failed to fetch server submission:', err);
      }
    }

    if (!subSid) {
      setEmailError(
        "Please click calculate first and then send yourself an email.",
      );
      setEmailSaving(false);
      return;
    }

    try {
      const emailEndpoint = `${localWebUrl}/api/cost-comparison-tool/email-update/${encodeURIComponent(subSid)}`;
      const headers = { "Content-Type": "application/json" };
      if (webToken) headers["X-WEB-TOKEN"] = webToken;

      const resp = await fetchWithTimeout(
        emailEndpoint,
        {
          method: "POST",
          credentials: "include",
          headers,
          body: JSON.stringify({ email: String(emailToSend).trim() }),
        },
        15000,
      );

      const ct = resp.headers.get("content-type") || "";
      const body = ct.includes("application/json")
        ? await resp.json()
        : await resp.text();
      if (!resp.ok) {
        const msg =
          body && body.error
            ? body.error
            : typeof body === "string"
              ? body
              : "Server error";
        setEmailError(`Server error: ${msg}`);
        return;
      }

      setEmailSent(true);
      setEmailMessage(`Confirmation email sent to ${emailToSend}.`);
    } catch (err) {
      console.error("handleEmailSubmit error", err);
      if (err.name === "AbortError") {
        setEmailError("Request timed out. Please try again.");
      } else {
        setEmailError("Network error. Your email was saved locally.");
        const mirrorSave = loadLocalMirror() || { payload: {} };
        mirrorSave.payload = mirrorSave.payload || {};
        mirrorSave.payload.email_to = String(emailToSend).trim();
        try {
          localStorage.setItem(LocalStorageKey, JSON.stringify(mirrorSave));
        } catch (e) {
          //console.warn('mirror save failed', e);
        }
      }
    } finally {
      setEmailSaving(false);
    }
  };

  // handle form submission

  const handleSubmit = async (e) => {
    if (e && typeof e.preventDefault === "function") {
      e.preventDefault();
    }
    // compute payload and update UI
    const payload = {
      costs: values,
      aid: aidValues,
      totals: totalCosts,
      resident: residentValue,
      campus: campusValue,
      labels,
      timestamp: new Date().toISOString(),
    };

    setShowTotals(true);

    //localStorage.setItem(LocalStorageKey, JSON.stringify(payload));

    try {
      const result = await saveToServer(payload);
      // console.log('saveToServer result', result);

      if (result && result.ok && result.body?.status === "success") {
        const { wsid, webTo } = result.body;

        const meta = {
          wsid: wsid ?? null,
          webTo: webTo ?? null,
          savedAt: new Date().toISOString(),
        };
        setSubmissionMeta(meta);
        setSid(wsid ?? null);

        saveLocalMirror({
          wsid: meta.wsid,
          webTo: meta.webTo,
          payload,
          savedAt: meta.savedAt,
        });

        //setShowTotals(true);
      } else {
        const message = result?.body?.message ?? result?.text ?? "Save failed";
        //console.warn('Save failed:', message);
        saveLocalMirror({
          wsid: submissionMeta.wsid,
          webTo: submissionMeta.webTo,
          payload,
          savedAt: new Date().toISOString(),
        });
        alert(`Save failed: ${message}`);
      }
    } catch (err) {
      console.error("Unhandled submit error", err);
      saveLocalMirror({
        wsid: submissionMeta.wsid,
        webTo: submissionMeta.webTo,
        payload,
        savedAt: new Date().toISOString(),
      });
      alert("Network error — your draft was saved locally.");
    }

    // Block submit if there are validation errors
    if (hasAidErrors || hasErrors) {
      alert("Please fix validation errors before calculating.");
      return;
    }
    if (isCalculating) return;
    setIsCalculating(true);
    setCalculateButton("Updating");

    setTimeout(() => {
      setCalculateButton("Calculate");
      setIsCalculating(false);
    }, 1000);
  };

  //Scholarship and loans fields change handler
  const handleAidChange = (data) => {
    setAidData(data);
  };

  const handleReset = () => {
    const v = {};
    INITIAL_ROWS.forEach((r) => {
      v[r.id] = { asu: "", school2: "", school3: "" };
    });

    // set everything once
    setValues(v);
    setAidData({});
    setTotalCosts({
      tuition: 0,
      housing: 0,
      books: 0,
      transportation: 0,
      other: 0,
    });
    setTotalGiftAid(0);
    setTotalLoans(0);
    setNetPrice(0);
    setRemainingCosts(0);
    setHasAidErrors(false);
    setShowTotals(false);
    setResidentValue("");
    setCampusValue("");
    setSchool2("");
    setSchool3("");
    setErrors({});
    setResetKey((k) => k + 1);
    try {
      localStorage.removeItem(LocalStorageKey);
    } catch (e) {
      //console.warn('handleReset: localStorage removal failed', e);
    }
    setReset(true);
    // turn off flag on next tick (so children that read it can react)
    setTimeout(() => setReset(false), 0);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  //code to download pdf

  const downloadPdf = async () => {
    if (!isHydrated || !printRef.current) return;
    const el = printRef.current;

    // hide placeholders
    const inputs = Array.from(el.querySelectorAll("input"));
    const placeholders = inputs.map((i) => ({ i, p: i.placeholder }));
    inputs.forEach((i) => (i.placeholder = ""));

    el.classList.add("pdf-mode");

    try {
      const [{ default: html2canvas }, { jsPDF }] = await Promise.all([
        import("html2canvas"),
        import("jspdf"),
      ]);

      // CONFIG
      const filename = "cost-comparison-results.pdf";
      const scale = 2; // 1–2 recommended
      const jpegQuality = 0.85; // 0.7–0.9
      const M = { top: 10, right: 10, bottom: 10, left: 10 }; // mm

      // render DOM to canvas
      const canvas = await html2canvas(el, {
        scale,
        useCORS: true,
        backgroundColor: null,
      });

      // white background (prevents black bars with JPEG)
      const full = document.createElement("canvas");
      full.width = canvas.width;
      full.height = canvas.height;
      const ctx = full.getContext("2d");
      ctx.fillStyle = "#fff";
      ctx.fillRect(0, 0, full.width, full.height);
      ctx.drawImage(canvas, 0, 0);

      const pdf = new jsPDF("p", "mm", "a4");

      const pageW = pdf.internal.pageSize.getWidth();
      const pageH = pdf.internal.pageSize.getHeight();
      const usableW = pageW - M.left - M.right;
      const usableH = pageH - M.top - M.bottom;

      // px → mm (effective DPI = 96 * scale)
      const pxToMm = (px) => (px * 25.4) / (96 * scale);

      const imgWmm = pxToMm(full.width);
      const imgHmm = pxToMm(full.height);

      // scale image to fit usable area
      const fitScale = Math.min(usableW / imgWmm, usableH / imgHmm);

      const renderW = imgWmm * fitScale;
      const renderH = imgHmm * fitScale;

      // center image within margins
      const x = M.left + (usableW - renderW) / 2;
      const y = M.top + (usableH - renderH) / 2;

      const imgData = full.toDataURL("image/jpeg", jpegQuality);
      pdf.addImage(imgData, "JPEG", x, y, renderW, renderH);

      pdf.save(filename);

      // cleanup
      full.width = full.height = 0;
      canvas.width = canvas.height = 0;
    } finally {
      placeholders.forEach(({ i, p }) => (i.placeholder = p));
      el.classList.remove("pdf-mode");
    }
  };

  const canCalculate = Boolean(residentValue && campusValue);

  return (
    <>
      <div id="cost-tool-print-area" ref={printRef}>
        <form
          className="cost-comparison-form"
          onSubmit={handleSubmit}
          onKeyDown={(e) => {
            if (e.key === "Enter" && e.target.tagName === "INPUT") {
              e.preventDefault();
            }
          }}
        >
          {/* Residency radio group — nothing is pre-selected */}
          <div className="row">
            <div className="gray-radio-block col-md-4 bg gray-2-bg">
              <div className="js-form-item form-item js-form-type-radio">
                <i class="fas fa-solid fa-globe color-fa"></i>
                <legend>I am an/a</legend>
                {resident_options.map((opt) => (
                  <label key={opt.value} className="form-check-label option">
                    <input
                      type="radio"
                      name="resident-status"
                      className="form-check-input react-input"
                      value={opt.value}
                      checked={residentValue === opt.value}
                      onChange={(e) => setResidentValue(e.target.value)}
                    />{" "}
                    {opt.label}
                  </label>
                ))}
              </div>
            </div>

            {/* Campus radio group — nothing is pre-selected */}
            <div className="gray-radio-block col-md-4 bg gray-2-bg">
              <div className="js-form-item form-item js-form-type-radio">
                <span class="fontawesome-icon-inline">
                  <span class="fa-solid fa-house-chimney color-fa"></span>
                </span>
                <legend>I will be living</legend>
                {campus_options.map((opt) => (
                  <label key={opt.value} className="form-check-label option">
                    <input
                      type="radio"
                      name="campus-choice"
                      className="form-check-input react-input"
                      value={opt.value}
                      checked={campusValue === opt.value}
                      onChange={(e) => setCampusValue(e.target.value)}
                    />{" "}
                    {opt.label}
                  </label>
                ))}
              </div>
            </div>
          </div>
          <Suspense fallback={<div>Loading…</div>}>
            <CostTable
              // key={`cost-${resetKey}`}
              rows={INITIAL_ROWS}
              columns={COLUMNS}
              values={values}
              setValues={setValues}
              onChange={handleChange}
              setTotalCosts={setTotalCosts}
              showTotals={showTotals}
              onLabelChange={setLabel}
              errors={errors}
              onValidationChange={setHasCostErrors}
              onLabelValidationChange={setHasLabelErrors}
            />

            <FinancialAidTable
              // key={`aid-${resetKey}`}
              onHydrated={() => setIsHydrated(true)}
              totalCosts={totalCosts}
              showTotals={showTotals}
              LocalStorageKey={LocalStorageKey}
              aidValues={aidValues}
              setValues={setAidData}
              onAidChange={handleAidChange}
              school2Name={labels.school2}
              school3Name={labels.school3}
              labels={labels}
              errors={errors}
              onValidationChange={setHasAidErrors}
              reset={reset}
            />
          </Suspense>
          <div className="col container flex-custom-column">
            <div className="submitButtons col-md-4">
              <div style={{ marginTop: 12 }}>
                <button
                  type="submit"
                  className="btn btn-primary no-print"
                  disabled={
                    hasAidErrors ||
                    hasErrors ||
                    hasLabelErrors ||
                    isCalculating ||
                    !canCalculate
                  }
                >
                  {calculateButton}
                </button>
              </div>

              <div>&nbsp;</div>
              <div style={{ marginTop: 12, marginLeft: 15 }}>
                {/* RESET BUTTON */}
                <button
                  type="button"
                  className="btn btn-primary no-print"
                  onClick={handleReset}
                >
                  Reset All
                </button>
              </div>
            </div>
            <div className="rightsideTextDiv col">
              {!isAuthenticated ? (
                <p>
                  <a href={loginUrl}>Sign in</a> to save your work or view your
                  previous work and to{" "}
                  <strong>email yourself your results</strong>
                </p>
              ) : (
                <p>
                  {" "}
                  <div className="email-form">
                    {" "}
                    <button
                      className="btn print-button no-print"
                      type="button"
                      onClick={() => setEmailModalOpen(true)}
                    >
                      Email yourself your results
                    </button>
                  </div>{" "}
                  <button
                    type="button"
                    className="btn print-button no-print"
                    onClick={downloadPdf}
                  >
                    Download as PDF
                  </button>
                </p>
              )}
            </div>
          </div>

          {emailModalOpen && (
            <div
              className="modal-overlay"
              id="email-modal-div"
              role="dialog"
              aria-modal="true"
              aria-label="Email your results"
            >
              <div className="modal-card">
                <header className="modal-header">
                  <div className="section-title">Email your results</div>
                  <button
                    type="button"
                    onClick={closeEmailModal}
                    aria-label="Close modal"
                  >
                    ×
                  </button>
                </header>{" "}
                <div className="modal-form-content">
                  {!emailSent ? (
                    <form
                      className="email-modal-form"
                      onSubmit={handleEmailSubmit}
                    >
                      <div className="modal-content">
                        <label htmlFor="emailTo">Email address</label>
                        <input
                          id="emailTo"
                          type="email"
                          value={emailToSend}
                          onChange={(e) => setEmailToSend(e.target.value)}
                          className={`form-textfield form-control ${emailError ? "input-error" : ""}`}
                          placeholder="you@example.edu"
                          required
                        />
                        {emailError && (
                          <div className="error" role="alert">
                            {emailError}
                          </div>
                        )}
                      </div>
                      <footer className="modal-footer">
                        <button
                          type="button"
                          className="btn btn-secondary"
                          onClick={closeEmailModal}
                          disabled={emailSaving}
                        >
                          Cancel
                        </button>
                        <button
                          type="submit"
                          className="btn btn-primary"
                          onClick={handleEmailSubmit}
                          disabled={emailSaving}
                        >
                          {emailSaving ? "Saving…" : "Save & Email"}
                        </button>
                      </footer>
                    </form>
                  ) : (
                    <div className="email-message">{emailMessage}</div>
                  )}
                </div>
              </div>
            </div>
          )}
        </form>
      </div>
    </>
  );
}
