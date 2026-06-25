// src/components/WaitCursor.js
import { useEffect } from "react";

export default function WaitCursor({ active, message }) {
  useEffect(() => {
    // Still toggle cursor globally
    document.body.style.cursor = active ? "wait" : "";
    return () => { document.body.style.cursor = ""; };
  }, [active]);

  if (!active) return null;

  // Inline message
  return <p style={{ fontStyle: "italic", color: "#191919" }}><strong>{message}</strong></p>;
}
