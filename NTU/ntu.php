<?php

require_once __DIR__ . '/lib/Curl.php';
require_once __DIR__ . '/lib/Crawler.php';
define('DEBUG', True);

function parseCategories ($url) {
	$curl = new Curl();
	$html = $curl->url($url)->add()->get();
	preg_match_all('/\/term\/([\d\+ ]+)">/', $html, $matches);
	$cats = [];
	foreach ($matches[1] as $match) {
		$cats = array_merge($cats, preg_split('/[ \+]+/', $match));
	}
	return array_unique($cats);
}

function parseMaxpage (&$html) {
	preg_match('/page=(\d+).+最後一頁/', $html, $match);
	return intval($match[1]);
}

function getHTML (&$curl) {
	$json = $curl->add()->get();
	$json = str_replace('\\x3c', '<', $json);
	$json = str_replace('\\x3e', '>', $json);
	$json = str_replace('\\x', '\\\\x', $json);
	return json_decode($json)->display;
}

function parseAndCraw (&$crawler, &$html, $exam_type = '入學考') {
	$tbody = strstr(strstr($html, '<tbody>'), '</tbody>', True);
	$trs = explode('</tr>', $tbody);
	foreach ($trs as $tr) {
		$attr = (object)[];
		if ( preg_match('/td class="views-field views-field-field-exam-year-value(?: active)?" >\s*(\d+)/', $tr, $attr->year) 
		&& preg_match('/td class="views-field views-field-tid(?: active)?" >\s*([^\s]+)/', $tr, $attr->department)
		&& preg_match('/td class="views-field views-field-title(?: active)?" >\s*([^\s]+)/', $tr, $attr->subject)
		&& preg_match('/<a href="([^"]+)">/', $tr, $url)
		) {
			foreach ($attr as &$row) {
				$row = $row[1];
			}
			$attr->year += 1911;
			$url = $url[1];
			$crawler->set('exam_type', $exam_type)
				->insert_urls($attr, [$url]);

			if (defined('DEBUG')) {
				print_r($attr);
			}
		}
	}
}

function main () {
	$crawler = new Crawler('NTU', './files/graduate/', './');
	$curl = new Curl();
	$root_url = 'http://exam.lib.ntu.edu.tw/views/ajax';
	// arguments
	// js: 必須是1
	// view_name: 必須是exam
	// view_display_id : page_1 : 研究所, page_2 : 轉學考
	// view_args: 系所, 可以用+來OR
	// page: 沒有則是第一頁, 有的話就是page+1頁
	// ?js=1&view_name=exam&view_display_id=page_1&view_args=3&page=1
	$curl->url('http://exam.lib.ntu.edu.tw/views/ajax')
		->key_value('js', '1')
		->key_value('view_name', 'exam');

	// 碩班
	$crawler->save_dir = './files/graduate/';
	$gradCategories = parseCategories('http://exam.lib.ntu.edu.tw/graduate');
	$curl->key_value('view_display_id', 'page_1')
		->key_value('view_args', implode(' ', $gradCategories));
	$html = getHTML($curl);
	$max_page = parseMaxpage($html);
	parseAndCraw($crawler, $html, '入學考');
	foreach (range(1, $max_page) as $i) {
		$curl->key_value('page', $i);
		$html = getHTML($curl);
		parseAndCraw($crawler, $html, '入學考');
	}

	// 轉學考
	$crawler->save_dir = './files/undergraduate/';
	$undgradCategories = parseCategories('http://exam.lib.ntu.edu.tw/undergra');
	$cats = implode(' ', $undgradCategories);
	// XXX: 管院那邊有某個category會炸掉
	$cats = str_replace(' 277', '', $cats);
	$curl->key_value('view_display_id', 'page_2')
		->key_value('view_args', $cats);
	$html = getHTML($curl);
	$max_page = parseMaxpage($html);
	parseAndCraw($crawler, $html, '轉學考');
	foreach (range(1, $max_page) as $i) {
		$curl->key_value('page', $i);
		$html = getHTML($curl);
		parseAndCraw($crawler, $html, '轉學考');
	}
}

main();