[start:time]<form action="{selfpath}" method="post">
<!--start:dateform--><p>
<span>
{dayselect}{monthselect}{yearselect}
</span>
<label for="year">{lang_showdatafor}</label>:
</p>
<!--end:dateform--><p>
<span>
<select name="TimeView[]" multiple="multiple" size="3" id="TimeView" tabindex="{selectviewindex}">
{viewselect}</select>
</span>
<label for="TimeView">{lang_chartsview}</label>:
</p>
<div class="buttons">
<input type="submit" value="{lang_show}" tabindex="{dateformindex}" class="button" />
</div>
</form>
{24hours}{month}{year}{years}{hours}{weekdays}[/end]

[start:chart]{cacheinfo}<table cellspacing="0" cellpadding="0" class="chart{class}" id="chart_{id}"{style}>
<thead>
<tr>
<th colspan="{colspan}">
<a href="{link}" tabindex="{tabindex}">
{title}
</a>
</th>
</tr>
</thead>
{chart}</table>
{summary}{switch}[/end]

[start:chart-bars-container]<td{class} id="{id}" style="width:{width}px;">
<div>
{bars}</div>
{tooltip}</td>
[/end]

[start:chart-bar]<div style="height:{height}px;margin-top:{margin}px;" class="{class}" title="{title}"></div>
[/end]

[start:chart-summary]<table cellpadding="0" cellspacing="0" class="summary">
<thead>
<tr>
<th colspan="5">{lang_summary}</th>
</tr>
</thead>
<tbody>
<tr>
<th>&nbsp;</th>
<th>{lang_sum}</th>
<th>{lang_most}</th>
<th>{lang_average}</th>
<th>{lang_least}</th>
</tr>
{rows}</tbody>
</table>
[/end]

[start:chart-summary-row]<tr>
<th>{text}</th>
<td{sumjs}>{sum}</td>
<td{maxjs}>{max}</td>
<td{avgjs}>{avg}</td>
<td{minjs}>{min}</td>
</tr>
[/end]