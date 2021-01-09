[start:detailed]{cacheinfo}<form action="{selfpath}" method="post">
<p>
<span>
<select name="Robots" id="Robots" tabindex="{selectrobotsindex}">
{robotsselect}</select>
<input type="submit" value="{lang_change}" tabindex="{robotsformindex}" class="button" />
</span>
<label for="Robots">{lang_robots}</label>:
</p>
</form>
<table cellpadding="2" cellspacing="0" id="detailed">
<thead>
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

[start:detailed-row]<tr class="{class}">
<td>
<!--start:details-{id}--><a href="{path}details/{id}/1{suffix}" title="{lang_details}" tabindex="{tabindex}">
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
{visits}</td>
<td>
{referrer}
</td>
<td>
{host}
</td>
<td title="{useragent}">
{configuration}</td>
</tr>
[/end]

[start:detailed-links]{links}[/end]

[start:detailed-legend]<h4>{lang_legend}:</h4>
<p>
<small class="user">{lang_yourvisits}</small>
</p>
<p>
<small class="online">{lang_onlinevisitors}</small>
</p>
<p>
<small class="returns">{lang_returnsvisitors}</small>
</p>
<p>
<small class="robot">{lang_robots}</small>
</p>
[/end]

[start:detailed-none]<tr>
<td colspan="7">
<strong>{lang_none}</strong>
</td>
</tr>
[/end]