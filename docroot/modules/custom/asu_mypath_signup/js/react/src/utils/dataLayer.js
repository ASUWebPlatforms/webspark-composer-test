export const pushToDataLayer = (data) => {
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push(data);
};
