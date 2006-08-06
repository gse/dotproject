<?php /* PUBLIC $Id$ */
require_once($baseDir . '/classes/ui.class.php');
require_once($baseDir . '/modules/calendar/calendar.class.php');

$callback 	= isset($_GET['callback']) ? $_GET['callback'] : 0;
$date 			= dpGetParam($_GET, 'date', null);
$prev_date 	= dpGetParam($_GET, 'uts', null);

$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : $dPconfig['host_style'];

// if $date is empty, set to null
$date = $date !== '' ? $date : null;

$this_month = new CDate($date);

$cal = new CMonthCalendar( $this_month );
$cal->setStyles('poptitle', 'popcal');
$cal->showWeek = false;
$cal->callback = $callback;
$cal->setLinkFunctions( 'clickDay' );

if(isset($prev_date))
{
	$highlights = array($prev_date => '#FF8888');
	$cal->setHighlightedDays($highlights);
	$cal->showHighlightedDays = true;
}


//$months = array();
$i = 0;
while ($i < 12) 
{
	$this_month->setMonth( ++$i );
	$months[$this_month->format( FMT_TIMESTAMP_DATE )] = substr($this_month->format('%b'), 0, 1);
}

$tpl->assign('months', $months);
$tpl->assign('calendar', $cal->show());
$tpl->assign('callback', $callback);
$tpl->assign('previous_year', $cal->prev_year->format(FMT_TIMESTAMP_DATE));
$tpl->assign('previous_year_display', $cal->prev_year->getYear());
$tpl->assign('next_year', $cal->next_year->format(FMT_TIMESTAMP_DATE));
$tpl->assign('next_year_display', $cal->next_year->getYear());
$tpl->assign('prev_date', $prev_date);

$tpl->displayFile('calendar');