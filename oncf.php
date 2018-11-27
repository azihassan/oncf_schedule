<?php
$codes = include 'codes.php';
if(in_array('--dump-stations', $argv))
{
	print_r(array_keys($codes));
	exit;
}
if($argc < 4)
{
	echo 'Usage : php ', $argv[0], ' <depart> <arrivee> <heure HH:MM> <date DD/MM/YYYY>', PHP_EOL;
	echo 'Executer avec --dump-stations pour afficher toutes les stations existantes.', PHP_EOL;
	exit;
}

$depart = $argv[1];
$arrivee = $argv[2];

if(!array_key_exists($depart, $codes))
	die('Unknown station : ' . $depart . PHP_EOL);
if(!array_key_exists($arrivee, $codes))
	die('Unknown station : ' . $arrivee . PHP_EOL);

$heure = $argv[3];
$date = $argv[4];
$url = sprintf('https://www.oncf.ma/fr/Horaires?from[%s][%s]=%s&to[%s][%s]=%s&datedep=%s+%s&dateret=&is-ar=0',
	$codes[$depart]['code_g'], $codes[$depart]['code_r'], urlencode($depart),
	$codes[$arrivee]['code_g'], $codes[$arrivee]['code_r'],  urlencode($arrivee),
	urlencode($date), urlencode($heure)
);

echo 'Fetching data from URL ', $url, PHP_EOL;

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
foreach($xpath->query('//div[@class="container"]/table/tbody/tr') as $row)
{
	$tds = $row->childNodes;
	$depart = trim($tds->item(0)->textContent);
	$arrivee = trim($tds->item(2)->textContent);
	$correspondance = trim($tds->item(4)->textContent);
	echo 'Depart : ', $depart, ', arrivee : ', $arrivee, ', correspondance : ', $correspondance, PHP_EOL;
}
