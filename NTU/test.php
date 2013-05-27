<?php

require_once __DIR__ . '/lib/Crawler.php';
$crawler = new Crawler('NTU', './files/', './');
$crawler->set('department', '中文所')
	->set('year', 2013)
	->set('exam_type', '入學考')
	->insert_urls((object)[
	'subject' => '中國文學史'
], ['http://exam.lib.ntu.edu.tw/sites/default/files/exam/graduate/102/102001.pdf']);

$crawler->save();