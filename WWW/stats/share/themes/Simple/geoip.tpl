[start:geoip]{cacheinfo}<!--start:dateform-->{dateform}<!--end:dateform--><!--start:admin--><form action="{selfpath}" method="post">
<!--end:admin--><!--start:singlecountry--><table cellpadding="0" cellspacing="0" border="1" width="100%">
<tr>
<td>
{cities}</td>
<td>
{regions}</td>
</tr>
</table>
<!--end:singlecountry--><!--start:!singlecountry--><table cellpadding="0" cellspacing="0" border="1" width="100%">
<tr>
<td>
{cities}</td>
<td>
{countries}</td>
<td>
{continents}</td>
</tr>
</table>
<!--end:!singlecountry--><!--start:map--><h3>{lang_map}</h3>
<div>
<img src="{path}image{suffix}{separator}id=geoip-{mapid}" alt="" usemap="#geoip_map" id="geoipmap" />
<map id="geoip_map" name="geoip_map">
{maphrefs}</map>
</div>
<div id="mapinfo">
{lang_author}:
<a href="{maplink}" tabindex="{maptabindex}"><strong>{mapauthor}</strong></a>
({maptime})
</div>
<!--end:map--><!--start:admin-->{adminbuttons}</form>
<!--end:admin-->[/end]