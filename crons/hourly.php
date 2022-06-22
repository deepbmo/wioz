<?php
/**
 * 이 파일은 bmo 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 매시간 처리합니다.
 *
 * @file /modules/bmo/crons/hourly.php
 * @author oz11
 * @license MIT License
 * @version 3.4.0
 * @modified 2022. 1. 1.
 */
if (defined('__IM_CRON__') == false) exit;

// 매시간 처리

// 해당 시간에만 처리
if (date('G') == 9) { // 매일 9시에 발송한다.
}

?>