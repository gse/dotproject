<?php /* $Id$ */
if (!defined('DP_BASE_DIR')) {
  die('You should not access this file directly.');
}

$AppUI->savePlace();

if (!($canAccess)) {
	$AppUI->redirect('m=public&a=access_denied');
}

// End of project status update
// retrieve any state parameters
if (isset($_GET['tab'])) {
	$AppUI->setState('ContactsIdxTab', $_GET['tab']);
}

$tab = $AppUI->getState('ContactsIdxTab') !== NULL ? $AppUI->getState('ContactsIdxTab') : 500;
//$currentTabId = $tab;
$active = intval(!$AppUI->getState('ContactsIdxTab'));


// load the contact types
$contact_types = dPgetSysVal('UserType');

// To configure an aditional filter to use in the search string
$additional_filter = "";
// retrieve any state parameters
if (isset($_GET['where'])) {
	$AppUI->setState('ContIdxWhere', $_GET['where']);
}
if (isset($_GET["search_string"])) {
	$AppUI->setState ('ContIdxWhere', "%".$_GET['search_string']);
				// Added the first % in order to find instrings also
	$additional_filter = "OR contact_first_name like '%{$_GET['search_string']}%'
	                      OR contact_last_name  like '%{$_GET['search_string']}%'
						  OR company_name       like '%{$_GET['search_string']}%'
						  OR contact_notes      like '%{$_GET['search_string']}%'
						  OR contact_email      like '%{$_GET['search_string']}%'";
}
$where = $AppUI->getState('ContIdxWhere') ? $AppUI->getState('ContIdxWhere') : '%';

$orderby = 'contact_order_by';

// Pull First Letters
$let = ":";
$search_map = array($orderby, 'contact_first_name', 'contact_last_name');
foreach ($search_map as $search_name)
{
	$q  = new DBQuery;
	$q->addTable('contacts');
	$q->addQuery("DISTINCT UPPER(SUBSTRING($search_name,1,1)) as L");
	$q->addWhere("contact_private=0 OR (contact_private=1 AND contact_owner=$AppUI->user_id)
								OR contact_owner IS NULL OR contact_owner = 0");
	$arr = $q->loadList();
	foreach ($arr as $L)
		$let .= $L['L'];
}

// optional fields shown in the list (could be modified to allow breif and verbose, etc)
$showfields = array(
	// "test" => "concat(contact_first_name,' ',contact_last_name) as test",    why do we want the name repeated?
    "contact_company" => "contact_company",
	"company_name" => "company_name",
	"contact_phone" => "contact_phone",
	"contact_email" => "contact_email"
);

require_once $AppUI->getModuleClass('companies');
$company =& new CCompany;
$allowedCompanies = $company->getAllowedSQL($AppUI->user_id);

// assemble the sql statement
$q = new DBQuery;
$q->addQuery('contact_id, contact_order_by');
$q->addQuery($showfields);
$q->addQuery('contact_first_name, contact_last_name, contact_phone');
$q->addTable('contacts', 'a');
$q->leftJoin('companies', 'b', 'a.contact_company = b.company_id');
foreach ($search_map as $search_name)
        $where_filter .=" OR $search_name LIKE '$where%'";
$where_filter = mb_substr($where_filter, 4);
$q->addWhere("($where_filter $additional_filter)");
$q->addWhere("
	(contact_private=0
		OR (contact_private=1 AND contact_owner=$AppUI->user_id)
		OR contact_owner IS NULL OR contact_owner = 0
	)");
if ($tab != 500) {
	$q->addWhere('contact_type = '.$tab);
}
if (count($allowedCompanies)) {
	$comp_where = implode(' AND ', $allowedCompanies);
	$q->addWhere('((' . $comp_where . ') OR contact_company = 0)');
}
$q->addOrder('contact_order_by');

$carr[] = array();
$carrWidth = 4;
$carrHeight = 4;

$sql = $q->prepare();
$q->clear();
$res = db_exec($sql);
if ($res)
	$rn = db_num_rows($res);
else {
	echo db_error();
	$rn = 0;
}

$t = floor($rn / $carrWidth);
$r = ($rn % $carrWidth);

if ($rn < ($carrWidth * $carrHeight)) {
	for ($y=0; $y < $carrWidth; $y++) {
		$x = 0;
		//if ($y<$r)	$x = -1;
		while (($x<$carrHeight) && ($row = db_fetch_assoc($res))) {
			$carr[$y][] = $row;
			$x++;
		}
	}
} else {
	for ($y=0; $y < $carrWidth; $y++) {
		$x = 0;
		if ($y<$r)	$x = -1;
		while (($x<$t) && ($row = db_fetch_assoc($res))) {
			$carr[$y][] = $row;
			$x++;
		}
	}
}

$tdw = floor(100 / $carrWidth);

/**
* Contact search form
*/
 // Let's remove the first '%' that we previously added to ContIdxWhere
$default_search_string = dPformSafe(mb_substr($AppUI->getState('ContIdxWhere'), 1, mb_strlen($AppUI->getState('ContIdxWhere'))), true);

$form = "<form action='./index.php' method='get'>".$AppUI->_('Search for').'
           <input type="text" name="search_string" value="'.$default_search_string.'" />
		   <input type="hidden" name="m" value="contacts" />
		   <input type="submit" value=">" />
		   <a href="./index.php?m=contacts&amp;search_string=">'.$AppUI->_('Reset search').'</a>
		 </form>';
// En of contact search form

$a2z = "\n<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
$a2z .= "\n<tr>";
$a2z .= "<td width='100%' align='right'>" . $AppUI->_('Show'). ": </td>";
$a2z .= '<td><a href="./index.php?m=contacts&where=0">' . $AppUI->_('All') . '</a></td>';
for ($c=65; $c < 91; $c++) {
	$cu = chr($c);
	$cell = mb_strpos($let, "$cu") > 0 ?
		"<a href=\"?m=contacts&where=$cu\">$cu</a>" :
		"<font color=\"#999999\">$cu</font>";
	$a2z .= "\n\t<td>$cell</td>";
}
$a2z .= "\n</tr>\n<tr><td colspan='28'>$form</td></tr></table>";


// setup the title block

$titleBlock = new CTitleBlock('Contacts', 'monkeychat-48.png', $m, "$m.$a");
$titleBlock->addCell($a2z);
if ($canAuthor) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new contact').'">', '',
		'<form action="?m=contacts&a=addedit" method="post">', '</form>'
	);
	$titleBlock->addCrumbRight(
		'<a href="./index.php?m=contacts&a=csvexport&suppressHeaders=true">' . $AppUI->_('CSV Download'). "</a> | " .
		'<a href="./index.php?m=contacts&a=vcardimport&dialog=0">' . $AppUI->_('Import vCard') . '</a>'
	);
}
$titleBlock->show();

?>
<script language="javascript">
// Callback function for the generic selector
function goProject(key, val) {
	var f = document.modProjects;
	if (val != '') {
		f.project_id.value = key;
		f.submit();
        }
}
</script>

<?php
$tabBox = new CTabBox('?m=contacts', DP_BASE_DIR . '/modules/contacts/', $tab);
$tabBox->add('vw_idx_list', $AppUI->_('All') , true,  500);
foreach ($contact_types as $psk => $project_status) {
		$tabBox->add('vw_idx_list', 
					 (($project_status_tabs[$psk]) ? $project_status_tabs[$psk] : $AppUI->_($project_status)), true, $psk);
}

$min_view = true;
$tabBox->show();
?>
