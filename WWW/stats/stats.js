eFlashVersion = 0;
ePlugins = navigator.plugins;
if (ePlugins.length) {
   eFlash = ePlugins["Shockwave Flash"];
   if (eFlash) {
      eFlashVersion = '%3F';
      eVersion = eFlash.description;
      if (eVersion) eFlashVersion = eVersion.charAt (eVersion.indexOf ('.') - 1);
      }
   }
else document.write ('<scr' + 'ipt language="VBScript">\n	on error resume next\n	For i = 2 to 10\n	If Not(IsObject(CreateObject("ShockwaveFlash.ShockwaveFlash." & i))) Then\n	Else\n	eFlashVersion = i\n	End If\n	Next\n</scr' + 'ipt>');
document.write ('<a href="http://estats.emdek.cba.pl"><img src="' + ePath + 'stats.php?javascript=1&cookies=' + (navigator.cookieEnabled?1:0) + '&flash=' + eFlashVersion + '&java=' + (navigator.javaEnabled ()?1:0) + '&width=' + screen.width + '&height=' + screen.height + '&adress=' + escape (window.location.href) + (document.title?'&title=' + escape (document.title):'') + '&estats=' + (new Date ().getTime ()) + (eCount?'&count=1':'') + '" border="0" width="0" height="0" /></a>');