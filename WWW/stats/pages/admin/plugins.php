<?php
if (isset ($Vars[3])) {
   if (is_file ('pages/plugins/'.$Vars[3].'/plugin.php')) {
      $Plugin = parse_ini_file ('pages/plugins/'.$Vars[3].'/plugin.ini', 1);
      if (!$Plugin['Options']['Enabled']) {
         $Information[] = array (e_i18n ('This plugin is disabled!'), 0);
         unset ($Vars[3]);
         }
      }
   else unset ($Vars[3]);
   }
if (!isset ($Vars[3])) {
   $Theme['page'] = e_announce (e_i18n ('For (de)activate of plugin You must edit file <em>plugin.ini</em> from its directory (example: <em>pages/plugins/editor/plugin.ini</em>) and set value of variable <em>enabled</em> (section <em>Options</em>) to <em>1</em> or <em>0</em>.'), 'information');
   $Plugins = glob ('pages/plugins/*', GLOB_ONLYDIR);
   for ($i = 0, $c = count ($Plugins); $i < $c; ++$i) {
       $PluginID = basename ($Plugins[$i]);
       $Plugin = parse_ini_file ('pages/plugins/'.$PluginID.'/plugin.ini', 1);
       $Theme['page'].= '<p>
<strong><em>'.($i + 1).'</em></strong>.
<a href="{path}admin/plugins/'.$PluginID.'{suffix}" tabindex="'.(++$TabIndex).'">
<strong>'.ucfirst (str_replace ('_', ' ', $PluginID)).'</strong>
</a>
<a href="'.htmlspecialchars ($Plugin['Information']['URL']).'" tabindex="'.(++$TabIndex).'" title="'.e_i18n ('Author').': '.$Plugin['Information']['Author'].'">
v'.$Plugin['Information']['Version'].' - '.$Plugin['Information']['Status'].'
</a>
('.date ('d.m.Y H:i:s', $Plugin['Information']['Time']).')
<strong class="'.($Plugin['Options']['Enabled']?'green':'red').'">['.e_i18n ($Plugin['Options']['Enabled']?'Enabled':'Disabled').']</strong><br />
    '.$Plugin['About'][isset ($Plugin['About'][$Vars[0]])?$Vars[0]:'en'].'
</p>
';
       }
   if (!$c) $Theme['page'].= '<p>
'.e_i18n ('None').'.
</p>
';
   }
else {
     e_locale_load ($Vars[0], './pages/plugins/'.$Vars[3].'/locale/');
     if (isset ($Plugin['Information']['Name'])) $Theme['title'].= ': '.e_i18n ($Plugin['Information']['Name']);
     include ('./pages/plugins/'.$Vars[3].'/plugin.php');
     }
?>