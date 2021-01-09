[start:menuentry]<li id="menu_entry_{id}">
<a href="{link}" tabindex="{tabindex}" accesskey="{accesskey}">
{text}
</a>
<!--start:submenu-{id}--><ul id="submenu_{id}">
{submenu}</ul>
<!--end:submenu-{id}--></li>
[/end]

[start:announcement]<div border="1">
<h4>{type}</h4>
{content}
</div>
[/end]

[start:dateform]<form action="{selfpath}" method="post">
<p>
<label for="year">{lang_showdatafor}</label>:<br>
{hourselect}{dayselect}{monthselect}{yearselect}{mapselect}<input type="submit" value="{lang_show}" tabindex="{dateformindex}">
</p>
</form>
[/end]

[start:config-row]<p>
<label for="{fid}">{desc}</label>:<br>
{form}
</p>
[/end]

[start:option-row]<p id="P_{id}">
<label for="F_{id}">
{option}:{desc}
</label><br>
{form}
<input type="button" value="{lang_default}" class="button" onclick="setDefault ('{id}', '{default}', {mode})" title="{lang_defaultvalue}: {defaultvalue}" tabindex="{tabindex}" />
</p>
[/end]