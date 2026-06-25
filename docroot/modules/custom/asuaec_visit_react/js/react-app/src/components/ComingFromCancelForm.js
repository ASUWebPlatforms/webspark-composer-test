// ComingFromCancelForm.js
// Populate React state from Cancel form URL

export async function populateFromCancelForm(
  setSelectedStudentType,
  setSelectedAreaOfInterest,
  setPersonTouched // <-- OPTIONAL (may be undefined)
) {
  try {
    const urlParams = new URLSearchParams(window.location.search);

    // Support both (normal flow) and (cancel flow)
    const ptype = urlParams.get("ptype") || urlParams.get("c-ptype");
    const sid   = urlParams.get("c-sid");

    // Interest can come directly from URL (cancel flow)
    const intVal = urlParams.get("int"); // e.g. "28"

    // 1) Populate person type if present
    if (ptype) {
      setSelectedStudentType(ptype);
      sessionStorage.setItem("persontype", ptype);

      // mark as touched so UI treats it like user selected it
      if (typeof setPersonTouched === "function") {
        setPersonTouched(true);
      }
    }

    // 2) Populate interest
    // If int is present, use it.
    if (intVal) {
      setSelectedAreaOfInterest(intVal);
      sessionStorage.setItem("interest", intVal);
      return;
    }

    // 3) Otherwise, fetch interest from API if sid is available
    if (sid) {
      const response = await fetch(`/custom-api/webform-submission-interest/${sid}`);
      if (!response.ok) {
        console.error("Failed to fetch interest:", response.status);
        return;
      }
      const data = await response.json();

      if (data.interest) {
        setSelectedAreaOfInterest(data.interest);
        sessionStorage.setItem("interest", data.interest);
      }
    }
  } catch (error) {
    console.error("Error populating from Cancel form:", error);
  }
}