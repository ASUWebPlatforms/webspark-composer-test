// takes in simple text answer, converts to lowercase, checks against correct answer
function checkTextAnswer(studentInput, correct) {
  if (studentInput === correct) {
    // alert(`Well done, the correct answer is ${metaUnitsAnswer}"`);
    alert(`Well done, the correct answer is ${Correct_answer.toUpperCase()}`);
  }
  else {
    alertIncorrect();
  }
}

// takes in simple nuber checks against correct answer
function checkNumAnswer(studentInput, correct){
  if (studentInput == correct) {
    // alert(`Well done, the correct answer is ${metaUnitsAnswer}"`);
    alert(`Well done, the correct answer is ${correct}.`);
  }
  else {
    alertIncorrect();
  }
}

function alertIncorrect() {
  alert("Oops! That is incorrect. Please review the lesson and try again");
  return false;
}



// Step 2, question 1
function checkOzoneHighQ3(){
  // The following is what I changed.
  Student_answer = document.querySelector('[name="ozoneHighQ3"]').value.toLowerCase();
  Correct_answer = "april";

  checkTextAnswer(Student_answer, Correct_answer);

}

// Step 2, question 2
function checkOzoneLowQ3(){
  // The following is what I changed.
  Student_answer = document.querySelector('[name="ozoneLowQ3"]').value.toLowerCase();
  Correct_answer = "october";

  checkTextAnswer(Student_answer, Correct_answer);
}

// Step 4, question 1
function checkOzoneHigh(){
  // The following is what I changed.
  Student_answer = document.querySelector('[name="ozoneHigh"]').value.toLowerCase();
  Correct_answer = "may";

  checkTextAnswer(Student_answer, Correct_answer);
}

// Step 4, question 2
function checkOzoneLow(){

  Student_answer = document.querySelector('[name="ozoneLow"]').value.toLowerCase();
  Correct_answer = "august";

  checkTextAnswer(Student_answer, Correct_answer);
}

// Step 5, question 1
function checkJsShift(){

  Student_answer = document.querySelector('[name="jsShift"]').value.toLowerCase();
  Correct_answer = "yes";

  checkTextAnswer(Student_answer, Correct_answer);
}

// Step 5, question 2
function checkJsSpeed(){

  Student_answer = document.querySelector('[name="jsSpeed"]').value.toLowerCase();
  Correct_answer = "yes";

  checkTextAnswer(Student_answer, Correct_answer);
}

// Step 5, question 3
function checkJsNorth(){

  Student_answer = document.querySelector('[name="jsNorth"]').value.toLowerCase();
  Correct_answer = "summer";

  checkTextAnswer(Student_answer, Correct_answer);
}

// Step 5, question 4
function checkSurfaceCurrents(){

  Student_answer = document.querySelector('[name="surfaceCurrents"]').value.toLowerCase();
  Correct_answer = "no";

  checkTextAnswer(Student_answer, Correct_answer);
}

// Step 5, question 4
function checkHPSLocation(){

  Student_answer = document.querySelector('[name="hpsLocation"]').value.toLowerCase();
  Correct_answer = "yes";

  checkTextAnswer(Student_answer, Correct_answer);
}

// Step 5, question 5
function checkJsContinent(){

  Student_answer = document.querySelector('[name="jsContinent"]').value.toLowerCase();
  Correct_answer = "asia";

  checkTextAnswer(Student_answer, Correct_answer);
}




// takes in simple text answer, converts to lowercase, checks against correct answer
function checkTextAnswer(studentInput, correct) {
  if (studentInput === correct) {
    // alert(`Well done, the correct answer is ${metaUnitsAnswer}"`);
    alert(`Well done, the correct answer is ${Correct_answer.toUpperCase()}`);
  }
  else {
    alertIncorrect();
  }
}

// takes in simple nuber checks against correct answer
function checkNumAnswer(studentInput, correct){
  if (studentInput == correct) {
    // alert(`Well done, the correct answer is ${metaUnitsAnswer}"`);
    alert(`Well done, the correct answer is ${correct}.`);
  }
  else {
    alertIncorrect();
  }
}

function alertIncorrect() {
  alert("Oops! That is incorrect. Please review the lesson and try again");
  return false;
}

// step 2 question 1
function checkMetaUnit(){
  Student_answer = document.querySelector('[name="metaUnit"]').value.toLowerCase()
  Correct_answer = "ppb"

  checkTextAnswer(Student_answer, Correct_answer);
}

// step 2 question 2
function checkMetaInterval(){
  Student_answer = document.querySelector('[name="metaInterval"]').value.toLowerCase();
  minute = "minute";
  hour = "hour";

  if (Student_answer.includes(minute) && Student_answer.includes(hour)) {
    alert("Well done, the correct answer is minute and hour.");
  }
  else {
    alertIncorrect();
  }
}

// step 4 question 1
function checkOzoneMax(){
  Student_answer = document.querySelector('[name="ozoneMax"]').value;
  Correct_answer = 67.88;

  checkNumAnswer(Student_answer, Correct_answer);
}

// step 4 question 2
function checkOzoneMin(){
  Student_answer = document.querySelector('[name="ozoneMin"]').value;
  Correct_answer = 6.67;

  checkNumAnswer(Student_answer, Correct_answer);
}

// step 4 question 3
function checkOzoneHighMonth(){
  Student_answer = document.querySelector('[name="ozoneHighMonth"]').value.toLowerCase();
  number = "3";
  month = "march";

  if (Student_answer.includes(number) || Student_answer.includes(month)) {
    // alert(`Well done, the correct answer is ${metaUnitsAnswer}"`);
    alert("Well done, the correct answer is March or 3" );
  }
  else {
    alertIncorrect();
  }
}

// step 4 question 4
function checkOzoneLowMonth(){
  Student_answer = document.querySelector('[name="ozoneLowMonth"]').value.toLowerCase();
  number = "6";
  month = "june";

  if (Student_answer.includes(number) || Student_answer.includes(month)) {
    // alert(`Well done, the correct answer is ${metaUnitsAnswer}"`);
    alert("Well done, the correct answer is June or 6");
  }
  else {
    alertIncorrect();
  }
}

// step 5 question 1
function checkSigFig() {
  Student_answer = document.querySelector('[name="sigFig"]').value;
  Correct_answer = 4;

  checkNumAnswer(Student_answer, Correct_answer);
}

// step 5 question 2
function checkSeasonYield() {
  Student_answer = document.querySelector('[name="seasonYield"]').value.toLowerCase();
  Correct_answer = "winter";

  checkTextAnswer(Student_answer, Correct_answer);
}

// step 6 question 1
function checkStdHigh() {
  Student_answer = document.querySelector('[name="stdHigh"]').value.toLowerCase();
  Correct_answer = "may";

  checkTextAnswer(Student_answer, Correct_answer);
}

// step 7 question 1
function checkJan16() {
  Student_answer = document.querySelector('[name="jan16"]').value;
  Correct_answer = 487;

  checkNumAnswer(Student_answer, Correct_answer);
}

// step 9 question 1 -- FINAL
function checkBdaOzoneLow() {
  Student_answer = document.querySelector('[name="bdaOzone"]').value.toLowerCase();
  Correct_answer = "summer";

  checkTextAnswer(Student_answer, Correct_answer);
}

