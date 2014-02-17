<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will
// be automatically rebuilt in the future.
$hook_version = 1;
$hook_array = array();

// position, file, function
$hook_array['after_login'] = array();
$hook_array['after_login'][] = array(1, 'SugarFeed old feed entry remover', 'modules/SugarFeed/SugarFeedFlush.php','SugarFeedFlush', 'flushStaleEntries');

$hook_array['after_save'] = array();
$hook_array['after_save'][] = array(1, 'Mailrelay after_save users hook', 'custom/include/class.mailrelayHooks.php', 'MailrelayHooks', 'afterSaveUser');
