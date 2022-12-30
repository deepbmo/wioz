<?php
/**
 * 이 파일은 포트폴리오 모듈의 일부입니다. (http://www.coursemos.kr)
 * 
 * 워크숍활동내역을 저장한다.
 *
 * @file /modules/portfolio/process/saveWorkshop.php
 * @modified 2022. 1. 1.
 */
if (defined('__IM__') == false) exit;

$introspection = Request('introspection');
$attachments = is_array(Request('attachments')) == true ? Request('attachments') : array();
for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
	$attachments[$i] = Decoder($attachments[$i]);
}

$introspection = $this->IM->getModule('wysiwyg')->encodeContent($introspection,$attachments);

if (count($errors) == 0) {
    $insert = array();
    $insert['introspection'] = $introspection;  // <p>test</p>
}