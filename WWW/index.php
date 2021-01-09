<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">

<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="content-Language" content="pl" />
<meta name="Keywords" content="" />
<meta name="Description" content="" />
<meta name="Copyright" content="VISMEDIA => www.vismedia.pl" />
<meta name="Author" content="VISMEDIA => www.vismedia.pl" />
<meta name="Robots" content="index,follow" />
<link rel="stylesheet" type="text/css" href="style.css" />
<script type="text/javascript" src="lytebox/lytebox.js"></script>
<link rel="stylesheet" href="lytebox/lytebox.css" media="screen" />
<title>KRÓLOWA KRAKOWA 2009 - I Ty możesz zotać Królową!</title>
</head>

<body>

<map id="sponsorzy" name="sponsorzy">
  <area href="http://www.voxfm.pl" shape="rect" coords="0,0,185,46" target="_blank" title="Radio VOX FM" />
  <area href="http://www.taawa.pl" shape="rect" coords="253,0,348,46" target="_blank" title="Taawa Music Club" />
  <area href="http://www.mediafm.net" shape="rect" coords="419,0,614,46" target="_blank" title="MediaFM.net" />
  
  <area href="http://www.vismedia.pl" shape="rect" coords="0,59,228,106" target="_blank" title="VISMEDIA" />
  <area href="http://www.hotel-ester.krakow.pl" shape="rect" coords="250,59,394,106" target="_blank" title="Hotel Ester" />
  <area href="http://www.mrgroup.pl" shape="rect" coords="405,47,518,106" target="_blank" title="MR" />
  <area href="http://www.euro-hostel.pl" shape="rect" coords="539,54,601,161" target="_blank" title="Euro Hostel" />
  
  <area href="http://www.bianconero.pl" shape="rect" coords="12,123,184,174" target="_blank" title="Bianco Nero" />
  <area href="http://www.hotel.aspel.com.pl" shape="rect" coords="229,128,363,168" target="_blank" title="Hotel Aspel" />
  
  <area href="http://www.slodkieupominki.pl" shape="rect" coords="10,180,165,220" target="_blank" title="Słodkie upominki" />
  <area href="http://www.rishabhmarbles.com" shape="rect" coords="379,180,601,219" target="_blank" title="Shree Rishabh Marble Pvt. Ltd." />
</map>

<table style="width: 100%; height: 1290px;" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td rowspan="2" style="background-image: url('images/kk_01.jpg'); background-repeat: no-repeat; background-position: top right;">&nbsp;</td>
		<td style="background-image: url('images/kk_02.jpg'); background-repeat: no-repeat; width: 1022px; height: 585px;"></td>
		<td rowspan="2" style="background-image: url('images/kk_03.jpg'); background-repeat: no-repeat; background-position: top left;">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td style="background-image: url('images/kk_04.jpg'); background-repeat: no-repeat; width: 1022px; height: 715px;">
    
      <div id="content" style="position: absolute; margin-left: 60px; margin-top: 25px; width: 935px; height: 630px; overflow: hidden;">

        <table style="width: 100%; height: 630px;" border="0" cellpadding="0" cellspacing="0">
          <tr valign="top" style="height: 280px;">
            <td style="width: 70%;">
              <img src="images/witamy.gif" alt="" />
              <br /><br />
              <div style="text-align: justify; padding-right: 25px;">
                W piątek, 29 maja 2009 roku, w Krakowie, w&nbsp;<b>Taawa Music Club</b>, odbyła się impreza <b>"KRÓLOWA KRAKOWA 2009"</b>. Po wcześniejszym castingu do finału wytypowano siedem kobiet. Zaszczytny tytuł oraz nagrody otrzymała <b>Joanna Miazga</b>. Nowa Królowa Krakowa mieszka w Bochnii i studiuje socjologię na Uniwersytecie Jagiellońskim w Krakowie.
                <br /><br />
                Imprezę poprowadzili: <b>Karol Śmiałek</b> - aktor i piosenkarz oraz <b>Anna Kukawska</b> - Miss Małopolski 2002. Wieczór uświetnili goście specjalni: <b>Stefano Terrazino</b> - dwukrotny zwycięzca polskiej edycji programu "Taniec z&nbsp;gwiazdami" oraz <b>Franco Narducci</b> - wiceprzewodniczący Komisji Spraw Zagranicznych Włoskiego Parlamentu.
              </div>
              <br />
              <div id="gallery" align="center" style="font-size: 13px;">
              
<?

$count = 48;
$images_per_page = 4;

$last = ceil($count/$images_per_page);

echo '<script type="text/javascript">
function GalleryPage(p) {

  for(i=1;i<='.$last.';i++) {
    if(p == i) document.getElementById(\'gallery-\' + i).style.display = \'block\';
    else  document.getElementById(\'gallery-\' + i).style.display = \'none\';
  }

}
</script>

';

for($i=1;$i<=$count;$i++) {

$page = ceil($i/$images_per_page);

if($page!=1) $prev = '<a href="javascript:GalleryPage('.($page-1).')"><img src="images/left.gif" alt="" border="0" /></a>';
else $prev='';

if($page!=$last) $next = '<a href="javascript:GalleryPage('.($page+1).')"><img src="images/right.gif" alt="" border="0" /></a>';
else $next='';

if($i%$images_per_page == 1) {

if($page==1) $display='block';
else $display='none';

echo '<div id="gallery-'.$page.'" style="display: '.$display.'">
  <table>
    <tr>
';

echo '      <td style="width: 20px;"><b>'.$prev.'</b></td>
';

}

echo '      <td style="width: 130px;" align="center"><a href="gallery/'.roundZeros($i,3).'.jpg" rel="lytebox[]"><img src="gallery/tn_'.roundZeros($i,3).'.jpg" alt="" border="0" /></a></td>
';

if($i%$images_per_page == 0) {

echo '      <td style="width: 20px;"><b>'.$next.'</b></td>
';

echo '    </tr>
  </table>
</div>

';

}

}

?>
              
              </div><br />
            </td>
            <td style="width: 30%; padding-right: 25px;" rowspan="2">
              <img src="images/organizator.gif" alt="" />
              <br /><br /><br /><br />
              <a href="http://www.dragon-tour.eu" target="_blank"><img src="images/logo_dragon.gif" alt="" border="0" /></a>
              <br /><br /><br />
              <img src="images/kontakt.gif" alt="" />
              <br /><br /><br />
              <b>Dragon Tour sp. z o.o.</b><br />
              ul. Librowszczyzna 3<br />
              31-030 Kraków<br /><br />
              tel. (012) 427-13-02, 516-734-266<br />
              e-mail: <a href="javascript:location.href='mailto:'+String.fromCharCode(107,114,111,108,111,119,97,64,100,114,97,103,111,110,45,116,111,117,114,46,101,117)+'?'">krolowa@dragon-tour.eu</a>
              <br /><br /><br />
              <img src="images/napisali.gif" alt="" />
              <br />
              <ul>
                <li><a href="http://mediafm.net/kultura/20176,Znamy-juz-Krolowa-Krakowa-2009.html" target="_blank">Znamy już Królową Krakowa 2009</a><br /><span class="maly">mediafm.net, 31.05.2009</span></li>
              </ul>
            </td>
          </tr>
          <tr valign="top" style="height: 350px;">
            <td>
              <img src="images/sponsorzy.gif" alt="" />
              <br /><br /><br />
              <img src="images/logos.gif" alt="" usemap="#sponsorzy" border="0" />
            </td>
          </tr>
        </table>

      </div>

      <div id="footer" style="position: absolute; margin-left: 50px; margin-top: 623px; width: 950px; height: 20px; text-align: right;" class="footer">
        Projekt i wykonanie: <a href="http://www.vismedia.pl" class="footer">VisMedia</a>
      </div>
    
    </td>
	</tr>
</table>

<?php
  define ('ESTATS_COUNT', 1);
  @include ('stats/stats.php');
?>

<script type="text/javascript">
var eCount = 1;
var ePath = '/stats/';
var eTitle = '';
var eAddress = '';
var eAntipixel = '';
</script>
<script type="text/javascript" src="/stats/stats.js"></script>

</body>

</html>

<?

function roundZeros($wartosc, $dokladnosc=1) {

$dlugosc = strlen($wartosc);
$prefix = '';

while($dlugosc<$dokladnosc) {
  $prefix .= '0';
  $dlugosc++;
}

return $prefix.$wartosc;

}

?>