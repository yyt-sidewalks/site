<?php require('header.php');
$cookie = tmpfile();
$userAgent = 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.64 Safari/537.31';
$today=date('Y-m-d');
$headerurl="https://map.stjohns.ca/snow/SnowRemovalWS/SnowRemovalWS.asmx/GetSubmissions?mode=Production&strStartDateTime=2020-02-01T03%3A30%3A00.000Z&strEndDateTime=".$today."T03%3A30%3A00.000Z";
$grabheaders = curl_init();
curl_setopt($grabheaders, CURLOPT_URL, $headerurl);
curl_setopt($grabheaders, CURLOPT_RETURNTRANSFER, true);
curl_setopt($grabheaders, CURLOPT_AUTOREFERER, true);
curl_setopt($grabheaders, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($grabheaders, CURLOPT_USERAGENT, $userAgent);
curl_setopt($grabheaders, CURLOPT_COOKIEFILE, $cookie);
$grabdataheaders=curl_exec($grabheaders);
curl_close($grabheaders);

if(strlen(trim($grabdataheaders))>0){
    $cleanheader=trim(preg_replace("/\n\r|\n|\r/ui",'',$grabdataheaders));
    $cleanheader=str_replace('<?xml version="1.0" encoding="utf-8"?>','',$cleanheader);
    $cleanheader=str_replace('<string xmlns="http://SnowRemovalWS.stjohns.com/">','',$cleanheader);
    $cleanheader=trim(str_replace('</string>','',$cleanheader));
    $headerjson=json_decode($cleanheader,true);
    foreach($headerjson as $header){
        $chkplanid=grabinfo('dailyplans','planid',$header['OBJECTID'],'1');
        if(!$chkplanid){
            $featuresurl="https://map.stjohns.ca/mapsrv/rest/services/SnowRemoval/SnowRemoval/FeatureServer/0/query?f=geojson&where=SubmitID%20%3D%20".$header['OBJECTID']."&returnGeometry=true&spatialRel=esriSpatialRelIntersects&outFields=*";
            $grab = curl_init();
            curl_setopt($grab, CURLOPT_URL, $featuresurl);
            curl_setopt($grab, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($grab, CURLOPT_AUTOREFERER, true);
            curl_setopt($grab, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($grab, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($grab, CURLOPT_COOKIEFILE, $cookie);
            $grabdata=curl_exec($grab);
            curl_close($grab);
            $json=json_decode($grabdata,true);
            if(strlen(trim($grabdata))>0 && array_key_exists('features', $json)){
                if(count($json['features'])>0){
                    $thisgrabhash=sha1($grabdata);
                    $chkhash=grabinfo('dailyplans','plandatahash',$thisgrabhash,'1');
                    if(!$chkhash){
                        mysqli_query($con, "insert into dailyplans (plandata,plandatahash,planid,startdate,enddate,submitted,description) values ('".mysqli_real_escape_string($con,$grabdata)."','".$thisgrabhash."','".$header['OBJECTID']."','".preg_replace("/[^0-9]/ui",'',$header['StartDateTime'])."','".preg_replace("/[^0-9]/ui",'',$header['EndDateTime'])."','".preg_replace("/[^0-9]/ui",'',$header['SubmittedDate'])."','".mysqli_real_escape_string($con,$header['LeadingText'])."')");
                        exit();
                    }
                }
            }
        }
    }    
}