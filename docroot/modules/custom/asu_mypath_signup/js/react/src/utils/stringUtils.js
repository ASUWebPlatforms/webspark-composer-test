export function splitLines(str) {
  if (!str) return [];
  return str
    .replace(/\r\n|\r/g, '\n')
    .split('\n')
    .map((s) => s.trim())
    .filter(Boolean);
}

// Parse one entry per line "key|Label" or "key|Label|Subtitle" → [{ value, label, subtitle }, ...]
export function parsePipeOptions(str) {
  return splitLines(str).map((pair) => {
    const [value, label, subtitle] = pair.split(':');
    return {
      value: (value || '').trim(),
      label: (label || value || '').trim(),
    };
  });
}
