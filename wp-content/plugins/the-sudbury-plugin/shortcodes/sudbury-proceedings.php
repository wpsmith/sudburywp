<?php

/**
 * A shortcode to compile a list of Town Meeting Proceedings
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_TM_Proceedings {

	function __construct() {
		add_action('init', array(&$this, 'init'));
	}

	function init() {
		add_shortcode('proceedings', array(&$this, 'shortcode'));
	}

	function shortcode() { ?>

<div id="proceedings_" class="tab visibletab">
<span class="link expandall" data-target="proceedings">Expand All</span>


<div class="tablecap">
	<h4>2013</h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=9572">Town Meeting Proceedings 2013</a></td>
			<td>The transcripts of the 2013 Town Meetings and Elections</td>
			<td>February 25, 2014<br>
				1595.32 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2012  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=8356">Town Meeting Proceedings 2012</a></td>
			<td>The transcripts of the 2012 Town Meetings and Elections</td>
			<td>February  1, 2013<br>
				1522.12 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2011  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=7323">Town Meeting Proceedings 2011</a></td>
			<td>The transcripts of the 2011 Town Meetings and Elections</td>
			<td>February  6, 2012<br>
				1941.73 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2010  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=6827">Town Meeting Proceedings 2010</a></td>
			<td>The transcripts of the 2010 Town Meetings and Elections</td>
			<td>September 20, 2011<br>
				2058.25 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2009  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5405">Town Meeting Proceedings 2009</a></td>
			<td>The complete proceedings of the 2009 Annual Town Meeting: April 6, May 11,and June 15, 2009</td>
			<td>February 17, 2010<br>
				2270.21 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2008  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=4776">Town Meeting Proceedings 2008</a></td>
			<td>The complete proceedings of the 2008 Annual Town Meeting:April 7, 8,and 9</td>
			<td>February  6, 2009<br>
				1601.07 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2007  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=4420">Town Meeting Proceedings 2007</a></td>
			<td>The complete transcript of the 2007 Town Meetings.</td>
			<td>August 11, 2008<br>
				1339.22 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2006  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=2875">Town Meeting Proceedings 2006</a></td>
			<td>The complete transcript of the 2006 Town Meeting.</td>
			<td>September 12, 2006<br>
				1992.82 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2005  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=2876">Town Meeting Proceedings 2005</a></td>
			<td>The complete transcript of the 2005 Town Meeting.</td>
			<td>September 12, 2006<br>
				692.39 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2004  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=2877">Town Meeting Proceedings 2004</a></td>
			<td>The complete transcript of the 2004 Town Meeting.</td>
			<td>September 12, 2006<br>
				582.07 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2003  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5466">Town Meeting Proceedings 2003</a></td>
			<td>The complete proceedings of the 2003 Annual Town Meeting.  The documents also includes all of the election results for 2003.</td>
			<td>February 19, 2010<br>
				49.09 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2002  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5421">Town Meeting Proceedings 2002</a></td>
			<td>The complete transcript of the 2002 Town Meeting (April 1st and April 2nd).  Also includes Annual Town Election March 25th, State Primary September 17th and State Election November 5th.</td>
			<td>February 10, 2010<br>
				63.13 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2001  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5465">Town Meeting Proceedings 2001</a></td>
			<td>The complete proceedings of the 2001 Annual Town Meeting.  The document also includes all of the election results for 2001.</td>
			<td>February 19, 2010<br>
				44.12 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>2000  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5464">Town Meeting Proceedings 2000</a></td>
			<td>The complete proceedings of the 2000 Annual Town Meeting.  The documents also includes all of the election results for 2000.</td>
			<td>April 17, 2013<br>
				3919.55 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1999  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5463">Town Meeting Proceedings 1999</a></td>
			<td>The complete proceedings of the 1999 Annual Town Meeting.  The documents also includes all of the election results for 1999.</td>
			<td>February 19, 2010<br>
				55.50 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1998  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5462">Town Meeting Proceedings 1998</a></td>
			<td>The complete proceedings of the 1998 Annual Town Meeting.  The documents also includes all of the election results for 1998.</td>
			<td>April 17, 2013<br>
				3243.05 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1997  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5461">Town Meeting Proceedings 1997</a></td>
			<td>The complete proceedings of the 1997 Annual Town Meeting.  The documents also includes all of the election results for 1997.</td>
			<td>April 11, 2014<br>
				6279.46 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1996  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5460">Town Meeting Proceedings 1996</a></td>
			<td>The complete proceedings of the 1996 Annual Town Meeting.  The documents also includes all of the election results for 1996.</td>
			<td>February 19, 2010<br>
				4870.68 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1995  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5459">Town Meeting Proceedings 1995</a></td>
			<td>The complete proceedings of the 1995 Annual Town Meeting.  The documents also includes all of the election results for 1995.</td>
			<td>February 19, 2010<br>
				3892.55 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1994  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5458">Town Meeting Proceedings 1994</a></td>
			<td>The complete proceedings of the 1994 Annual Town Meeting.  The documents also includes all of the election results for 1994.</td>
			<td>February 19, 2010<br>
				51.31 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1993  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5457">Town Meeting Proceedings 1993</a></td>
			<td>The complete proceedings of the 1993 Annual Town Meeting.  The documents also includes all of the election results for 1993.</td>
			<td>February 19, 2010<br>
				42.94 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1992  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5456">Town Meeting Proceedings 1992</a></td>
			<td>The complete proceedings of the 1992 Annual Town Meeting.  The documents also includes all of the election results for 1992.</td>
			<td>February 19, 2010<br>
				4638.26 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1991  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5455">Town Meeting Proceedings 1991</a></td>
			<td>The complete proceedings of the 1991 Annual Town Meeting.  The documents also includes all of the election results for 1991.</td>
			<td>February 19, 2010<br>
				46.43 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1990  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5454">Town Meeting Proceedings 1990</a></td>
			<td>The complete proceedings of the 1990 Annual Town Meeting.  The documents also includes all of the election results for 1990.</td>
			<td>February 19, 2010<br>
				47.26 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1989  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5453">Town Meeting Proceedings 1989</a></td>
			<td>The complete proceedings of the 1989 Annual Town Meeting.  The documents also includes all of the election results for 1989.</td>
			<td>February 19, 2010<br>
				48.56 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1988  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5452">Town Meeting Proceedings 1988</a></td>
			<td>The complete proceedings of the 1988 Annual Town Meeting.  The documents also includes all of the election results for 1988.</td>
			<td>February 19, 2010<br>
				59.02 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1987  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5451">Town Meeting Proceedings 1987</a></td>
			<td>The complete proceedings of the 1987 Annual Town Meeting.  The documents also includes all of the election results for 1987.</td>
			<td>February 19, 2010<br>
				40.36 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1986  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5450">Town Meeting Proceedings 1986</a></td>
			<td>The complete proceedings of the 1986 Annual Town Meeting.  The documents also includes all of the election results for 1986.</td>
			<td>February 19, 2010<br>
				4563.10 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1985  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5448">Town Meeting Proceedings 1985</a></td>
			<td>Proceedings of the 1985 Annual Town Meeting.  The document includes the election results for 1985.</td>
			<td>February 19, 2010<br>
				52.36 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1984  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5447">Town Meeting Proceedings 1984</a></td>
			<td>The complete proceedings of the 1984 Annual Town Meeting.  The documents also includes all of the election results for 1984.</td>
			<td>February 19, 2010<br>
				56.71 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1983  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5446">Town Meeting Proceedings 1983</a></td>
			<td>The complete proceedings of the 1983 Annual Town Meeting.  The documents also includes all of the election results for 1983.</td>
			<td>February 19, 2010<br>
				4631.32 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1982  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5445">Town Meeting Proceedings 1982</a></td>
			<td>The complete proceedings of the 1982 Annual Town Meeting.  The documents also includes all of the election results for 1982.</td>
			<td>February 19, 2010<br>
				52.12 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1981  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5444">Town Meeting Proceedings 1981</a></td>
			<td>The complete proceedings of the 1981 Annual Town Meeting.  The documents also includes all of the election results for 1981.</td>
			<td>February 19, 2010<br>
				57.78 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1980  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5443">Town Meeting Proceedings 1980</a></td>
			<td>The complete proceedings of the 1980 Annual Town Meeting.  The documents also includes all of the election results for 1980.</td>
			<td>February 19, 2010<br>
				65.20 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1979  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5442">Town Meeting Proceedings 1979</a></td>
			<td>The complete proceedings of the 1979 Annual Town Meeting.  The documents also includes all of the election results for 1979.</td>
			<td>February 19, 2010<br>
				94.27 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1978  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5441">Town Meeting Proceedings 1978</a></td>
			<td>The complete proceedings of the 1978 Annual Town Meeting.  The documents also includes all of the election results for 1978.</td>
			<td>February 19, 2010<br>
				83.06 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1977  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5440">Town Meeting Proceedings 1977</a></td>
			<td>The complete proceedings of the 1977 Annual Town Meeting.  The documents also includes all of the election results for 1977.</td>
			<td>February 19, 2010<br>
				93.94 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1976  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5439">Town Meeting Proceedings 1976</a></td>
			<td>The complete proceedings of the 1976 Annual Town Meeting.  The documents also includes all of the election results for 1976.</td>
			<td>February 19, 2010<br>
				3431.73 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1975  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5438">Town Meeting Proceedings 1975</a></td>
			<td>The complete proceedings of the 1975 Annual Town Meeting.  The documents also includes all of the election results for 1975.</td>
			<td>February 19, 2010<br>
				101.15 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1974  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5815">Town Meeting Proceedings 1974</a></td>
			<td>The complete proceedings of the 1974 Annual Town Meeting.  The documents also includes all of the election results for 1974.</td>
			<td>August  2, 2010<br>
				4325.47 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1973  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5437">Town Meeting Proceedings 1973</a></td>
			<td>The complete proceedings of the 1973 Annual Town Meeting.  The documents also includes all of the election results for 1973.</td>
			<td>February 19, 2010<br>
				128.48 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1972  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5436">Town Meeting Proceedings 1972</a></td>
			<td>The complete proceedings of the 1972 Annual Town Meeting.  The document also includes all of the election results for 1972.</td>
			<td>February 19, 2010<br>
				4836.85 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1971</h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=5435">Town Meeting Proceedings 1971</a></td>
			<td>The complete proceedings of the 1971 Annual Town Meeting.  The document also includes all of the election results for 1971.</td>
			<td>February 19, 2010<br>
				253.39 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1964  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=8652">Town Meeting Proceedings 1964</a></td>
			<td>The complete proceedings of the 1964 Annual Town Meeting.  The document also includes all of the election results for 1964.</td>
			<td>April 23, 2013<br>
				1703.68 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1963  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=8651">Town Meeting Proceedings 1963</a></td>
			<td>The complete proceedings of the 1963 Annual Town Meeting.  The document also includes all of the election results for 1963.</td>
			<td>April 23, 2013<br>
				1507.25 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1961  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=8650">Town Meeting Proceedings 1961</a></td>
			<td>The complete proceedings of the 1961 Annual Town Meeting.  The document also includes all of the election results for 1961.</td>
			<td>April 23, 2013<br>
				1335.56 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1960  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=8649">Town Meeting Proceedings 1960</a></td>
			<td>The complete proceedings of the 1960 Annual Town Meeting.  The document also includes all of the election results for 1960.</td>
			<td>April 23, 2013<br>
				1566.69 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>

<div class="tablecap">
	<h4>1959  | <span href="#" class="epl-proceedings link epl-current">Expand</span></h4>

	<table cellspacing="0" style="display: none;">
		<tbody><tr>
			<th width="25%">Title</th>
			<th>Description</th>
			<th width="20%">Info</th>
		</tr>
		<tr>
			<!-- Clerk -->
			<td class="coltitle"><a href="/documents/download.asp?id=8648">Town Meeting Proceedings 1959</a></td>
			<td>The complete proceedings of the 1959 Annual Town Meeting.  The document also includes all of the election results for 1959.</td>
			<td>April 23, 2013<br>
				1807.88 KB</td>

		</tr>

		</tbody></table>
	<div class="foot"></div>
</div>
</div>

<?php

	}


}