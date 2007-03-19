<?php
/*
Plugin Name: Level2Categories
Plugin URI: http://www.pirex.com.br/wordpress-plugins/
Description: Allows you to create a relationship between User Levels and Categories, so only users with a defined level will be able to post on a chosen category. 
Author: LeoGermani
Version: 0.5
Author URI: http://leogermani.pirex.com.br/
*/ 

// mySQL table
$l2c_table = $table_prefix."level2categories";

l2c_check_table();

function l2c_check_table() {
	global $l2c_table;

	$query=mysql_query("SHOW TABLES");

	while ($fetch=mysql_fetch_array($query)) {
		if ($fetch[0] == $calendar_table) { return; }
	}
	// table does not exists. creating.
	mysql_query("

		CREATE TABLE `$l2c_table` (
		  `cat_ID` int(11) NOT NULL default '0',
		  `level` int(11) NOT NULL default '0',
		  PRIMARY KEY  (`cat_ID`),
		  UNIQUE KEY `cat_ID` (`cat_ID`)
		) TYPE=MyISAM COMMENT='levels2catefories plugin by LeoGermani'
	");	


}

// administration panel

function l2c_admin() {
	if (function_exists('add_management_page')) {
		add_management_page('Levels2Categories Options', 'Levels2Categories', 8, basename(__FILE__), 'levels2categories_admin_page');
	}
}

function levels2categories_admin_page() {
	global $l2c_table, $table_prefix;

	if (isset($_POST['submit_level'])) {


		echo "<div class=\"updated\"><p><strong>";
		
		$l2c_cat_ID=$_POST['level2cat_select'];
		$l2c_level=$_POST['level'];
			
		$queryCount=mysql_query("SELECT * FROM $l2c_table WHERE cat_ID = $l2c_cat_ID ");
		$existCat=mysql_num_rows($queryCount);		
		
		if ($existCat > 0) {
			mysql_query("UPDATE $l2c_table SET level = $l2c_level WHERE cat_ID = $l2c_cat_ID");
			_e('Category Level updated!','');
		}
		else { 
			mysql_query("INSERT INTO $l2c_table VALUES($l2c_cat_ID, $l2c_level)");
			_e('Category Level added!','');
		}

		echo "</strong></p></div>";
	}
	
	
	if (isset($_POST['delete_level'])) {
		if(isset($_POST['l2c_delete'])){		

			$levels_to_delete=implode(",",$_POST['l2c_delete']);
			mysql_query("DELETE FROM $l2c_table WHERE cat_ID IN ($levels_to_delete)");
			echo "<div class=\"updated\"><p><strong>";
			_e('Setting(s) deleted!','');
			echo "</strong></p></div>";
		}
	}
	?>



	<div class=wrap>
	  <form name="l2c" method="post">
	    <h2>Assign Level to Categories</h2>

		<div style="height: 200px; width: 95%; overflow: auto; margin: 0px;">
			<table cellspacing="0" cellpadding="0" border="0">
			<tr><td><h3>Category</h3></td><td><h3>Minimum Level a user must have to post on this category</h3></td></tr>
				
	<?
	$l2c_category_table=$table_prefix."categories";
	$query=mysql_query("SELECT cat_ID, cat_name FROM $l2c_category_table ORDER BY cat_name");
	$cat_total=mysql_num_rows($query);
	if ($cat_total > 0) {

		echo "<tr><td>";		
		echo "<select name=\"level2cat_select\">";

		while ($fetch=mysql_fetch_array($query)) {

			echo "<option value=\"".$fetch["cat_ID"]."\">".$fetch["cat_name"]."</option>";

		}

		echo "</select></td><td align=\"right\"><select name=\"level\">";

		for ($i = 1; $i <= 10; $i++) {
		   echo "<option value=\"".$i."\">".$i."</option>";
		}

		echo "</select>";
	

	}

	$querystring="SELECT ".$l2c_table.".cat_ID, ".$l2c_table.".level, ".$l2c_category_table.".cat_name as cat_name FROM ".$l2c_table." INNER JOIN ".$l2c_category_table." ON ".$l2c_table.".cat_ID = ".$l2c_category_table.".cat_ID";		
	$query=mysql_query($querystring);
	$cat_total=mysql_num_rows($query);
	if ($cat_total > 0) {

		echo "<tr><td colspan=\"2\"><h3>Categories already assigned to levels</h3> (just use the fields above if you want to change a category level)<BR><br></td></tr>";		
		
		while ($fetch=mysql_fetch_array($query)) {

			echo "<tr><td><input type=\"checkbox\" name=\"l2c_delete[]\" value=\"".$fetch["cat_ID"]."\"> ".$fetch["cat_name"]."</td><td align=\"right\">".$fetch["level"]."</td></tr>";

		}

			

	}




	?>
	</table>
	</div>
	<BR><BR>
	<div class="submit">
	<input type="submit" name="submit_level" value="<?php _e('Add/Update Settings', '') ?> &raquo;">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<? if($cat_total>0) { ?>
	<input type="submit" name="delete_level" value="<?php _e('Remove Selected Settings', '') ?> &raquo;">
	<? } ?>
	</div>
	  </form>
	</div>

	<?php

}



function l2c_disable_cats(){
	global $l2c_table, $user_level;
	$query=mysql_query("SELECT * FROM $l2c_table");
	$cat_total=mysql_num_rows($query);
	if ($cat_total > 0) {
		echo "<script>";		
		while ($fetch=mysql_fetch_array($query)) {
			if ($user_level<$fetch['level']){
				echo "document.getElementById('category-".$fetch['cat_ID']."').style.display='none';";
			}
		}
		echo "</script>";
	}
}


add_action('simple_edit_form', 'l2c_disable_cats');
add_action('edit_form_advanced', 'l2c_disable_cats');
add_action('admin_menu', 'l2c_admin');
?>
