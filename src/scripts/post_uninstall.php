<?php

global $db;
$db->query("DELETE FROM `config` WHERE `category` = 'mailrelay' AND `name` = 'settings'");
