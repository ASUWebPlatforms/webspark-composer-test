<div class='Section4'>

	<p class='MsoNormal' align='center' style='text-align: center; line-height: 200%; mso-no-proof: yes'>
		<a name='toc'><span style='text-transform: uppercase'>Table of Contents</span></a>
	</p>

	<p class='MsoNormal' align='right' style='line-height: 200%; mso-no-proof: yes'>
		<span style='mso-tab-count: 10'>Page</span><o:p></o:p>
  </p>
  
  <?php
    $num_of_opts = 0;
    $num_of_chapters = count($chapters);

    if (in_array('figures', $document_sections)) {
      $num_of_opts++;
    }
    if (in_array('tables', $document_sections)) {
      $num_of_opts++;
    }
    if (in_array('symbols', $document_sections)) {
      $num_of_opts++;
    }
    if (in_array('preface', $document_sections)) {
      $num_of_opts++;
    }
    if (in_array('appendix', $document_sections)) {
      $num_of_opts += 3;
    }
    if (in_array('biographical', $document_sections)) {
      $num_of_opts++;
    }

    if ($approved_font == 'Arial') {
      $page_line_tolerance = 28;
    } elseif ($approved_font == 'Century') {
      $page_line_tolerance = 24;
    } elseif ($approved_font == 'Garamond') {
      $page_line_tolerance = 24;
    } elseif ($approved_font == 'Georgia') {
      $page_line_tolerance = 25;
    } elseif ($approved_font == 'Microsoft San Serif') {
      $page_line_tolerance = 28;
    } elseif ($approved_font == 'Tahoma') {
      $page_line_tolerance = 26;
    } elseif ($approved_font == 'Times New Roman') {
      $page_line_tolerance = 23;
    } elseif ($approved_font == 'Verdana') {
      $page_line_tolerance = 26;
    } else {   //Else should not be needed but serves as redundancy
      $page_line_tolerance = 26;
    }

    $num_of_toc_pages = ceil((($num_of_chapters * 3) + $num_of_opts + 4) / $page_line_tolerance);
  
    $count =  1 + $num_of_toc_pages;
    
    if(in_array('acknowledgments', $document_sections)) { $count++; }
    if(in_array('dedication', $document_sections)) { $count++; }
    
    $romans = array('0', 'i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii', 'xiii', 'xiv', 'xv', 'xvi', 'xvii', 'xviii', 'xix', 'xx');
  
  ?>

  <?php
    if (in_array('tables', $document_sections)):
    $count++;
  ?>

    <p class='MsoToc10' style='line-height: 200%; mso-no-proof: yes' align='right'>
		  <span>LIST OF TABLES<span style='mso-tab-count: 1 dotted; mso-no-proof: yes'>.</span><?php echo $romans[$count] ?></span>
		  <o:p></o:p>
    </p>

  <?php endif; ?>

  <?php
    if (in_array('figures', $document_sections)):
    $count++;
  ?>

    <p class='MsoToc10' style='line-height: 200%; mso-no-proof: yes' align='right'>
      <span>LIST OF FIGURES<span style='mso-tab-count: 1 dotted; mso-no-proof: yes'>.</span><?php echo $romans[$count] ?></span>
      <o:p></o:p>
    </p>

  <?php endif; ?>

  <?php
    if (in_array('symbols', $document_sections)):
    $count++;
  ?>

    <p class='MsoToc10' style='line-height: 200%; mso-no-proof: yes' align='right'>
      <span>LIST OF SYMBOLS / NOMENCLATURE<span style='mso-tab-count: 1 dotted; mso-no-proof: yes'>.</span><?php echo $romans[$count] ?></span>
      <o:p></o:p>
    </p>

  <?php endif; ?>

  <?php
    if (in_array('preface', $document_sections)):
    $count++;
  ?>

    <p class='MsoToc10' style='margin-left:18.0pt;line-height: 200%; mso-no-proof: yes;text-align:left;tab-stops:right dotted 100%;' align='right'>
      <span>PREFACE <span style='mso-tab-count: 1 dotted; mso-no-proof: yes'>.</span><?php echo $romans[$count] ?></span>
      <o:p></o:p>
    </p>

  <?php endif; ?>

  <p class='MsoNormal' align='left' style='line-height:200%'><span style='text-transform:uppercase'>Chapter</span><o:p></o:p></p>

  <?php if ($chapter_headings): ?>
    <?php 
      if ($chapters > 0): 
        $x = 0;
    ?>
      <ol>
        <?php foreach ($chapters as $chapter) : ?>
            <?php $x++; ?>
            <?php if ($chapter !== null) : ?>
                <li>
                    <p class='MsoToc1' align='left' style='text-indent:-18.0pt;mso-list:l0 level1 lfo1;margin-left:15.0pt;tab-stops:right dotted 100%;'>
                        <span>
                            <?php echo strtoupper($chapter . (strlen($chapter) < 20 ? ' ' : '')); ?>
                            <span style='mso-tab-count:1 dotted; mso-no-proof:yes'>.</span>
                        </span> 
                        <span style='mso-no-proof:yes'><?php echo $x; ?></span>
                        <o:p></o:p>
                    </p>
                </li>
            <?php else : ?>
                <p class='MsoToc1'>
                    <span style='mso-no-proof:yes; text-transform:uppercase'>CHAPTER <?php echo $x; ?></span>
                    <span style='mso-tab-count:1 dotted; mso-no-proof:yes'>.</span>
                    <span style='mso-no-proof:yes'><?php echo $x; ?></span>
                </p>
            <?php endif; ?>

            <!-- Assuming the sections are related to the current chapter, if not, adjust accordingly -->
            <?php for ($section = 1; $section <= 2; $section++) : ?>
                <p class='MsoToc2' style='line-height:200%; mso-no-proof:yes; margin-left:-3pt' align='right'>
                    <span>Section <?php echo $section; ?>
                        <span style='mso-tab-count:1 dotted; mso-no-proof:yes'>.</span>
                        <?php echo $x; ?>
                    </span>
                </p>
            <?php endfor; ?>
        <?php endforeach; ?>
      </ol>
    <?php else: ?>
      <p class='MsoToc1'><span> <span style='mso-tab-count: 1; mso-no-proof: yes'>CHAPTER 1</span> <span style='mso-tab-count: 1 dotted; mso-no-proof: yes'>.</span> </span></p>
      <p class='MsoToc2' style='line-height: 200%; mso-no-proof: yes'>Section 1<span style='mso-tab-count: 1 dotted; mso-no-proof: yes'>.</span>1</p>
      <p class='MsoToc2' style='line-height: 200%; mso-no-proof: yes'>Section 2<span style='mso-tab-count: 1 dotted; mso-no-proof: yes'>.</span>1</p>
    <?php endif; ?>
  <?php else: ?>
    <?php $x = 1 ?>
  <?php endif; ?>
  <?php $count++; ?>
  <p class='MsoToc10' style='margin-left:18.0pt;line-height: 200%; mso-no-proof: yes;text-align:left;tab-stops:right dotted 100%;' align='right'>
    <span>
      <span style='text-transform: uppercase; mso-no-proof: yes'>REFERENCES </span>
      <span style='mso-tab-count: 1 dotted; mso-no-proof: yes'>.</span><?php echo ($x + 1) ?>
    </span>
  </p>

  <?php
    if (in_array('appendix', $document_sections)):
    $count++;
  ?>
    <p class='MsoNormal' align='left' style='line-height:200%'><span style='text-transform:uppercase'>Appendix</span><o:p></o:p></p>
    <p class='MsoToc1' align='right'><![if !supportLists]> <span style='mso-no-proof: yes;'> </span> <![endif]> <span> <span style='mso-no-proof: yes; text-transform: uppercase'>A&nbsp;&nbsp;&nbsp;&nbsp; [Insert Title]</span> <span style='mso-no-proof: yes'><?php echo ($x + 2) ?></span> </span></p>
    <p class='MsoToc1' align='right'><![if !supportLists]> <span style='mso-no-proof: yes;'> </span> <![endif]> <span> <span style='mso-no-proof: yes; text-transform: uppercase'>B&nbsp;&nbsp;&nbsp;&nbsp; [Insert Title]</span> <span style='mso-tab-count: 1 dotted; mso-no-proof: yes'>.</span> <span style='mso-no-proof: yes'><?php echo ($x + 4) ?></span> </span></p>
    <?php if (in_array('biographical', $document_sections)): ?>
      <p class='MsoToc10' style='line-height: 200%'  align='right'><span style='text-transform: uppercase'>Biographical Sketch</span><span style='mso-tab-count: 1 dotted; mso-no-proof: yes'>.</span><?php echo ($x + 6) ?><o:p></o:p> </p>
    <?php endif; ?>
  <?php else: ?>
    <?php if (in_array('biographical', $document_sections)): ?>
      <p class='MsoToc10' style='line-height: 200%' align='right'><span style='text-transform: uppercase'>Biographical Sketch</span><span style='mso-tab-count: 1 dotted; mso-no-proof: yes'>.</span><?php echo ($x + 1) ?><o:p></o:p></p>
    <?php endif; ?>
  <?php endif; ?>

  <span style='mso-ansi-language: EN-US; mso-fareast-language: EN-US; font-weight: normal'>
    <br clear='ALL' style='page-break-before: always; mso-break-type: section-break'>
  </span>

</div>