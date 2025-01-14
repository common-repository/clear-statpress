<?php
/*
	Plugin Name: Clear StatPress
	Plugin URI: http://blog.leniy.org/clear-statpress.html
	Description: 我安装的statpress插件，虽然设置了不记录蜘蛛访问记录，但有些不表明身份的蜘蛛的访问数据仍然会保存，严重占用数据库空间。登陆后台phpmyadmin太麻烦了，刚刚粗略的学习了下插件制作，写了个简单的插件，执行清理作业。
	Version: 1.4.5
	Author: leniy
	Author URI: http://blog.leniy.org/
	Text Domain: leniylang
*/

//菜单栏目设置
require_once(plugin_dir_path( __FILE__ ).'/inc/leniy_admin_menu.php');
function qw_CSP_menu() {
	add_submenu_page('leniy-plugins', 'Clear StatPress', 'Clear StatPress', 'manage_options', 'leniy-plugins/Clear-StatPress.php', 'qw_CSP_page');
}
add_action('admin_menu', 'qw_CSP_menu');

//语言本地化
add_action('plugins_loaded', 'CSP_lang');
function CSP_lang() {
	$currentLocale = get_locale();
	if(!empty($currentLocale)) {
		$moFile = dirname(__FILE__) . "/lang/" . $currentLocale . ".mo";
		if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('leniylang', $moFile);
	}
}

//菜单页设置
function qw_CSP_page() {
	echo "<div id='CSPbutton'></div><h2>Clear-StatPress</h2>";
	CSP_sql_del();
}


function CSP_sql_del() {
	global $wpdb;
//下面的是查询规则，及sql语句where后面的部分
	$query_temp = "
	`agent` LIKE  '%bot%'
	OR  `agent` LIKE  '%spide%'
	OR  `agent` LIKE  '%PHP/%'
	OR  `agent` LIKE  '%BackLinks%'
	OR  `agent` LIKE  '%PostLinks%'
	OR (
		`search` =  ''
		AND (
			`urlrequested` LIKE  '%/page%'
		OR  `urlrequested` LIKE  '%/tag%'
		OR  `urlrequested` LIKE  '%/category%'
		OR  `urlrequested` LIKE  '%/2006%'
		OR  `urlrequested` LIKE  '%/2007%'
		OR  `urlrequested` LIKE  '%/2008%'
		OR  `urlrequested` LIKE  '%/2009%'
		OR  `urlrequested` LIKE  '%/2010%'
		OR  `urlrequested` LIKE  '%/2011%'
		OR  `urlrequested` LIKE  '%/2012%'
		OR  `urlrequested` LIKE  '%/2013%'
		OR  `urlrequested` LIKE  '%replytocom=%'
			)
		)
	OR (
			`agent` LIKE  '%http%'
		AND `agent` NOT LIKE  '%liferea%'
		AND `agent` NOT LIKE  '%google%'
		AND `agent` NOT LIKE  '%sixxs%'
		AND `agent` NOT LIKE  '%bsalsa%'
		AND `agent` NOT LIKE  '%feedsky%'
		AND `agent` NOT LIKE  '%flipboard%'
		)
	OR (
			`urlrequested` =  ''
		AND `statuscode` =  ''
		AND `referrer` =  ''
		)
	OR (
			`urlrequested` =  ''
		AND `referrer` =  ''
		AND `browser` =  ''
		AND `os` =  ''
		)
	OR `ip` LIKE '220.231.192.24'
	OR `ip` LIKE '211.154.149.132'
	OR `ip` LIKE '211.154.151.110'
	OR `ip` LIKE '211.154.151.118'
	OR `ip` LIKE '211.154.151.117'
	OR `ip` LIKE '204.155.149.26'
	OR `ip` LIKE '14.17.41.12'
	OR `ip` LIKE '163.177.71.12'
	OR `ip` LIKE '117.28.255.42'
	OR `ip` LIKE '119.179.54.204'
	OR `ip` LIKE '66.11.12.141'
	OR `ip` LIKE '72.233.72.139'
	OR `ip` LIKE '173.44.37.%'
	OR `ip` LIKE '96.47.224.%'
	OR `ip` LIKE '96.47.225.%'
	OR `ip` LIKE '220.181.51.%'
	OR `ip` LIKE '220.181.165.%'
	OR `ip` LIKE '61.135.186.%'
	OR `ip` LIKE '60.211.191.214'
	OR `ip` LIKE '88.126.248.44'
	OR `ip` LIKE '87.240.198.182'
";
//然后生成查询和删除的完整语句
	$query_sel = "SELECT* FROM " . $wpdb->prefix . 'statpress '." WHERE " . $query_temp;
	$query_del = "DELETE  FROM " . $wpdb->prefix . 'statpress '." WHERE " . $query_temp;

	$output = $wpdb->get_results($query_sel);

//首先加载css样式
echo '<link type="text/css" rel="stylesheet" href="' . plugins_url('clear-statpress/css/leniy_csp.css') . '" />';

//然后开始显示确认删除按钮
global $thisplugin_author;
global $thisplugin_url;
	echo "
	<table>
		<td>" . __('Items to be deleted','leniylang') . "：</td>
		<td><form method='post'><input type='submit' name='confirmdel' value='" . __('Confirm to delete','leniylang') . "' class='button-primary' /></form></td>
		<td>" . __('Contact Author','leniylang') . "：<a href='http://blog.leniy.org' target='_blank'>Leniy</a></td>
	</table>
	<br>";

//下面是查询到的详细数据
	echo "<div class=\"datagrid\"><table>
	<colgroup>
		<col class=\"colno\" />
		<col class=\"colid\" />
		<col class=\"coldate\" />
		<col class=\"coltime\" />
		<col class=\"colip\" />
		<col class=\"colurlrequested\" />
		<col class=\"colstatuscode\" />
		<col class=\"colagent\" />
		<col class=\"colreferrer\" />
		<col class=\"colsearch\" />
		<col class=\"colnation\" />
	</colgroup>
	<thead><tr>
		<th>No.</th>
		<th>id</th>
		<th>date</th>
		<th>time</th>
		<th>ip</th>
		<th>urlrequested</th>
		<th>statuscode</th>
		<th>agent</th>
		<th>referrer</th>
		<th>search</th>
		<th>nation</th>
	</tr></thead>

	<tbody>";
	$tempcolor = 0;//用来使表格两行背景颜色不同
	$tempNo = 1;//用来记录第几行
	foreach ($output as $o) {
		if($tempcolor == 0) { echo "<tr>"; $tempcolor = 1;}
		else { echo "<tr class=\"alt\">"; $tempcolor = 0;}
		echo "<td>" . $tempNo . "</td>";
		$tempNo = $tempNo + 1;
		echo "<td style=\"word-break:break-all;\">" . $o->id . "</td>";
		echo "<td style=\"word-break:break-all;\">" . $o->date . "</td>";
		echo "<td style=\"word-break:break-all;\">" . $o->time . "</td>";
		echo "<td style=\"word-break:break-all;\">" . $o->ip . "</td>";
		echo "<td style=\"word-break:break-all;max-width:200px;\">" . $o->urlrequested . "</td>";
		echo "<td style=\"word-break:break-all;\">" . $o->statuscode . "</td>";
		echo "<td style=\"word-break:break-all;max-width:200px;\">" . $o->agent . "</td>";
		echo "<td style=\"word-break:break-all;max-width:200px;\">" . $o->referrer . "</td>";
		echo "<td style=\"word-break:break-all;\">" . $o->search . "</td>";
		echo "<td style=\"word-break:break-all;\">" . $o->nation . "</td>";
		echo "</tr>";
	}
	echo "</tbody></table></div>";


	//这儿开始执行删除操作
	if($_POST['confirmdel'] != "") {
		$wpdb->query($query_del);
		echo "<br>" . __('Deleted','leniylang');
	}
	else {
		echo "<br>" . __('Please confirm to delete or not','leniylang') . "：<a href='#CSPbutton'>" . __('Jump to DELETE button','leniylang') . "</a>";
	}
}

?>
