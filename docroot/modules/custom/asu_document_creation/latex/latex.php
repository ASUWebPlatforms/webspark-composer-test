<?php
/*
 * Get current User node
 */
function getuser_node() {
  global $user;
  $result = db_select('node', 'n')
          ->fields('n', array('nid'))
          ->condition('uid', $user->uid, '=')
          ->condition('status', 0, '>')
          ->condition('type', 'formatadvising', '=')
          ->execute()
          ->fetchAssoc();
  
  return $result['nid'];
}

/*
 * Download the Latex File
 */

function download_latex() {
  global $user;
  
  $user_node = node_load(getuser_node());
  for ($ct = 0; $ct < count($user_node->field_chapter_title['und']); $ct++) {
    $field_chapter_titles[] = strtoupper($user_node->field_chapter_title['und'][$ct]['value']);
  }

  $latex_path = DRUPAL_ROOT . '/' . drupal_get_path('module', 'formatadv') . "/latex";

  $structure = DRUPAL_ROOT . '/sites/default/files/latex';
  if (!file_exists($structure)) {
    mkdir($structure, 0755);
  }

  $download_path = DRUPAL_ROOT . '/sites/default/files/latex';
  $filename = $download_path .'/' . $user->name . '.zip';
  chmod($filename, 755);
  
  if($filename) {
    unlink($filename);
  }
  
  $zip = new ZipArchive();
  $opened = $zip->open($filename, ZIPARCHIVE::CREATE);
  if ($opened !== TRUE) {
    die("cannot open {$filename} for writing.");
  }
  $extraFiles = array(
    'ack.tex',
    'appendix1.tex',
    'asudis.bst',
    'dis.bib',
    'notation.tex',
    'README.md',
    'vita.tex'
  );
  foreach ($extraFiles as $eF) {
    $zip->addFile($latex_path . '/extraFiles/' . $eF, '' . $eF);
  }
  $zip->addFromString('asudis.sty', _get_sty());
  $zip->addFromString('dis.tex', _get_tex());
  $zip->addFromString('abstract.tex', _get_abstract());
  for ($x = 0; $x < count($field_chapter_titles); $x++) {
    $chapter_num = $x + 1;
    $zip->addFromString('chapter' . $chapter_num . '.tex', _get_chapter($field_chapter_titles[$x], $chapter_num));
  }

  $zip->close();
  
  
  if (file_exists($filename)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filename));
    flush(); // Flush system output buffer
    readfile($filename);
    exit;
  }
}


/*
 * Return values for _get_sty function
 */

function _get_sty() {
  $user_node = node_load(getuser_node());
  $formatadv_ct_form = drupal_get_form("formatadvising_node_form", $user_node);
  $field_document_type = $formatadv_ct_form['field_document_type']['und']['#options'][$user_node->field_document_type['und'][0]['value']];

  $sty = "
% Dissertation style file by John Shumway.
% Attempts to meet requirements of the Graduate Education of Arizona State 
% University
% No guarantees - PLEASE CHECK FORMAT CAREFULLY!
% 
% Setup the page layout (8 1/2 x 11 one and one-half spaced page, for 12pt font)
% 1.25\" side margins, 1.00\" top and bottom margins
% 
%\geometry{top=71.0truept,hmargin=90.344truept,height=648.0truept,includefoot,
%letterpaper}%,showframe,showcrop}
%
\geometry{top=1.0in,hmargin=1.25in,height=9.0in,includefoot,
letterpaper}%,showframe,showcrop}
%
% Define singlespace and doublespace commands for 12pt fonts.
%
\\newcommand{\doublespace} {
\\renewcommand{\baselinestretch}{1.66}\small\\normalsize
}
\\newcommand{\oneandhalfspace} {
\\renewcommand{\baselinestretch}{1.24}\small\\normalsize
}
\\newcommand{\singlespace} {
\\renewcommand{\baselinestretch}{0.9}\small\\normalsize
}
\singlespace

%
% Title page.
%
% Define \"\defensemonth\" \"\gradmonth\" \"\gradyear\" commands for title page.
\\title{\\tt$\backslash$\string title}
\author{\\tt$\backslash$\string author}
\\newcommand{\defensemonth}[1]{\\renewcommand{\@defensemonth}{#1}}
\\newcommand{\@defensemonth}{\\tt$\backslash$\string defensemonth}
\\newcommand{\gradmonth}[1]{\\renewcommand{\@gradmonth}{#1}}
\\newcommand{\@gradmonth}{\\tt$\backslash$\string gradmonth}
\\newcommand{\gradyear}[1]{\\renewcommand{\@gradyear}{#1}}
\\newcommand{\@gradyear}{\\tt$\backslash$\string gradyear}
% Define \"\chair\" and \member commands for title page.
\\newcommand{\chair}[1]{\\renewcommand{\@chair}{#1}}
\\newcommand{\@chair}{\\tt$\backslash$\string chair}
\\newcommand{\memberOne}[1]{\\renewcommand{\@memberOne}{#1}}
\\newcommand{\@memberOne}{\\tt$\backslash$\string memberOne}
\\newcommand{\memberTwo}[1]{\\renewcommand{\@memberTwo}{#1}}
\\newcommand{\@memberTwo}{\\tt$\backslash$\string memberTwo}
\\newcommand{\memberThree}[1]{\\renewcommand{\@memberThree}{#1}}
\\newcommand{\@memberThree}{\\tt$\backslash$\string memberThree}
\\newcommand{\memberFour}[1]{\\renewcommand{\@memberFour}{#1}}
\\newcommand{\@memberFour}{\\tt$\backslash$\string memberFour}
% Define \"\degreeName\" command for Title Page.
% (should set to \"Doctor of Philosophy\")
\\newcommand{\degreeName}[1]{\\renewcommand{\@degreeName}{#1}}
\\newcommand{\@degreeName}{\\tt$\backslash$\string degreeName}
%
% Redefine maketitle
\\newlength{\\fiveblanklines}\setlength{\\fiveblanklines}{0.7 in}
\\newlength{\\tenblanklines}\setlength{\\tenblanklines}{1.5 in}
\\renewcommand\maketitle{
\\enlargethispage{0.5in} %make extra room where page number would go
\\thispagestyle{empty}
\\noindent
  \begin{minipage}[t][647.0truept][t]{\linewidth}
  \begin{center}
  \@title\\\ \\ \\\
by\\\ \\ \\\
\@author
\\end{center}
\\vspace{\\fiveblanklines}
\begin{center}
\singlespace
A " . $field_document_type . " Presented in Partial Fulfillment\\\
of the Requirements for the Degree\\\
\@degreeName
\\end{center}
\\vspace{\\tenblanklines}
\begin{center}
\singlespace
Approved \@defensemonth\ \@gradyear\ by the\\\
Graduate Supervisory Committee:\\\
\ \\\
\@chair\\\
\\end{center}
\\vfill
\begin{center}
\doublespace
ARIZONA STATE UNIVERSITY\\\
\@gradmonth\ \@gradyear
\\end{center} 
\\end{minipage}
\clearpage
}
		
%
% redefine abstract environment
%
\\renewenvironment{abstract}{
\setcounter{page}{1}
\begin{center}
\doublespace
ABSTRACT
\\end{center}
} {
\clearpage
}

%
% make acknowledgements environment
%
\\newenvironment{acknowledgements}{
\begin{center}
\doublespace
ACKNOWLEDGMENTS
\\end{center}
} {
\clearpage
}
		
%
% define \acknowledgementpage
%
\\newcommand{\acknowledgementpage}[1]{
\begin{center}
\doublespace
ACKNOWLEDGMENTS
{\itshape #1}
\\end{center}
\clearpage
}

%
% define \dedicationpage
%
%\\newcommand{\dedicationpage}[1]{
%\clearpage\\vspace*{0.5\\textheight}
%\centerline{\itshape #1}\clearpage
\\newcommand{\dedicationpage}[1]{
\begin{center}
\doublespace
DEDICATION
{\itshape #1}
\\end{center}
\clearpage	
}
		
%
% define \blankpage
%
\\newcommand{\blankpage}{
\begin{center}
\doublespace
\\end{center}
} {
\clearpage
}
		
%
% define Preface Page
%
\\newcommand{\prefacepage}[1]{
\begin{center}
\doublespace
PREFACE
{\itshape #1}
\\end{center}
\clearpage	
}
			
%		
% Define Symbols Page
%
\\newcommand{\symbolspage}[1]{
\begin{center}
\doublespace
LIST OF SYMBOLS
{\\\Symbol~\hfill Page \par}
\\end{center}
\clearpage	
}
		
%		
% Define Biographical Page
%
\\newcommand{\biographicalpage}[1]{
\begin{center}
\doublespace
BIOGRAPHICAL SKETCH
{\itshape #1}
\\end{center}
\clearpage	
}
		
%
% Fix the table of contents
%
%
% Get leader dots right.  They should all be spaced the same and need to be
% added for chapters and parts.
%
\\renewcommand{\cftchapdotsep}{1.7}
\\renewcommand{\cftchapleader}{\cftdotfill{\cftchapdotsep}}
\\renewcommand{\cftpartdotsep}{1.7}
\\renewcommand{\cftpartleader}{\cftdotfill{\cftpartdotsep}}
\\renewcommand{\cftsecdotsep}{1.7}
\\renewcommand{\cftsubsecdotsep}{1.7}
\\renewcommand{\cfttabdotsep}{1.7}
\\renewcommand{\cftfigdotsep}{1.7}
%
% Fonts for the chapter titles and part titles.
%
\\renewcommand{\cftchapfont}{\\rm}
\\renewcommand{\cftpartfont}{\\rm}
\\renewcommand{\cftchappagefont}{\\rm}
\\renewcommand{\cftpartpagefont}{\\rm}
%
% Indentations: These are in accordance with the chart at the top of page 4 of
% the document
% ftp://tug.ctan.org/pub/tex-archive/macros/latex/contrib/tocloft/tocloft.pdf
% I simply moved each of the following sections \"up\" one level to get the
% indentations right.
%
\cftsetindents{chapter}{1.5em}{1.5em}
\cftsetindents{section}{3.0em}{2.3em}
\cftsetindents{subsection}{5.3em}{3.2em}
%
% Spacing between entries is taken care of by the double-spacing in this 
% section, so no need for extra space before chapter or part entries.
%
\setlength{\cftbeforepartskip}{0truept}
\setlength{\cftbeforechapskip}{0truept}
%
% Get the title for the TOC normal sized, centered, and at the right height.
%
\setlength{\cftbeforetoctitleskip}{-64.0truept}
\setlength{\cftaftertoctitleskip}{0truept}
%
\\renewcommand{\contentsname}{TABLE OF CONTENTS}
\\renewcommand{\cfttoctitlefont}{\hfill\\normalsize\\rm}
\\renewcommand{\cftaftertoctitle}{\hfill}
%
\\newcommand{\cftlabel}{CHAPTER}
%
% Get rid of the header underline.
\\renewcommand{\headrulewidth}{0pt}
%
% Get the header on subsequent pages right.
\\renewcommand{\@cfttocstart}{ 
\\newgeometry{top=1.0in,hmargin=1.25in,height=9.0in,
includehead,includefoot,letterpaper}%,showcrop,showframe}

\doublespace
\pagestyle{fancyplain}
\afterpage{\lhead{\cftlabel}\\rhead{Page}}
}

\\renewcommand{\@cfttocfinish}{
\\restoregeometry\clearpage\afterpage{\lhead{}\\rhead{}}
}
%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%
%\setlength{\cftbeforelostitleskip}{-64.0truept}
%\setlength{\cftafterlostitleskip}{0truept}
%
%\\renewcommand{\listsymbolname}{LIST OF SYMBOLS}
%\\renewcommand{\cftlostitlefont}{\hfill\\normalsize\\rm}
%\\renewcommand{\cftafterlostitle}{\hfill}
% 
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
% Get the title for the LOT normal sized, centered, and at the right height.
%
\setlength{\cftbeforelottitleskip}{-64.0truept}
\setlength{\cftafterlottitleskip}{0truept}
%
\\renewcommand{\listtablename}{LIST OF TABLES}
\\renewcommand{\cftlottitlefont}{\hfill\\normalsize\\rm}
\\renewcommand{\cftafterlottitle}{\hfill}
% 
% Get the title for the LOF normal sized, centered, and at the right height.
%
\setlength{\cftbeforeloftitleskip}{-64.0truept}
\setlength{\cftafterloftitleskip}{0truept}
%
\\renewcommand{\listfigurename}{LIST OF FIGURES}
\\renewcommand{\cftloftitlefont}{\hfill\\normalsize\\rm}
\\renewcommand{\cftafterloftitle}{\hfill}
% 
% Here is a hack.  The report class automatically adds a bit of extra vertical
% space between table and figure entries in the LOT and LOF if they are from 
% different chapters.  I have simply commmented out the lines in the definition
% of \@chapter which do this.  As far as I can tell, the tocloft package doesn't
% have a command for this so a hack is necessary.
%
\def\@chapter[#1]#2{\ifnum \c@secnumdepth >\m@ne
\\refstepcounter{chapter}%
\\typeout{\@chapapp\space\\thechapter.}%
\addcontentsline{toc}{chapter}%
{\protect\\numberline{\\thechapter}#1}%
\\else
\addcontentsline{toc}{chapter}{#1}%
\\fi
\chaptermark{#1}%

%These two lines below are commented out.

%\addtocontents{lof}{\protect\addvspace{10\p@}}%
%\addtocontents{lot}{\protect\addvspace{10\p@}}%
\if@twocolumn
\@topnewpage[\@makechapterhead{#2}]%
\\else
\@makechapterhead{#2}%
\@afterheading
\\fi}

%
% Change some of the rules for float placement.
%
\setcounter{topnumber}{2}          %Maximum 2 floats on top
\setcounter{bottomnumber}{1}       %Maximum 1 float on bottom
%\\renewcommand{\\topfraction}{0.7}    %Top float max size is 70%
%\\renewcommand{\bottomfraction}{0.7} %Bottom float max size is 70%
\\renewcommand{\\topfraction}{0.9}    %Top float max size is 70%
\\renewcommand{\bottomfraction}{0.8} %Bottom float max size is 70%
\\renewcommand{\\floatpagefraction}{0.7}
		
		
%
% Fix chapter and section formats
%
% Chapters names are all caps, centered under \"Chapter N\"
\\renewcommand{\@makechapterhead}[1]{
\begin{center}
\chaptername\ \\thechapter\\vspace{\baselineskip}\\\
\uppercase\\expandafter{#1}\\vspace{\baselineskip}
\\end{center}
}
% Numbers chapters, sections, and subsections
\setcounter{secnumdepth}{2}
% Center section titles
\\renewcommand{\section}{\@startsection{section}{1}{0 in}{1em}{1em}{\centering}}
% Center and italicize subsection titles
\\renewcommand{\subsection}{\@startsection{subsection}{2}{0 in}{1em}{1em}
{\centering\slshape}}
		
% This is a bit of a hack.  I couldn't figure out a better way, but surely there
% is one.  What I want is for the title page of the appendix to appear with
% just one double space between the words \"Appendix A\" and the title.  So, here,
% I redefine the command with our \singlespace command, and then inside the 
% actual appendix, I use \doublespace.  This gets the spacing of the appendix
% title page right.
		
\\renewcommand\appendix{\par
\setcounter{chapter}{0}%
\setcounter{section}{0}%
\gdef\@chapapp{\appendixname}%
\gdef\\thechapter{\@Alph\c@chapter}
   
% I added this line.

\singlespace}

%
% Fix bibliography header.
%
\\renewcommand\bibname{References}
\\renewcommand\bibsection{
\begin{center}\uppercase\\expandafter{\bibname}\\vspace{1em}\\end{center}
}
		
%
% Modify figure captions so that they print singlespace.
%
\\renewcommand{\@makecaption}[2]{% #1 is e.g. Figure 1, #2 is captiontext
\singlespace
{\\textbf{#1:} #2\par}
}";
  return $sty;
}


/*
 * Return values for _get_tex
 */

function _get_tex() {
  
  $user_node = node_load(getuser_node());
  $formatadv_ct_form = drupal_get_form("formatadvising_node_form", $user_node);
  $field_full_name = $user_node->field_full_name['und'][0]['value'];
  $field_first_title = $user_node->field_first_title['und'][0]['value'];
  $field_second_title = $user_node->field_second_title['und'][0]['value'];
  $field_third_title = $user_node->field_third_title['und'][0]['value'];
  $field_degree = ($formatadv_ct_form['field_degree']['und']['#options'][$user_node->field_degree['und'][0]['value']] == 'Other') ? $user_node->field_degree_other['und'][0]['value'] : $formatadv_ct_form['field_degree']['und']['#options'][$user_node->field_degree['und'][0]['value']];
  $field_defense_date = strtotime($user_node->field_defense_date['und'][0]['value']);
  $field_graduation_date = strtotime($user_node->field_graduation_date['und'][0]['value']);

  $field_committee_chair_name = $user_node->field_committee_chair_name['und'][0]['value'];
  $field_committee_mem_first = $user_node->field_committee_mem_first['und'][0]['value'];
  $field_committee_chairs = $user_node->field_committee_chairs['und'][0]['value'];
  $field_your_committee[] = $field_committee_chair_name;
  $field_your_committee[] = $field_committee_mem_first;
  for($com = 0; $com < count($user_node->field_your_committee['und']); $com++) {
    $field_your_committee[] = $user_node->field_your_committee['und'][$com]['value'];
  }
  for($ds = 0; $ds < count($user_node->field_doc_sections['und']); $ds++) {
    $field_doc_sections[] = $formatadv_ct_form['field_doc_sections']['und']['#options'][$user_node->field_doc_sections['und'][$ds]['value']];
  }
  
  for ($ct = 0; $ct < count($user_node->field_chapter_title['und']); $ct++) {
    $field_chapter_titles[] = $user_node->field_chapter_title['und'][$ct]['value'];
  }

  $tex = '
\documentclass[12pt,letterpaper]{report}
\usepackage{natbib}
\usepackage{geometry}
%\usepackage{fancyheadings} fancyheadings is obsolete: replaced by fancyhdr. JL
\usepackage{fancyhdr}
\usepackage{afterpage}
\usepackage{graphicx}
\usepackage{amsmath,amssymb,amsbsy}
\usepackage{dcolumn,array}
\usepackage{tocloft}
\usepackage{asudis}
		
\begin{document}
%-----------------------front matter
\pagenumbering{roman}
\title{' . $field_first_title;
  if ($field_second_title != '') {
    $tex .= "\\\ \\ \\\ " . $field_second_title;
  }
  if ($field_third_title != '') {
    $tex .= "\\\ \\ \\\ " . $field_third_title;
  }
  $tex .= '}
\author{' . $field_full_name . '}
\degreeName{' . $field_degree . '}
\defensemonth{' . date("F", $field_defense_date) . '}
\gradmonth{' . date("F", $field_graduation_date) . '}
\gradyear{' . date("Y", $field_graduation_date) . '}';
  
  if ($field_committee_chairs == '1') {
    $tex .= '
\chair{' . $field_committee_chair_name . ', Chair \\\ ' . $field_committee_mem_first;
    
    for ($i = 2; $i < count($field_your_committee); $i++) {
      $tex .= '\\\ ' . $field_your_committee[$i];
     // $tex .= ($i == (count($field_your_committee) - 1)) ? '.' : ',';
    }
    $tex .='}';
    
  } else {
    $tex .= '
\chair{' . $field_committee_chair_name . ', Co-Chair \\\ ' . $field_committee_mem_first . ', Co-Chair ';
    for ($i = 2; $i < count($field_your_committee); $i++) {
      $tex .= '\\\ ' .$field_your_committee[$i];
      //$tex .= ($i == (count($field_your_committee) - 1)) ? '.' : ',';
    }
    
    $tex .= '}';
  }
  $tex .= '		
\maketitle
\doublespace
\include{abstract}';
  if (in_array('Dedication', $field_doc_sections)) {
    $tex .= '
\dedicationpage{\\\Enter content here.}';
  }
  if (in_array('Acknowledgments', $field_doc_sections)) {
    $tex .= '
\acknowledgementpage{}
%\include{ack}';
  }
  $tex .= '
\tableofcontents
% This puts the word "Page" right justified above everything else.
\addtocontents{toc}{~\hfill Page\par}
% Asking LaTeX for a new page here guarantees that the LOF is on a separate page
% after the TOC ends.
\newpage
% Making the LOT and LOF "parts" rather than chapters gets them indented at
% level -1 according to the chart: top of page 4 of the document at
% ftp://tug.ctan.org/pub/tex-archive/macros/latex/contrib/tocloft/tocloft.pdf

% This gets the headers for the LOT right on the first page.  Subsequent pages
% are handled by the fancyhdr code in the asudis.sty file.';
  if (!(in_array('Tables', $field_doc_sections) || in_array('Figures', $field_doc_sections) || in_array('Symbols', $field_doc_sections) || in_array('Preface', $field_doc_sections))) {
    $tex .= '
\addtocontents{toc}{CHAPTER \par}
% \listoftables';
  }

  if (in_array('Tables', $field_doc_sections)) {
    if (in_array('Figures', $field_doc_sections) || in_array('Symbols', $field_doc_sections) || in_array('Preface', $field_doc_sections)) {
      $tex .= '
\addcontentsline{toc}{part}{LIST OF TABLES}
\listoftables
\addtocontents{lot}{Table~\hfill Page \par}
\newpage';
    } else {
      $tex .= '
\addcontentsline{toc}{part}{LIST OF TABLES}
\addtocontents{toc}{CHAPTER \par}
\listoftables
\addtocontents{lot}{Table~\hfill Page \par}
\newpage';
    }
  }

  if (in_array('Figures', $field_doc_sections)) {
    if (in_array('Symbols', $field_doc_sections) || in_array('Preface', $field_doc_sections)) {
      $tex .= '
\addcontentsline{toc}{part}{LIST OF FIGURES}
\listoffigures
\addtocontents{lof}{Figure~\hfill Page \par}
\newpage';
    } else {
      $tex .= '
\addcontentsline{toc}{part}{LIST OF FIGURES}
\addtocontents{toc}{CHAPTER \par}
\listoffigures
\addtocontents{lof}{Figure~\hfill Page \par}
\newpage';
    }
  }

  if (in_array('Symbols', $field_doc_sections)) {
    if (in_array('Preface', $field_doc_sections)) {
      $tex .= '
\addcontentsline{toc}{part}{LIST OF SYMBOLS}
\clearpage
\symbolspage{}';
    } else {
      $tex .= '
\addcontentsline{toc}{part}{LIST OF SYMBOLS}
\clearpage
\addtocontents{toc}{CHAPTER \par}
\symbolspage{}
\clearpage';
    }
  }

  if (in_array('Preface', $field_doc_sections)) {
    $tex .= '
\addcontentsline{toc}{part}{PREFACE}
\clearpage
\addtocontents{toc}{CHAPTER \par}					
\prefacepage{\\\Enter content here.}';
  }

  $tex .= '

% This gets the headers for the LOF right on the first page.  Subsequent pages
% are handled by the fancyhdr code in the asudis.sty file.


%-----------------------body
\doublespace
\pagenumbering{arabic}';
  for ($x = 1; $x <= count($field_chapter_titles); $x++) {
    $tex .= '
\include{chapter' . $x . '}';
  }
  $tex .= '
%-----------------------back matter
{\singlespace
% Making the references a "part" rather than a chapter gets it indented at
% level -1 according to the chart: top of page 4 of the document at
% ftp://tug.ctan.org/pub/tex-archive/macros/latex/contrib/tocloft/tocloft.pdf
\addcontentsline{toc}{part}{REFERENCES}
\bibliographystyle{asudis}
\bibliography{dis}}';
  if (in_array('Appendix', $field_doc_sections)) {
    $tex .= '
\renewcommand{\chaptername}{APPENDIX}
\addtocontents{toc}{APPENDIX \par}
\appendix
\include{appendix1}';
  }
  if (in_array('Biographical', $field_doc_sections)) {
    $tex .= '
\addcontentsline{toc}{part}{BIOGRAPHICAL SKETCH}
\biographicalpage{\\\Enter content here.}';
  }

  $tex .= '
\include{vita}
\\end{document}';
  return $tex;
}

/*
 * Return values for _get_abstract
 */

function _get_abstract() {
  $user_node = node_load(getuser_node());
  $field_abstract = !empty($user_node->field_abstract) ? $user_node->field_abstract['und'][0]['value'] : '';
  
  $field_abstract = '\begin{abstract}' . $field_abstract . '\\end{abstract}';

  return $field_abstract;
}

/*
 * Return Values for _get_chapter
 */

function _get_chapter($field_chapter_title) {
  $chapter = "\\chapter{" . $field_chapter_title . "}
\\section{Section1}
\\section{Section2}";
  return $chapter;
}

function delete_zip() {
  global $user;
  $latex_path = DRUPAL_ROOT . '/' . drupal_get_path('module', 'formatadv') . "/latex";
  unlink($latex_path . '/zips/' . $user->name . '.zip');
}
