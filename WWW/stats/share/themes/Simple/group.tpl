[start:group-page]{dateform}<form action="{selfpath}" method="post">
<table cellspacing="0" cellpadding="2" border="1" width="100%">
{group}</table>
{adminbuttons}</form>
[/end]

[start:group]<tr>
<th colspan="4">
<a href="{link}" tabindex="{tabindex}">
{title}<!--start:group_{id}_info-->
({displayed} {lang_of} {amount})<!--end:group_{id}_info-->
</a>
</th>
</tr>
{informations}{rows}{sum}{admin}[/end]

[start:group-row]<tr>
<td>
<em>{num}</em>.
</td>
<td>
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
<td colspan="2" align="center">
<strong>{lang_sum}:</strong>
</td>
<td colspan="2">
<strong>{amount}</strong>
</td>
</tr>
[/end]

[start:group-none]<tr>
<td colspan="4" align="center">
<strong>{lang_none}</strong>
</td>
</tr>
[/end]

[start:group-informations]<tr>
<td colspan="4" align="center">
{informations}</td>
</tr>
[/end]

[start:group-admin]<tr>
<td colspan="4">
{admin}</td>
</tr>
[/end]