[start:group-page]{dateform}<form action="{selfpath}" method="post">
<table cellspacing="0" cellpadding="0" id="singlegroup">
<tr>
<th>
{lang_fulllist}
</th>
<!--start:group_chart--><th>
{lang_chart}
</th>
<!--end:group_chart--></tr>
<tr>
<td>
{group}</td>
<!--start:group_chart--><td id="piechart">
<img src="{path}image{suffix}{separator}id={chartid}" alt="" />
</td>
<!--end:group_chart--></tr>
</table>
{adminbuttons}</form>
[/end]

[start:group]<table cellspacing="0" cellpadding="0" id="group_{id}">
<!--start:group_{id}_header--><thead>
<tr>
<th colspan="4">
<a href="{link}" tabindex="{tabindex}">
{title}<!--start:group_{id}_info-->
({displayed} {lang_of} {amount})<!--end:group_{id}_info-->
</a>
</th>
</tr>
</thead>
<!--end:group_{id}_header--><tbody>
{informations}{rows}{sum}{admin}</tbody>
</table>
[/end]

[start:group-row]<tr>
<td class="auto">
<em>{num}</em>.
</td>
<td class="wide">
{icon}{value}
</td>
<td>
{amount}
</td>
<td>
<em>{percent}%</em>
</td>
</tr>
[/end]

[start:group-amount]<tr>
<td colspan="2">
<strong>{lang_sum}:</strong>
</td>
<td>
<strong>{amount}</strong>
</td>
<td>&nbsp;</td>
</tr>
[/end]

[start:group-none]<tr>
<td colspan="4">
<strong>{lang_none}</strong>
</td>
</tr>
[/end]

[start:group-informations]<tr>
<td colspan="4">
{informations}</td>
</tr>
[/end]

[start:group-admin]<tr>
<td colspan="4" class="settings">
{admin}</td>
</tr>
[/end]