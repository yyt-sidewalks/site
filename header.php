<?php require('config.php');
global $con;
$con = mysqli_connect($configs['dbhost'],$configs['dbuser'],$configs['dbpassword'],$configs['db'],$configs['dbport']);
if (!$con){
	die('Could not connect: ' . mysqli_error($con));
}
mysqli_query($con,'set names "utf8"');
setlocale(LC_ALL, 'UTF8');

function grabnewestid($table,$field){
	global $con;
	$grabsql="select max(".$field.") as newid from ".$table." limit 0,1";
	$grabinfo=mysqli_query($con,$grabsql);
	if(mysqli_num_rows($grabinfo)){
		$thisinfo=mysqli_fetch_assoc($grabinfo);
		return $thisinfo['newid'];
	}else{
		return false;
	}
}

function grabinfo($table,$field,$term=false,$number=false,$order=false,$d='asc',$start='0'){
	global $con;
	$multiplefields=false;
	$allfields=array();
	$terms=array();
	if(!$order){
		$order=$field;
	}
	if($table=='stories'){
		$item='story';
	}else{
		$item=trim($table,'s');
	}
	if(strpos($field,",")!==false){
		$multiplefields=true;
		$allfields=explode(",",$field);
	}
	$search=array();
	$thisinfo=false;
	$hastable=mysqli_query($con,"SHOW TABLES LIKE '".$table."'");
	if(mysqli_num_rows($hastable)){
		$grabinfosql="select * from ".$table;
		if(!empty($allfields)){
			//make sure commas aren't on the outside just in case
			$term=trim($term,', ');
			if(strpos($term,",")!==false){
				$terms=explode(",",$term);
				foreach($allfields as $k=>$thisfield){
					$thisterm=mysqli_real_escape_string($con,$terms[$k]);
					if(!is_numeric($terms[$k])){
						$thisterm="'".$thisterm."'";
					}
					$search[]=$thisfield."=".$thisterm;
				}
			}else{
				foreach($allfields as $k=>$thisfield){
					if(!is_numeric($term)){
						$term="'".mysqli_real_escape_string($con,$term)."'";
					}
					$search[]=$thisfield."=".$term;
				}
			}
		}else{
			if($term!==false){
				$testterm=str_replace(",","",$term);
				if(!is_numeric($testterm)){
					$term="'".mysqli_real_escape_string($con,$term)."'";
				}
				if(strpos($term,",")!==false){
					$search[]=$field." in (".$term.")";
				}else{
					$search[]=$field."=".$term;
				}
			}
		}

		if(!empty($search)){
			$grabinfosql.=" where ".implode(" and ",$search);
		}
		//orderby
		$grabinfosql.=" order by ".$order." ".$d;
		if($number!==false){
			$grabinfosql.=" limit ".$start.",".$number;
		}
		$grabinfo=mysqli_query($con,$grabinfosql);
		//print $grabinfosql;
		if(mysqli_num_rows($grabinfo)){
			switch($number){
				case '1':
					$thisinfo=mysqli_fetch_assoc($grabinfo);
					if(array_key_exists("modified",$thisinfo)){
						$thisinfo['nicedate']=date('F j, Y',strtotime($thisinfo['modified']));
						$thisinfo['nicetime']=date('H:i',strtotime($thisinfo['modified']));
					}
					break;
				case false:
				default:
					while($thisrow=mysqli_fetch_assoc($grabinfo)){
						if(array_key_exists("modified",$thisrow)){
							$thisrow['nicedate']=date('F j, Y',strtotime($thisrow['modified']));
							$thisrow['nicetime']=date('H:i',strtotime($thisrow['modified']));
						}
						$thisinfo[]=$thisrow;
					}
			}
		}
	}
	return $thisinfo;
}

function getContents($url){
	$ch=curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER,0);	// DO NOT RETURN HTTP HEADERS
	curl_setopt($ch, CURLOPT_RETURNTRANSFER	,1);	// RETURN THE CONTENTS
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT	,0);
	$Rec_Data=curl_exec($ch);
	return $Rec_Data;
}

//Cleans all MS office curly characters from strings
function cleancurly($text){
	/* First, replace UTF-8 characters.
	$text=str_replace(
	array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
	array("'", "'", '"', '"', '-', '--', '...'),
	$text);
	// Next, replace their Windows-1252 equivalents.
	//$text=str_replace(
	//array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
	//array("'", "'", '"', '"', '-', '--', '...'),
	//$text);
	return $text;*/

	$chr_map=array(
		// Windows codepage 1252
		"\xC2\x82" => "'", // U+0082⇒U+201A single low-9 quotation mark
		"\xC2\x84" => '"', // U+0084⇒U+201E double low-9 quotation mark
		"\xC2\x8B" => "'", // U+008B⇒U+2039 single left-pointing angle quotation mark
		"\xC2\x91" => "'", // U+0091⇒U+2018 left single quotation mark
		"\xC2\x92" => "'", // U+0092⇒U+2019 right single quotation mark
		"\xC2\x93" => '"', // U+0093⇒U+201C left double quotation mark
		"\xC2\x94" => '"', // U+0094⇒U+201D right double quotation mark
		"\xC2\x9B" => "'", // U+009B⇒U+203A single right-pointing angle quotation mark
		"\xC2\x96" => '-', // En dash
		"\xC2\x97" => "--", // Em dash
		"\xC2\x85" => "...", // Ellipsis

		// Regular Unicode	 // U+0022 quotation mark (")
		// U+0027 apostrophe	 (')
		"\xC2\xAB"	 => '"', // U+00AB left-pointing double angle quotation mark
		"\xC2\xBB"	 => '"', // U+00BB right-pointing double angle quotation mark
		"\xE2\x80\x98" => "'", // U+2018 left single quotation mark
		"\xE2\x80\x99" => "'", // U+2019 right single quotation mark
		"\xE2\x80\x9A" => "'", // U+201A single low-9 quotation mark
		"\xE2\x80\x9B" => "'", // U+201B single high-reversed-9 quotation mark
		"\xE2\x80\x9C" => '"', // U+201C left double quotation mark
		"\xE2\x80\x9D" => '"', // U+201D right double quotation mark
		"\xE2\x80\x9E" => '"', // U+201E double low-9 quotation mark
		"\xE2\x80\x9F" => '"', // U+201F double high-reversed-9 quotation mark
		"\xE2\x80\xB9" => "'", // U+2039 single left-pointing angle quotation mark
		"\xE2\x80\xBA" => "'", // U+203A single right-pointing angle quotation mark
		"\xE2\x80\x93" => '-', // En dash
		"\xE2\x80\x94" => "--", // Em dash
		"\xE2\x80\xa6" => "..." // Ellipsis
	);
	$chr=array_keys	($chr_map); // but: for efficiency you should
	$rpl=array_values($chr_map); // pre-calculate these two arrays
	$text=str_replace($chr,$rpl, html_entity_decode($text, ENT_QUOTES, "UTF-8"));
	return $text;
}

function timeago($time){
	 $periods=array("second", "minute", "hour", "day", "week", "month", "year", "decade");
	 $lengths=array("60","60","24","7","4.35","12","10");

	 $now=time();

		 $difference	=$now - $time;
		 $tense		="ago";

	 for($j=0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++){
		 $difference /= $lengths[$j];
	 }

	 $difference=round($difference);

	 if($difference != 1){
		 $periods[$j].= "s";
	 }

	 return "$difference $periods[$j] ago";
}

function is_valid_callback($subject){
	$identifier_syntax
	 ='/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

	$reserved_words=array('break', 'do', 'instanceof', 'typeof', 'case',
		'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue',
		'for', 'switch', 'while', 'debugger', 'function', 'this', 'with',
		'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum',
		'extends', 'super', 'const', 'export', 'import', 'implements', 'let',
		'private', 'public', 'yield', 'interface', 'package', 'protected',
		'static', 'null', 'true', 'false');

	return preg_match($identifier_syntax,$subject)
		&& ! in_array(mb_strtolower($subject, 'UTF-8'),$reserved_words);
}

function mb_str_split($string){
	# Split at all position not after the start: ^
	# and not before the end: $
	return preg_split('/(?<!^)(?!$)/u',$string);
}


function in_array_case_insensitive($needle,$haystack){
	return in_array( strtolower($needle), array_map('strtolower',$haystack));
}

function multi_array_search($search_for,$search_in){
	foreach ($search_in as $element){
		if(($element === $search_for) || (is_array($element) && multi_array_search($search_for,$element))){
			return true;
		}
	}
	return false;
}

/**
 * Case in-sensitive array_search() with partial matches
 *
 * @param string $needle	 The string to search for.
 * @param array	$haystack The array to search in.
 *
 * @author Bran van der Meer <branmovic@gmail.com>
 * @since 29-01-2010
 */
function array_find($needle, array $haystack){
	foreach ($haystack as $key => $value){
		if(false !== stripos($value,$needle)){
			return $key;
		}
	}
	return $needle;
}

function sortarraybylength($a,$b){
	return strlen($b)-strlen($a);
}

function make_clickable($ret){
	$ret=' '.$ret;
	// in testing, using arrays here was found to be faster
	$ret=preg_replace_callback('#([\s>])([\w]+?://[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_url_clickable_cb',$ret);
	$ret=preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_web_ftp_clickable_cb',$ret);
	$ret=preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb',$ret);
	// this one is not in an array because we need it to run last, for cleanup of accidental links within links
	$ret=preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>",$ret);
	$ret=trim($ret);
	return $ret;
}

function do_post_request($url,$data,$optional_headers=null){
	$params=array('http' => array(
				'method' => 'POST',
				'content' => $data
			));
	if($optional_headers !== null){
		$params['http']['header']=$optional_headers;
	}
	$ctx=stream_context_create($params);
	$fp=@fopen($url, 'rb', false,$ctx);
	if(!$fp){
		throw new Exception("Problem with $url,$php_errormsg");
	}
	$response=@stream_get_contents($fp);
	if($response === false){
		throw new Exception("Problem reading data from $url,$php_errormsg");
	}
	return $response;
}

function convert_smart_quotes($string){
	$search=array(chr(145),
	chr(146),
	chr(147),
	chr(148),
	chr(151));

	$replace=array("'",
	"'",
	'"',
	'"',
	'-');

	return str_replace($search,$replace,$string);
}

function array_remove_keys($array,$keys=array()){

	// If array is empty or not an array at all, don't bother
	// doing anything else.
	if(empty($array) || (! is_array($array))){
		return $array;
	}

	// If $keys is a comma-separated list, convert to an array.
	if(is_string($keys)){
		$keys=explode(',',$keys);
	}

	// At this point if $keys is not an array, we can't do anything with it.
	if(! is_array($keys)){
		return $array;
	}

	// array_diff_key() expected an associative array.
	$assocKeys=array();
	foreach($keys as $key){
		$assocKeys[$key]=true;
	}

	return array_diff_key($array,$assocKeys);
}

function strstrb($h,$n){
	return array_shift(explode($n,$h,2));
}

function exists($u){
	$handle=@fopen($u,'r');
	return $handle;
}

function r_implode($glue,$pieces){
	foreach($pieces as $r_pieces ){
			if(is_array($r_pieces )){
					$retVal[]=r_implode($glue,$r_pieces);
			}else{
					$retVal[]=$r_pieces;
			}
		}
		return implode($glue,$retVal);
}

function stripos_array($haystack,$needles){
	if(is_array($needles)){
		foreach ($needles as $str){
			if(is_array($str)){
				$pos=stripos_array($haystack,$str);
			}else{
				$pos=stripos($haystack,$str);
			}
			if($pos !== FALSE){
				return $pos;
			}
		}
	}else{
		return stripos($haystack,$needles);
	}
}

function cleancommas($text){
	$text=preg_replace('/,+/', ',',$text);
	$text=trim($text,",");
	return $text;
}

function convertnulltostr(&$item,$key){
	if($item===null){
		$item=strval(null);
	}
}
function formatBytes($bytes,$precision=2){
	$units=array('B', 'KB', 'MB', 'GB', 'TB');

	$bytes=max($bytes, 0);
	$pow=floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow=min($pow, count($units) - 1);

	$bytes /= pow(1024,$pow);

	return round($bytes,$precision).' '.$units[$pow];
}

//Create list with commas and last item 'and'
function listing($items,$and=' and '){
	if(is_array($items)){
	if(count($items)>1){
		$lastItem=array_pop($items);
		$items=implode(', ',$items);
		$items=$items.$and.$lastItem;
	}else{
	$items=implode('',$items);
	}
	}

	return $items;
}

function checkkey($data){
	$keychk['status']='failed';
	if($data['f']=='story'){
		$thisdata=grabinfo('stories','identifier',$data['fid'],'1');
	}else{
		$thisdata=grabinfo('obstructions','identifier',$data['fid'],'1');
	}
	if($thisdata){
		if($thisdata['passkey']==sha1($data['key'])){
			$keychk['status']='verified';
			$keychk['key']=sha1($data['key']);
		}
	}
	return $keychk;
}

function showdata($f,$fid){
	$issinglepoint=false;
	switch($f){
		case 'story':
			$thisdata=grabinfo('stories','identifier',$fid,'1');
			$issinglepoint=true;
		break;
		case "obstruction":
			$thisdata=grabinfo('obstructions','identifier',$fid,'1');
			$issinglepoint=true;
		break;
		case "plan":
			$thisdata=grabinfo('dailyplans','planid',$fid,'1');
		break;
	}
	if($thisdata){
		if($issinglepoint){
			$thisdata['status']='ok';
			unset($thisdata['passkey']);
			$thisdata['longitude']=floatval($thisdata['longitude']);
			$thisdata['latitude']=floatval($thisdata['latitude']);
			$thisdata['timeofday']=intval($thisdata['timeofday']);
			$thisdata['timeofyear']=intval($thisdata['timeofyear']);
			$thisdata['source']=intval($thisdata['source']);
			$thisdata['type']=intval($thisdata['type']);
		}else{
			$thisdata['geojson']=json_decode($thisdata['plandata'],true);
			unset($thisdata['plandata'],$thisdata['plandatahash']);
		}
	}else{
		$thisdata['status']='nomatch';
	}
	return $thisdata;
}

function listdata($f,$a=true,$year='all',$sort='asc'){
	$issinglepoint=false;
	$thisdata=array();
	switch($f){
		case 'story':
			$issinglepoint=true;
			if($a){
				$listdata=grabinfo('stories','active','1',false,'modified',$sort);
			}else{
				$listdata=grabinfo('stories','active',false,false,'modified',$sort);
			}
		break;
		case "obstruction":
			$issinglepoint=true;
			if($a){
				$listdata=grabinfo('obstructions','active','1',false,'modified',$sort);
			}else{
				$listdata=grabinfo('obstructions','active',false,false,'modified',$sort);
			}
		break;
		case "plan":
			$listdata=grabinfo('dailyplans','planid');
		break;
	}
	foreach($listdata as $data){
		if($issinglepoint){
			unset($data['passkey']);
			if(key_exists('year',$data)){
				$thisyear=trim($data['year']);
			}else{
				$modified=explode("-",$data['modified']);
				$thisyear=trim($modified[0]);
			}
			$data['longitude']=floatval($data['longitude']);
			$data['latitude']=floatval($data['latitude']);
			$data['timeofday']=intval($data['timeofday']);
			$data['timeofyear']=intval($data['timeofyear']);
			$data['source']=intval($data['source']);
			$data['type']=intval($data['type']);
		}else{
			$data['geojson']=json_decode($data['plandata'],true);
			unset($data['plandata'],$data['plandatahash']);
		}
		if($year=='all'){
			$thisdata[]=$data;
		}elseif($year==$thisyear){
			$thisdata[]=$data;
		}
		
	}
	return $thisdata;
}

function createobstruction($createdata){
	global $con;
	// Set default as fail
	$showobstruction['status']='fail';
	$thisobstruction=$createdata['obstructionid'];
	
	if(!empty($createdata['type'])){
		$type=mysqli_real_escape_string($con,$createdata['type']);
	}else{
		$type="0";
	}
	if(!empty($createdata['description'])){
		$description="'".mysqli_real_escape_string($con,cleancurly($createdata['description']))."'";
	}else{
		$description="NULL";
	}

	if(!empty($createdata['longitude'])){
		$longitude=mysqli_real_escape_string($con,$createdata['longitude']);
	}else{
		$longitude="0";
	}
	
	if(!empty($createdata['latitude'])){
		$latitude=mysqli_real_escape_string($con,$createdata['latitude']);
	}else{
		$latitude="0";
	}

	if(!empty($createdata['active'])){
		$active=mysqli_real_escape_string($con,$createdata['active']);
	}else{
		$active="0";
	}
	if(!empty($createdata['key'])){
		$key=mysqli_real_escape_string($con,$createdata['key']);
	}else{
		$key="NULL";
	}
	if($createdata['type']!='' && $createdata['longitude']!='' && $createdata['latitude']!=''){
		$passkey=sha1($key);
		switch($createdata['j']){
			case "a":
				$sql="insert ignore into obstructions (type,description,longitude,latitude,active,passkey) VALUES (".$type.",".$description.",".$longitude.",".$latitude.",".$active.",'".$passkey."')";
			break;
			case "e":
				$sql="update obstructions SET type=".$type."',description=".$description.",longitude=".$longitude.",latitude=".$latitude.",active=".$active.",passkey='".$passkey."' WHERE obstructionid =".$thisobstruction;
			break;
			case "d":
				$sql="delete from obstructions WHERE obstructionid =".$thisobstruction;
			break;
		}
	}
	mysqli_query($con,$sql);
	if(mysqli_errno($con)!=0){
		$showobstruction= array();
		$showobstruction['status']='fail';
		$showobstruction['errno']=mysqli_errno($con);
		$showobstruction['error']=mysqli_error($con);
		$showobstruction['sql']=$sql;
	}else{
		if($createdata['j']=='a'){
			$thisobstruction=mysqli_insert_id($con);
			$identifier=trim(sprintf("%'.05d\n", $thisobstruction));
			mysqli_query($con,"update obstructions set identifier='OB".$identifier."' where obstructionid =".$thisobstruction);
		}
		if($thisobstruction){
			$info=grabinfo('obstructions','obstructionid',$thisobstruction,'1');
			$showobstruction['id']=$info['identifier'];
			$showobstruction['description']=$info['description'];
			$showobstruction['status']='ok';
		}
	}
	return $showobstruction;
}

function createstory($createdata){
	global $con;
	// Set default as fail
	$showstory['status']='fail';
	$thisstory=$createdata['storyid'];

	if(!empty($createdata['type'])){
		$type=mysqli_real_escape_string($con,$createdata['type']);
	}else{
		$type="0";
	}
	if(!empty($createdata['who'])){
		$who="'".mysqli_real_escape_string($con,cleancurly($createdata['who']))."'";
	}else{
		$who="NULL";
	}
	if(!empty($createdata['date'])){
		$date="'".mysqli_real_escape_string($con,cleancurly($createdata['date']))."'";
	}else{
		$date="NULL";
	}
	
	if(!empty($createdata['timeofday'])){
		$timeofday=mysqli_real_escape_string($con,$createdata['timeofday']);
	}else{
		$timeofday="0";
	}
	
	if(!empty($createdata['timeofyear'])){
		$timeofyear=mysqli_real_escape_string($con,$createdata['timeofyear']);
	}else{
		$timeofyear="0";
	}
	
	if(!empty($createdata['year'])){
		$year=mysqli_real_escape_string($con,$createdata['year']);
	}else{
		$year="2020";
	}
	
	if(!empty($createdata['sidewalks'])){
		$sidewalks="'".mysqli_real_escape_string($con,cleancurly($createdata['sidewalks']))."'";
	}else{
		$sidewalks="NULL";
	}
	
	if(!empty($createdata['weather'])){
		$weather="'".mysqli_real_escape_string($con,cleancurly($createdata['weather']))."'";
	}else{
		$weather="NULL";
	}
	
	if(!empty($createdata['vehicles'])){
		$vehicles="'".mysqli_real_escape_string($con,cleancurly($createdata['vehicles']))."'";
	}else{
		$vehicles="NULL";
	}
	
	if(!empty($createdata['description'])){
		$description="'".mysqli_real_escape_string($con,cleancurly($createdata['description']))."'";
	}else{
		$description="NULL";
	}

	if(!empty($createdata['longitude'])){
		$longitude=mysqli_real_escape_string($con,$createdata['longitude']);
	}else{
		$longitude="0";
	}
	
	if(!empty($createdata['latitude'])){
		$latitude=mysqli_real_escape_string($con,$createdata['latitude']);
	}else{
		$latitude="0";
	}
	
	if(!empty($createdata['impact'])){
		$impact="'".mysqli_real_escape_string($con,$createdata['impact'])."'";
	}else{
		$impact="NULL";
	}

	if(!empty($createdata['source'])){
		$source=mysqli_real_escape_string($con,$createdata['source']);
	}else{
		$source="0";
	}

	if(!empty($createdata['link'])){
		$link="'".mysqli_real_escape_string($con,$createdata['link'])."'";
	}else{
		$link="NULL";
	}

	if(!empty($createdata['active'])){
		$active=mysqli_real_escape_string($con,$createdata['active']);
	}else{
		$active="0";
	}
	if(!empty($createdata['key'])){
		$key=mysqli_real_escape_string($con,$createdata['key']);
	}else{
		$key="NULL";
	}
	if($createdata['description']!='' && $createdata['longitude']!='' && $createdata['latitude']!=''){
		$passkey=sha1($key);
		switch($createdata['j']){
			case "a":
				$sql="insert ignore into stories (type,who,date,timeofday,timeofyear,year,sidewalks,weather,vehicles,description,longitude,latitude,impact,source,link,active,passkey) VALUES (".$type.",".$who.",".$date.",".$timeofday.",".$timeofyear.",".$year.",".$sidewalks.",".$weather.",".$vehicles.",".$description.",".$longitude.",".$latitude.",".$impact.",".$source.",".$link.",".$active.",'".$passkey."')";
			break;
			case "e":
				$sql="update stories SET type=".$type.",who=".$who.",date=".$date.",timeofday=".$timeofday.",timeofyear=".$timeofyear.",year=".$year.",sidewalks=".$sidewalks.",weather=".$weather.",vehicles=".$vehicles.",description=".$description.",longitude=".$longitude.",latitude=".$latitude.",impact=".$impact.",source=".$source.",link=".$link.",active=".$active.",passkey='".$passkey."' WHERE storyid =".$thisstory;
			break;
			case "d":
				$sql="delete from stories WHERE storyid =".$thisstory;
			break;
		}
	}
	mysqli_query($con,$sql);
	if(mysqli_errno($con)!=0){
		$showstory= array();
		$showstory['status']='fail';
		$showstory['errno']=mysqli_errno($con);
		$showstory['error']=mysqli_error($con);
		$showstory['sql']=$sql;
	}else{
		if($createdata['j']=='a'){
			$thisstory=mysqli_insert_id($con);
			$identifier=trim(sprintf("%'.05d\n", $thisstory));
			mysqli_query($con,"update stories set identifier='S".$identifier."' where storyid =".$thisstory);
		}
		if($thisstory){
			$info=grabinfo('stories','storyid',$thisstory,'1');
			$showstory['id']=$info['identifier'];
			$showstory['status']='ok';
		}
	}
	return $showstory;
}

date_default_timezone_set('America/St_Johns');
ini_set('max_execution_time', 1000);
session_name("sidewalkssession");
if(!empty($_COOKIE['sidewalkssession'])){
		session_id($_COOKIE['sidewalkssession']);
}
session_start();
setcookie(session_name(), session_id(), time()+60*60*24, '/',"yyt-sidewalks.info");
header("Access-Control-Allow-Origin: *");?>