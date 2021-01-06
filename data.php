<?php require('header.php');
header('Expires: Thu, 19 Nov 1981 08:52:00 GMT' );
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0' );
header('Pragma: no-cache' );
header("Content-Type: application/json" );
$job=false;
$thisdata=[];
$year='all';
$sort='asc';
if($argv){
	parse_str(implode('&', array_slice($argv, 1)), $_POST);
}
if($_POST['j']){
	$_GET['j']=$_POST['j'];
	$job=$_POST['j'];
}
if($_GET['j']){
	$_POST['j']=$_GET['j'];
	$job=$_GET['j'];
}
if($_POST['f']){
	$_GET['f']=$_POST['f'];
	$for=$_POST['f'];
	$id=$_POST['fid'];
	$active=$_POST['a'];
}
if($_GET['f']){
	$_POST['f']=$_GET['f'];
	$for=$_GET['f'];
	$id=$_GET['fid'];
	$active=$_GET['a'];
}
if($_POST['year']){
	$_GET['year']=$_POST['year'];
	$year=$_POST['f'];
}
if($_GET['year']){
	$_POST['year']=$_GET['year'];
	$year=$_GET['year'];
}
if($_POST['sort']){
	$_GET['sort']=$_POST['sort'];
	$sort=$_POST['f'];
}
if($_GET['sort']){
	$_POST['sort']=$_GET['sort'];
	$sort=$_GET['sort'];
}
if($job){
	$data=array();
	switch($job){
		case "a":
		case "e":
		case "d":
			$data=$_POST;
			switch($for){
				case "story":
					$func="createstory";
				break;
				case "obstruction":
					$func="createobstruction";
				break;
			}
			$thisdata=$func($data);
		break;
		case "k":
			$thisdata=checkkey($_POST);
		break;
		case "s":
			if($id && $for){
				$thisdata=showdata($for,$id);
			}
		break;
		case "l":
			if($for){
				$thisdata=listdata($for,$active,$year,$sort);
			}
		break;
	}
	print json_encode($thisdata);
}?>