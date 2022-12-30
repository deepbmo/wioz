<?php  
ini_set('memory_limit', '10240M');
REQUIRE_ONCE '../../../configs/init.config.php';

$IM = new iModule();
$mCoursemos = $IM->getModule('coursemos');

$oDB = $IM->db();
$oDB->setPrefix('');


$lists = $mCoursemos->db()->select($mCoursemos->getTable('member'));
$lists->where('jidx','','!=');
$lists = $lists->get();

if (count($lists) > 0) {
    $mCoursemos->db()->startTransaction();

    foreach ($lists as $list) {
        if ($list->didx) $mCoursemos->db()->update($mCoursemos->getTable('major'),array('didx'=>$list->didx))->where('idx',$list->jidx)->execute();
        echo '.';
        ForceFlush();
    }

    $mCoursemos->db()->commit();
    echo 'END';
} else {
    echo 'jidx 값이 있는 row가 없습니다.';
}