<?php
if (!defined('ABSPATH')) {
	die('Nope, you\'re cheating');
}
/*
 * Elections Database Project
 * include.php
 * Aditya Bhandaru, July 2010
 * Version 1.0
*/
//connect to database
include_once("db_conn.php");
//global vars
define("CANDIDATES", "c");
define("QUESTIONS", "q");
$ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$date_fields = array("month", "day", "year");
$c_fields = array("name", "position", "party", "address", "notes", "votes_pct1", "votes_pct2", "votes_pct3", "votes_pct4", "votes_pct5");
$q_categories = array("yes", "no", "blank");
$q_votes = array("pct1", "pct2", "pct3", "pct4", "pct5");
//
//set up functions
//
function mkOfficialDiv() {
	$status_box = "<div id=\"status-box\" class=\"official\">\n"
	. "<b>This is an official record.</b>\n"
	. "<br />\nPlease make revisions very carefully.\n"
	. "</div>\n";
	return $status_box;
}
function mkUnofficialDiv($err = null) {
	$status_box = "<div id=\"status-box\" class=\"unofficial\">\n"
	. "<b>This election record is not official.</b>\n"
	. "<br />The election may have not yet occured, or the record is undergoing revision.";
	if($err != null && count($err) > 0) {
		//dipslay errors
		$status_box .= "<div style=\"margin: 4px 0px\">\n"
		. "<img src=\"/lib/icons/error.png\" />\n"
		. "The following errors were found during validation\n"
		. "</div>\n"
		. "<ul>\n";
		foreach($err as $e) $status_box .= "<li>".$e."</li>\n";
		$status_box .= "</ul>\n";
	}
	$status_box .= "</div>\n";
	return $status_box;
}
function mkDropDown($arr, $name, $selected = "") {
	global $date_fields;
	if(in_array($name, $date_fields)) $save_action = "saveDate";
	else if($name == "status")        $save_action = "validateForm";
	else                              $save_action = "saveField";
	$str = "<select name=\"".$name."\" id=\"".$name."\" onChange=\"".$save_action."('".$name."')\">\n";
	foreach($arr as $v) {
		$v = stripslashes($v);
		$str .= "	<option value=\"".$v."\"".(($v == $selected) ? " selected" : "").">".$v."</option>\n";
	}
	$str .= "</select>\n";
	return $str;
}
function mkTextArea($str, $name, $rows = 5, $cols = "103") {
	$txt = "<textarea name=\"".$name."\" id=\"".$name."\" rows=\"".$rows."\" cols=\"".$cols."\" onBlur=\"saveField('".$name."')\">"
		. stripslashes($str)
		. "</textarea>\n";
	return $txt;
}
function mkCTable($e_id) {
	global $c_fields;
	$c_table = "";
	$c_i = 0;
	$c_query = mysql_query("SELECT * FROM `candidates` WHERE `election_id` = ".$e_id." ORDER BY `position`, `party`, `name`");
	$office = "NONE";
	$office_id = 0;
	while($c = mysql_fetch_array($c_query)) {
		$c_j = 0;
		if($office != $c['position']) {
			$office = $c['position'];
			$office_id++;
			//spacer
			if($c_i > 0) $c_table .= "<tr class=\"head\"><td colspan=\"12\"></td></tr>\n";
			//draw header
			$c_table .= mkCHeader($office_id, $office);
			//label the first row in the section, so we can insert rows at the top	
			$find_1st_row = " c-".$office_id."-first";
		} else //there was no section change
			$find_1st_row = "";
		$row_id = CANDIDATES."-".$c['id'];
		$c_table .= "<tr id=\"".$row_id."\" class=\"".(($c_i % 2 == 0) ? "bgD" : "bgL").$find_1st_row."\">\n";
		$c_table .= "<td><img src=\"/lib/icons/user_delete.png\" title=\"Delete Candidate\" onClick=\"deleteRow('".$row_id."')\" /></td>";
		//draw all the cells
		foreach($c_fields as $v) {
			$c_table .= mkCellAddr($c_i, $c_j, $c, $v, CANDIDATES);
			$c_j++;
		}
		$c_table .= "</tr>\n";
		$c_i++;
	}
	return $c_table;
}
function mkQTable($e_id) {
	global $q_categories, $q_votes;
	$q_table = "";
	$q_i = 0;
	$q_query = mysql_query("SELECT * FROM `ballot_questions` WHERE `election_id` = ".$e_id." ORDER BY `question_number`");
	while($q = mysql_fetch_array($q_query)) {
		//question header
		$row_id = QUESTIONS."-".$q['id'];
		$q_table .= "<tbody id=\"".$row_id."\">\n";
		$q_table .= "<tr class=\"subhead\">
<td width=\"16\"><img src=\"/lib/icons/delete.png\" title=\"Delete Ballot Question\" onClick=\"deleteRow('".$row_id."')\" /></td>
<td>Ballot Question Number</td>\n"
. mkCellAddr($q_i, 0, $q, "question_number", QUESTIONS)
. "<td colspan=\"4\" class=\"not-bold\">(Determines the order of the questions)</td>
</tr>\n";
		$q_i++;
		//question text area
		$question_ref = QUESTIONS."-question-".$q['id'];
		$question_addr = QUESTIONS."-".$q_i."-0";
		$q_table .= "<tr>
<td id=\"".$question_addr."\" class=\"cell\" data=\"".$question_ref."\" colspan=\"7\">\n".mkCellTextArea($question_addr, QUESTIONS, $q_i, 0, $q['question'])."</td>
</tr>\n";
		$q_i++;
		//votes table
		$q_table .= "<tr class=\"head\">\n<td colspan=\"2\">Ballot Question Results</td>\n<td>Pct1</td>\n<td>Pct2</td>\n<td>Pct3</td>\n<td>Pct4</td>\n<td>Pct5</td>\n</tr>";
		foreach($q_categories as $cat) {
			$q_j = 0;
			$q_table .= "<tr class=\"".(($q_i % 2 == 0) ? "bgD" : "bgL")."\">\n"
				. "<td class=\"bold\" colspan=\"2\">".$cat."</td>\n";
			foreach($q_votes as $v) {
				$field = $cat."_".$v;
				$q_table .= mkCellAddr($q_i, $q_j, $q, $field, QUESTIONS);
				$q_j++;
			}
			$q_i++;
		}
		$q_table .= "</tr>\n"
		. "<tr class=\"subhead\"><td colspan=\"8\"></td></tr>\n"
		. "<tr class=\"spacer\"><td colspan=\"8\"></td></tr>\n"
		. "</tbody>\n";
	}
	return $q_table;
}
function mkCellAddr($i, $j, $arr, $field, $pre, $len = 30) {
	$cell = $pre."-".$i."-".$j;
	$ref = $pre."-".$field."-".$arr['id'];
	$val = $arr[$field];
	return "<td id=\"".$cell."\" class=\"cell\" data=\"".$ref."\">\n" . mkCellInput($cell, $pre, $i, $j, $val, $len) . "</td>\n";
}
function mkCellInput($cell, $pre, $i, $j, $val, $len = 30) {
	$size = max(1, min(strlen($val) - 2, $len));
	return "	<input type=\"text\" size=\"".$size."\" id=\"".$cell."-input\" value=\"".$val."\"
		onKeyDown=\"keyCheck(event, '".$pre."', ".$i.", ".$j.", this.value)\"
		onKeyUp=\"autoComplete(event, '".$cell."')\"
		onBlur=\"saveCell('".$cell."')\"
	/>\n";
}
function mkCellTextArea($cell, $pre, $i, $j, $val) {
	return "	<textarea id=\"".$cell."-input\"
		rows=\"5\"
		onBlur=\"saveCell('".$cell."')\">".stripslashes($val)."</textarea>\n";
}
function mkCHeader($office_id, $office = "") {
	if($office == "") $title = "";
	else $title =  " - <span class=\"not-bold\">".subtitle($office, 20)."</span>";
	//draw table header
	return "<tr class=\"head\" id=\"c-".$office_id."\">
<td width=\"16\"><img src=\"/lib/icons/add.png\" title=\"Add Candidate to Position\" onClick=\"addCandidate('".CANDIDATES."-".$office_id."', '".$office."')\" /></td>
<td>Name</td>\n<td>Office".$title."</td>
<td>Party</td>\n<td>Address</td>\n<td>Notes</td>\n<td>Pct 1</td>
<td>Pct 2</td>\n<td>Pct 3</td>\n<td>Pct 4</td>\n<td>Pct 5</td>\n
</tr>\n";
}
function returnError($err, $val) {
	echo "error|".$err."|".$val; exit;
}
function subTitle($str, $max = 40) {
	if(strlen($str) > $max)
		return substr($str, 0, $max - 3) . "...";
	else
		return $str;
}
function numRows($query) {
	$num_query = mysql_query("SELECT COUNT(`id`) FROM ".$query);
	return @mysql_result($num_query, 0, 0);
}
?>