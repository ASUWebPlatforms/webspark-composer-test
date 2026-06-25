let slideIndex = 0;
let stopSliding = false;
showSlides();
let timeOutMethod;

function currentSlide(n) {
 // console.log("slideIndex", slideIndex );
 // console.log(n, (slideIndex + (n)));
  slideIndex = slideIndex + (n);

  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  if (slideIndex > slides.length ) {
    slideIndex = 1
  }else if(slideIndex == 0){
    slideIndex = slides.length;
  }
  
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active-slide", "");
  }
  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active-slide";
  // stopSliding = true;
  clearTimeout(stopSliding);
  stopSliding = setTimeout(showSlides, 10000);
}

function showReqSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  slideIndex = n;
  if (slideIndex > slides.length) {slideIndex = 1}    
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active-slide", "");
  }
  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active-slide";
  clearTimeout(stopSliding);
  stopSliding = setTimeout(showSlides, 10000);
  
}

function showSlides() {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  slideIndex++;
  if (slideIndex > slides.length) {slideIndex = 1}    
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active-slide", "");
  }
  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active-slide";

  // if(!stopSliding){
    stopSliding = setTimeout(showSlides, 3000); // Change image every 3 seconds
  // }
}preprocess: false