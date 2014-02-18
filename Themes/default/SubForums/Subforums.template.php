<?php
/**
* Subforums.template.php
*
* Software Version: SMF Subforums v1.41
* Software by: PortaMx corp.
**/

function template_main()
{
	global $context, $settings, $modSettings, $scripturl, $base_boardurl, $mbname, $txt;

	echo '
	<div style="overflow:hidden;">
	<div class="cat_bar">
		<h3 class="catbg">'. $txt['admin_subforums_title'] .'</h3>
	</div>
	<p class="windowbg description">'. $txt['admin_subforums_description'] .'</p>

	<div class="windowbg">
		<form id="SubforumsID" accept-charset="', $context['character_set'], '" name="Subforums" action="' . $scripturl . '?action=admin;area=subforums;'. $context['session_var'] .'=' .$context['session_id'] .'" method="post" style="margin:0; padding:0;">
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input id="SubforumsAction" type="hidden" name="" value="" />

			<table class="table_grid" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:0;">
				<tr class="catbg">';

	if(empty($context['subforums']['action']))
		echo '
					<th class="first_th" width="22%" align="left">&nbsp;'. $txt['admin_subforums_host_name'] .'</th>
					<th width="28%" align="left">&nbsp;'. $txt['admin_subforums_cats'] .'</th>
					<th width="28%" align="left">&nbsp;'. $txt['admin_subforums_theme_access'] .'</th>
					<th class="last_th" width="22%" align="left">&nbsp;'. $txt['admin_subforums_theme'] .'</th>
				</tr>';
	else
		echo '
					<th class="first_th" width="24%" align="left">&nbsp;'. $txt['admin_subforums_host'] .'</th>
					<th width="28%" align="left">&nbsp;'. $txt['admin_subforums_cats'] .'</th>
					<th width="24%" align="left">&nbsp;'. $txt['admin_subforums_access'] .'</th>
					<th class="last_th" width="24%" align="left">&nbsp;'. $txt['admin_subforums_theme'] .'</th>
				</tr>';

	if(empty($context['subforums']['action']))
	{
		$urlparts = parse_url($scripturl);
		$baseurl = parse_url($base_boardurl);
		foreach($context['subforums']['data'] as $part)
		{
			echo '
				<tr id="tr.'. $part['host'] .'">
					<td valign="top" class="smalltext" style="padding:3px 8px;">
						<a href="javascript:void(\'\')"  onclick="SubforumsSwitchto(\''. $part['host'] .'\')"><b>'. $part['host'] .'</b></a>';

			if($_SERVER['SERVER_NAME'] == $part['host'])
				echo '
						&nbsp;<img src="'. $settings['default_theme_url'] .'/SubForums/images/subforums_act.gif' .'" alt="**" />';

			echo '
						<br />'. (empty($part['name']) ? $mbname : $part['name']) .'
						<hr />
						'. sprintf($txt['admin_subforums_info'], $part['topics'], $part['posts']) .'
					</td>
					<td valign="top" class="smalltext" style="padding:3px 8px;">';

			$islast = count($part['cats']) -1;
			foreach($part['cats'] as $catid)
			{
				echo str_replace(' ', '&nbsp;', $context['subforums']['SMF_Cats'][$catid]) . (!empty($islast) ? ', ' : '');
				$islast--;
			}

			echo '
					</td>
					<td valign="top" class="smalltext" style="padding:3px 8px;">';

			if(count($part['groups']) > 0)
			{
				$islast = count($part['groups']) -1;
				foreach($part['groups'] as $grpID)
				{
					echo str_replace(' ', '&nbsp;', $context['subforums']['SMF_groups'][$grpID]) . (!empty($islast) ? ', ' : '');
					$islast--;
				}
			}
			else
				echo $txt['admin_subforums_nogroups'];

			echo '
					</td>
					<td valign="top" class="smalltext" align="left" style="padding:3px 8px;border-right:0;">
						<div id="div.'. $part['host'] .'">
							'. (!empty($context['subforums']['SMF_Themes'][$part['theme']]) ? $context['subforums']['SMF_Themes'][$part['theme']] : $txt['admin_subforums_themedefault']) .'<br />
							'. (!empty($context['subforums']['languages'][$part['language']]) ? $context['subforums']['languages'][$part['language']]['name'] : $txt['admin_subforums_themedefault']) .'
							<hr />
						</div>
						<div id="pos.'. $part['host'] .'" style="position:relative; top:0; text-align:right;">';

			if($_SERVER['SERVER_NAME'] != $part['host'])
				echo '
							<input class="button_submit smalltext" type="button" value="'. $txt['admin_subforums_delete'] .'" name="" onclick="SubforumsSubmit(\'sf_delete\', \''. $part['id'] .'\', \''. sprintf($txt['admin_subforums_delete_msg'], $part['host']) .'\')" />';

			echo '
							&nbsp;<input class="button_submit smalltext" type="button" value="'. $txt['admin_subforums_edit'] .'" name="" onclick="SubforumsSubmit(\'sf_edit\', \''. $part['id'] .'\')" />
						</div>
					</td>
				</tr>';
		}

		echo '
				<tr>
					<td colspan="2" class="smalltext" style="padding:3px 8px;">
						<a href="javascript:void(\'\')"  onclick="SubforumsSwitchto(\''. $baseurl['host'] .'\')"><b>[ '. $baseurl['host'] .' ]</b></a>';

		if($_SERVER['SERVER_NAME'] == $baseurl['host'])
			echo '
						<img src="'. $settings['default_theme_url'] .'/SubForums/images/subforums_act.gif' .'" alt="**" />';

		echo '
						<br />'. $mbname .'
					</td>
					<td valign="top" class="smalltext" style="padding:3px 8px;">
						<input type="hidden" name="subforums_adminonly" value="0" />
						<span style="vertical-align:top;">'. $txt['admin_subforums_onlyadmin'] .'</span>
						<input style="float:right;" class="input_check" type="checkbox" name="subforums_adminonly" value="1"'. (!empty($modSettings['subforums_adminonly']) ? ' checked="checked"' : '') .' onchange="SubforumsSubmit(\'sf_save\', \'main\')" />
						<br style="clear:both;" />
						<input type="hidden" name="subforums_xmlrss" value="0" />
						<span style="vertical-align:top;">'. $txt['admin_subforums_xmlrss'] .'</span>
						<input style="float:right;" class="input_check" type="checkbox" name="subforums_xmlrss" value="1"'. (!empty($modSettings['subforums_xmlrss']) ? ' checked="checked"' : '') .' onchange="SubforumsSubmit(\'sf_save\', \'main\')" />
					</td>
					<td valign="top" align="right" style="padding:6px 8px;border-right:0;">
						<input class="button_submit smalltext" type="button" value="'. $txt['admin_subforums_add'] .'" name="" onclick="SubforumsSubmit(\'sf_add\', 0)" />
					</td>
				</tr>';
	}

	elseif($context['subforums']['action'] == 'edit')
	{
			echo '
				<tr>
					<td valign="top" align="left" style="padding:8px 8px 3px 8px;">
						<div style="height:77px">
							<input type="text" name="SF_host" value="'. $context['subforums']['data']['host'] .'" class="input_text" style="width:97%;" />
							<br />
							<span class="smalltext">'. $txt['admin_subforums_host_help'] .'</span>
						</div>
					</td>
					<td valign="top" align="left" rowspan="2" style="padding:8px 8px 3px 8px;">
						<select name="SF_category[]" multiple="multiple" size="9" style="width:100%;" class="smalltext">';

			foreach($context['subforums']['SMF_Cats'] as $catID => $catName)
				echo'
							<option value="'. $catID .'"'. (in_array($catID, $context['subforums']['data']['cats']) ? 'selected="selected"' : ''). '>'. $catName .'</option>';

			echo '
						</select>
						<br /><span class="smalltext">'. $txt['admin_subforums_select_help'] .'</span>
					</td>
					<td valign="top" align="left" rowspan="2" style="padding:8px 8px 3px 8px;">
						<select name="SF_groups[]" size="9" multiple="multiple" style="width:100%;" class="smalltext">';

			foreach($context['subforums']['SMF_groups'] as $grpID => $grpName)
				echo'
							<option value="'. $grpID .'"'. (!empty($context['subforums']['data']['groups']) && in_array($grpID, $context['subforums']['data']['groups']) ? 'selected="selected"' : ''). '>'. $grpName .'</option>';

			echo '
						</select>
						<br /><span class="smalltext">'. $txt['admin_subforums_access_help'] .'</span>
					</td>
					<td valign="top" align="left" style="padding:8px 8px 3px 8px;border-right:0;">
						<div style="min-height:25px;">
						'. $txt['admin_subforums_theme_sel'] .'<select name="SF_idtheme" size="1" style="float:right;width:50%;" class="smalltext">
							<option value="0"'. (empty($context['subforums']['data']['theme']) ? 'selected="selected"' : '') .'>'. $txt['admin_subforums_themedefault'] .'</option>';

			foreach($context['subforums']['SMF_Themes'] as $thID => $thName)
				echo'
							<option value="'. $thID .'"'. ($thID == $context['subforums']['data']['theme'] ? 'selected="selected"' : ''). '>'. $thName .'</option>';

			echo '
						</select>
						</div>
						<div style="min-height:20px;">
						'. $txt['admin_subforums_language_sel'] .'<select name="SF_language" size="1" style="float:right;width:50%;" class="smalltext">
							<option value=""'. (empty($context['subforums']['data']['language']) ? 'selected="selected"' : '') .'>'. $txt['admin_subforums_themedefault'] .'</option>';

			foreach($context['subforums']['languages'] as $lang => $langData)
				echo'
							<option value="'. $lang .'"'. ($lang == $context['subforums']['data']['language'] ? 'selected="selected"' : ''). '>'. $langData['name'] .'</option>';

			echo '
						</select>
						</div>
						<span class="smalltext">'. $txt['admin_subforums_theme_help'] .'</span>
					</td>
				</tr>

				<tr>
					<td valign="top">
						<table class="table_grid" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:4px;">
							<tr class="catbg">
								<th class="first_th" scope="col" align="left">&nbsp;'. $txt['admin_subforums_name'] .'</th>
								<th class="last_th" width="2%" scope="col" align="left"></th>
							</tr>
						</table>
						<div  style="padding:0px 5px 0 5px;">
							<input type="text" name="SF_name" value="'. $context['subforums']['data']['name'] .'" class="input_text" style="width:97%;" />
							<br /><span class="smalltext">'. $txt['admin_subforums_name_help'] .'</span>
						</div>
					</td>
					<td valign="top" style="padding:6px 8px; border-right:0;">
						<div style="min-height:90px;">
							<div style="padding-bottom:5px;">'. $txt['admin_subforums_reg_memgroup'] .'</div>
							<select name="SF_reggroup" size="1" style="float:right;width:70%;" class="smalltext">';

			foreach($context['subforums']['SMF_groups'] as $grpID => $grpName)
			{
				if($grpID == -1)
					$grpName = $txt['admin_subforums_themedefault'];
				echo'
								<option value="'. $grpID .'"'. (!empty($context['subforums']['data']['reg_group']) && $grpID == $context['subforums']['data']['reg_group'] ? 'selected="selected"' : ''). '>'. $grpName .'</option>';
			}

			echo '
							</select>
						</div>
						<div style="clear:both;text-align:right;">
							<input class="button_submit smalltext" type="button" value="'. $txt['modify_cancel'] .'" name="" onclick="SubforumsSubmit(\'sf_cancel\', \'0\')" />&nbsp;
							<input class="button_submit smalltext" type="button" value="'. $txt['save'] .'" name="" onclick="SubforumsSubmit(\'sf_save\', \''. $context['subforums']['data']['id'] .'\')" />
						</div>
					</td>
				</tr>';
	}

	echo '
			</table>
			<span class="botslice"><span></span></span>
		</form>
	</div>
	</div>

	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function SubforumsSwitchto(host)
		{
			var oldhost = window.location.hostname;
			window.location.href = window.location.href.replace(oldhost, host);
		}
		function SubforumsSubmit(sfName, sfVal, sfMsg)
		{
			if(sfMsg)
			{
				if(!confirm(sfMsg) == true)
				return;
			}
			document.getElementById("SubforumsAction").name = sfName;
			document.getElementById("SubforumsAction").value = sfVal;
			document.getElementById("SubforumsID").submit();
		}';

	if(empty($context['subforums']['action']))
	{
		echo '
		function SubforumsSetpos(elm)
		{
			var divpos = document.getElementById("tr." +elm).offsetHeight - document.getElementById("div." +elm).offsetHeight;
			document.getElementById("pos." +elm).style.top = (divpos - 37) +"px";
		}';

		foreach($context['subforums']['data'] as $part)
			echo '
		SubforumsSetpos("'. $part['host'] .'");';
	}

	echo '
	// ]]></script>';
}
?>
