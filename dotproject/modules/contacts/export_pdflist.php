<?php /* CONTACTS $Id$ */
if (!defined('DP_BASE_DIR')) {
  die('You should not access this file directly.');
}

$canRead = getPermission('contacts', 'view');
if (!($canRead)) {
	$AppUI->redirect('m=public&a=access_denied');
}

// load the contact types
$contact_types = dPgetSysVal('UserType');
$contact_types[500] = $AppUI->_('All Contacts');

$contact_type = dPgetParam($_REQUEST, 'contact_type', 500);


$q = new DBQuery;
$q->addQuery('contact_id, contact_identifier, contact_order_by');
$q->addQuery('contact_first_name, contact_last_name, contact_phone, contact_phone2, contact_mobile, contact_email, contact_email2');
$q->addQuery('contact_address1, contact_address2, contact_zip, contact_city, contact_country');
$q->addTable('contacts');
//$q->leftJoin('companies', 'b', 'a.contact_company = b.company_id');
/*foreach ($search_map as $search_name)
        $where_filter .=" OR $search_name LIKE '$where%'";
$where_filter = mb_substr($where_filter, 4);
$q->addWhere("($where_filter $additional_filter)");
$q->addWhere("
	(contact_private=0
		OR (contact_private=1 AND contact_owner=$AppUI->user_id)
		OR contact_owner IS NULL OR contact_owner = 0
	)");
*/
if (isset($contact_type) && $contact_type != 500) {
	$q->addWhere('contact_type = '.$contact_type);
}
/*
if (count($allowedCompanies)) {
	$comp_where = implode(' AND ', $allowedCompanies);
	$q->addWhere('((' . $comp_where . ') OR contact_company = 0)');
}
*/
$q->addOrder('contact_order_by');
$contacts = $q->loadList();
$q->clear();

include_once ($AppUI->getLibraryClass('tcpdf/config/lang/eng'));
include_once ($AppUI->getLibraryClass('tcpdf/tcpdf'));

//var_export($contacts);

// create new PDF document
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('dotproject');
$pdf->SetTitle($AppUI->_('List of').' '. $AppUI->_('Contact Types:').' '.$contact_types[$contact_type]);
$pdf->SetSubject('PDF Contact List');
$pdf->SetKeywords('PDF, dotproject, contacts, list, '.$contact_types[$contact_type]);

// set default header data
$pdf->SetHeaderData('', 0, $AppUI->_('List of').' '. $AppUI->_('Contact Types:').' '.$contact_types[$contact_type], '');

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 

//set some language-dependent strings
$pdf->setLanguageArray($l); 

// ---------------------------------------------------------

// set font
$pdf->SetFont('times', '', 8);

// add a page
$pdf->AddPage(); 

$tbl ='
	<table border="1" cellpadding="2" cellspacing="0">
		<thead>
			<tr style="background-color:#EEEEEE;color:#000000;">
				<td width="40" align="center"><b>'.$AppUI->_('ID').'</b></td>
				  <td width="80" align="center"><b>'.$AppUI->_('Name').'</b></td>
				  <td width="80" align="center"><b>'.$AppUI->_('Vorname').'</b></td>
				  <td width="120" align="center"><b>'.$AppUI->_('Phones').'</b></td>
				  <td width="140" align="center"><b>'.$AppUI->_('Emails').'</b></td>
				  <td width="200" align="center"><b>'.$AppUI->_('Address').'</b></td>
			 </tr>
		</thead>';

foreach ($contacts as $contact) {

	//preparing contact details
	$phones =  $contact['contact_phone'];
	if ($contact['contact_phone2'] != '') {
	$phones .= '<br/>'.$contact['contact_phone2'];
	}
	if ($contact['contact_mobile'] != '') {
	$phones .= '<br/>'.$contact['contact_mobile'];
	}

	$emails =  '<a href="mailto:'.$contact['contact_email'].'">'.$contact['contact_email'].'</a>';
	if ($contact['contact_email2'] != '') {
	$emails .= '<br/><a href="mailto:'.$contact['contact_email2'].'">'.$contact['contact_email2'].'</a>';
	}

	$address =  $contact['contact_address1'];
	if ($contact['contact_address2'] != '') {
	$address .= ', '.$contact['contact_address2'];
	}
	if ($contact['contact_zip'] != '' || $contact['contact_city'] != '') {
	$address .= '<br/>'.$contact['contact_zip'].' '.$contact['contact_city'];
	}
	if ($contact['contact_country'] != '') {
	$address .= ', '.$contact['contact_country'];
	}


	$tbl .='
			<tr>
				<td width="40" align="left">'.$contact['contact_identifier'].'</td>
				<td width="80" align="left">'.$contact['contact_last_name'].'</td>
				<td width="80" align="left">'.$contact['contact_first_name'].'</td>
				<td width="120" align="left">'.$phones.'</td>
				<td width="140" align="left">'.$emails.'</td>
				<td width="200" align="left">'.$address.'</td>
			</tr>';
}

$tbl .='</table>';

$pdf->writeHTML($tbl, true, false, false, false, '');

// ---------------------------------------------------------
$now = new CDate();
//Close and output PDF document
$pdf->Output('contacts_'.$now->format(FMT_TIMESTAMP).'.pdf', 'D'); 

?>
