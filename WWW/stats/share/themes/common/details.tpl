[start:details]<table cellpadding="0" cellspacing="0" id="details" class="{class}">
<thead>
<tr class="detailsheader">
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
<tr class="detailsheader">
<th>
#
</th>
<th>
{lang_date}
</th>
<th>
{lang_site}
</th>
<td rowspan="{rowspan}">
{referrer}
</td>
<td rowspan="{rowspan}">
{keywords}
</td>
<td rowspan="{rowspan}">
{host}
</td>
<td rowspan="{rowspan}" title="{useragent}">
{configuration}</td>
</tr>
{rows}{links}</tbody>
</table>
<!--start:other-visits--><h3>
{lang_othervisits}
</h3>
<table cellpadding="0" cellspacing="0" id="othervisits">
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
<td>
<em>{num}.</em>
</td>
<td>
{date}
</td>
<td title="{title}">
{link}
</td>
</tr>
[/end]

[start:details-links]<tr>
<td colspan="3">
{links}
</td>
</tr>
[/end]

[start:other-visits-row]<tr>
<td>
<!--start:details-{id}--><a href="{path}details/{id}/1" title="{lang_details}" tabindex="{tabindex}">
<!--end:details-{id}--><strong><em>{id}</em></strong>
<!--start:details-{id}--></a>
<!--end:details-{id}--></td>
<td>
{first}
</td>
<td>
{last}
</td>
<td>
{amount}
</td>
</tr>
[/end]

[start:details-none]<div class="warning" title="{lang_warning}">
{lang_dataunavailable}.
</div>
[/end]