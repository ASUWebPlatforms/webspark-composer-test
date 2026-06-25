<?php

namespace Drupal\sp_learningmod\Service;

class CouncilMeetingService
{
  /**
   * Returns all the questions and correct answers for the council quiz.
   */
  public function getQuestions()
  {
    return [
      [
        'question' => '1. The prostitution problem along Scott Avenue MAINLY involves which of the following?',
        'options' => [
          'A' => 'A. Middle-age women seeking male prostitutes.',
          'B' => 'B. Homosexual men seeking male prostitutes.',
          'C' => 'C. Middle-age men seeking teenage and middle-age female prostitutes.',
          'D' => 'D. College-age men seeking college-aged female prostitutes.',
          'E' => 'E. Men of all ages seeking transvestite prostitutes.',
        ],
        'correct_answer' => 'C',
        'correct_explanation' => '<h3>Correct</h3><p><strong>You answered: C. Middle-age men seeking teenage and middle-age female prostitutes.</strong></p><p>You are correct.</p><p>This was established in the university research report and supported in many interviews in which the prostitutes are described as female and the clients male.</p>',
        'incorrect_explanation' => '<h3>Incorrect</h3><p><strong>You answered: {USER_ANSWER}</strong></p><p>The correct answer was: C. Middle-age men seeking teenage and middle-age female prostitutes.</p><p>This was established in the university research report and supported in many interviews in which the prostitutes are described as female and the clients male.</p>',
      ],
      [
        'question' => '2. Where do MOST of the negotiations for prostitution take place?',
        'options' => [
          'A' => 'A. In nearby motels.',
          'B' => 'B. In clients\' cars.',
          'C' => 'C. Over cellular telephones.',
          'D' => 'D. On the street and at curbside in front of bars.',
          'E' => 'E. In bars with the bartenders serving as the brokers.',
        ],
        'correct_answer' => 'D',
        'correct_explanation' => "<h3>Correct</h3><p><strong>You answered: D. On the street and at curbside in front of bars.</strong></p><p>You are correct.</p><p>This was established in the university research report; the electronic surveillance report; and the interviews with the prostitute, Tammy Faith, and client, David Mallard. Negotiations do occur in motels, clients' cars, over cell phones, and in bars, but not as commonly as on the street.</p>",
        'incorrect_explanation' => "<h3>Incorrect</h3><p><strong>You answered: {USER_ANSWER}</strong></p><p>The correct answer was: D. On the street and at curbside in front of bars.</p><p>This was established in the university research report; the electronic surveillance report; and the interviews with the prostitute, Tammy Faith, and client, David Mallard. Negotiations do occur in motels, clients' cars, over cell phones, and in bars, but not as commonly as on the street.</p>",
      ],
      [
        'question' => '3. Which of the following is NOT a contributing factor to the prostitution problem on Scott Avenue?',
        'options' => [
          'A' => 'A. Bar owners, managers, and staff tolerate and at times facilitate the prostitution trade.',
          'B' => 'B. Police corruptly ignore the prostitution problem by accepting payoffs.',
          'C' => 'C. The owner, manager, and staff of the Secrete Inn tolerate and facilitate the trade.',
          'D' => 'D. The street drug market\'s clients often are also prostitution clients.',
          'E' => 'E. Many prostitutes are drug-addicted and work to support their habit.',
        ],
        'correct_answer' => 'B',
        'correct_explanation' => "<h3>Correct</h3><p><strong>You answered: B. Police corruptly ignore the prostitution problem by accepting payoffs from prostitutes, pimps and bar owners.</strong></p><p>You are correct.</p><p>Although the current police response might be ineffective, there is no evidence that police corruption is a contributing factor.</p>
        <p>The bar owners' and staffs' tolerance and facilitation was established in the interviews with Detective Allen; the prostitute, Amy; bar owners, Don Karner and Lucky Petersen; bartender, Rex Blue; student Pete Flash; and the letter to the editor.</p>
        <p>The contribution of sporting events and conventions was established in the interview with the prostitute, Tammy Faith and Officer Ryan.</p>
        <p>The Secrete Inn's tolerance was established in the interviews of Detective Wilson; former undercover officer Geoff Tomson; Secrete Inn manager, Bill Webster; and supported by the interview of maid, Mimi Rodriguez, and by the commerce report.</p>
        <p>The link between the drug market and the prostitution trade was established in the interviews with Detective Wright; Officer Fabel; the prostitute, Betty; the client, Jim Paxton; and the police reports on prostitution and cocaine, and narcotics.</p>
        <p>The drug addiction of prostitutes was established in the interviews with the prostitute, Princess; Rev. Francis Powell; and social workers Claire Lambert, Linda Loftin, Kathy Wilkes, and Cathy Lask.</p>",
        'incorrect_explanation' => "<h3>Incorrect</h3><p><strong>You answered: {USER_ANSWER}</strong></p><p>The correct answer was: B. Police corruptly ignore the prostitution problem by accepting payoffs from prostitutes, pimps and bar owners.</p><p>Although the current police response might be ineffective, there is no evidence that police corruption is a contributing factor.</p>
        <p>The bar owners' and staffs' tolerance and facilitation was established in the interviews with Detective Allen; the prostitute, Amy; bar owners, Don Karner and Lucky Petersen; bartender, Rex Blue; student Pete Flash; and the letter to the editor.</p>
        <p>The contribution of sporting events and conventions was established in the interview with the prostitute, Tammy Faith and Officer Ryan.</p>
        <p>The Secrete Inn's tolerance was established in the interviews of Detective Wilson; former undercover officer Geoff Tomson; Secrete Inn manager, Bill Webster; and supported by the interview of maid, Mimi Rodriguez, and by the commerce report.</p>
        <p>The link between the drug market and the prostitution trade was established in the interviews with Detective Wright; Officer Fabel; the prostitute, Betty; the client, Jim Paxton; and the police reports on prostitution and cocaine, and narcotics.</p>
        <p>The drug addiction of prostitutes was established in the interviews with the prostitute, Princess; Rev. Francis Powell; and social workers Claire Lambert, Linda Loftin, Kathy Wilkes, and Cathy Lask.</p>",
      ],
      [
        'question' => '4. Where do the sex acts MAINLY take place?',
        'options' => [
          'A' => "A. In clients' cars.",
          'B' => "B. In the bars' bathrooms.",
          'C' => 'C. In nearby hotels.',
          'D' => 'D. In the alleys behind the businesses.',
          'E' => 'E. In the bushes in adjacent residential neighborhoods.',
        ],
        'correct_answer' => 'A',
        'correct_explanation' => "<h3>Correct</h3><p><strong>You answered: A. In clients' cars.</strong></p><p>You are correct.</p><p>The sex acts mainly occur in the clients' cars. This was established in the interviews of Officer Nelson; the prostitute, Vee Lox; and client, David Mallard. Sex acts do occur in these other locations, but not as commonly as in clients' cars.</p>",
        'incorrect_explanation' => "<h3>Incorrect</h3><p><strong>You answered: {USER_ANSWER}</strong></p><p>The correct answer was: A. In clients' cars.</p><p>The sex acts mainly occur in the clients' cars. This was established in the interviews of Officer Nelson; the prostitute, Vee Lox; and client, David Mallard. Sex acts do occur in these other locations, but not as commonly as in clients' cars.</p>",
      ],
      [
        'question' => '5. Which of the following is true?',
        'options' => [
          'A' => 'A. Some of the clients are in town attending a convention.',
          'B' => 'B. Some of the clients are men who live in the community.',
          'C' => 'C. Some of the clients are married.',
          'D' => 'D. Some clients are local college and high school students.',
          'E' => 'E. Many of the clients are very concerned about getting caught.',
          'F' => 'F. All of the above.',
          'G' => 'G. None of the above.',
        ],
        'correct_answer' => 'F',
        'correct_explanation' => "<h3>Correct</h3><p><strong>You answered: F. All of the above.</strong></p><p>You are correct.</p><p>The diverse backgrounds of the clients was established in interviews of them; interviews of Officers Fabel and Mosby; and the prostitute, Amy.</p><p>Clients' concern about getting caught was established in the interviews of Officer Mosby; and clients, Stanley Wiltern and Richard Meyer.</p><p>Clients are from many different walks of life and are not from any particular social or cultural background in Central City. Most of them are very cautious which is why they prefer to make their deals away from the public, preferably in their cars.</p>",
        'incorrect_explanation' => "<h3>Incorrect</h3><p><strong>You answered: {USER_ANSWER}</strong></p><p>The correct answer was: F. All of the above.</p><p>The diverse backgrounds of the clients was established in interviews of them; interviews of Officers Fabel and Mosby; and the prostitute, Amy.</p><p>Clients' concern about getting caught was established in the interviews of Officer Mosby; and clients, Stanley Wiltern and Richard Meyer.</p><p>Clients are from many different walks of life and are not from any particular social or cultural background in Central City. Most of them are very cautious which is why they prefer to make their deals away from the public, preferably in their cars.</p>",
      ],
      [
        'question' => '6. What is the CURRENT main police department response to street prostitution?',
        'options' => [
          'A' => 'A. Closure of streets to change traffic patterns.',
          'B' => 'B. Arrests of prostitutes and clients.',
          'C' => 'C. Relaxation of the regulation of indoor prostitution.',
          'D' => 'D. Redevelopment of the area.',
          'E' => 'E. Diversion of prostitutes into drug rehabilitation.',
        ],
        'correct_answer' => 'B',
        'correct_explanation' => '<h3>Correct</h3><p><strong>You answered: B. Arrests of prostitutes and clients and police patrol in the area.</strong></p><p>You are right. The primary responses of the Central City police have been to patrol the area, move prostitutes along, and send in the vice squad to make arrests. This was established in the interviews of Officer Jordan, Detective Allen, Officer Ryan, Detective Wilson, Officer Rickels, and Commander Rule, and the police report on sex offenses.</p><p>Although some prostitutes end up in drug rehab in Central City it is not the primary police response and there is no reported attempt to divert them away from the criminal justice system. Relaxing the regulation of indoor prostitution (in massage parlors, brothels, or strip clubs), redeveloping the area or changing traffic patterns have to date not been considered.</p>',
        'incorrect_explanation' => '<h3>Incorrect</h3><p><strong>You answered: {USER_ANSWER}</strong></p><p>The correct answer was: B. Arrests of prostitutes and clients and police patrol in the area.</p><p>The primary responses of the Central City police have been to patrol the area, move prostitutes along, and send in the vice squad to make arrests. This was established in the interviews of Officer Jordan, Detective Allen, Officer Ryan, Detective Wilson, Officer Rickels, and Commander Rule, and the police report on sex offenses.</p><p>Although some prostitutes end up in drug rehab in Central City it is not the primary police response and there is no reported attempt to divert them away from the criminal justice system. Relaxing the regulation of indoor prostitution (in massage parlors, brothels, or strip clubs), redeveloping the area or changing traffic patterns have to date not been considered.</p>',
      ],
      [
        'question' => '7. How do clients find prostitutes?',
        'options' => [
          'A' => 'A. They cruise the area looking for prostitutes they know.',
          'B' => 'B. A pimp acts as the middle-man.',
          'C' => "C. They call prostitutes on their cell phones and arrange to meet in Lucky's Bar.",
          'D' => 'D. They answer advertisements in local newspapers.',
          'E' => 'E. They call numbers written on toilet walls.',
        ],
        'correct_answer' => 'A',
        'correct_explanation' => "<h3>Correct</h3><p><strong>You answered: A. They cruise the area looking for prostitutes they know.</strong></p><p>You are correct.</p><p>This is established by the electronic surveillance report; and in interviews with clients, Rick Sampier, Richard Meyer, and Stanley Wiltern. It is well known that driving down Scott Avenue clients will find prostitutes in certain areas, usually around Lucky's Bar, or other areas that are run down or near abandoned buildings. Investigators do not report that pimps are operating on Scott Avenue.</p>",
        'incorrect_explanation' => "<h3>Incorrect</h3><p><strong>You answered: {USER_ANSWER}</strong></p><p>The correct answer was: A. They cruise the area looking for prostitutes they know.</p><p>This is established by the electronic surveillance report; and in interviews with clients, Rick Sampier, Richard Meyer, and Stanley Wiltern. It is well known that driving down Scott Avenue clients will find prostitutes in certain areas, usually around Lucky's Bar, or other areas that are run down or near abandoned buildings. Investigators do not report that pimps are operating on Scott Avenue.</p>",
      ],
      [
        'question' => '8. Which is NOT a reliable measure of success after plan implementation?',
        'options' => [
          'A' => 'A. Decreased citizen complaints about street prostitution.',
          'B' => 'B. Increased arrests of clients and prostitutes.',
          'C' => 'C. Decreased number of prostitutes visible on the streets at particular times.',
          'D' => 'D. Decreased calls for service from Scott Avenue area.',
          'E' => 'E. All of the above are reliable measures.',
          'F' => 'F. None of the above are reliable measures.',
        ],
        'correct_answer' => 'B',
        'correct_explanation' => '<h3>Correct</h3><p><strong>You answered: B. Increased arrests of clients and prostitutes.</strong></p><p>You are correct.</p><p>The volume of arrests (whether an increase or decrease) is not necessarily a valid indicator of success; it might merely reflect the level of police activity in dealing with the problem. Measuring the number of arrests may be important, but only in the proper context (for example, if the level of police effort to make arrests could be controlled for, then the number of arrests made might be a valid measure of the level of prostitution activity). Ultimately, police want prostitution in the area to cease, and constantly increasing arrest rates would suggest that is not occurring. The mayor acknowledged in the news article that a doubling of prostitution arrests has not solved the problem and the police report on sex offenses confirms this.</p>',
        'incorrect_explanation' => '<h3>Incorrect</h3><p><strong>You answered: {USER_ANSWER}</strong></p><p>The correct answer was: B. Increased arrests of clients and prostitutes.</p><p>The volume of arrests (whether an increase or decrease) is not necessarily a valid indicator of success; it might merely reflect the level of police activity in dealing with the problem. Measuring the number of arrests may be important, but only in the proper context (for example, if the level of police effort to make arrests could be controlled for, then the number of arrests made might be a valid measure of the level of prostitution activity). Ultimately, police want prostitution in the area to cease, and constantly increasing arrest rates would suggest that is not occurring. The mayor acknowledged in the news article that a doubling of prostitution arrests has not solved the problem and the police report on sex offenses confirms this.</p>',
      ],
      [
        'question' => '9. Where are reported crimes and arrests of all types most heavily concentrated?',
        'options' => [
          'A' => 'A. In the upper end of Scott Avenue (1200-1400 blocks).',
          'B' => 'B. There is no concentration; reported crimes and arrests are evenly distributed throughout the area.',
          'C' => 'C. In the residential neighborhoods south of Scott Avenue.',
          'D' => 'D. In the lower end of Scott Avenue (200-300 blocks).',
          'E' => 'E. None of the above.',
        ],
        'correct_answer' => 'D',
        'correct_explanation' => '<h3>Correct</h3><p><strong>You answered: D. In the lower end of Scott Avenue (the 200-300 blocks)</strong>.</p><p>You are correct.</p><p>This is clearly established on the crime and arrest maps; in the interviews with Detectives Wright and Allen, and Officer Fabel; and in the electronic surveillance report.</p>',
        'incorrect_explanation' => '<h3>Incorrect</h3><p><strong>You answered: {USER_ANSWER}</strong></p><p>The correct answer was: D. In the lower end of Scott Avenue (the 200-300 blocks).</p><p>This is clearly established on the crime and arrest maps; in the interviews with Detectives Wright and Allen, and Officer Fabel; and in the electronic surveillance report.</p>',
      ],
      [
        'question' => '10. Which of the following is NOT among the specific harms being caused by street prostitution in Central City?',
        'options' => [
          'A' => 'A. Discarded waste (condoms and syringes) is unsightly and hazardous.',
          'B' => 'B. Prostitutes are commonly injured in assaults by clients.',
          'C' => 'C. Legitimate business is being disrupted by prostitution.',
          'D' => 'D. Citizen confidence in the police is being eroded.',
          'E' => 'E. Girls as young as 12 are being kidnapped and trafficked to other cities to work as prostitutes.',
        ],
        'correct_answer' => 'E',
        'correct_explanation' => "<h3>Correct</h3><p><strong>You answered: E. Girls as young as 12 are being kidnapped and trafficked to other cities to work as prostitutes.</strong></p><p>You are correct.</p><p>There is no evidence of kidnapping and trafficking of young girls.</p><p>Concerns about discarded waste (condoms and used syringes) was established in the interview of resident, Chris Glatz; the public health article; and the police discarded articles report.</p><p>Prostitutes' injuries from assaults by clients was established in the interviews of nurse, Shari Williams; social workers, Linda Loftin and Cathy Lask; and supported (although not established) by the EMS run sheet report and the police crime statistics report.</p><p>The disruption to legitimate business was established in the letter to the editor; the interviews with Theodore Howell, Alderperson Stephen Bets, residents Wanda Fops and Randy Bright, senior home director, Melvin Goodrich; the citizen survey; and the commerce report.</p>",
        'incorrect_explanation' => "<h3>Incorrect</h3><p><strong>You answered: {USER_ANSWER}</strong></p><p>The correct answer was: E. Girls as young as 12 are being kidnapped and trafficked to other cities to work as prostitutes.</p><p>There is no evidence of kidnapping and trafficking of young girls.</p><p>Concerns about discarded waste (condoms and used syringes) was established in the interview of resident, Chris Glatz; the public health article; and the police discarded articles report.</p><p>Prostitutes' injuries from assaults by clients was established in the interviews of nurse, Shari Williams; social workers, Linda Loftin and Cathy Lask; and supported (although not established) by the EMS run sheet report and the police crime statistics report.</p><p>The disruption to legitimate business was established in the letter to the editor; the interviews with Theodore Howell, Alderperson Stephen Bets, residents Wanda Fops and Randy Bright, senior home director, Melvin Goodrich; the citizen survey; and the commerce report.</p>",
      ],
    ];
  }

  /**
   * Evaluates the user's answers.
   *
   * @param array $user_answers
   *   The answers submitted by the user.
   *
   * @return array
   *   Evaluation results including score and feedback.
   */
  public function evaluateAnswers(array $user_answers)
  {
    $questions = $this->getQuestions();
    $correct_count = 0;
    $total_questions = count($questions);
    $results = [];

    foreach ($questions as $index => $question) {
      $user_answer = $user_answers[$index] ?? null;
      $correct_answer = $question['correct_answer'];

      $is_correct = ($user_answer === $correct_answer);
      if ($is_correct) {
        $correct_count++;
      }

      $results[] = [
        'question' => $question['question'],
        'user_answer' => $user_answer,
        'correct_answer' => $correct_answer,
        'is_correct' => $is_correct,
        'explanation' => $question['explanation'],
      ];
    }

    $score_percentage = ($correct_count / $total_questions) * 100;
    $passed = $score_percentage >= 80;

    return [
      'results' => $results,
      'score' => $score_percentage,
      'passed' => $passed,
    ];
  }
}
