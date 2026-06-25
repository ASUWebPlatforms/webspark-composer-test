function isMobile() {
    var mobile = "show";
    if (window.location.search.indexOf("?mobile=true") > -1) {
      mobile = "hide";
    }
    return mobile
}

export default isMobile;