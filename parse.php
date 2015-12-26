<?php
$codes = include 'codes.php';
if(in_array('--dump-stations', $argv))
{
	print_r(array_keys($codes));
	exit;
}
if($argc < 4)
{
	echo 'Usage : php ', $argv[0], ' <depart> <arrivee> <heure HHMM> <date DD/MM/YYYY>', PHP_EOL;
	echo 'Executer avec --dump-stations pour afficher toutes les stations existantes.', PHP_EOL;
	exit;
}
$depart = $argv[1];
if(!array_key_exists($depart, $codes))
	die('Unknown station : ' . $depart . PHP_EOL);
$arrivee = $argv[2];
if(!array_key_exists($arrivee, $codes))
	die('Unknown station : ' . $arrivee . PHP_EOL);
$heure = $argv[3];
$date = $argv[4];
$url = sprintf('http://www.oncf.ma/Pages/ResultatsHoraire.aspx?depart=%s&arrivee=%s&CodeRD=%s&CodeGD=%s&CodeRA=%s&CodeGA=%s&heure=%s&date=%s',
	urlencode($depart), urlencode($arrivee),
	urlencode($codes[$depart]['code_r']), urlencode($codes[$depart]['code_g']),
	urlencode($codes[$arrivee]['code_r']), urlencode($codes[$arrivee]['code_g']),
	$heure, $date
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$html = curl_exec($ch);
if($html === false)
	exit('Erreur lors du telechargement des horaires : ' . curl_error($ch));
file_put_contents('horaires.html', $html);
$dom = new DOMDocument;
$dom->recover = true;
$dom->strictErrorChecking = false;
@$dom->loadHTML($html);

$xpath = new DOMXPath($dom);
foreach($xpath->query('//table[@bordercolor="#ffffff"]/tr') as $row)
{
	if(!$row->hasAttribute('bgcolor'))
		continue;
	$tds = $row->childNodes;
	$depart = $tds->item(0)->textContent;
	$arrivee = $tds->item(2)->textContent;
	$correspondance = $tds->item(4)->textContent;
	echo 'Depart : ', $depart, ', arrivee : ', $arrivee, ', correspondance : ', $correspondance, PHP_EOL;
}
