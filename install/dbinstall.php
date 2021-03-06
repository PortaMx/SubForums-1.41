<?php
/******************************
* file dbinstall.php          *
* Database tables install     *
* Coypright by PortaMx corp.  *
*******************************/

global $db_prefix, $user_info, $boardurl, $boarddir, $sourcedir, $txt, $dbinstall_string;

// Load the SSI.php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	function _dbinst_write($string) { echo $string; }

	require_once(dirname(__FILE__) . '/SSI.php');

	// on manual installation you have to logged in
	if(!$user_info['is_admin'])
	{
		if($user_info['is_guest'])
		{
			echo '<b>', $txt['admin_login'],':</b><br />';
			ssi_login($boardurl.'/dbinstall.php');
			die();
		}
		else
		{
			loadLanguage('Errors');
			fatal_error($txt['cannot_admin_forum']);
		}
	}
}
// no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> SSI.php not found. Please verify you put this in the same place as SMF\'s index.php.');
else
{
	function _dbinst_write($string)
	{
		global $dbinstall_string;
		$dbinstall_string .= $string;
	}
}

// split of dbname (mostly for SSI)
$pref = explode('.', $db_prefix);
if(!empty($pref[1]))
	$pref = $pref[1];
else
	$pref = $db_prefix;

// Load the SMF DB Functions
db_extend('packages');
db_extend('extra');

/********************
* Define the tables *
*********************/
$tabledate = array(
	// tablename
	'subforums' => array(
		// column defs
		array(
			array('name' => 'id', 'type' => 'int', 'null' => false, 'auto' => true),
			array('name' => 'forum_host', 'type' => 'varchar', 'size' => '128', 'default' => '', 'null' => false),
			array('name' => 'forum_name', 'type' => 'varchar', 'size' => '128', 'default' => '', 'null' => false),
			array('name' => 'cat_order', 'type' => 'varchar', 'size' => '255', 'default' => '', 'null' => false),
			array('name' => 'id_theme', 'type' => 'int', 'default' => 0, 'null' => false),
			array('name' => 'language', 'type' => 'varchar', 'size' => '128', 'default' => '', 'null' => false),
			array('name' => 'acs_groups', 'type' => 'varchar', 'size' => '128', 'default' => '', 'null' => false),
			array('name' => 'reg_group', 'type' => 'int', 'default' => -1, 'null' => false),
			array('name' => 'total_posts', 'type' => 'int', 'default' => 0, 'null' => false),
			array('name' => 'total_topics', 'type' => 'int', 'default' => 0, 'null' => false),
		),
		// index defs (type: primary, unique, index)
		array(
			array('type' => 'primary', 'name' => 'primary', 'columns' => array('id')),
		),
		// options
		array()
	),
);

// loop through each table
$newline = '';
$created = array();

foreach($tabledate as $tblname => $tbldef)
{
	$updconvert = '';

	// check if the table exist
	_dbinst_write($newline .'Processing Table "'. $pref . $tblname .'".<br />');
	$newline = '<br />';
	$exist = false;
	$tablelist = $smcFunc['db_list_tables'](false, $pref. $tblname);
	if(!empty($tablelist) && in_array($pref . $tblname, $tablelist))
	{
		// exist .. check the cols, the type and value
		_dbinst_write('.. Table exist, checking columns and indexes.<br />');
		$exist = true;
		list($cols, $index, $params) = $tbldef;
		$structure = $smcFunc['db_table_structure']('{db_prefix}'. $tblname, true);

		$drop = check_columns($cols, $structure['columns']);
		if(empty($drop))
			$drop = check_indexes($index, $structure['indexes'], $pref . $tblname);

		if(empty($drop))
			_dbinst_write('.. Table successful checked.<br />');
	}

	if(!empty($drop))
	{
		$request = $smcFunc['db_query']('', '
				SELECT id, forum_host, forum_name, cat_order, id_theme, acs_groups
				FROM {db_prefix}subforums
				ORDER BY id',
			array()
		);
		while($row = $smcFunc['db_fetch_assoc']($request))
			$updconvert[] = array(
				'id' => $row['id'],
				'host' => $row['forum_host'],
				'name' => $row['forum_name'],
				'cats' => $row['cat_order'],
				'theme' => $row['id_theme'],
				'language' => '',
				'groups' => $row['acs_groups'],
				'reg_group' => -1,
			);
		$smcFunc['db_free_result']($request);

		// drop table
		$smcFunc['db_drop_table']('{db_prefix}'. $tblname);
		$exist = false;
		_dbinst_write('.. Table not identical, dropped.<br />');
	}

	if(empty($exist))
	{
		// create the table
		$created[] = $tblname;
		list($cols, $index, $params) = $tbldef;
		$smcFunc['db_create_table']('{db_prefix}'. $tblname, $cols, $index, $params, 'error');
		_dbinst_write('.. Table successful created.<br />');

		if(!empty($updconvert))
		{
			foreach($updconvert as $i => $data)
			{
				// get Messages and Topics
				$result = $smcFunc['db_query']('', '
					SELECT SUM(num_posts + unapproved_posts) AS total_posts, SUM(num_topics + unapproved_topics) AS total_topics
					FROM {db_prefix}boards
					WHERE id_cat IN ('. $data['cats'] .')',
					array()
				);
				$row = $smcFunc['db_fetch_assoc']($result);
				$posts = empty($row['total_posts']) ? 0 : $row['total_posts'];
				$topics = empty($row['total_topics']) ? 0 : $row['total_topics'];
				$smcFunc['db_free_result']($result);

				$smcFunc['db_insert']('', '
					{db_prefix}subforums',
					array(
						'forum_host' => 'string',
						'forum_name' => 'string',
						'cat_order' => 'string',
						'id_theme' => 'int',
						'language' => 'string',
						'acs_groups' => 'string',
						'reg_group' => 'int',
						'total_posts' => 'int',
						'total_topics' => 'int',
					),
					array(
						$data['host'],
						$data['name'],
						$data['cats'],
						$data['theme'],
						$data['language'],
						$data['groups'],
						$data['reg_group'],
						$posts,
						$topics,
					),
					array('id')
				);
			}
		}
		_dbinst_write('.. Table successful converted.<br />');
	}
}

// on update setup the dbuninstall string to current version
$dbupdates = array();
foreach($tabledate as $tblname => $tbldef)
{
	if(!in_array($tblname, $created))
		$dbupdates[] = array('remove_table', $pref . $tblname);
}

if(!empty($dbupdates))
{
	$found = array();
	// get last exist version
	$request = $smcFunc['db_query']('', '
		SELECT id_install, themes_installed
		FROM {db_prefix}log_packages
		WHERE package_id LIKE {string:pkgid} AND version LIKE {string:vers}
		ORDER BY id_install DESC
		LIMIT 1',
		array(
			'pkgid' => 'portamx_corp:SubForums%',
			'vers' => '1.%',
		)
	);
	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		$found['id'] = $row['id_install'];
		$found['themes'] = $row['themes_installed'];
	}
	$smcFunc['db_free_result']($request);

	if(!empty($found['id']))
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}log_packages
			SET package_id = {string:pkgid}, db_changes = {string:dbchg},'. (!empty($found['themes']) ? ' themes_installed = {string:thchg},' : '') .' install_state = 1
			WHERE id_install = {int:id}',
			array(
				'id' => $found['id'],
				'pkgid' => 'portamx_corp:SubForums',
				'thchg' => (!empty($found['themes']) ? $found['themes'] : ''),
				'dbchg' => serialize($dbupdates),
			)
		);
	}
}

// setup the hooks we use
_dbinst_write('<br />Setup integration functions.<br />');

$hooklist = array(
	'integrate_pre_include' => '$sourcedir/SubForums/Subforums.php',
	'integrate_admin_areas' => 'Subforums_AdminMenu',
	'integrate_register' => 'Subforums_Register',
);

foreach($hooklist as $hook => $value)
	remove_integration_function($hook, $value);

// get the hooks from database
$smfhooks = array();
$request = $smcFunc['db_query']('', '
	SELECT variable, value FROM {db_prefix}settings
	WHERE variable IN ({array_string:hooks})',
	array('hooks' => array_keys($hooklist))
);
if($smcFunc['db_num_rows']($request) > 0)
{
	while($row = $smcFunc['db_fetch_assoc']($request))
		$smfhooks[$row['variable']] = $row['value'];
	$smcFunc['db_free_result']($request);
}

// update the hooks
foreach($hooklist as $hookname => $value)
{
	if(isset($smfhooks[$hookname]))
		$smfhooks[$hookname] = trim($hooklist[$hookname] .','. trim(str_replace($value, '', $smfhooks[$hookname]), ','), ',');
	else
		$smfhooks[$hookname] = trim($value);

	$smcFunc['db_insert']('replace', '
		{db_prefix}settings',
		array('variable' => 'string', 'value' => 'string'),
		array($hookname, $smfhooks[$hookname]),
		array()
	);
}

// clear the cache
cache_put_data('modSettings', NULL, 90);

_dbinst_write('Setup PortaMx package server.<br />');

// setup Portamx package server
$request = $smcFunc['db_query']('', '
	SELECT id_server
	FROM {db_prefix}package_servers
	WHERE url = {string:url}',
	array(
		'url' => 'http://docserver.portamx.com'
	)
);
if($row = $smcFunc['db_fetch_assoc']($request))
	$smcFunc['db_free_result']($request);
else
{
	$smcFunc['db_insert']('', '
		{db_prefix}package_servers',
		array(
			'name' => 'string',
			'url' => 'string'
		),
		array(
			'PortaMx File Server',
			'http://docserver.portamx.com'
		),
		array('id_server')
	);
}

// done
if(!empty($dbinstall_string))
{
	$filename = str_replace('dbinstall.php', '', __FILE__) .'installdone.html';
	$instdone = file_get_contents($filename);
	$instdone = str_replace('<div></div>', '<div style="text-align:left;"><strong>Database install results:</strong><br />'. $dbinstall_string .'</div>', $instdone);
	$fh = fopen($filename, 'w');
	if($fh)
	{
		fwrite($fh, $instdone);
		fclose($fh);
	}
	else
		log_error($dbinstall_string);
}

/************************
* Column check function *
*************************/
function check_columns($cols, $data)
{
	// col count same?
	if(count($cols) != count($data))
		$drop = true;
	else
	{
		// yes, check each col
		$drop = false;
		foreach($cols as $col)
		{
			if(array_key_exists($col['name'], $data))
			{
				$check = $data[$col['name']];
				foreach($col as $def => $val)
					$drop = (isset($check[$def]) && ($check[$def] == $val || ($check[$def] == "''" && empty($val)))) ? $drop : true;
			}
			else
				$drop = true;
		}
	}
	return $drop;
}

/**
* Index check function
**/
function check_indexes($indexes, $data, $tblname)
{
	// index count same?
	if(count($indexes) != count($data))
		$drop = true;
	else
	{
		// yes, check each index
		$drop = false;
		foreach($indexes as $index => $values)
		{
			// find the index type
			$check = '';
			foreach($data as $fnd)
			{
				if(strcasecmp($fnd['name'], $values['name']) == 0 || strcasecmp($fnd['name'],$tblname .'_'. $values['name']) == 0)
				{
					$check = $fnd;
					$check['name'] = $values['name'];
					break;
				}
				elseif(strcasecmp($fnd['name'], $tblname .'_pkey') == 0 && strtolower($values['name']) == 'primary')
				{
					$check = $fnd;
					$check['name'] = 'primary';
					break;
				}
			}

			// now check the values
			if(!empty($check))
			{
				foreach($values as $def => $value)
				{
					// index cols?
					if(is_array($value))
					{
						if(array_diff($check[$def], $value) != array())
							$drop = true;
					}
					// no, type and name
					elseif((isset($check[$def]) && ($check[$def] == $value || $check[$def] == strtoupper($value))) === false)
						$drop = true;
				}
			}
			else
				$drop = true;
		}
	}
	return $drop;
}
?>