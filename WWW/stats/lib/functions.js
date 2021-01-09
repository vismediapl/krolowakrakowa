function IEover () {
	this.className = 'hover';
	}
function IEout () {
	this.className = '';
	}
function IEsupport () {
	document.getElementById('body').style.height = '100%';
	document.getElementById('body').style.position = 'static';
	document.getElementById('footer').style.position = 'static';
	Menu = Array['general', 'technical', 'geoip', 'time', 'admin'];
	for (i = 0, c = Menu.length; i < c; i++) {
		if (!document.getElementById('menu_entry_' + Menu[i])) continue;
		document.getElementById('menu_entry_' + Menu[i]).style.position = 'relative';
		document.getElementById('menu_entry_' + Menu[i]).onmouseover = IEover;
		document.getElementById('menu_entry_' + Menu[i]).onmouseout = IEout;
		}
	}
function setCookie (Name, Value) {
	Time = new Date ();
	Time.setTime (Time.getTime () + 31536000);
	document.cookie = Name + '=' + escape (Value) + '; expires=' + (Value?Time.toGMTString ():-1) + '; path=' + escape ('/');
	}
function levelsShowHide (ID) {
	HRs = document.getElementById('chart_' + ID).getElementsByTagName('hr');
	for (i = 0, c = HRs.length; i < c; i++) HRs[i].style.display = (document.getElementById('levels_switch_' + ID).checked?'block':'none');
	setCookie ('estats_time_levels_chart_' + ID, !document.getElementById('levels_switch_' + ID).checked);
	}
function highlightColumns (IDs, ChartID, Type, ID, Mode) {
	for (i = ((ID == 3)?0:ID), c = ((ID == 3)?2:ID); i <= c; i++) {
		document.getElementById('level_' + ChartID + '_' + Type + '_' + i).style.borderColor = (Mode?highlightColor:(Type?chartLevelAll:chartLevelUni));
		document.getElementById('level_' + ChartID + '_' + Type + '_' + i).style.zIndex = (Mode?1:0);
		}
	for (i = 0, c = IDs.length; i < c; i++) {
		if (IDs[i] == undefined) continue;
		DIVs = document.getElementById((IDs[i] == '_')?'chart_' + ChartID:'bar_' + ChartID + '_' + IDs[i]).getElementsByTagName('div');
		for (j = 0, c = DIVs.length; j < c; j++) if (DIVs[j].className == Type || DIVs[j].className == Type + ' max') DIVs[j].style.borderColor = (Mode?highlightColor:((DIVs[j].className.split('ma').length > 1)?chartMaxBorder:chartColumnBorder));
		}
	}
function expandRow (ID, container) {
	container.style.display = 'block';
	document.getElementById(ID).className = 'expanded';
	document.getElementById(ID).style.display = 'block';
	}
function QueryRows (GroupID, SID, Query, Mode) {
	Paragraphs = document.getElementById(Mode?GroupID:SID).getElementsByTagName('p');
	for (k = 0, m = Paragraphs.length; k < m; k++) {
		ParagraphID = Paragraphs[k].id;
		if (document.getElementById('ShowModified').checked && Paragraphs[k].className != 'changed') continue;
		Fields = document.getElementById(ParagraphID).getElementsByTagName(document.getElementById(ParagraphID).getElementsByTagName('textarea').length?'textarea':'input');
		Description = document.getElementById(ParagraphID).getElementsByTagName('dfn');
		if (Description.length) Description = Description[0].innerHTML;
		else Description = '';
		SearchInString = ' ' + Fields[0].value + Fields[0].name + Description + ' ';
		SearchInString = SearchInString.toLowerCase ();
		if (SearchInString.split(Query).length > 1) {
			if (Paragraphs[k].style.display != 'block') document.getElementById('ResultsAmount').innerHTML++;
			if (!Mode) expandRow (SID, Paragraphs[k]);
			expandRow (GroupID, Paragraphs[k]);
			}
		}
	}
function search (Query) {
	Query = Query.toLowerCase ();
	Fieldsets = document.getElementById('advanced').getElementsByTagName('Fieldset');
	Rows = document.getElementById('advanced').getElementsByTagName('p');
	document.getElementById('ResultsAmount').innerHTML = 0;
	if (Query != '') {
		for (i = 0, c = Fieldsets.length; i < c; i++) Fieldsets[i].style.display = 'none';
		for (i = 0, c = Rows.length; i < c; i++) Rows[i].style.display = 'none';
		}
	else {
		for (i = 0, c = Fieldsets.length; i < c; i++) {
			Fieldsets[i].className = 'notexpanded';
			Fieldsets[i].style.display = 'block';
			}
		for (i = 0, c = Rows.length; i < c; i++) Rows[i].style.display = 'block';
			document.getElementById('ResultsAmount').innerHTML = ResultsAmount;
			return (0);
			}
	for (i = 0, c = Fieldsets.length; i < c; i++) {
		if (Fieldsets[i].id.split('.').length == 1) {
			GroupID = Fieldsets[i].id;
			Groups = document.getElementById(GroupID).getElementsByTagName('Fieldset');
			for (j = 0, l = Groups.length; j < l; j++) {
				SID = Groups[j].id;
				QueryRows (GroupID, SID, Query, 0);
				QueryRows (GroupID, SID, Query, 1);
				}
			}
		}
	}
function checkDefault (Field, Value, Type) {
	if (Type) Change = !(document.getElementById('F_' + Field).checked == Value);
	else Change = !(document.getElementById('F_' + Field).value == Value);
	document.getElementById('P_' + Field).className = (Change?'changed':'');
	document.getElementById('P_' + Field).title = (Change?ChangedValueString:'');
	}
function setDefault (Field, Value, Type) {
	if (Type) document.getElementById('F_' + Field).checked = ((Value == '1')?true:false);
	else document.getElementById('F_' + Field).value = Value;
	checkDefault (Field, Value, Type);
	}
function changeClassName (ID) {
	document.getElementById(ID).className = ((document.getElementById(ID).className == 'expanded')?'notexpanded':'expanded');
	document.getElementById('ShowAll').checked = 0;
	}
function resetAll () {
	Inputs = document.getElementById('advanced').getElementsByTagName('input');
	for (i = 0, c = Inputs.length; i < c; i++) {
		if (Inputs[i].type == 'button') eval (Inputs[i].getAttribute ('onclick'));
		}
	}
function hideAll () {
	Fieldsets = document.getElementById('advanced').getElementsByTagName('Fieldset');
	for (i = 0, c= Fieldsets.length; i < c; i++) Fieldsets[i].className = 'notexpanded';
	}
function expandAll () {
	Fieldsets = document.getElementById('advanced').getElementsByTagName('Fieldset');
	for (i = 0, c = Fieldsets.length; i < c; i++) {
		Fieldsets[i].className = (Expanded?'notexpanded':'expanded');
		Fieldsets[i].style.display = 'block';
		}
	Paragraphs = document.getElementById('advanced').getElementsByTagName('p');
	for (i = 0, c = Paragraphs.length; i < c; i++) Paragraphs[i].style.display = 'block';
	document.getElementById('ResultsAmount').innerHTML = ResultsAmount;
	}
function showAll () {
	Expanded = !document.getElementById('ShowAll').checked;
	document.getElementById('ShowModified').checked = 0;
	document.getElementById('AdvancedSearch').style.color = 'gray';
	document.getElementById('AdvancedSearch').value = SearchString;
	expandAll ();
	}
function showModified () {
	Expanded = !document.getElementById('ShowModified').checked;
	SearchValue = document.getElementById('AdvancedSearch').value;
	if (Expanded) {
		expandAll ();
		if (SearchValue != SearchString) {
			search (SearchValue);
			document.getElementById('AdvancedSearch').value = SearchValue;
			}
		return (0);
		}
	document.getElementById('ShowAll').checked = 0;
	document.getElementById('ResultsAmount').innerHTML = 0;
	Fieldsets = document.getElementById('advanced').getElementsByTagName('Fieldset');
	for (i = 0, c = Fieldsets.length; i < c; i++) {
		Modified = 0;
		Paragraphs = Fieldsets[i].getElementsByTagName('p');
		for (j = 0, l = Paragraphs.length; j < l; j++) {
			if (Paragraphs[j].className == 'changed') {
				document.getElementById('ResultsAmount').innerHTML++;
				Paragraphs[j].style.display = 'block';
				Modified = 1;
				}
			else Paragraphs[j].style.display = 'none';
			}
		if (Modified) {
			Fieldsets[i].className = 'expanded';
			Fieldsets[i].style.display = 'block';
			}
		else {
			Fieldsets[i].className = 'notexpanded';
			Fieldsets[i].style.display = 'none';
			}
		}
	if (SearchValue != SearchString) {
		search (SearchValue);
		document.getElementById('AdvancedSearch').value = SearchValue;
		}
	}