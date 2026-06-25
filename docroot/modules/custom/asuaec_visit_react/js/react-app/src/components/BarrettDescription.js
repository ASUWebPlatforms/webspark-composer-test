// BarrettDescription.js
import React, { useEffect, useRef, useState } from "react";

export default function BarrettDescription({
  enabled = true,
  description = "",
  ariaLabel = "More info about Barrett tour",
}) {
  const [open, setOpen] = useState(false);
  const wrapRef = useRef(null);

  // Close when clicking outside (same behavior pattern as campus tooltip)
  useEffect(() => {
    const onDocClick = (e) => {
      if (open && wrapRef.current && !wrapRef.current.contains(e.target)) {
        setOpen(false);
      }
    };
    document.addEventListener("mousedown", onDocClick);
    return () => document.removeEventListener("mousedown", onDocClick);
  }, [open]);

  if (!enabled || !description) return null;

  return (
    <span className="campus-desc-wrap" ref={wrapRef}>
      <button
        type="button"
        className="campus-info-btn barrett"
        aria-label={ariaLabel}
        onMouseEnter={() => setOpen(true)}
        onMouseLeave={() => setOpen(false)}
        onFocus={() => setOpen(true)}
        onBlur={() => setOpen(false)}
        onClick={() => setOpen((v) => !v)}
      >
        i
      </button>

      {open && (
        <div className="campus-info-popover barrett" role="dialog" aria-live="polite">
          <p>{description}</p>
        </div>
      )}
    </span>
  );
}