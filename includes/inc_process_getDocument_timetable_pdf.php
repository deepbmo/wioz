<?php

$idx = Request('idx');

$program = $this->getProgram($idx);

if ($!program) {
  $results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
	return;
}


$title = '시간표';
$default_font = 'nanummyeongjobi';

$lists = $this->db()->select($this->table->application);
$lists->where('pidx', $program->idx);
$lists = $lists->get();


$pdf = new PDF('P','mm','A4',true,'UTF-8', false);
$pdf->SetCreator($this->getModule()->getConfig('school_name'));
$pdf->SetMargins(15, 5);
$pdf->SetDefaultMonospacedFont($default_font); //기본폰트
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetFont($default_font,'', 8);
$pdf->AddPage();

$html = '';

// 스타일 지정
$html .= '
  <style>

  </style>';


// 구조
$html .= '
  <div class="box-view">
    <h2 class="title"></h2>';

    foreach($ary_table as $item) {
      $html .= '
        <div class="tb">
          <table>

            <thead>
              <tr>
                <th>이름</th>
              </tr>
            </thead>

            <tbody>';

              $loopnum = 1;
              foreach($lists as $item) {
                $html .= '
                  <tr>
                    <td>'.$item->name.'</td>
                  </tr>';
              }
              $loopnum++;

                $html .= '
            </tbody>

          </table>
        </div>

        <br pagebreak="true">'; // 페이지 구분
    }

    $html .= '
  </div>';


$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

$document = $mAttachment->getTempFile(true);
$pdf->Output($document,'F');

$mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
$file = basename($document);

$results->success = true;
$results->file_name = $title;
$results->file = $file;
$results->mime = $mime;

?>