<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN">
<html dir="{dir}">
<head>
{meta}<link href="{datapath}share/icons/misc/estats.png" rel="shortcut icon" type="image/png">
<title>eStats :: {title}</title>
<!--
Simple theme for eStats 4.9
Author: Emdek
URL: http://estats.emdek.cba.pl
Licence: GPL
-->
<script type="text/javascript" src="{datapath}share/themes/{theme}/theme.js"></script>
<script type="text/javascript" src="{datapath}lib/functions.js"></script>
</head>
<body bgcolor="#DDDDDD" align="center">
<div>
<a name="top"></a>
<div align="right">
<h1 align="center">
{header}
</h1>
<!--start:selectform--><form action="{selfpath}" method="post">
<div>
{languageselect}{themeselect}<input type="submit" value="{lang_change}" tabindex="{selectformindex}">
</div>
</form>
<!--end:selectform-->{date}<br />
<!--start:loggedin--><a {logoutlink}>{lang_logout}</a><br>
<!--end:loggedin--></div>
<div align="left">
<!--start:menu--><hr>
<ul>
{menu}</ul>
<!--end:menu--><hr>
<!--start:announcements-->{info}{debug}<hr>
<!--end:announcements--><!--start:!critical--><!--start:!antyflood--><h2 align="center">{title}</h2>
<!--end:!antyflood--><!--end:!critical-->{page}<hr>
</div>
<div align="center">
Powered by<br>
<a href="http://estats.emdek.cba.pl/" tabindex="{tabindex}">
<img src="{datapath}share/antipixels/default/simple.png" alt="eStats" title="eStats" border="0">
</a><br><br>
&copy; 2005 - 2008 <a href="http://emdek.cba.pl/" tabindex="{tabindex}"><strong>Emdek</strong></a>
<div align="right">
<a href="#top" title="{lang_gototop}" tabindex="{tabindex}" id="gototop"><b>^</b></a><br>
</div>
<small>{pagegeneration}</small>
</div>
</div>
</body>
</html>