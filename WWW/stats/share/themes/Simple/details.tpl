[start:details]<table cellpadding="0" cellspacing="0" border="1" width="100%">
<thead>
<tr>
<th colspan="3">
{lang_visitedpages} ({visits})
</th>
<th>
{lang_referrer}
</th>
<th>
{lang_keywords}
</th>
<th>
{lang_host}
</th>
<th>
{lang_configuration}
</th>
</tr>
</thead>
<tbody>
<tr>
<th>
#
</th>
<th>
{lang_date}
</th>
<th>
{lang_site}
</th>
<td rowspan="{rowspan}" align="center">
{referrer}
</td>
<td rowspan="{rowspan}" align="center">
{keywords}
</td>
<td rowspan="{rowspan}" align="center">
{host}
</td>
<td rowspan="{rowspan}" title="{useragent}" align="center">
{configuration}</td>
</tr>
{rows}{links}</tbody>
</table>
<!--start:other-visits--><h3 align="center">
{lang_othervisits}
</h3>
<table cellpadding="0" cellspacing="0" border="1" width="100%">
<tr>
<th>
#
</th>
<th>
{lang_firstvisit}
</th>
<th>
{lang_lastvisit}
</th>
<th>
{lang_visitsamount}
</th>
</tr>
{othervisits}</tr>
</table>
<!--end:other-visits-->{detailed-legend}[/end]

[start:details-row]<tr>
<td align="center">
<em>{num}.</em>
</td>
<td align="center">
{date}
</td>
<td title="{title}" align="center">
{link}
</td>
</tr>
[/end]

[start:details-links]<tr>
<td colspan="3" align="center">
{links}
</td>
</tr>
[/end]

[start:other-visits-row]<tr>
<td align="center">
<!--start:details-{id}--><a href="{path}details/{id}/1" title="{lang_details}" tabindex="{tabindex}">
<!--end:details-{id}--><strong><em>{id}</em></strong>
<!--start:details-{id}--></a>
<!--end:details-{id}--></td>
<td align="center">
{first}
</td>
<td align="center">
{last}
</td>
<td align="center">
{amount}
</td>
</tr>
[/end]

[start:details-none]<h4>{lang_warning}</h4>
{lang_dataunavailable}.<br>
[/end]