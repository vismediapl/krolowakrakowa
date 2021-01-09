[start:detailed]{cacheinfo}<form action="{selfpath}" method="post">
<p>
<label for="Robots">{lang_robots}</label>:<br>
<select name="Robots" id="Robots" tabindex="{selectrobotsindex}">
{robotsselect}</select>
<input type="submit" value="{lang_change}" tabindex="{robotsformindex}">
</p>
</form>
<table cellpadding="2" cellspacing="0" border="1" width="100%">
<thead>
<tr>
<th>
&nbsp;
</th>
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
<th>
{lang_referrer}
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
{rows}</tbody>
</table>
{links}{detailed-legend}[/end]

[start:detailed-row]<tr>
<td>
{simpletype}
</td>
<td align="center">
<!--start:details-{id}--><a href="{path}details/{id}/1{suffix}" title="{lang_details}" tabindex="{tabindex}">
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
{visits}</td>
<td align="center">
{referrer}
</td>
<td align="center">
{host}
</td>
<td title="{useragent}" align="center">
{configuration}</td>
</tr>
[/end]

[start:detailed-links]<div align="center">
{links}</div>
[/end]

[start:detailed-legend]<h4>{lang_legend}:</h4>
<p>
<small>{lang_yourvisits}: <strong>!</strong></small>
</p>
<p>
<small>{lang_onlinevisitors}: <strong>+</strong></small>
</p>
<p>
<small>{lang_returnsvisitors}: <strong>^</strong></small>
</p>
<p>
<small>{lang_robots}: <strong>$</strong></small>
</p>
[/end]

[start:detailed-none]<tr>
<td colspan="7" align="center">
<strong>{lang_none}</strong>
</td>
</tr>
[/end]