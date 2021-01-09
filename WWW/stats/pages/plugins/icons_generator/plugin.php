<?php
if (!defined ('eStats')) die ();
function e_create_icon ($FileName) {
         $Ext = explode ('.', $FileName);
         $Ext = strtolower (end ($Ext));
         switch ($Ext) {
                case 'png':
                $Image = imagecreatefrompng ($FileName);
                break;
                case 'jpg':
                case 'jpeg':
                $Image = imagecreatefromjpeg ($FileName);
                break;
                case 'gif':
                $Image = imagecreatefromgif ($FileName);
                default:
                return ('error');
                }
         $Size = getimagesize ($FileName);
         if ($Size[0] > $Size[1]) $NewSize = array (16, (($Size[1] * 16) / $Size[0]));
         else $NewSize = array ((($Size[0] * 16) / $Size[1]), 16);
         $TmpImage = imagecreatetruecolor ($NewSize[0], $NewSize[1]);
         imagecopyresampled ($TmpImage, $Image, 0, 0, 0, 0, $NewSize[0], $NewSize[1], $Size[0], $Size[1]);
         imagecolortransparent ($TmpImage, imagecolorallocate ($TmpImage, 0, 0, 0));
         imagedestroy ($Image);
         if ($NewSize[0] != 16 || $NewSize[1] != 16) {
            if ($NewSize[0] > $NewSize[1]) $Dist = array (0, (int) ((16 - $NewSize[1]) / 2));
            else $Dist = array ((int) ((16 - $NewSize[0]) / 2), 0);
            $Image = imagecreatetruecolor (16, 16);
            imagefill ($Image, 0, 0, 5);
            imagecolortransparent ($Image, 5);
            imagecopymerge ($Image, $TmpImage, $Dist[0], $Dist[1], 0, 0, $NewSize[0], $NewSize[1], 100);
            $TmpImage = $Image;
            }
         imagetruecolortopalette ($TmpImage, 0, 256);
         unlink ($FileName);
         $FileName = $GLOBALS['DataDir'].'temp/'.basename ($FileName).'.png';
         touch ($FileName);
         chmod ($FileName, 0666);
         imagepng ($TmpImage, $FileName);
         imagedestroy ($TmpImage);
         return ($FileName);
         }
if (!is_dir ($DataDir.'tmp/')) {
   mkdir ($DataDir.'tmp/', 0777);
   chmod ($DataDir.'tmp/', 0777);
   if (!is_writable ($DataDir.'tmp/')) $Information[] = array (e_i18n ('Error occured during temporary directory creation (<em>data/tmp/</em>)!'), 'error');
   }
if (!function_exists ('gd_info')) $Information[] = array (e_i18n ('<em>GD</em> extension is not available!'), 'error');
$Image = '';
if (isset ($_FILES['Image']) && is_uploaded_file ($_FILES['Image']['tmp_name'])) {
   $Name = explode ('.', $_FILES['Image']['name']);
   if (in_array (end ($Name), array ('jpg', 'jpeg', 'png', 'gif'))) {
      move_uploaded_file ($_FILES['Image']['tmp_name'], $DataDir.'tmp/'.$_FILES['Image']['name']);
      if (($FileName = e_create_icon ($DataDir.'tmp/'.$_FILES['Image']['name'])) != 'error') $Image = $FileName;
      }
   else $Information[] = array (e_i18n ('Wrong file type!'), 'error');
   }
if (is_dir ($DataDir.'tmp/')) {
   $Theme['page'] = '<h3>'.e_i18n ('Generate new file').'</h3>
<form action="'.$_SERVER['PHP_SELF'].'" method="post" enctype="multipart/form-data">
<p>
<span>
<input type="file" name="Image" id="Image" tabindex="'.(++$TabIndex).'" />
<input type="submit" value="'.e_i18n ('Send').'" tabindex="'.(++$TabIndex).'" class="button" />
</span>
<label for="Image">'.e_i18n ('Select file for conversion (<em>JPEG, PNG</em> or <em>GIF</em>)').'</label>:
</p>
</form>
'.(($Image && is_file ($FileName))?'<p>
<strong>'.e_i18n ('Generated file').':</strong><br />
<img src="'.$Theme['datapath'].$FileName.'" alt="" />
</p>
':'').'<h3>'.e_i18n ('Manage existing').'</h3>
';
   if (isset ($_GET['delete']) && is_file ($DataDir.'tmp/'.$_GET['delete'].'.png')) {
      if ( unlink ($DataDir.'tmp/'.$_GET['delete'].'.png')) $Information[] = array (e_i18n ('Image deleted successful.'), 'success');
      else $Information[] = array (e_i18n ('An error occured during image deleteing!'), 'error');
      }
   clearstatcache ();
   $Files = glob ($DataDir.'tmp/*.png', GLOB_BRACE);
   for ($i = 0, $c = count ($Files); $i < $c; ++$i) $Theme['page'].= '<p>
<a href="'.$Theme['datapath'].$Files[$i].'" tabindex="'.(++$TabIndex).'">'.basename ($Files[$i], '.png').'</a>
<span>
<a href="{selfpath}{separator}delete='.urlencode (basename ($Files[$i], '.png')).'" tabindex="'.(++$TabIndex).'">'.e_i18n ('Delete').'</a>
</span>
</p>
';
   if (!$c) $Theme['page'].= '<p>
'.e_i18n ('None').'
</p>';
   }
else $Theme['page'] = '';
?>