<?php
/*
Plugin Name: NaNoWriMo Report Card
Plugin URI: http://davidized.com/
Description: Makes it easy to keep track of your novel progress on your wordpress powered blog.
Version: 0.2
Author: David Williamson
Author URI: http://davidized.com/
*/

function nanorc_init() { // This is so everything loads first

add_option("nanorc_db_version", "0.2");
add_action('admin_menu', 'nanorc_add_pages');

add_option('nanorc_title', 'Untitled', 'Title of your Novel', 'yes');
add_option('nanorc_author', '', 'Probably your name, or a psudonym', 'yes');
add_option('nanorc_wcgoal', '50000', 'Your word count goal (numbers only, please)', 'yes');


function nanorc_add_pages() {
     add_submenu_page('index.php', 'NaNoWriMo', 'NaNoWriMo', 8, 'nanorc.php', 'nanorc_dashboard_page');

} // End: function nanorc_add_page()


function nanorc_install () {
   global $table_prefix, $user_level;

   $table_name = $table_prefix . "nanorc";

   get_currentuserinfo();
   if ($user_level < 8) { return; }
      $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  day int(2) DEFAULT '0' NOT NULL,
	  totalwc int(8) DEFAULT '0' NOT NULL,
	  hours float(5) DEFAULT '0' NOT NULL,
	  sessions int(8) DEFAULT '0' NOT NULL,
	  scenes float(5) DEFAULT '0' NOT NULL,
	  UNIQUE KEY id (id)
	);";

    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
    dbDelta($sql);

} // End: function nanorc_install()




function nanorc_dashboard_page() {
	global $table_prefix, $wpdb;
// A couple needed variables
   $table_name = $wpdb->prefix . "nanorc";

   if ( isset($_GET['delete']) ) {

     		 $sql = "DELETE FROM " . $table_name . " WHERE day='" . $_GET['delete'] . "' LIMIT 1";
		      $results = $wpdb->query( $sql );
?>
<div id="message" class="updated fade"><p><strong>Report Card updated.</strong></p></div>
<?php
		}

	if ( isset($_GET['edit']) && !isset($_GET['updated']) ) {

	$sql = "SELECT day, totalwc, hours, sessions, scenes FROM `$table_name` WHERE day = '" . $_GET['edit'] . "'";

	$data = $wpdb->get_results($sql);
?>
<div class="wrap">
<h2>NaNoWriMo Report Card</h2>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . basename(__FILE__) . '&edit=' . $_GET['edit']; ?>&updated=true">

<table class="widefat">
 <thead>
  <tr>
	<th scope="col">Day</th scope="col">
	<th>Total WC</th>
	<th scope="col">Hours</th>
	<th scope="col">Sessions</th>
	<th scope="col">Scenes Complete</th>
        <th></th>
  </tr>
 </thead>
 <tbody id="the-list">


<?php
foreach ($data as $data) {
?>

<tr>
 <th scope="row"><?php echo $data->day; ?><input type="hidden" name="nanorc_day" value="<?php echo $data->day; ?>" /></th>
 <td><input type="text" name="nanorc_totalwc" size="8" value="<?php echo $data->totalwc; ?>" /></td>
 <td><input type="text" name="nanorc_hours" size="5" value="<?php echo $data->hours; ?>" /></td>
 <td><input type="text" name="nanorc_sessions" size="5" value="<?php echo $data->sessions; ?>" /></td>
 <td><input type="text" name="nanorc_scenes" size="5" value="<?php echo $data->scenes; ?>" /></td>
 <td class="submit"><input type="submit" name="submit" value="<?php _e('Update') ?>" /></td>
</tr>

<?php
} // End foreach
?>
	</tbody>
</table>
</div>
<?php
exit; // so it doesn't display the rest of the page






	} // if editing

	if (isset($_POST['submit']) ) {
 
             if (isset($_POST['nanorc_title']) && isset($_POST['nanorc_author']) && isset($_POST['nanorc_wcgoal'])) {
		update_option('nanorc_title', $_POST['nanorc_title']);
		update_option('nanorc_author', $_POST['nanorc_author']);
		update_option('nanorc_wcgoal', $_POST['nanorc_wcgoal']);
              } // End if options are updated

		if (!empty($_POST['nanorc_day']) && !empty($_POST['nanorc_totalwc']) && !empty($_POST['nanorc_hours']) && !empty($_POST['nanorc_sessions']) && !empty($_POST['nanorc_scenes'])) {

		$new_day = $wpdb->escape($_POST['nanorc_day']);
		$new_totalwc = $wpdb->escape($_POST['nanorc_totalwc']);
		$new_hours = $wpdb->escape($_POST['nanorc_hours']);
		$new_sessions = $wpdb->escape($_POST['nanorc_sessions']);
		$new_scenes = $wpdb->escape($_POST['nanorc_scenes']);

     		 if (isset($_GET['edit'])) {
     		 $sql = "UPDATE " . $table_name .
		            " SET totalwc = '" . $new_totalwc . "', hours = '" . $new_hours . "', sessions = '" . $new_sessions . "', scenes = '" . $new_scenes . "' WHERE day = '" . $_GET['edit'] . "'";
     		 } else {
     		 $sql = "INSERT INTO " . $table_name .
		            " (day, totalwc, hours, sessions, scenes) " .
		            "VALUES ('" . $new_day . "','" . $new_totalwc . "','" . $new_hours . "','" . $new_sessions . "','" . $new_scenes . "')";
     		 } // End else (isset($_GET['edit']))
		      $results = $wpdb->query( $sql );
?>
<div id="message" class="updated fade"><p><strong>Report Card updated.</strong></p></div>
<?php
		} else {
?>
<div id="message" class="error"><p><strong>Error.</strong></p></div>
<?php
		}

?>

<?php
	} // if submitted
?>
<div class="wrap">
<h2>NaNoWriMo Report Card</h2>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . basename(__FILE__); ?>&updated=true">

<?php 

// Pull the options from the database
$nanorc_title = get_option('nanorc_title'); 
$nanorc_author = get_option('nanorc_author');
$nanorc_wcgoal = get_option('nanorc_wcgoal');

?>

<p style="font-weight: bold;">
<label for="nanorc_title">Title</label>: <input type="text" name="nanorc_title" value="<?php echo $nanorc_title; ?>" /><br />

<label for="nanorc_author">Author</label>: <input type="text" name="nanorc_author" value="<?php echo $nanorc_author; ?>" /><br />

<label for="nanorc_wcgoal">Word Count Goal</label>: <input type="text" name="nanorc_wcgoal" value="<?php echo $nanorc_wcgoal; ?>" /><br />
</p>
<input type="hidden" name="page_options" value="nanorc_title,nanorc_author,nanorc_wcgoal" />

<table class="widefat">
 <thead>
  <tr>
	<th scope="col">Day</th scope="col">
	<th>Total WC</th>
	<th scope="col">Hours</th>
	<th scope="col">Sessions</th>
	<th scope="col">Scenes Complete</th>
	<th scope="col"></th>
	<th scope="col"></th>
  </tr>
 </thead>
 <tbody id="the-list">

<?php
$count = 0;
$class = "";

// Query the database for records that already exist

	$sql = "SELECT day, totalwc, hours, sessions, scenes FROM `$table_name` ORDER BY day ASC";

	$dailydata = $wpdb->get_results($sql);

foreach ($dailydata as $dailydata) {

   if( ( $count / 2 ) == intval( $count / 2 ) ){
        $class = "author-self status-publish";
    }else{
        $class = "alternate author-self status-publish";
    } 
	echo "<tr class='" . $class . "'><th scope=\"row\">" . $dailydata->day . "</th>";
	echo "<td>" . $dailydata->totalwc . "</td>";
	echo "<td>" . $dailydata->hours . "</td>";
	echo "<td>" . $dailydata->sessions . "</td>";
	echo "<td>" . $dailydata->scenes . "</td>";
        echo "<td><a href=\"" . $_SERVER['PHP_SELF'] . "?page=" . basename(__FILE__) . "&edit=" . $dailydata->day . "\">Edit</a></td>";
        echo "<td><a href=\"" . $_SERVER['PHP_SELF'] . "?page=" . basename(__FILE__) . "&delete=" . $dailydata->day . "\">Delete</a></td>";
        echo "</tr>";

        $count++;
}


?>
<tr>
 <td><input type="text" name="nanorc_day" size="3" /></td>
 <td><input type="text" name="nanorc_totalwc" size="8" /></td>
 <td><input type="text" name="nanorc_hours" size="5" /></td>
 <td><input type="text" name="nanorc_sessions" size="5" /></td>
 <td><input type="text" name="nanorc_scenes" size="5" /></td>
 <td></td>
 <td></td>
</tr>
	</tbody>
</table>
<p class="submit">
<input type="submit" name="submit" value="<?php _e('Update') ?>" />
</p>
</form>
</div>

<?php

} //End: function nanorc_dashboard_page()


function nanorc_report($wrap_before = "", $wrap_after = "", $before_title = "<h2>", $after_title = "</h2>", $a_title = "NaNo Report Card", $a_totalwc = true, $a_hours = true, $a_sessions = true, $a_scenes = true, $a_words = true, $a_wordshr = true, $a_wordsgoal = true, $a_daysr = true, $a_avgwordsday = true, $a_goaltomorrow = true, $a_chgpace = true, $a_doneon = true, $a_pccomplete = true, $a_hrsrate = true, $a_avgscene = true) {
	global $table_prefix, $wpdb;
// A couple needed variables
   $table_name = $wpdb->prefix . "nanorc";

// Pull the options from the database
$nanorc_title = get_option('nanorc_title'); 
$nanorc_author = get_option('nanorc_author');
$nanorc_wcgoal = get_option('nanorc_wcgoal');

$dailygoal = $nanorc_wcgoal/30;

?>

<?php echo $wrap_before; ?>
<?php echo $before_title . $a_title . $after_title; ?>

<table>
 <tr>
  <th>Day</th>
<?php if ($a_totalwc == true) { echo "<th>Total WC</th>"; } ?>
<?php if ($a_hours == true) { echo "<th>Hours</th>";  } ?>
<?php if ($a_sessions == true) { echo "<th>Sessions</th>"; } ?>
<?php if ($a_scenes == true) { echo "<th>Scenes Complete</th>"; } ?>
<?php if ($a_words == true) { echo "<th>WC Today</th>"; } ?>
<?php if ($a_wordshr == true) { echo "<th>Words/Hour</th>"; } ?>
<?php if ($a_wordsgoal == true) { echo "<th>Words to Goal</th>"; } ?>
<?php if ($a_daysr == true) { echo "<th>Days Remaining</th>"; } ?>
<?php if ($a_avgwordsday == true) { echo "<th>Avg Words/Day</th>"; } ?>
<?php if ($a_goaltomorrow == true) { echo "<th>Goal for Tomorrow</th>"; } ?>
<?php if ($a_chgpace == true) { echo "<th>% Change in Pace</th>"; } ?>
<?php if ($a_doneon == true) { echo "<th>Done On</th>"; } ?>
<?php if ($a_pccomplete == true) { echo "<th>Percent Complete</th>"; } ?>
<?php if ($a_hrsrate == true) { echo "<th>Hours Left at Today's Rate</th>"; } ?>
<?php if ($a_avgscene == true) { echo "<th>Avg Scene Length</th>"; } ?>
</tr>

<?php

// Set yesterday to zero (for the math)
$prev->day = 0;
$prev->totalwc = 0;
$prev->hours = 0;
$prev->sessions = 0;
$prev->scenes = 0;

$cycle = 0;

// Query the database for records that already exist

	$sql = "SELECT day, totalwc, hours, sessions, scenes FROM `$table_name` ORDER BY day ASC";

	$dailydata = $wpdb->get_results($sql);

foreach ($dailydata as $dailydata) {

$today->words = $dailydata->totalwc - $prev->totalwc;

if ( $dailydata->hours != 0 ) {
  $today->wordshr = $today->words / $dailydata->hours;
} else {
  $today->wordshr = $today->words; 
}

$today->wordsgoal = $nanorc_wcgoal - $dailydata->totalwc;
$today->daysr = 30 - $dailydata->day;

if ( $dailydata->day != 0 ) {
  $today->avgwordsday = $dailydata->totalwc / $dailydata->day;
} else {
  die("The day cannot be zero");
}

if ( $today->words > 0 ) {

  $daystoadd = number_format($today->wordsgoal / $today->words);
  if ( $daystoadd <= 0 ) {
	$daystoadd = 1;
  }
  $today->doneon = date("M d, Y", mktime(0, 0, 0, 11, $today->day+$daystoadd, date(Y)));

} else {
  $today->doneon = "Never";
}

if ( $nanorc_wcgoal != 0 ) {
  $today->pccomplete = $dailydata->totalwc / $nanorc_wcgoal;
} else {
  die("nanorc_wcgoal cannot be zero");
}

if ( $today->wordshr != 0 ) {
  $today->hrsrate = $today->wordsgoal / $today->wordshr;
} else {
  die("Error in Rate at Words per Hour");
}

if ( $dailydata->scenes != 0 ) {
  $today->avgscene = ( $dailydata->totalwc / $dailydata->scenes );
} else {
  $today->avgscene = $dailydata->totalwc;
}

if ( $cycle == 0 ) {
  $today->chgpace = 1;
} else {
  if ( $prev->words > 0 ) {
    $today->chgpace = ( ($today->words - $prev->words) / ( $prev->words) );
  } else {
    $today->chgpace = "N/A";
  }
}

if ( $today->daysr == 0 ) {
  $today->goaltomorrow = "N/A";
} else {
  if ( ($today->wordsgoal / $today->daysr) < $dailygoal ) {
    $today->goaltomorrow = $dailygoal;
  } else {
    $today->goaltomorrow = $today->wordsgoal / $today->daysr;
  }
}


	echo "<tr><td>" . $dailydata->day . "</td>";
if ($a_totalwc == true) { echo "<td>" . $dailydata->totalwc . "</td>"; } 
if ($a_hours == true) { echo "<td>" . $dailydata->hours . "</td>"; }
if ($a_sessions == true) { echo "<td>" . $dailydata->sessions . "</td>"; }
if ($a_scenes == true) { echo "<td>" . $dailydata->scenes . "</td>"; }
if ($a_words == true) { echo "<td>" . $today->words . "</td>"; }
if ($a_wordshr == true) { echo "<td>" . $today->wordshr . "</td>"; }
if ($a_wordsgoal == true) { echo "<td>" . $today->wordsgoal . "</td>"; }
if ($a_daysr == true) { echo "<td>" . $today->daysr . "</td>"; }
if ($a_avgwordsday == true) { echo "<td>" . number_format($today->avgwordsday) . "</td>"; }
if ($a_goaltomorrow == true) { echo "<td>" . number_format($today->goaltomorrow) . "</td>"; }
if ($a_chgpace == true) { echo "<td>" . number_format($today->chgpace * 100, 1) . "%</td>"; }
if ($a_doneon == true) { echo "<td>" . $today->doneon . "</td>"; }
if ($a_pccomplete == true) { echo "<td>" . number_format($today->pccomplete * 100, 1) . "%</td>"; }
if ($a_hrsrate == true) { echo "<td>" . number_format($today->hrsrate, 1) . "</td>"; }
if ($a_avgscene == true) { echo "<td>" . number_format($today->avgscene, 1) . "</td>"; }

echo "</tr>";


$prev->words = $today->words;


$prev->day = $dailydata->day;
$prev->totalwc = $dailydata->totalwc;
$prev->hours = $dailydata->hours;
$prev->sessions = $dailydata->sessions;
$prev->scenes = $dailydata->scenes;

$cycle++;
} // For


?>
</table>

<?php
echo $wrap_after;

} //End: function nanorc_report($wrap_before, $wrap_after, $before_title, $after_title, $a_totalwc, $a_hours, $a_sessions, $a_scenes, $a_words, $a_wordshr, $a_wordsgoal, $a_daysr, $a_avgwordsday, $a_goaltomorrow, $a_chgpace, $a_doneon, $a_pccomplete, $a_hrsrate, $a_avgscene)

function nanorc_graph($wrap_before, $wrap_after, $before_title, $after_title, $title = "Novel Progress", $a_barcolor = "#666666", $a_height = "200", $width = "1000") {

// A couple needed variables
   global $table_prefix, $wpdb;
   $table_name = $wpdb->prefix . "nanorc";

   $nanorc_wcgoal = get_option('nanorc_wcgoal');


// Left shift
$leftshift = intval($width / 30);

?>
<style type="text/css"> .verticalBarGraph { border: 1px solid #FFF; height: <?php echo $a_height; ?>px; margin: 0; padding: 0; position: relative; } 	.verticalBarGraph li { border: 1px solid #555; border-bottom: none; bottom: 0; list-style:none; margin: 0; padding: 0; position: absolute; text-align: center; 	width: <?php echo $leftshift - 2; ?>px; } .verticalBarGraph li.p1{ background-color: <?php echo $a_barcolor; ?>; } </style>

<?
echo $wrap_before;
echo $before_title . $title . $after_title;

// Initilize Days
$daycount = 1;
$yesterdaywc = 0;

	$sql = "SELECT day, totalwc FROM `$table_name` ORDER BY day ASC";

	$dailydata = $wpdb->get_results($sql);

echo "<ul class=\"verticalBarGraph\">";

foreach ($dailydata as $dailydata) {

  $todayshift = intval($dailydata->day * $leftshift);
  $todayheight = intval( ($dailydata->totalwc / $nanorc_wcgoal) * $a_height );
  $yesterdayheight = intval( ($yesterdaywc / $nanorc_wcgoal) * $a_height );

  if ($daycount == $dailydata->day) {
?>
 	<li class="p1" style="height: <?php echo $todayheight; ?>px; left: <?php echo $todayshift; ?>px;"><?php echo $daycount; ?></li> 
<?php
  } else {
// Display the same thing
     while ($daycount < $dailydata->day) {
       $newshift = intval($daycount * $leftshift);
?>
 	<li class="p1" style="height: <?php echo $yesterdayheight; ?>px; left: <?php echo $newshift; ?>px;"><?php echo $daycount; ?></li> 
<?php
        $daycount++;
     } // End While

// Now display the new day
?>
 	<li class="p1" style="height: <?php echo $todayheight; ?>px; left: <?php echo $todayshift; ?>px;"><?php echo $daycount; ?></li> 
<?php
  } // Endif

  $yesterdaywc = $dailydata->totalwc;
  $daycount++;

} // End: foreach

echo "</ul>";


echo $wrap_after;

} // End: function nanorc_graph($wrap_before, $wrap_after, $before_title, $after_title, $title, $barcolor)


function nanorc_basicinfo($wrap_before, $wrap_after, $before_title, $after_title, $show_wcgoal = true, $show_dailygoal = false) {

// Pull the options from the database
$nanorc_title = get_option('nanorc_title'); 
$nanorc_author = get_option('nanorc_author');
$nanorc_wcgoal = get_option('nanorc_wcgoal');

$dailygoal = intval($nanorc_wcgoal/30);

echo $wrap_before;
echo $before_title . $nanorc_title . $after_title;
echo "by: " . $nanorc_author . "<br />";

if ($show_wcgoal == true) { echo "Goal: " . $nanorc_wcgoal . "<br />"; }
if ($show_dailygoal == true) { echo "Daily Goal: " . $dailygoal . "<br />"; }
 
echo $wrap_after;

} // End: function nanorc_basicinfo($wrap_before, $wrap_after, $before_title, $after_title, $show_wcgoal = true, $show_dailygoal = false) 



function nanorc_summary($wrap_before = "", $wrap_after = "", $before_title = "<h2>", $after_title = "</h2>", $a_title = "NaNo Summary", $a_totalwc = true, $a_totalhours = true, $a_avgperhour = true, $a_avgperday = true, $a_avghoursday = true, $a_wordstogoal = true, $a_daysr = true, $a_avgsession = true) {

// A couple needed variables
	global $table_prefix, $wpdb;
   $table_name = $wpdb->prefix . "nanorc";

// Pull the options from the database
$nanorc_title = get_option('nanorc_title'); 
$nanorc_author = get_option('nanorc_author');
$nanorc_wcgoal = get_option('nanorc_wcgoal');

$dailygoal = $nanorc_wcgoal/30;

echo $wrap_before;
echo $before_title . $a_title . $after_title; 
?>

<ul>

<?php

// Query the database for records that already exist

	$sql = "SELECT day, totalwc, hours, sessions, scenes FROM `$table_name` ORDER BY day ASC";

	$dailydata = $wpdb->get_results($sql);

// Make Empty total variables
$total->hours = 0;
$total->sessions = 0;

foreach ($dailydata as $dailydata) {
  $total->hours = $total->hours + $dailydata->hours;
  $total->sessions = $total->sessions + $dailydata->sessions;

} // For


if ( $total->hours == 0 ) {
	$a_avgperhour = FALSE;
} else {
	$avgperhour = intval($dailydata->totalwc / $total->hours);
}

if ( $dailydata->day == 0 ) {
	$a_avgperday = FALSE;
	$a_avghoursday = FALSE;
} else {
	$avgperday = intval($dailydata->totalwc / $dailydata->day);
	$avghoursday = number_format($total->hours / $dailydata->day, 1);
}
if ( $total->sessions == 0 ) {
	$a_avgsession = FALSE;
} else {
	$avgsession = number_format($total->hours / $total->sessions, 1);
}

$wordstogoal =  intval($nanorc_wcgoal - $dailydata->totalwc);
$daysr = intval(30 - $dailydata->day);


//Word Count Reached	last totalwc	
if ($a_totalwc == true) { echo "<li><strong>Total Word Count</strong>: " . $dailydata->totalwc . "</li>"; }

//Total Hours Spent Writing	total hours	
if ($a_totalhours == true) { echo "<li><strong>Total Writing Hours</strong>: " . $total->hours . "</li>"; }

//Avg Words Per Hour	last totalwc / total hours	
if ($a_avgperhour == true) {echo "<li><strong>Avg Words per Hour</strong>: " . $avgperhour . "</li>"; }

//Avg Words Per Day	last totalwc / last day	
if ($a_avgperday == true) {echo "<li><strong>Avg Words Per Day</strong>: " . $avgperday . "</li>"; }

//Avg Hours Spent Writing Per Day	total hours / last day	
if ($a_avghoursday == true) { echo "<li><strong>Avg Writing Hours per Day</strong>: " . $avghoursday . "</li>"; }

//Words Remaining To Goal	goal - last totalwc
if ($a_wordstogoal == true) { echo "<li><strong>Words to Goal</strong>: " . $wordstogoal . "</li>";	}

//Days Remaining In November	30 - last day	
if ($a_daysr == true) { echo "<li><strong>Days Remaining</strong>: " . $daysr . "</li>"; }

//Avg Time Per Writing Session	total hours / total sessions	
if ($a_avgsession == true) { echo "<li><strong>Avg Time Per Session</strong>: " . $avgsession . "</li>"; }

?>

</ul>

<?php
echo $wrap_after;

} //End: function nanorc_summary($wrap_before, $wrap_after, $before_title, $after_title, $a_title, $a_totalwc, $a_totalhours, $a_avgperhour, $a_avgperday, $a_avghoursday $a_wordstogoal, $a_daysr, $a_avgsession )

if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
    return; // ...and if not, exit gracefully from the script.

function widget_nanorc($args) {
    extract($args);
?>
        <?php echo $before_widget; ?>
            <?php echo $before_title . 'NaNoWriMo Progress' . $after_title; ?>
            <?php nanorc_summary("", "", "", "", "", true, true, true, true, true, true, true); ?>
        <?php echo $after_widget; ?>
<?php
} // End: function widget_nanorc($args)

  register_sidebar_widget('NaNo Report Card', 'widget_nanorc');



if (isset($_GET['activate']) && $_GET['activate'] == 'true') {
 global $table_prefix,$wpdb;
 $tablename = $table_prefix . "nanorc";
 
 if($wpdb->get_var("show tables like '$tablename'") != $tablename) {
   add_action('init', 'nanorc_install');
 }
}

} // Everything active function nanorc_init()

add_action('plugins_loaded', 'nanorc_init');
?>
