<?php

// Author @ Eric Reinsmidt
// Date @ 2011.04.27

function curl($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	return curl_exec($ch);
	curl_close ($ch);
}

$stock = strtoupper($_POST["stock"]);
$predictor = strtoupper($_POST["predictor"]);
$predictor2 = '^DJI';
$time = $_POST["time"];

$dayS = date('d', strtotime('-1 day'));
$monthS = date('m', strtotime('-1 day')) - 1;
$yearS = date('Y', strtotime('-1 day'));
$dayF = date('d', strtotime('-5 year'));
$monthF = date('m', strtotime('-5 year')) - 1;
$yearF = date('Y', strtotime('-5 year'));
$timeLength = ($yearS - $yearF);

// Stock to predict
$csv = "http://ichart.finance.yahoo.com/table.csv?s=".$stock."&a=".$monthF."&b=".$dayF."&c=".$yearF."&d=".$monthS."&e=".$dayS."&f=".$yearS."&g=d&ignore=.csv";
$query = curl($csv);
$query = str_replace(' ', ',', $query);
$query = str_replace(PHP_EOL, ',', $query);
$stockInfo = explode(",",$query);

/*
$tester = 0;
for ($i=0; $i < sizeof($stockInfo) - 1; $i++) { 
	$tester++;
}
echo $tester;*/

// Predictor 1, User defined, will be directly related predictor
$csv2 = "http://ichart.finance.yahoo.com/table.csv?s=".$predictor."&a=".$monthF."&b=".$dayF."&c=".$yearF."&d=".$monthS."&e=".$dayS."&f=".$yearS."&g=d&ignore=.csv";
$query2 = curl($csv2);
$query2 = str_replace(' ', ',', $query2);
$query2 = str_replace(PHP_EOL, ',', $query2);
$stockInfo2 = explode(",",$query2);

// Predictor 2, Dow Jones Industrial Average, will be a directly related predictor
$csv3 = "http://ichart.finance.yahoo.com/table.csv?s=".$predictor2."&a=".$monthF."&b=".$dayF."&c=".$yearF."&d=".$monthS."&e=".$dayS."&f=".$yearS."&g=d&ignore=.csv";
$query3 = curl($csv3);
$query3 = str_replace(' ', ',', $query3);
$query3 = str_replace(PHP_EOL, ',', $query3);
$stockInfo3 = explode(",",$query3);

$begin = 0;
$end = 0;

if ($time == 1) {
	$begin = 0;
	$end = 1771;
}

if ($time == 2) {
	$begin = 1772;
	$end = 3535;
}

if ($time == 3) {
	$begin = 3536;
	$end = 5292;
}

if ($time == 4) {
	$begin = 5293;
	$end = 7056;
}

if ($time == 5) {
	$begin = 7057;
	$end = (sizeof($stockInfo) - 1);
}

	echo '<html>'.
		 	'<head>'.
				'<link rel="stylesheet" type="text/css" href="css/predictor.css" />'.
			'</head>'.
			'<title>Bayesian Prediction Model</title>'.
			'<style type="text/css">'.
				'h3{text-align: center;}'.
				'table{margin-left: auto; margin-right: auto;}'.
				'td{border:1px solid black; text-align: center;}'.
				'#results{text-align: center;float:right;width:30%;}'.
				'#left{width:36%;height:350px;float:left;margin-left:0px;}'.
				'#right{width:60%;height:350px;float:right;}'.
				'#header_left{width:40%;height: 100px;float:left;}'.
				'#header_right{width:60%;height: 100px;float:right;}'.
				'#explanation{margin-left:auto;margin-right:auto;float:left;text-align:center;}'.
			'</style>'.
			'<body style="width:1160px;margin-left: auto; margin-right: auto;">'.
				'<div id="wrapper" style="height:90%;">';

echo 	'<div id="header">'.
			'S = '.$stock.', '.
			'P<sub>1</sub> = '.$predictor.
			', &amp; P<sub>2</sub> = '.$predictor2.
			'<br />for the testing period of the 5 previous years with the exception of year '.$time.'<br /><br /><br /><br />'.
		'</div>';


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////	first block start	////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$count = 0;
$SI_PI = 0;
$SI_PD = 0;
$SD_PI = 0;
$SD_PD = 0;

for ($i = 14; $i < sizeof($stockInfo) - 1; $i += 7) {
	if ($i <= $begin || $i > $end) {
		$count++;
		if ($stockInfo[$i] > $stockInfo[$i + 7]) {

			if ($stockInfo2[$i] > $stockInfo2[$i + 7]) {
				$SI_PI++;
			}
			else {
				$SI_PD++;
			}
		}
		else {
			if ($stockInfo2[$i] > $stockInfo2[$i + 7]) {
				$SD_PI++;
			}
			else {
				$SD_PD++;
			}
		}
	}
}

$SD = ($SD_PD + $SD_PI);
$SI = ($SI_PD + $SI_PI);
$PD = ($SI_PD + $SD_PD);
$PI = ($SI_PI + $SD_PI);
$totalFrequency = ($SI_PD + $SI_PI + $SD_PD + $SD_PI);
echo '<div id="main">'.
		'<div id="left">';
echo '<table>'.
		'<tr>'.
			'<td style="border: 0px; width: 26px;"></td>'.
			'<td style="border: 0px;"></td>'.
			'<td colspan="2">P<sub>1</sub></td>'.
			'<td style="border: 0px;"></td>'.
		'</tr>'.
		'<tr>'.
			'<td style="border: 0px; width: 26px;"></td>'.
			'<td style="border: 0px;"></td>'.
			'<td style="color: #0AC92B">Increase</td>'.
			'<td style="color: #B22222">Decrease</td>'.
			'<td>Frequency</td>'.
		'</tr>'.
		'<tr>'.
			'<td rowspan="2" style="width: 26px;">S</td>'.
			'<td style="color: #0AC92B">Increase</td>'.
			'<td>' . $SD_PD . '</td>'.
			'<td>' . $SD_PI . '</td>'.
			'<td>' . $SD . '</td>'.
		'</tr>'.
		'<tr>'.
			'<td style="color: #B22222">Decrease</td>'.
			'<td>' . $SI_PD . '</td>'.
			'<td>' . $SI_PI . '</td>'.
			'<td>' . $SI . '</td>'.
		'</tr>'.
		'<tr>'.
			'<td style="border: 0px; width: 26px;"></td>'.
			'<td>Frequency</td>'.
			'<td>' . $PD . '</td>'.
			'<td>' . $PI . '</td>'.
			'<td style="border:1px solid blue;background-color: rgba(0,0,255,0.15);">' . $totalFrequency . '</td>'.
		'</tr>'.
	'</table><br /><br />';

$probability = round($SI_PI/$PI*100, 2);

$dd1 = ($SD_PD/$PD);
//echo $dd1.'<br />';
$di1 = ($SD_PI/$PI);
//echo $di1.'<br />';
$id1 = ($SI_PD/$PD);
//echo $id1.'<br />';
$ii1 = ($SI_PI/$PI);
//echo $ii1.'<br />';


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////	first block finish	////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////	second block start	////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


$count = 0;
$SI_PI = 0;
$SI_PD = 0;
$SD_PI = 0;
$SD_PD = 0;

for ($i = 14; $i < sizeof($stockInfo) - 1; $i += 7) {
	if ($i <= $begin || $i > $end) {
		$count++;
		if ($stockInfo[$i] > $stockInfo[$i + 7]) {
			if ($stockInfo3[$i] > $stockInfo3[$i + 7]) {
				$SI_PI++;
			}
			else {
				$SI_PD++;
			}
		}
		else {
			if ($stockInfo3[$i] > $stockInfo3[$i + 7]) {
				$SD_PI++;
			}
			else {
				$SD_PD++;
			}
		}
	}
}

$SD = ($SD_PD + $SD_PI);
$SI = ($SI_PD + $SI_PI);
$PD = ($SI_PD + $SD_PD);
$PI = ($SI_PI + $SD_PI);
$totalFrequency = ($SI_PD + $SI_PI + $SD_PD + $SD_PI);

echo '<table>'.
		'<tr>'.
			'<td style="border: 0px; width: 26px;"></td>'.
			'<td style="border: 0px;"></td>'.
			'<td colspan="2">P<sub>2</sub></td>'.
			'<td style="border: 0px;"></td>'.
		'</tr>'.
		'<tr>'.
			'<td style="border: 0px; width: 26px;"></td>'.
			'<td style="border: 0px;"></td>'.
			'<td style="color: #0AC92B">Increase</td>'.
			'<td style="color: #B22222">Decrease</td>'.
			'<td>Frequency</td>'.
		'</tr>'.
		'<tr>'.
			'<td rowspan="2" style="width: 26px;">S</td>'.
			'<td style="color: #0AC92B">Increase</td>'.
			'<td>' . $SD_PD . '</td>'.
			'<td>' . $SD_PI . '</td>'.
			'<td>' . $SD . '</td>'.
		'</tr>'.
		'<tr>'.
			'<td style="color: #B22222">Decrease</td>'.
			'<td>' . $SI_PD . '</td>'.
			'<td>' . $SI_PI . '</td>'.
			'<td>' . $SI . '</td>'.
		'</tr>'.
		'<tr>'.
			'<td style="border: 0px; width: 26px;"></td>'.
			'<td>Frequency</td>'.
			'<td>' . $PD . '</td>'.
			'<td>' . $PI . '</td>'.
			'<td style="border:1px solid blue;background-color: rgba(0,0,255,0.15);">' . $totalFrequency . '</td>'.
		'</tr>'.
	'</table><br /><br />'.
	'</div>';

$probability = round($SI_PI/$PI*100, 2);

$dd2 = ($SD_PD/$PD);
//echo $dd2.'<br />';
$di2 = ($SD_PI/$PI);
//echo $di2.'<br />';
$id2 = ($SI_PD/$PD);
//echo $id2.'<br />';
$ii2 = ($SI_PI/$PI);
//echo $ii2.'<br />';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////	second block finish	////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////	third block start	////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$ddd = 0;
$ddi = 0;
$did = 0;
$dii = 0;
$idd = 0;
$idi = 0;
$iid = 0;
$iii = 0;

$binary_table_frequency = 0;

for ($i = 14; $i < sizeof($stockInfo) - 1; $i += 7) {
	if ($i <= $begin || $i > $end) {
		if (($stockInfo[$i] >= $stockInfo[$i + 7]) && ($stockInfo2[$i] >= $stockInfo2[$i + 7]) && ($stockInfo3[$i] >= $stockInfo3[$i + 7])) {
			$ddd++;
		}
		if (($stockInfo[$i] >= $stockInfo[$i + 7]) && ($stockInfo2[$i] >= $stockInfo2[$i + 7]) && ($stockInfo3[$i] < $stockInfo3[$i + 7])) {
			$ddi++;
		}
		if (($stockInfo[$i] >= $stockInfo[$i + 7]) && ($stockInfo2[$i] < $stockInfo2[$i + 7]) && ($stockInfo3[$i] >= $stockInfo3[$i + 7])) {
			$did++;
		}
		if (($stockInfo[$i] >= $stockInfo[$i + 7]) && ($stockInfo2[$i] < $stockInfo2[$i + 7]) && ($stockInfo3[$i] < $stockInfo3[$i + 7])) {
			$dii++;
		}
		if (($stockInfo[$i] < $stockInfo[$i + 7]) && ($stockInfo2[$i] >= $stockInfo2[$i + 7]) && ($stockInfo3[$i] >= $stockInfo3[$i + 7])) {
			$idd++;
		}
		if (($stockInfo[$i] < $stockInfo[$i + 7]) && ($stockInfo2[$i] >= $stockInfo2[$i + 7]) && ($stockInfo3[$i] < $stockInfo3[$i + 7])) {
			$idi++;
		}
		if (($stockInfo[$i] < $stockInfo[$i + 7]) && ($stockInfo2[$i] < $stockInfo2[$i + 7]) && ($stockInfo3[$i] >= $stockInfo3[$i + 7])) {
			$iid++;
		}
		if (($stockInfo[$i] < $stockInfo[$i + 7]) && ($stockInfo2[$i] < $stockInfo2[$i + 7]) && ($stockInfo3[$i] < $stockInfo3[$i + 7])) {
			$iii++;
		}
	}
}

$stack = array();

$dddp = (($dd1*$dd2)/$SD)*10000;
$ddip = (($dd1*$di2)/$SD)*10000;
$didp = (($di1*$dd2)/$SD)*10000;
$diip = (($di1*$di2)/$SD)*10000;
$iddp = (($id1*$id2)/$SI)*10000;
$idip = (($id1*$ii2)/$SI)*10000;
$iidp = (($ii1*$id2)/$SI)*10000;
$iiip = (($ii1*$ii2)/$SI)*10000;

$binary_table_frequency = ($ddd+$ddi+$did+$dii+$idd+$idi+$iid+$iii);

array_push($stack, $dddp*($ddd/$binary_table_frequency));
array_push($stack, $ddip*($ddi/$binary_table_frequency));
array_push($stack, $didp*($did/$binary_table_frequency));
array_push($stack, $diip*($dii/$binary_table_frequency));
array_push($stack, $iddp*($idd/$binary_table_frequency));
array_push($stack, $idip*($idi/$binary_table_frequency));
array_push($stack, $iidp*($iid/$binary_table_frequency));
array_push($stack, $iiip*($iii/$binary_table_frequency));

//print_r($stack);
//echo max($stack);


$result_label = '';

echo '<div id="right">'.
		'<table>'.
			'<tr>'.
				'<td>S</td>'.
				'<td>P<sub>1</sub></td>'.
				'<td>P<sub>2</sub></td>'.
				'<td>Local Frequency</td>'.
				'<td>Probability</td>'.
				'<td>Weighted Probability</td>'.
			'</tr>'.
			'<tr>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="background-color: rgba(255,255,0,0.15);">'.$ddd.'</td>'.
				'<td style="background-color: rgba(139,69,19,0.15);">'.round($dddp, 5).'</td>';
				if (round(max($stack), 5) == round($stack[0], 5)) {
					echo '<td style="border:1px solid red; background-color: rgba(255,0,0,0.15);">'.round($stack[0], 5).'</td>';
					$result_label = 'ddd';
				}
				else
					echo '<td style="background-color: rgba(0,255,0,0.15);">'.round($stack[0], 5).'</td>';
			echo '</tr>'.
			'<tr>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="background-color: rgba(255,255,0,0.15);">'.$ddi.'</td>'.
				'<td style="background-color: rgba(139,69,19,0.15);">'.round($ddip, 5).'</td>';
				if (round(max($stack), 5) == round($stack[1], 5)) {
					echo '<td style="border:1px solid red; background-color: rgba(255,0,0,0.15);">'.round($stack[1], 5).'</td>';
					$result_label = 'ddi';
				}
				else
					echo '<td style="background-color: rgba(0,255,0,0.15);">'.round($stack[1], 5).'</td>';
			echo '</tr>'.
			'<tr>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="background-color: rgba(255,255,0,0.15);">'.$did.'</td>'.
				'<td style="background-color: rgba(139,69,19,0.15);">'.round($didp, 5).'</td>';
				if (round(max($stack), 5) == round($stack[2], 5)) {
					echo '<td style="border:1px solid red; background-color: rgba(255,0,0,0.15);">'.round($stack[2], 5).'</td>';
					$result_label = 'did';
				}
				else
					echo '<td style="background-color: rgba(0,255,0,0.15);">'.round($stack[2], 5).'</td>';
			echo '</tr>'.
			'<tr>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="background-color: rgba(255,255,0,0.15);">'.$dii.'</td>'.
				'<td style="background-color: rgba(139,69,19,0.15);">'.round($diip, 5).'</td>';
				if (round(max($stack), 5) == round($stack[3], 5)) {
					echo '<td style="border:1px solid red; background-color: rgba(255,0,0,0.15);">'.round($stack[3], 5).'</td>';
					$result_label = 'dii';
				}
				else
					echo '<td style="background-color: rgba(0,255,0,0.15);">'.round($stack[3], 5).'</td>';
			echo '</tr>'.
			'<tr>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="background-color: rgba(255,255,0,0.15);">'.$idd.'</td>'.
				'<td style="background-color: rgba(139,69,19,0.15);">'.round($iddp, 5).'</td>';
				if (round(max($stack), 5) == round($stack[4], 5)) {
					echo '<td style="border:1px solid red; background-color: rgba(255,0,0,0.15);">'.round($stack[4], 5).'</td>';
					$result_label = 'idd';
				}
				else
					echo '<td style="background-color: rgba(0,255,0,0.15);">'.round($stack[4], 5).'</td>';
			echo '</tr>'.
			'<tr>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="background-color: rgba(255,255,0,0.15);">'.$idi.'</td>'.
				'<td style="background-color: rgba(139,69,19,0.15);">'.round($idip, 5).'</td>';
				if (round(max($stack), 5) == round($stack[5], 5)) {
					echo '<td style="border:1px solid red; background-color: rgba(255,0,0,0.15);">'.round($stack[5], 5).'</td>';
					$result_label = 'idi';
				}
				else
					echo '<td style="background-color: rgba(0,255,0,0.15);">'.round($stack[5], 5).'</td>';
			echo '</tr>'.
			'<tr>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="color: #B22222">Decrease</td>'.
				'<td style="background-color: rgba(255,255,0,0.15);">'.$iid.'</td>'.
				'<td style="background-color: rgba(139,69,19,0.15);">'.round($iidp, 5).'</td>';
				if (round(max($stack), 5) == round($stack[6], 5)) {
					echo '<td style="border:1px solid red; background-color: rgba(255,0,0,0.15);">'.round($stack[6], 5).'</td>';
					$result_label = 'iid';
				}
				else
					echo '<td style="background-color: rgba(0,255,0,0.15);">'.round($stack[6], 5).'</td>';
			echo '</tr>'.
			'<tr>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="color: #0AC92B">Increase</td>'.
				'<td style="background-color: rgba(255,255,0,0.15);">'.$iii.'</td>'.
				'<td style="background-color: rgba(139,69,19,0.15);">'.round($iiip, 5).'</td>';
				if (round(max($stack), 5) == round($stack[7], 5)) {
					echo '<td style="border:1px solid red; background-color: rgba(255,0,0,0.15);">'.round($stack[7], 5).'</td>';
					$result_label = 'iii';
				}
				else
					echo '<td style="background-color: rgba(0,255,0,0.15);">'.round($stack[7], 5).'</td>';
			echo '</tr>'.
			'<tr>'.
				'<td style="border: 0px;"></td>'.
				'<td style="border: 0px;"></td>'.
				'<td style="border: 0px;"></td>'.
				'<td style="border:1px solid blue;background-color: rgba(0,0,255,0.15);">' . $binary_table_frequency.'</td>'.
				'<td style="border: 0px;"></td>'.
				'<td style="border: 0px;"></td>'.
				//255-228-196
				//'<td>Prediction Based On</td>'.
				//'<td style="border:1px solid red; background-color: rgba(255,0,0,0.15);">' . round(max($stack), 5).'</td>'.
			'</tr>'.
		'</table><br /><br />'.
		'</div>';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////	third block finish	////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo 	'<div id="explanation">'.
			'<a style="background-color: rgba(255,0,0,0.15);">Prediction</a> = Max(<a style="background-color: rgba(0,255,0,0.15);">Weighted Probability</a>)<br />'.
			'<a style="background-color: rgba(0,255,0,0.15);">Weighted Probability</a> = <a style="background-color: rgba(139,69,19,0.15);">Probability</a> * (<a style="background-color: rgba(255,255,0,0.15);">Local Frequency</a> / <a style="background-color: rgba(0,0,255,0.15);">Total Frequency</a>)<br />'.
			'<a style="background-color: rgba(139,69,19,0.15);">Probability</a> = (P(S|P<sub>1</sub>) * P(S|P<sub>2</sub>))/P(S) for each of the eight possible outcomes.'.
		'</div>';
//$tester = 0;

$correct = 0;
$incorrect = 0;

for ($i = 14; $i < sizeof($stockInfo) - 1; $i += 7) {
	if ($i >= $begin && $i < $end) {
		if ($result_label == 'ddd') {
			if (($stockInfo2[$i] >= $stockInfo2[$i + 7]) && ($stockInfo3[$i] >= $stockInfo3[$i + 7])) {
				if ($stockInfo[$i] >= $stockInfo[$i + 7])
					$correct++;
				else
					$incorrect++;
			}
		}
		if ($result_label == 'ddi') {
			if (($stockInfo2[$i] >= $stockInfo2[$i + 7]) && ($stockInfo3[$i] < $stockInfo3[$i + 7])) {
				if ($stockInfo[$i] >= $stockInfo[$i + 7])
					$correct++;
				else
					$incorrect++;
			}
		}
		if ($result_label == 'did') {
			if (($stockInfo2[$i] < $stockInfo2[$i + 7]) && ($stockInfo3[$i] >= $stockInfo3[$i + 7])) {
				if ($stockInfo[$i] >= $stockInfo[$i + 7])
					$correct++;
				else
					$incorrect++;
			}
		}
		if ($result_label == 'dii') {
			if (($stockInfo2[$i] < $stockInfo2[$i + 7]) && ($stockInfo3[$i] < $stockInfo3[$i + 7])) {
				if ($stockInfo[$i] >= $stockInfo[$i + 7])
					$correct++;
				else
					$incorrect++;
			}
		}
		if ($result_label == 'idd') {
			if (($stockInfo2[$i] >= $stockInfo2[$i + 7]) && ($stockInfo3[$i] >= $stockInfo3[$i + 7])) {
				if ($stockInfo[$i] < $stockInfo[$i + 7])
					$correct++;
				else
					$incorrect++;
			}
		}
		if ($result_label == 'idi') {
			if (($stockInfo2[$i] >= $stockInfo2[$i + 7]) && ($stockInfo3[$i] < $stockInfo3[$i + 7])) {
				if ($stockInfo[$i] < $stockInfo[$i + 7])
					$correct++;
				else
					$incorrect++;
			}
		}
		if ($result_label == 'iid') {
			if (($stockInfo2[$i] < $stockInfo2[$i + 7]) && ($stockInfo3[$i] >= $stockInfo3[$i + 7])) {
				if ($stockInfo[$i] < $stockInfo[$i + 7])
					$correct++;
				else
					$incorrect++;
			}
		}
		if ($result_label == 'iii') {
			if (($stockInfo2[$i] < $stockInfo2[$i + 7]) && ($stockInfo3[$i] < $stockInfo3[$i + 7])) {
				if ($stockInfo[$i] < $stockInfo[$i + 7])
					$correct++;
				else
					$incorrect++;
			}
		}
	}
}
//echo $tester;
echo    '<div id="results">'.
			'<table>'.
				'<tr>'.
					'<td colspan="2">Results for Year '.$time.'</td>'.
				'</tr>'.
				'<tr>'.
					'<td>Correct</td>'.
					'<td>Incorrect</td>'.
				'</tr>'.
				'<tr>'.
					'<td>'.$correct.'</td>'.
					'<td>'.$incorrect.'</td>'.
				'</tr>'.
			'</table><br /><br /><br />'.
			'Percentage Correct = '.round($correct/($correct+$incorrect)*100, 2).'%'.
		'</div>';

echo'</div>'. 
	'</body>'.
	'<html>';

?>