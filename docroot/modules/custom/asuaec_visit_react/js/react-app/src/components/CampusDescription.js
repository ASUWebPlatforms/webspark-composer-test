// src/components/CampusDescription.js
import React, { useEffect, useState, useRef } from 'react';

/**
 * Props:
 *  - campusTid: number|string (taxonomy term ID for campus)
 *  - interestTid: number|string (taxonomy term ID for visit bucket)
 *  - enabled: boolean (only render for Undergrad)
 */
export default function CampusDescription({ campusTid, interestTid, enabled = true }) {
  const [open, setOpen] = useState(false);
  const [html, setHtml] = useState('');
  const [loading, setLoading] = useState(false);
  const popRef = useRef(null);

  // Graduate check
  const personType = (sessionStorage.getItem('persontype') || '').toLowerCase();
  const isGraduate = personType.includes('graduate');

  // Do not render anything for Graduate
  if (!enabled || isGraduate) return null;


  if (!enabled) return null;

  useEffect(() => {
    let cancelled = false;

    async function fetchDescription() {

      // Don’t fetch campus description for Graduate (or non-numeric interestTid)
      const personType = (sessionStorage.getItem('persontype') || '').toLowerCase();
      const isGraduate = personType.includes('graduate');
      const isNumericTid = (v) => /^\d+$/.test(String(v || ''));

      if (!enabled || isGraduate || !campusTid || !interestTid || !isNumericTid(interestTid)) {
        setHtml('');
        return;
      }


      try {
        setLoading(true);
        const res = await fetch(
          `/visit-revamp-api/campus-description/${campusTid}/${interestTid}`
        );
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!cancelled) {
          setHtml(data?.html || '');
        }
      } catch (e) {
        if (!cancelled) setHtml('');
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    fetchDescription();
    return () => {
      cancelled = true;
    };
  }, [campusTid, interestTid, enabled]);

  // Close popover on outside click
  useEffect(() => {
    function onDocClick(e) {
      if (open && popRef.current && !popRef.current.contains(e.target)) {
        setOpen(false);
      }
    }
    document.addEventListener('mousedown', onDocClick);
    return () => document.removeEventListener('mousedown', onDocClick);
  }, [open]);

  return (
    <span className="campus-desc-wrap" ref={popRef}>
      <button
        type="button"
        className="campus-info-btn"
        aria-label="More info"
        onMouseEnter={() => setOpen(true)}
        onMouseLeave={() => setOpen(false)}
        onFocus={() => setOpen(true)}
        onBlur={() => setOpen(false)}
        onClick={() => setOpen(v => !v)}
      >
        i
      </button>

      {open && (
        <div className="campus-info-popover" role="dialog" aria-live="polite">
          {loading ? (
            <div className="muted small">Loading…</div>
          ) : html ? (
            <div dangerouslySetInnerHTML={{ __html: html }} />
          ) : (
            <div className="muted small">No description available.</div>
          )}
        </div>
      )}
    </span>
  );
}