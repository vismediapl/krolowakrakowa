<?php
if (!defined ('eStats')) die ();
if (is_file ('lib/gd.php')) include ('./lib/gd.php');
if (!include ('./lib/group.php')) e_error_message ('lib/group.php', __FILE__, __LINE__);
$Data = e_group_init ($Blocks['technical'], 'technical', $Date);
if (isset ($Vars[2]) && isset ($Blocks['technical'][$Vars[2]])) {
   $Theme['page'] = $Theme['group-page'];
   $Theme['group'] = e_group ($Vars[2], $Blocks['technical'][$Vars[2]], $Data[$Vars[2]], $Date, 1);
   $Theme['title'].= ' - '.e_i18n ($Titles[$Vars[2]]);
   }
else {
     foreach ($Blocks['technical'] as $Key => $Value) $Theme[$Key] = e_group ($Key, $Value, $Data[$Key], $Date, 0);
     }
?>