<?php
/*
Extract L('xxxx') expression from *.js file of the Titanium project and help to translate them.

Attention! this tool is not for public space.
I recommend to use this only your local pc.

*/

define('RESOURCE_PATH', 'path to your Titanium resource dir');
define('STRINGS_PATH', 'path to your Titanium i18n stirngs.xml file');


function array_fullpath($path, $dir) {
	$fullpath = array();
	
	foreach ($dir as $id=>$name) {
		if (is_array($name)){
			$fullpath = array_merge($fullpath,  array_fullpath($path.'/'.$id, $name));
		} else {
			$fullpath[] = $path.'/'.$name;
		}
	}
	return $fullpath;
}
function array_dirlist($path, $level = 30) {
	if( !$level ) return;
	$dir = array();
	
	if( !file_exists($path) || !($dh = opendir($path)) ) exit();
	while (($file = readdir($dh))) {
		if ($file == '.' || $file == '..') continue;
		
		$realpath = $path.'/'.$file;
		if (is_link($realpath)) continue;
		
		switch( true ){
			case is_file($realpath): $dir[] = $file; break;
			case is_dir($realpath):  $dir[$file] = array_dirlist($realpath, $level-1);
		}
	}
	closedir($dh);
	return $dir;
}


$nodata = $array = array();


if( $_POST ){
	$path = RESOURCE_PATH;
	$dir = array_dirlist($path);
	$fullpath = array_fullpath($path, $dir);
	
	foreach( $fullpath as $file ){
		if( strpos(basename($file), '.js') !== strlen(basename($file)) - 3 ) continue;
		$lines = explode("\n", file_get_contents($file));
		foreach( $lines as $line ){
			if( preg_match_all("/L\([\"'](.*?)[\"']\)/", $line, $r ) ){
				foreach( $r[1] as $a => $b ) $array[$b] = '';
			}
		}
	}
	$inputs = explode("\n", $_POST['text']);
	foreach( $inputs as $input ){
		list($ja, $en) = explode("\t", trim($input));
		if( isset($array[$en]) ) { $array[$en] = $ja; }
	}
	if( $array ) {
		$nodata = array();
		$str = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$str .= '<resources>'."\n";
		foreach( $array as $en => $ja ) {
			if( $ja ) $str .= '<string name="'.$en.'">'.$ja.'</string>'."\n";
			else $nodata[] = $en;
		}
		$str .= '</resources>'."\n";
		file_put_contents(STRINGS_PATH, $str);
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja" dir="ltr"> 
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<title>Extract Ja for Titanium Project</title>
</head>
<body>
<?php
if( $nodata ) {
	echo '<h1>Shortfall</h1>';
	echo '<p>We found shortfall words from your source. These words have not been saved yet. Add to the left column of your local tab-delimited file and fill in the translated string to the right column. If you do so, copy all words from tab-delimited file and paste to the [How to use] textarea and click [Format] again.</p>';
	echo '<ul>';
	foreach( $nodata as $line ) echo '<li>'.$line.'</li>';
	echo '</ul>';
	echo '<hr />';
}
if( $array ) {
	echo '<h1>strings.xml was updated</h1>';
	echo '<p>Copy the below text to your local tab-delimited file.</p>';
	echo '<textarea style="width:80%;height:200px">';
	foreach( $array as $text => $ja ) echo $text."\t".$ja."\n";
	echo '</textarea>';
	echo '<hr />';
}
?>
<h1>How to Use</h1>
<ul>
	<li>If you are the first time to use this tool, click [Format] button.</li>
	<li>If you have already prepared a tab-delimited file, copy the data to the below textarea ant click [Format] button.</li>
</ul>
<form action="extractJa.php" method="post">
<textarea name="text" style="width:80%;height:200px;margin-bottom: 10px"></textarea><br />
<input type="submit" value =" Format " />
</form>
</body>
</html>
