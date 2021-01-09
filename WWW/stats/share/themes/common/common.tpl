[start:menuentry]<li id="menu_entry_{id}">
<a href="{link}" tabindex="{tabindex}" accesskey="{accesskey}" class="{class}">
<!--start:submenu-{id}-->&#8595;&nbsp;<!--end:submenu-{id}--><span>{text}</span>
</a>
<!--start:submenu-{id}--><ul id="submenu_{id}">
{submenu}</ul>
<!--end:submenu-{id}--></li>
[/end]

[start:announcement]<div class="{class}" title="{type}">
{content}
</div>
[/end]

[start:dateform]<form action="{selfpath}" method="post" id="dateform">
<p>
<span>
{hourselect}{dayselect}{monthselect}{yearselect}{mapselect}<input type="submit" value="{lang_show}" tabindex="{dateformindex}" class="button" />
</span>
<label for="year">{lang_showdatafor}</label>:
</p>
</form>
[/end]

[start:config-row]<p>
<span>
{form}
</span>
<label for="{fid}">{desc}</label>:
</p>
[/end]

[start:option-row]<p id="P_{id}"{changed}>
<span>
{form}
<input type="button" value="{lang_default}" class="button" onclick="setDefault ('{id}', '{default}', {mode})" title="{lang_defaultvalue}: {defaultvalue}" tabindex="{tabindex}" />
</span>
<label for="F_{id}">
{option}:{desc}
</label>
</p>
[/end]