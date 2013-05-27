<?php

require_once __DIR__ . '/lib/Curl.php';
require_once __DIR__ . '/lib/Crawler.php';

$root_url = 'http://exam.lib.ntu.edu.tw/views/ajax';
// arguments
// js: 必須是1
// view_name: 必須是exam
// view_display_id : page_1 : 研究所, page_2 : 轉學考
// view_args: 系所, 可以用+來OR
// page: 沒有則是第一頁, 有的話就是page+1頁
// ?js=1&view_name=exam&view_display_id=page_1&view_args=3&page=1
