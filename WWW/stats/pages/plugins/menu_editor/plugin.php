<?php
if (!defined ('eStats')) die ();
if (!is_writeable ('conf/menu.php')) $Information[] = array (e_i18n ('File <em>conf/menu.php</em> is not writeable!'), 'error');
$IMenu = 1;
function e_me_menu_option ($ID, $Array, $SubMenu = 0) {
         global $Titles;
         return (e_config_row (e_i18n ('Position'), 'menu['.$ID.'][position]', (($SubMenu == 2)?'':($GLOBALS[$SubMenu?'ISMenu':'IMenu']++)), 2).e_config_row (e_i18n ('ID'), 'menu['.$ID.'][id]', $ID, 0).e_config_row (e_i18n ('Text'), 'menu['.$ID.'][text]', (isset ($Array['text'])?$Array['text']:''), 0).e_config_row (e_i18n ('Link'), 'menu['.$ID.'][link]', (isset ($Array['link'])?$Array['link']:''), 0));
         }
if (isset ($_POST['save'])) {
   $MenuFile = '<?php
$Menu = array (
';
   $TMenus = $TSubMenus = array ();
   foreach ($_POST['menu'] as $Key => $Value) {
           if (!$Value['id']) continue;
           if (strstr ($Value['id'], '|')) {
              $ID = explode ('|', $Value['id']);
              if ($Value['position']) $TSubMenus[$ID[0]][$Value['position']] = $Value;
              else $TSubMenus[$ID[0]][] = $Value;
              }
           else {
                if ($Value['position']) $TMenus[$Value['position']] = $Value;
                else $TMenus[] = $Value;
                }
           }
   ksort ($TMenus);
   $Menu[$_POST['menu_level']] = array ();
   foreach ($TMenus as $Key => $Value) {
           $Menu[$_POST['menu_level']][$Value['id']] = array ();
           if ($Value['text']) $Menu[$_POST['menu_level']][$Value['id']]['text'] = $Value['text'];
           if ($Value['link']) $Menu[$_POST['menu_level']][$Value['id']]['link'] = $Value['link'];
           if (isset ($TSubMenus[$Value['id']])) {
              ksort ($TSubMenus[$Value['id']]);
              foreach ($TSubMenus[$Value['id']] as $Key2 => $Value2) {
                      $ID = explode ('|', $Value2['id']);
                      $Menu[$_POST['menu_level']][$Value['id']]['submenu'][$ID[1]] = array ();
                      if ($Value2['text']) $Menu[$_POST['menu_level']][$Value['id']]['submenu'][$ID[1]]['text'] = $Value2['text'];
                      if ($Value2['link']) $Menu[$_POST['menu_level']][$Value['id']]['submenu'][$ID[1]]['link'] = $Value2['link'];
                      }
              }
           }
   for ($MenuLevel = 0; $MenuLevel < 3; $MenuLevel++) {
       $MenuFile.= '	array (
';
       foreach ($Menu[$MenuLevel] as $Key => $Value) {
               $MenuFile.= '		\''.$Key.'\' => array (
';
               foreach ($Menu[$MenuLevel][$Key] as $Key2 => $Value2) {
                       if ($Key2 != 'submenu') $MenuFile.= '			\''.$Key2.'\' => \''.$Value2.'\',
';
                       else {
                            $MenuFile.= '			\'submenu\' => array (
';
                            foreach ($Value2 as $Key3 => $Value3) {
                                    $MenuFile.= '				\''.$Key3.'\' => array (
';
                                     foreach ($Value3 as $Key4 => $Value4) $MenuFile.= '					\''.$Key4.'\' => \''.$Value4.'\',
';
                                    $MenuFile.= '					),
';
                                    }
                            $MenuFile.= '				),
';

                            }

                       }

       $MenuFile.= '			),
';

               }
       $MenuFile.= '		),
';
       }
       $MenuFile.= '	);
?>';
   if (file_put_contents ('conf/menu.php', $MenuFile)) $Information[] = array (e_i18n ('Changes saved successfull.'), 'success');
   else $Information[] = array (e_i18n ('An error occured during file saving!'), 'error');
   include ('./conf/menu.php');
   }
if (!isset ($_POST['menu_level'])) $_POST['menu_level'] = 0;
$MLSelect = '';
$Array = array ('User', 'Logged in user', 'Administrator');
for ($i = 0; $i < 3; ++$i) $MLSelect.= '<option value="'.$i.'"'.(($_POST['menu_level'] == $i)?' selected="selected"':'').'>'.e_i18n ($Array[$i]).'</option>
';
$Theme['page'] = '<form action="{selfpath}" method="post" id="menu_editor">
<p>
<span>
<select name="menu_level" id="menu_level" tabindex="'.(++$TabIndex).'">
'.$MLSelect.'</select>
<input type="submit" value="'.e_i18n ('Edit').'" tabindex="'.(++$TabIndex).'" class="button" />
</span>
<label for="menu_level">'.e_i18n ('Menu level for edit').'</label>:
</p>
'.e_announce (e_i18n ('In case of empty field value will be generated using ID.'), 'information').'<div style="margin:5px;">
';
foreach ($Menu[$_POST['menu_level']] as $Key => $Value) {
        $Theme['page'].= '<fieldset class="expanded" id="m_'.$Key.'">
<legend onclick="changeClassName (\'m_'.$Key.'\')">'.$Key.' - <em>'.e_i18n (isset ($Value['text'])?$Value['text']:($Titles[$Key])).'</em> <a href="'.(isset ($Value['link'])?$Value['link']:$Theme['path'].$Key).'" tabindex="'.(++$TabIndex).'"><strong>#</strong></a></legend>
<div>
'.e_me_menu_option ($Key, $Value).'<input type="button" value="'.e_i18n ('Delete entry').'" onclick="removeOption (\'m_'.$Key.'\')" tabindex="'.(++$TabIndex).'" class="button" />
<input type="button" value="'.((isset ($Value['submenu']) && count ($Value['submenu']))?e_i18n ('Delete submenu'):e_i18n ('Add submenu')).'" onclick="ARSubmenu (\'sm_'.$Key.'\')" id="sm_'.$Key.'_switch" tabindex="'.(++$TabIndex).'" class="button" /><br />
<div id="sm_'.$Key.'"'.((isset ($Value['submenu']) && count ($Value['submenu']))?'':' style="display:none;"').'>
';
        if (isset ($Value['submenu']) && count ($Value['submenu'])) {
           $ISMenu = 1;
           foreach ($Value['submenu'] as $SKey => $SValue) $Theme['page'].= '<fieldset class="expanded" id="m_'.$Key.'_'.$SKey.'">
<legend onclick="changeClassName (\'m_'.$Key.'_'.$SKey.'\')">'.$SKey.' - <em>'.e_i18n (isset ($SValue['text'])?$SValue['text']:$Titles[$SKey]).'</em> <a href="'.(isset ($SValue['link'])?$SValue['link']:$Theme['path'].$Key.'/'.$SKey).'" tabindex="'.(++$TabIndex).'"><strong>#</strong></a></legend>
<div>
'.e_me_menu_option ($Key.'|'.$SKey, $SValue, 1).'<input type="button" value="'.e_i18n ('Delete entry').'" onclick="removeOption (\'m_'.$Key.'_'.$SKey.'\')" tabindex="'.(++$TabIndex).'" class="button" />
</div>
</fieldset>
';
           }
        $Theme['page'].= '<span id="me_submenu_'.$Key.'"></span>
<input type="button" value="'.e_i18n ('Add entry').'" onclick="addOption (\'me_submenu_'.$Key.'\', \''.$Key.'\')" tabindex="'.(++$TabIndex).'" class="button" />
</div>
</div>
</fieldset>
';
        }
$Theme['page'].= '<span id="me_menu_main"></span>
<p>
<input type="button" value="'.e_i18n ('Add entry').'" onclick="addOption (\'me_menu_main\', 0)" tabindex="'.(++$TabIndex).'" class="button" />
</p>
</div>
<div class="buttons">
<input type="submit" onclick="if (!confirm ('.e_i18n ('Do You really want to save?').'\')) return false" name="save" value="'.e_i18n ('Save').'" tabindex="'.(++$TabIndex).'" class="button" />
<input type="reset" value="'.e_i18n ('Reset').'" tabindex="'.(++$TabIndex).'" class="button" />
</div>
</form>
<script type="text/javascript">
// <![CDATA[
function changeClassName (id) {
         document.getElementById(id).className = (document.getElementById(id).className == \'expanded\')?\'notexpanded\':\'expanded\';
         }
function hideAll () {
         a = document.getElementById(\'menu_editor\').getElementsByTagName(\'fieldset\');
         for (i = 0; i < a.length; i++) a[i].className = \'notexpanded\';
         }
function ARSubmenu (id) {
         Expanded = !(document.getElementById(id).style.display == \'none\');
         document.getElementById(id).style.display = (Expanded?\'none\':\'block\');
         document.getElementById(id + \'_switch\').value = (Expanded?\''.e_i18n ('Add submenu').'\':\''.e_i18n ('Delete submenu').'\');
         if (Expanded) {
            textarea = document.getElementById(id).getElementsByTagName(\'textarea\');
            for (i = 0; i < textarea.length; i++) textarea[i].disabled = 1;
            fieldset = document.getElementById(id).getElementsByTagName(\'fieldset\');
            for (i = 0; i < fieldset.length; i++) fieldset[i].style.display = \'none\';
            }
         }
function removeOption (id) {
         textarea = document.getElementById(id).getElementsByTagName(\'textarea\');
         for (i = 0; i < textarea.length; i++) textarea[i].disabled = 1;
         document.getElementById(id).style.display = \'none\';
         }
function addOption (id, v) {
         menuID = prompt (\''.e_i18n ('Give new ID').':\');
         if (!menuID) return (0);
         document.getElementById(id).innerHTML += \''.str_replace ('
', '\n', '<fieldset class="expanded" id="m_\' + (v?v + \'_\':\'\') + menuID + \'">
<legend onclick="changeClassName (\\\'m_\' + (v?v + \'_\':\'\') + menuID + \'\\\')"><em>'.e_i18n ('New entry').': \' + menuID + \'</em></legend>
<div>
'.e_me_menu_option ('\' + (v?v + \'|\':\'\') + menuID + \'', array (), 2).'<input type="button" value="'.e_i18n ('Delete entry').'" onclick="removeOption (\\\'m_\' + (v?v + \'_\':\'\') + menuID + \'\\\')" class="button" />
\' + (v?\'\':\'<input type="button" value="'.e_i18n ('Add submenu').'" onclick="ARSubmenu (\\\'sm_\' + menuID + \'\\\')" id="sm_\' + menuID + \'_switch" class="button" /><br />
<div id="sm_\' + menuID + \'" style="display:none;">
<span id="me_submenu_\' + menuID + \'"></span>
<input type="button" value="'.e_i18n ('Add entry').'" onclick="addOption (\\\'me_submenu_\' + menuID + \'\\\', \\\'\' + menuID + \'\\\')" class="button" />
</div>
\') + \'
</div>
</fieldset>
').'\';
         }
window.onload = hideAll ();
// ]]>
</script>
';
?>