<form action="./index.php" method='get' name="modProjects">
  <input type='hidden' name='m' value='projects' />
  <input type='hidden' name='a' value='view' />
  <input type='hidden' name='project_id' />
</form>
<?php
global $AppUI, $carr, $carrWidth, $contact_types, $currentTabId, $currentTabName, $showfields, $tdw, $x, $z;
?>
<table width="100%" border="0" cellpadding="1" cellspacing="1" height="400" class="contacts">
<tr>
<?php
	for ($z=0; $z < $carrWidth; $z++) {
?>
	<td valign="top" align="left" bgcolor="#f4efe3" width="<?php echo $tdw;?>%">
	<?php
		for ($x=0; $x < @count($carr[$z]); $x++) {
	?>
		<table width="100%" cellspacing="1" cellpadding="1">
		<tr>
			<td width="100%">
                                <?php $contactid = $carr[$z][$x]['contact_id']; ?>
				<a href="./index.php?m=contacts&a=view&contact_id=<?php echo $contactid; ?>"><strong><?php echo $carr[$z][$x]['contact_first_name'] . ' ' . $carr[$z][$x]['contact_last_name'];?></strong></a>&nbsp;
				&nbsp;<a  title="<?php echo $AppUI->_('Export vCard for').' '.$carr[$z][$x]["contact_first_name"].' '.$carr[$z][$x]["contact_last_name"]; ?>" href="?m=contacts&a=vcardexport&suppressHeaders=true&contact_id=<?php echo $contactid; ?>" ><?php echo dPshowImage('./images/obj/contact.gif', 12, 12, ''); ?></a>
                                &nbsp;<a title="<?php echo $AppUI->_('Edit'); ?>" href="?m=contacts&a=addedit&contact_id=<?php echo $contactid; ?>"><?php echo dPshowImage('./images/icons/pencil.gif', 12, 12, ''); ?></a>
<?php
$q  = new DBQuery;
$q->addTable('projects');
$q->addQuery('count(*)');
$q->addWhere("project_contacts like \"" .$carr[$z][$x]["contact_id"]
	.",%\" or project_contacts like \"%," .$carr[$z][$x]["contact_id"] 
	.",%\" or project_contacts like \"%," .$carr[$z][$x]["contact_id"]
	."\" or project_contacts like \"" .$carr[$z][$x]["contact_id"] ."\"");
	
 $res = $q->exec();
 $projects_contact = db_fetch_row($res);
 $q->clear();
 if ($projects_contact[0]>0)
   echo "				&nbsp;<a href=\"\" onClick=\"	window.open('./index.php?m=public&a=selector&dialog=1&callback=goProject&table=projects&user_id=" .$carr[$z][$x]["contact_id"] ."', 'selector', 'left=50,top=50,height=250,width=400,resizable')
;return false;\">".$AppUI->_('Projects')."</a>";
?>
			</td>
		</tr>
		<tr>
			<td class="hilite">
			<?php
				reset($showfields);
				while (list($key, $val) = each($showfields)) {
					if (mb_strlen($carr[$z][$x][$key]) > 0) {
						if ($val == "contact_email") {
						  echo "<A HREF='mailto:{$carr[$z][$x][$key]}' class='mailto'>{$carr[$z][$x][$key]}</a>\n";
                        } else if ($val == "contact_company" && is_numeric($carr[$z][$x][$key])) {
						} else {
						  echo  $carr[$z][$x][$key]. "<br />";
						}
					}
				}
			?>
			</td>
		</tr>
		</table>
		<br />&nbsp;<br />
	<?php }?>
	</td>
<?php }?>
</tr>
</table>
