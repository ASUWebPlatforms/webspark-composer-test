/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./templates/**/*.twig", "./src/**/*.{twig,js}"],
  safelist: ["py-8", "py-24"],
  prefix: "tw-",
  important: false,
  corePlugins: { preflight: false },
  theme: {
    extend: {
      colors: {
        "asu-gold": "#FFC627",
        "asu-maroon": "#8C1D40",
        "asu-green": "#78BE20",
        "asu-orange": "#FF7F32",
        "asu-blue": "#00A3E0",
        "asu-gray-1": "#FAFAFA",
        "asu-gray-2": "#E8E8E8",
        "asu-gray-3": "#D0D0D0",
        "asu-gray-4": "#BFBFBF",
        "asu-gray-5": "#747474",
        "asu-gray-6": "#484848",
        "asu-gray-7": "#191919"
      },
      backgroundImage: {
        "sample-report": "url('/themes/custom/analytics/assets/images/sample-report.jpg')"
      }
    }
  }
};
