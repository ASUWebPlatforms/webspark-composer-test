<?php

namespace Drupal\asu_document_creation;

use Drupal\Core\File\FileSystemInterface;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Tab;
use PhpOffice\PhpWord\Shared\Converter;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

class WordDocumentCreation {

  protected $currentUser;
  private $phpWord;
  private $pageNumber = 4; // Start with page 4
  private $arabicPageNumber = 1; // Start with page 1
  private $sectionStyle;

  // Paragraph types
  private $single;
  private $double;
  private $centeredDouble;
  private $centeredSingle;
  private $rightAligned;
  private $coverParagraphStyle;
  private $referenceParagraphStyle;

  // Font
  private $font;
  private $fontSize;

  public function __construct(AccountProxyInterface $current_user,) {
    $this->currentUser = $current_user;
    $this->font = 'Times New Roman';
    $this->fontSize = 12;
    $this->sectionStyle = $this->getSectionStyle();
    $this->single = ['space' => ['line' => 120], 'spaceBefore' => 0, 'spaceAfter' => 0];
    $this->double = ['space' => ['line' => 240], 'spaceBefore' => 0, 'spaceAfter' => 0];
    $this->centeredDouble = ['alignment' => Jc::CENTER, 'space' => ['line' => 240], 'spaceBefore' => 0, 'spaceAfter' => 0];
    $this->centeredSingle = ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0];
    $this->rightAligned = ['alignment' => Jc::END, 'space' => ['line' => 240],'spaceBefore' => 0, 'spaceAfter' => 0];
    $this->coverParagraphStyle = ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]; // remove 8pt space after paragraph
    $this->referenceParagraphStyle = ['alignment' => Jc::CENTER, 'space' => ['line' => 120], 'spaceBefore' => 0, 'spaceAfter' => 240]; // 12pt space after paragraph
    \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  public function createDocument($fields) {

    extract($fields);

    $document_sections = $document_sections ?? [];

    $this->changeFont($approved_font, $font_size);

    $this->addCoverSection(
      [$first_title, $second_title, $third_title],
      $full_name,
      $degree,
      $graduation_date,
      $defense_date,
      $document_type,
      $this->memberChairs($committee_chairs, $committee_chair_name, $committee_mem_first, $members)
    );
    $this->addAbstractSection($abstract);

    if (in_array('dedication', $document_sections)) {
      $this->addDedicationSection();
    }

    if (in_array('acknowledgments', $document_sections)) {
      $this->addAcknowledgmentsSection();
    }

    $this->addTableOfContentsSection($document_sections, $chapters);

    if (in_array('tables', $document_sections)) {
      $this->addSectionWithTitles("LIST OF TABLES", "Table", 3);
    }

    if (in_array('figures', $document_sections)) {
      $this->addSectionWithTitles("LIST OF FIGURES", "Figure", 3);
    }

    if (in_array('symbols', $document_sections)) {
      $this->addSectionWithTitles("LIST OF SYMBOLS / NOMENCLATURE", "Symbol", 3);
    }

    if (in_array('preface', $document_sections)) {
      $this->addPrefaceSection();
    }

    foreach ($chapters as $chapter) {
      $this->addChapterSection($chapter);
    }

    $this->addReferencesSection();

    if (in_array('appendix', $document_sections)) {
      $this->addAppendixSection();
    }

    if (in_array('biographical', $document_sections)) {
      $this->addBiographicalSection();
    }

    // Save the document
    return $this->saveDocument($this->currentUser->getAccountName() . '-' . $document_type . '.docx');
  }

  private function getSectionStyle() {
    $marginRight = 1.25 * 1440; // 1.25 inches
    $marginLeft = 1.25 * 1440;  // 1.25 inches
    $marginTop = 1 * 1440;      // 1 inch
    $marginBottom = 1 * 1440;   // 1 inch

    return [
      'marginTop' => $marginTop,
      'marginBottom' => $marginBottom,
      'marginLeft' => $marginLeft,
      'marginRight' => $marginRight,
      'footerHeight' => 1 * 1440, // 1 inch
      'orientation' => 'portrait',
      'pageSizeH' => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(11),
      'pageSizeW' => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(8.5)
    ];
  }

  private function changeFont($approved_font, $font_size) {
    $this->font = $approved_font;
    $this->fontSize = $font_size;
    $this->phpWord = new PhpWord();
    $this->phpWord->setDefaultFontName($this->font);
    $this->phpWord->setDefaultFontSize((int)$this->fontSize);
  }

  private function toRoman($number) {
    $map = [
      'm' => 1000,
      'cm' => 900,
      'd' => 500,
      'cd' => 400,
      'c' => 100,
      'xc' => 90,
      'l' => 50,
      'xl' => 40,
      'x' => 10,
      'ix' => 9,
      'v' => 5,
      'iv' => 4,
      'i' => 1
    ];
    $returnValue = '';
    while ($number > 0) {
      foreach ($map as $roman => $int) {
        if ($number >= $int) {
          $number -= $int;
          $returnValue .= $roman;
          break;
        }
      }
    }
    return $returnValue;
  }

  private function memberChairs($committee_chairs, $committee_chair_name, $committee_mem_first, $members) {
    $chair = [];
    if ($committee_chairs == 1) {
      $chair[] = $committee_chair_name . ', Chair';
    } else {
      $chair[] = $committee_chair_name . ', Co-Chair';
      $chair[] = $committee_mem_first . ', Co-Chair';
    }
    foreach ($members as $member) {
      $chair[] = $member;
    }
    return $chair;
  }

  private function addCoverSection($titles, $full_name, $degree, $graduation_date, $defense_date, $document_type, $members) {
    $section = $this->phpWord->addSection($this->sectionStyle);

    // Title
    foreach ($titles as $title) {
      if (!empty($title)) {
        $section->addText($title, null, $this->centeredDouble);
      }
    }
    $section->addText("by", null, $this->centeredDouble);
    $section->addText($full_name, null, $this->centeredDouble);
    $section->addTextBreak(5, null, $this->coverParagraphStyle);

    // Purpose
    //$section->addText("A {$document_type}", null, $this->centeredSingle);

    $section->addText("A {$document_type} ". "Presented in Partial Fulfillment", null, $this->coverParagraphStyle);
    $section->addText("of the Requirements for the Degree", null, $this->coverParagraphStyle);
    $section->addText($degree, null, $this->coverParagraphStyle);
    $section->addTextBreak(5, null, $this->coverParagraphStyle); // Adjust the number of breaks as needed // reduced 9 to 3

    // Approved by
    $section->addText("Approved {$defense_date} by the", null, $this->coverParagraphStyle);
    $section->addText("Graduate Supervisory Committee:", null, $this->centeredDouble);
    foreach ($members as $member) {
      $section->addText($member, null, $this->coverParagraphStyle);
    }
    $section->addTextBreak(2); // Adjust the number of breaks as needed // reduced 6 to 2

    // Final phrase at the end of the page
    $footer = $section->addFooter();
    $footer->addText("ARIZONA STATE UNIVERSITY", null, $this->centeredDouble);
    $footer->addText($graduation_date, null, $this->centeredDouble);
  }

  private function addAbstractSection($abstract) {

    $indentation = ['indentation' => array('left' => 0, 'firstLine' => 720)];
    $section = $this->phpWord->addSection($this->sectionStyle);
    $section->getStyle()->setPageNumberingStart(1);
    $section->addText(strtoupper("Abstract"), null, $this->centeredDouble);
    $section->addText($abstract, null, array_merge($this->double, $indentation));
    $section->addText("[Enter text here]", null, $this->double);
    $footer = $section->addFooter();
    // New page numering
    $textRun = $footer->addTextRun(array('alignment' => Jc::CENTER));
    $textRun->addField('PAGE', array('format' => 'roman'));
  }

  private function addDedicationSection() {
    $section = $this->phpWord->addSection($this->sectionStyle);
    $section->addText("DEDICATION", null, $this->centeredDouble);
    $section->addText("[Enter text here]", null, $this->double);
    $footer = $section->addFooter();
    // New page numering
    $textRun = $footer->addTextRun(array('alignment' => Jc::CENTER));
    $textRun->addField('PAGE', array('format' => 'roman'));
  }

  private function addAcknowledgmentsSection() {
    $section = $this->phpWord->addSection($this->sectionStyle);
    $section->addText("ACKNOWLEDGMENTS", null, $this->centeredDouble);
    $section->addText("[Enter text here]", null, $this->double);
    $footer = $section->addFooter();
    // New page numering
    $textRun = $footer->addTextRun(array('alignment' => Jc::CENTER));
    $textRun->addField('PAGE', array('format' => 'roman'));
  }

  private function addReferencesSection() {
    $section = $this->phpWord->addSection($this->sectionStyle);
    $section->addText(strtoupper("References"), null, $this->centeredDouble);
    $section->addText("[Enter text here]", null, $this->referenceParagraphStyle);
    $footer = $section->addFooter();
    // New page numering
    $textRun = $footer->addTextRun(array('alignment' => Jc::CENTER));
    $textRun->addField('PAGE', array('format' => 'arabic'));
  }

  private function addTableOfContentsSection($documents, $chapters) {

    $spaces = "     ";

    $section = $this->phpWord->addSection($this->sectionStyle);

    $section->addText("TABLE OF CONTENTS", null, $this->centeredDouble);
    $section->addText("Page", null, $this->rightAligned);

    $paragraphStyleName = 'TOCParagraphStyle';
    $fontStyleName = 'ChapterFontStyle';
    $this->phpWord->addFontStyle($fontStyleName, ['name' => $this->font, 'size' => $this->fontSize]);
    $this->phpWord->addParagraphStyle($paragraphStyleName, array_merge(
      [
        'tabs' => [
          new Tab('right', 8625, 'dot')  // Tab position set to 8250 with dot leader
        ]
      ],
      $this->double
    ));

    $name_map = [
      "tables" => "LIST OF TABLES",
      "figures" => "LIST OF FIGURES",
      "symbols" => "LIST OF SYMBOLS / NOMENCLATURE",
      "preface" => "PREFACE",
      "chapters" => [
        "CHAPTER" => $chapters
      ],
      "references" => "REFERENCES",
      "appendix" => [
        "APPENDIX" => [
          "A" => 'First appendix',
          "B" => 'Second appendix',
        ],
      ],
      "biographical" => "BIOGRAPHICAL SKETCH"
    ];

    $page_number = $this->pageNumber;
    $chapter_number =  $this->arabicPageNumber;
    $chapters = false;

    foreach ($name_map as $key => $value) {
      if (!is_array($value)) {
        if (in_array($key, $documents) || $key == 'references') {
          if ($chapters) {
            $section->addText($value . "\t" . $chapter_number, $fontStyleName, $paragraphStyleName);
            $chapter_number++;
          } else {
            $page_number++;
            $section->addText($value . "\t" . $this->toRoman($page_number), $fontStyleName, $paragraphStyleName);
          }
        }
      } else {
        $section->addText(array_key_first($value), $fontStyleName, $paragraphStyleName);
        if ($key == 'chapters') {
          foreach ($value[array_key_first($value)] as $chapter) {
            $section->addText($spaces . "  " . $chapter_number . "  " . strtoupper($chapter) . "\t" . $chapter_number, $fontStyleName, $paragraphStyleName);
            $section->addText($spaces . $spaces . $spaces . 'Section 1' . "\t" . $chapter_number, $fontStyleName, $paragraphStyleName);
            $section->addText($spaces . $spaces . $spaces . 'Section 2' . "\t" . $chapter_number, $fontStyleName, $paragraphStyleName);
            $chapter_number++;
          }
          $chapters = true;
        } else {
          foreach ($value[array_key_first($value)] as $letter => $chapter) {
            $section->addText($spaces . "  " . $letter . "  " . strtoupper($chapter) . "\t" . $chapter_number, $fontStyleName, $paragraphStyleName);
            $chapter_number++;
          }
        }
      }
    }

    $footer = $section->addFooter();
    // New page numering
    $textRun = $footer->addTextRun(array('alignment' => Jc::CENTER));
    $textRun->addField('PAGE', array('format' => 'roman'));
  }

  private function addSectionWithTitles($sectionTitle, $entriesTitle, $entriesCount) {
    $section = $this->phpWord->addSection($this->sectionStyle);
    $section->addText($sectionTitle, null, $this->centeredDouble);

    // Define paragraph style for the section header (e.g., "Figure", "Table")
    $this->defineParagraphStyle('figurePageStyle', 8620, $this->double);
    $section->addText($entriesTitle . "\tPage", null, 'figurePageStyle');

    // Define paragraph style for the entries with dot leader tabs
    $this->defineParagraphStyle('TOCParagraphStyle', 9360, $this->double, 'dot');
    $this->defineFontStyle('ChapterFontStyle', ['name' => 'Times New Roman', 'size' => 12, 'bold' => true]);

    for ($i = 1; $i <= $entriesCount; $i++) {
      $title = $i . ". [" . $entriesTitle . " Title Here]\tX";  // Customize your title here
      $section->addText($title, 'ChapterFontStyle', 'TOCParagraphStyle');
    }

    $this->addFooterWithPageNumber($section);
  }

  private function defineParagraphStyle($name, $tabPos, $styleArray, $tabLeader = null) {
    $tabs = [new Tab('right', $tabPos, $tabLeader)];
    $this->phpWord->addParagraphStyle($name, array_merge(['tabs' => $tabs], $styleArray));
  }

  private function defineFontStyle($name, $styleArray) {
    $this->phpWord->addFontStyle($name, $styleArray);
  }

  private function addFooterWithPageNumber($section) {
    $footer = $section->addFooter();
    // New page numering
    $textRun = $footer->addTextRun(array('alignment' => Jc::CENTER));
    $textRun->addField('PAGE', array('format' => 'roman'));
  }

  private function addPrefaceSection() {
    $section = $this->phpWord->addSection($this->sectionStyle);
    $section->addText("PREFACE", null, $this->centeredDouble);
    $section->addText("[Enter text here]", null, $this->double);
    $footer = $section->addFooter();
    // New page numering
    $textRun = $footer->addTextRun(array('alignment' => Jc::CENTER));
    $textRun->addField('PAGE', array('format' => 'roman'));
  }

  private function addAppendixSection() {
    $section = $this->phpWord->addSection($this->sectionStyle);
    $section->addText("APPENDIX A", null, $this->centeredDouble);
    $section->addText(strtoupper("[Enter title here. Place appendix content on the next page]"), null, $this->centeredDouble);
    //$section->addText("[ENTER TEXT HERE]", null, $this->centeredDouble);
    $footer = $section->addFooter();
    // New page numering
    $textRun = $footer->addTextRun(array('alignment' => Jc::CENTER));
    $textRun->addField('PAGE', array('format' => 'arabic'));

    $section = $this->phpWord->addSection($this->sectionStyle);
    $section->addText("APPENDIX B", null, $this->centeredDouble);
    $section->addText(strtoupper("[Enter title here. Place appendix content on the next page]"), null, $this->centeredDouble);
    //$section->addText("[ENTER TEXT HERE]", null, $this->centeredDouble);
    $footer = $section->addFooter();
    // New page numering
    $textRun = $footer->addTextRun(array('alignment' => Jc::CENTER));
    $textRun->addField('PAGE', array('format' => 'arabic'));
  }

  private function addBiographicalSection() {
    $section = $this->phpWord->addSection($this->sectionStyle);
    $section->addText("BIOGRAPHICAL SKETCH", null, $this->centeredDouble);
    $section->addText("[Enter text here]", null, $this->single);
    $footer = $section->addFooter();
    // New page numering
    $textRun = $footer->addTextRun(array('alignment' => Jc::CENTER));
    $textRun->addField('PAGE', array('format' => 'arabic'));

  }

  private function addChapterSection($chapter) {
    $section = $this->phpWord->addSection($this->sectionStyle);
    // For the Chapters section, start page numbering at 1
    if ($this->arabicPageNumber == '1') {
      $section->getStyle()->setPageNumberingStart(1);
    }
    $section->addText("CHAPTER " . $this->arabicPageNumber++, null, $this->centeredDouble);
    $section->addText(strtoupper($chapter), null, $this->centeredDouble);
    $section->addText("Section 1", null, $this->centeredDouble);
    $section->addText("[Insert your text here]", null, $this->double);
    $section->addText("Section 2", null, $this->centeredDouble);
    $section->addText("[Insert your text here]", null, $this->double);

    $footer = $section->addFooter();
    // New page numering
    $textRun = $footer->addTextRun(array('alignment' => Jc::CENTER));
    $textRun->addField('PAGE', array('format' => 'arabic'));
  }


  private function saveDocument($fileName) {
    $file_system = \Drupal::service('file_system');
    $directory = 'public://word';
    $file_system->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $fullPath = $file_system->realpath($directory) . '/' . $fileName;
    $writer = IOFactory::createWriter($this->phpWord, 'Word2007');
    try {
      $tempFilePath = tempnam(sys_get_temp_dir(), 'PHPWord');
      $writer->save($tempFilePath);
      $file_system->move($tempFilePath, $fullPath, FileSystemInterface::EXISTS_REPLACE);
      return $file_system->realpath($directory . '/' . $fileName);
    } catch (\Exception $e) {
      echo "Error saving document: " . $e->getMessage();
      return NULL;
    }
  }
}