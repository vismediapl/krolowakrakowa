<?php
function e_gd_available () {
	return (function_exists ('gd_info') && $GLOBALS['GDEnabled']);
	}
function e_colors_array ($String) {
	$Colors = explode ('|', $String);
	for ($i = 0, $c = count ($Colors); $i < $c; ++$i) {
		for ($j = 0; $j < 3; ++$j) $Array[$i][$j] = hexdec ('0x'.substr ($Colors[$i], (2 * $j), 2));
		}
	return ($Array);
	}
function e_image_text ($Image, $X, $Y, $Size, $String, $Color = 0) {
	static $Text;
	if (!$Text) $Text = imagecolorallocate ($Image, 60, 60, 60);
	imagettftext ($Image, $Size, 0, $X, $Y, ($Color?imagecolorallocate ($Image, $Color[0], $Color[1], $Color[2]):$Text), ESTATS_PATH.'share/fonts/DejaVuSansMono.ttf', $String);
	}
function e_image_cache ($CacheFile, $Time) {
	if (!e_cache_status ($CacheFile, $Time, '.png')) die (e_image_output ($CacheFile));
	}
function e_image_data_save ($CacheFile, $Image) {
	imagetruecolortopalette ($Image, 0, 256);
	$FileName = ESTATS_PATH.$GLOBALS['DataDir'].$CacheFile.'_'.$GLOBALS['DBID'].'.png';
	if (is_file ($FileName)) unlink ($FileName);
	touch ($FileName);
	chmod ($FileName, 0666);
	if (!imagepng ($Image, $FileName)) {
		header ('Content-type: image/png');
		imagepng ($Image);
		}
	imagedestroy ($Image);
	e_image_output ($CacheFile);
	}
function e_image_output ($CacheFile) {
	header ('Content-type: image/png');
	die (file_get_contents (ESTATS_PATH.$GLOBALS['DataDir'].$CacheFile.'_'.$GLOBALS['DBID'].'.png'));
	}
function e_chart_pie ($ID, $Data, $CacheFile, $Icons = 0) {
	arsort ($Data['data']);
	$Others = $Num = $i = 0;
	$Percents = $Slices = array ();
	$Amount = count ($Data['data']);
	foreach ($Data['data'] as $Key => $Value) {
		if ($ID == 'websearchers' || $ID == 'referrers') {
			if ($Key != '?') {
				$Key = substr ($Key, 7);
				$Data['data'][$Key] = $Value;
				}
			}
		if ($ID == 'sites') $Key = ($Value[1]?$Value[1]:$Key);
		if (is_array ($Value)) $Data['data'][$Key] = $Value = $Value[0];
		$Percent = (($Value / $Data['sum']) * 100);
		if (++$i <= 20 && ($Percent >= 5 || (!$Others && $i == $Amount))) {
			$Percents[$Key] = $Percent;
			$Num += $Value;
			}
		else ++$Others;
		}
	if ($Data['sum'] - $Num > 0) {
		$Data['data']['others'] = ($Data['sum'] - $Num);
		$Percents['others'] = ((($Data['sum'] - $Num) / $Data['sum']) * 100);
		}
	$Amount = count ($Percents);
	$RGB = e_colors_array ($GLOBALS['ThemeConfig']['PieChartColors']);
	$Image = imagecreatetruecolor (450, (420 + (42 * $Amount)));
	imagefill ($Image, 0, 0, (isset ($RGB[2])?imagecolorallocate ($Image, $RGB[2][0], $RGB[2][1], $RGB[2][2]):imagecolorallocate ($Image, 255, 255, 255)));
	$Start = 150;
	$End = $Color = 0;
	$X = 390;
	$RGBStep = array (
	(($RGB[1][0] - $RGB[0][0]) / $Amount),
	(($RGB[1][1] - $RGB[0][1]) / $Amount),
	(($RGB[1][2] - $RGB[0][2]) / $Amount)
	);
	foreach ($Percents as $Key => $Value) {
		$Slices[$Key] = array (
	$Start,
	($Start = ceil (($End += $Value) * 3.6) + 150),
	imagecolorallocate ($Image, $RGB[0][0], $RGB[0][1], $RGB[0][2]),
	imagecolorallocate ($Image, ($RGB[0][0] - 40), ($RGB[0][1] - 40), ($RGB[0][2] - 40))
	);
		$Rad =	deg2rad ($Slices[$Key][0] + (($Slices[$Key][1] - $Slices[$Key][0]) / 2));
		$Coords[$Key] = array (((int) (100 * cos ($Rad)) + 150), ((int) (75 * sin ($Rad)) + 105));
		$X += 42;
		$RGB[0][0] += $RGBStep[0];
		$RGB[0][1] += $RGBStep[1];
		$RGB[0][2] += $RGBStep[2];
		}
	for ($i = 170; $i > 150; $i--) {
		foreach ($Slices as $Key => $Value) {
			if ($Percents[$Key] < 1) continue;
			$Others = ($Key === 'others' && $Percents['others'] > 5 && count ($Percents) > 1);
			imagefilledarc ($Image, (200 + ($Others?(($Coords[$Key][0] - 150) / 3):0)), ($i + ($Others?(($Coords[$Key][1] - 105) / 3):0)), 397, 297, $Value[0], $Value[1], $Value[3], IMG_ARC_PIE);
			}
		}
	foreach ($Slices as $Key => $Value) {
		if ($Percents[$Key] < 1) continue;
		$Others = ($Key == 'others' && $Percents['others'] > 5 && count ($Percents) > 1);
		imagefilledarc ($Image, (200 + ($Others?(($Coords[$Key][0] - 150) / 3):0)), (150 + ($Others?(($Coords[$Key][1] - 105) / 3):0)), 397, 297, $Value[0], $Value[1], $Value[2], IMG_ARC_PIE);
		}
	$FinalImage = imagecreatetruecolor (300, (240 + ($Amount * 20)));
	imagefill ($FinalImage, 0, 0, (isset ($RGB[2])?imagecolorallocate ($Image, $RGB[2][0], $RGB[2][1], $RGB[2][2]):imagecolorallocate ($Image, 255, 255, 255)));
	imagecopyresampled ($FinalImage, $Image, 50, 30, 0, 0, 225, (240 + ($Amount * 21)), 450, (480 + ($Amount * 42)));
	imagedestroy ($Image);
	if (function_exists ('imagefilter')) imagefilter ($FinalImage, IMG_FILTER_SMOOTH, 5);
	e_image_text ($FinalImage, 5, 215, 11, e_i18n ('Legend').':', $RGB[3]);
	$Level = 240;
	foreach ($Percents as $Key => $Value) {
		$Num = (($Key == 'others' && isset ($Percents['others']) && $Percents['others'] > 5)?4:3);
		$Coords[$Key][0] -= (($Coords[$Key][0] - 150) / $Num);
		$Coords[$Key][1] -= (($Coords[$Key][1] - 105) / $Num);
		if ($Key === 'others') $Text = e_i18n ('Others');
		else $Text = e_item_title ($ID, $Key);
		imagefilledrectangle ($FinalImage, 5, ($Level - 16), 25, ($Level + 4), $Slices[$Key][2]);
		if ($Icons && $Key != 'others') {
			$ImageFile = e_icon_path ($ID, $Key);
			if (is_file (ESTATS_PATH.$ImageFile)) {
			 $TmpImage = imagecreatefrompng (ESTATS_PATH.$ImageFile);
			 imagecolortransparent ($TmpImage, imagecolorat ($TmpImage, 0, 0));
			 imagecopymerge ($FinalImage, $TmpImage, 7, ($Level - 13), 0, 0, 16, 16, 100);
			 if ($Percents[$Key] != 100) imagecopymerge ($FinalImage, $TmpImage, ($Coords[$Key][0] - 6), ($Coords[$Key][1] + 2), 0, 0, 16, 16, 100);
			 imagedestroy ($TmpImage);
			 }
			}
		if ($Percents[$Key] != 100) {
			$String = round ($Percents[$Key], 2).'%';
			if ($Percents[$Key] >= 1) e_image_text ($FinalImage, ($Coords[$Key][0] - 10), $Coords[$Key][1], 6, ((strlen ($String) < 5)?str_repeat (' ', ((7 - strlen ($String)) / 2)):'').$String, $RGB[3]);
			}
		e_image_text ($FinalImage, 30, ($Level - 2), 8, e_string_cut ($Text, 20).' - '.e_number ($Data['data'][$Key], 0).' ('.(($Percents[$Key] == 100)?'100%':$String).')', $RGB[3]);
		$Level += 21;
		}
	imagecolortransparent ($FinalImage, (isset ($RGB[2])?imagecolorallocate ($FinalImage, $RGB[2][0], $RGB[2][1], $RGB[2][2]):imagecolorallocate ($FinalImage, 255, 255, 255)));
	if (function_exists ('imagefilter')) imagefilter ($FinalImage, IMG_FILTER_SMOOTH, 30);
	e_image_data_save ($CacheFile, $FinalImage);
	}
function e_chart_time ($ID, $Data, $Summary, $CacheFile, $Type, $Join) {
	$Image = imagecreatetruecolor (1500, 340);
	imagefill ($Image, 0, 0, imagecolorallocate ($Image, 255, 255, 255));
	imagecolortransparent ($Image, imagecolorallocate ($Image, 255, 255, 255));
	$RGB = e_colors_array ($GLOBALS['ThemeConfig']['TimeChartColors']);
	$ThemeArray = array ('views', 'unique', 'returns');
	for ($i = 0; $i < 3; ++$i) {
		$Colors[$ThemeArray[$i]] = imagecolorallocatealpha ($Image, $RGB[$i][0], $RGB[$i][1], $RGB[$i][2], 50);
		$DarkColors[$ThemeArray[$i]] = imagecolorallocatealpha ($Image, ($RGB[$i][0] - 30), ($RGB[$i][1] - 30), ($RGB[$i][2] - 30), 50);
		}
	if (!$Summary['maxall']) {
		$FinalImage = imagecreatetruecolor (700, 170);
		imagecopyresampled ($FinalImage, $Image, 0, 0, 0, 0, 750, 170, 1400, 340);
		imagedestroy ($Image);
		e_image_data_save ($CacheFile, $FinalImage);
		}
	$TypesAmount = count ($Summary['types']);
	$Width = (2 * round (700 / ($Summary['amount'])));
	if ($Summary['chart'] == 'weekdays' && 	$Summary['weekstart']) {
		$WeekDayTransition = range (0, 6);
		$WeekDayTransition = array_merge (array_slice ($WeekDayTransition, 	$Summary['weekstart']), array_slice ($WeekDayTransition, 0, 	$Summary['weekstart']));
		}
	$ChartData = array ();
	for ($i = 0; $i < $Summary['amount']; ++$i) {
		if ($Summary['chart'] == 'year') $Summary['step'] = (date ('t', $Summary['timestamp']) * 86400);
		else if ($Summary['chart'] == 'years') $Summary['step'] = ((date ('L', $Summary['timestamp']) + 365) * 86400);
		if ($Summary['currenttime'] && $Summary['step']) $TimeStamp += $Summary['step'];
		if ($Summary['chart'] == 'hours') $UnitID = $i;
		else if ($Summary['chart'] == 'weekdays') {
			if ($Summary['weekstart']) $UnitID = $WeekDayTransition[$i];
			else $UnitID = $i;
			}
		else $UnitID = date ($Summary['datestring'], $Summary['timestamp']);
		for ($j = 0; $j < $TypesAmount; ++$j) $ChartData[$Summary['types'][$j]][$i] = (isset ($Data[$UnitID][$Summary['types'][$j]])?$Data[$UnitID][$Summary['types'][$j]]:0);
		if (!$Summary['currenttime'] && $Summary['step']) $Summary['timestamp'] += $Summary['step'];
		}
	unset ($Data);
	if ($Type == 'bars') {
		$BarWidth = (($Width / $TypesAmount) * 0.8);
		$BarMargin = (($Width / $TypesAmount) * 0.3);
		}
	else {
		for ($i = 0; $i < $TypesAmount; ++$i) {
			$ChartData[$Summary['types'][$i]][-1] = $ChartData[$Summary['types'][$i]][$Join?($Summary['amount'] - 1):0];
			$ChartData[$Summary['types'][$i]][$Summary['amount']] = $ChartData[$Summary['types'][$i]][$Join?0:($Summary['amount'] - 1)];
			}
		}
	for ($i = 0; $i < $TypesAmount; ++$i) {
		$X = (($Type == 'bars')?0:-($Width / 2));
		for ($j = (($Type == 'bars')?0:-1); $j < $Summary['amount']; ++$j) {
			$Y = (336 - (($ChartData[$Summary['types'][$i]][$j] / $Summary['maxall']) * 300));
			switch ($Type) {
				case 'bars':
				imagefilledrectangle ($Image, ($X + $BarMargin + ($BarWidth * $i) - 2), ($Y - 2), ($X + ($BarWidth * ($i + 1)) + 2), 340, $DarkColors[$Summary['types'][$i]]);
				imagefilledrectangle ($Image, ($X + $BarMargin + ($BarWidth * $i)), $Y, ($X + ($BarWidth * ($i + 1))), 340, $Colors[$Summary['types'][$i]]);
				break;
				case 'lines':
				if ($j != ($Summary['amount'] - 1)) {
					imageline ($Image, $X, $Y, ($X + $Width), (336 - (($ChartData[$Summary['types'][$i]][$j+ 1] / $Summary['maxall']) * 300)), $Colors[$Summary['types'][$i]]);
					imageline ($Image, $X, ($Y + 1), ($X + $Width), (337 - (($ChartData[$Summary['types'][$i]][$j+ 1] / $Summary['maxall']) * 300)), $Colors[$Summary['types'][$i]]);
					}
				break;
				case 'areas':
				$Points = array (
	$X, 340,
	$X, $Y,
	($X + $Width), (336 - (($ChartData[$Summary['types'][$i]][$j + 1] / $Summary['maxall']) * 300)),
	($X + $Width), 340
	);
				imagefilledpolygon ($Image, $Points, 4, $Colors[$Summary['types'][$i]]);
				break;
				}
			if ($Type != 'bars' && $ChartData[$Summary['types'][$i]][$j]) imagefilledellipse ($Image, $X, $Y, 8, 8, $DarkColors[$Summary['types'][$i]]);
			$X += $Width;
			}
		}
	$TmpImage = imagecreatetruecolor (700, 170);
	imagecopyresampled ($TmpImage, $Image, 0, 0, 0, 0, 700, 170, 1400, 340);
	imagedestroy ($Image);
	$FinalImage = imagecreatetruecolor (700, 170);
	$Background = imagecreatefrompng (ESTATS_PATH.'share/themes/'.$_SESSION['eStats']['theme'].'/images/chart.png');
	imagesettile ($FinalImage, $Background);
	imagefill ($FinalImage, 0, 0, IMG_COLOR_TILED);
	imagedestroy ($Background);
	imagecolortransparent ($TmpImage, imagecolorallocate ($TmpImage, 255, 255, 255));
	imagecopymerge ($FinalImage, $TmpImage, 0, 0, 0, 0, 700, 170, 75);
	imagedestroy ($TmpImage);
	if (function_exists ('imagefilter')) {
		imagefilter ($FinalImage, IMG_FILTER_SMOOTH, 0);
		imagefilter ($FinalImage, IMG_FILTER_SMOOTH, 10);
		}
	e_image_data_save ($CacheFile, $FinalImage);
	}
function e_map ($MapType, $Data, $CacheFile) {
	e_geo_init ();
	$Continents = isset ($Data['continents']);
	$Map = parse_ini_file (ESTATS_PATH.'share/maps/'.$MapType[0].'/map.ini', 1);
	if ($MapType[0] == 'world' && !$Continents && is_file (ESTATS_PATH.'share/maps/world/countries/flags.ini')) $Flags = parse_ini_file (ESTATS_PATH.'share/maps/world/countries/flags.ini');
	$Image = imagecreatefrompng (ESTATS_PATH.'share/maps/'.$MapType[0].'/map.png');
	if ($Data['max']) {
		$Border = imagecolorat ($Image, 0, 0);
		foreach ($Map['Data'] as $Key => $Value) {
			$Key = trim ($Key, '\\');
			if ($MapType[0] != 'world') $Key = $MapType[0].'-'.$Key;
			if ($Continents) $Key = $GLOBALS['CountryToContinent'][$Key];
			if (isset ($Data[$Continents?'continents':'data'][$Key])) {
				$Num = (220 - (75 * ((floor (($Data[$Continents?'continents':'data'][$Key] / $Data['sum']) * 100) * $Data['max']) / 16500)));
				$Value = explode (',', $Value);
				for ($i = 0, $c = count ($Value); $i < $c; $i += 2) imagefilltoborder ($Image, $Value[$i], $Value[$i + 1], $Border, imagecolorallocate ($Image, $Num, $Num, $Num));
				}
			if (isset ($Flags[$Key])) {
				$ImageFile = e_icon_path ('countries', $Key);
				if (is_file (ESTATS_PATH.$ImageFile)) {
					$Coords = explode (',', $Flags[$Key]);
					$TmpImage = imagecreatefrompng (ESTATS_PATH.$ImageFile);
					imagecolortransparent ($TmpImage, imagecolorat ($TmpImage, 0, 0));
					imagecopymerge ($Image, $TmpImage, $Coords[0], $Coords[1], 0, 0, 16, 16, 100);
					imagedestroy ($TmpImage);
					}
				}
			}
		$LegendCoords = explode ('|', $Map['Options']['LegendLocation']);
		$Border = imagecolorallocate ($Image, 80, 80, 80);
		imagefilledrectangle ($Image, $LegendCoords[0], $LegendCoords[1], ($LegendCoords[0] + 7), ($LegendCoords[1] + 52), $Border);
		for ($i = 50; $i <= 100; ++$i) {
			$Num = (floor ($i * 1.5) + 70);
			imageline ($Image, ($LegendCoords[0] + 1), ($LegendCoords[1] - 49 + $i), ($LegendCoords[0] + 6), ($LegendCoords[1] - 49 + $i), imagecolorallocate ($Image, $Num, $Num, $Num));
			}
		e_image_text ($Image, ($LegendCoords[0] + 10), ($LegendCoords[1] + 8), 7, $Data['max'].' ('.round ((($Data['max'] / $Data['sum']) * 100), 2).'%)');
		e_image_text ($Image, ($LegendCoords[0] + 10), ($LegendCoords[1] + 52), 7, '0');
		}
	e_image_data_save ($CacheFile, $Image);
	}
?>