<?php

ob_start();

header("Content-type: text/xml; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

define('DEFAULT_PATH',	'');

include_once('php/init.php');

function putSiteMap($url, $date, $freq = "daily", $prio = "1.0") {
	global $map_cnt, $site_map_body;
	
	$site_map_body .= "<url>\n" ;
	$site_map_body .= "<loc>{$url}</loc>\n";
	if($date) $site_map_body .= "<lastmod>{$date}</lastmod>\n";
	if($freq) $site_map_body .= "<changefreq>{$freq}</changefreq>\n";
	$site_map_body .= "<priority>{$prio}</priority>\n";
	$site_map_body .= "</url>\n";

	$map_cnt++;

	if($map_cnt > 500) {
		$map_group_cnt	++;
		writeSiteMap($site_map_body, $map_group_cnt);
		$map_cnt		= 0;		
		$site_map_body	= "";
	}
}

function writeSiteMap($data, $map_group_cnt) {
	global $site_map_header, $site_map_footer;

	$file_name = "sitemap/sitemap_".sprintf('%03d', $map_group_cnt).".xml";	
	file_put_contents($file_name, $site_map_header.$data.$site_map_footer);
}

$filename	= "sitemap/sitemap.xml";
if(file_exists($filename)) {
	$times	= filectime($filename);
	if($times > time() - 600) {
		echo file_get_contents($filename);
		exit;
	}
}
ob_start();

$map_cnt		= 0;
$map_group_cnt	= 0;
$site_map_body	= "";

$site_map_header	= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$site_map_header	.= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n";
$site_map_footer	= "</urlset>";
$base_url			= ABSOLUTE_PATH_SHOP."index.php?channel=";
$today				= date("Y-m-d") . "T" . date("h:i:s") . "+09:00";

putSiteMap(ABSOLUTE_PATH_SHOP, $today);
putSiteMap($base_url."best", "", "", "0.8");
putSiteMap($base_url."new", "", "", "0.8");

$sql = "SELECT cate, cate_sub FROM mallRN_cate WHERE cate_dep = '1' && used = '1' ORDER BY sequence ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()){	
	putSiteMap($base_url."list&amp;cate={$row['cate']}", "", "", "0.8");

	if($row['cate_sub'] == 1) {
		$sql = "SELECT cate, cate_sub FROM mallRN_cate WHERE cate_parent = '{$row['cate']}' && used = '1' ORDER BY sequence ASC";
		$mysql->query2($sql);

		while($row2 = $mysql->fetch_array('2')) {
			putSiteMap($base_url."list&amp;cate={$row2['cate']}", "", "", "0.8");

			if($row2['cate_sub'] == 1) {
				$sql = "SELECT cate, cate_sub FROM mallRN_cate WHERE cate_parent = '{$row2['cate']}' && used = '1' ORDER BY sequence ASC";
				$mysql->query3($sql);

				while($row3 = $mysql->fetch_array('3')) {
					putSiteMap($base_url."list&amp;cate={$row3['cate']}", "", "", "0.8");

					if($row3['cate_sub'] == 1) {
						$sql = "SELECT cate, cate_sub FROM mallRN_cate WHERE cate_parent = '{$row3['cate']}' && used = '1' ORDER BY sequence ASC";
						$mysql->query4($sql);

						while($row4 = $mysql->fetch_array('4')) {
							putSiteMap($base_url."list&amp;cate={$row4['cate']}", "", "", "0.8");

						}		
					}
				}		
			}
		}		
	} 		
}


$sql = "SELECT uid, signdate FROM mallRN_goods WHERE display_use = 1 && auth_ck = 'Y' && cate_hide = 0 && vendor_hide = 0 ORDER BY re_uid ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()){	
	$date	= date("Y-m-d", $row['signdate']) . "T" . date("h:i:s", $row['signdate']) . "+09:00";
	putSiteMap($base_url."view&amp;uid={$row['uid']}", $date);
}

if($map_cnt > 0) {
	$map_group_cnt	++;
	writeSiteMap($site_map_body, $map_group_cnt);
	$map_cnt		= 0;		
	$site_map_body	= "";
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
for($i = 0; $i < $map_group_cnt; $i ++) {
	echo "<sitemap>\n";
	echo "	<loc>".ABSOLUTE_PATH_SHOP."sitemap/sitemap_".sprintf('%03d', ($i + 1)).".xml</loc>\n";
	echo "</sitemap>\n";
}
echo "</sitemapindex>";


$tmps = ob_get_contents();
ob_end_flush(); 
ob_end_clean(); 
file_put_contents($filename, $tmps);

echo $tmps;

?>