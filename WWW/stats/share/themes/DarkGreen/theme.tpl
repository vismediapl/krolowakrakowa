<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{language}" dir="{dir}" id="html">
<head>
{meta}<link href="{datapath}share/themes/common/common.css" rel="stylesheet" type="text/css" />
<link href="{datapath}share/themes/{theme}/theme.css" rel="stylesheet" type="text/css" title="{theme}" />
<link href="{datapath}share/icons/misc/estats.png" rel="shortcut icon" type="image/png" />
<title>eStats :: {title}</title>
{css}<!--
DatkGreen theme for eStats 4.9
Author: Neo, updated by Emdek
Licence: GPL
-->
<script type="text/javascript" src="{datapath}share/themes/{theme}/theme.js"></script>
<script type="text/javascript" src="{datapath}lib/functions.js"></script>
</head>
<body>
<div id="body">
<div id="content">
<div id="header">
<div id="headerright">
<!--start:selectform--><form action="{selfpath}" method="post">
<div>
{languageselect}{themeselect}<input type="submit" value="{lang_change}" tabindex="{selectformindex}" class="button" />
</div>
</form>
<!--end:selectform-->{date}<br />
<!--start:loggedin--><a {logoutlink} id="logout">{lang_logout}</a><br />
<!--end:loggedin--></div>
<h1>
{header}
</h1>
<!--start:menu--><ul>
{menu}</ul>
<!--end:menu--></div>
{info}{debug}<!--start:!critical--><!--start:!antyflood--><h2>{title}</h2>
<!--end:!antyflood--><!--end:!critical-->{page}</div>
<div id="preloader">
<img src="{datapath}share/themes/{theme}/images/menu.png" alt="" />
<img src="{datapath}share/themes/{theme}/images/menu_active.png" alt="" />
<img src="{datapath}share/themes/{theme}/images/gototop_active.png" alt="" />
</div>
<div id="footer">
<div>
Powered by<br />
<a href="http://estats.emdek.cba.pl/" tabindex="{tabindex}">
<img src="{datapath}share/antipixels/default/darkgreen.png" alt="eStats" title="eStats" />
</a>
<br />
<div>
Design by <strong>Neo</strong><br />
&copy; 2005 - 2008 <a href="http://emdek.cba.pl/" tabindex="{tabindex}"><strong>Emdek</strong></a>
</div>
<a href="#header" title="{lang_gototop}" tabindex="{tabindex}" id="gototop">&nbsp;</a>
<small>{pagegeneration}</small>
</div>
</div>
</div>
<!--[if lt IE 7.0]>
<script type="text/javascript">
window.onload = IEsupport ();
</script>
<![endif]-->
</body>
</html>