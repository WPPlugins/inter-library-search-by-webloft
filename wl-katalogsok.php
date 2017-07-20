<?php
/*
Plugin Name: WL Katalogs&oslash;k
Plugin URI: http://www.bibvenn.no/
Description: Interlibrary search for your Wordpress site! NORWEGIAN: Setter inn s&oslash;kefelt som lar deg s&oslash;ke i mange forskjellige bibliotekssystemer.
Version: 3.3.3
Author: H&aring;kon Sundaune / Bibliotekarens beste venn
Author URI: http://www.bibvenn.no/
Text Domain: inter-library-search-by-webloft
*/

/* Nyttig info om Alma AVA AVE AVD felter:
https://knowledge.exlibrisgroup.com/Alma/Product_Documentation/Alma_Online_Help_%28English%29/Alma-Primo_Integration/030Publishing_Alma_Data_to_Primo/Exporting_Alma_Records_to_Primo
*/

define('ILS_URL', plugins_url('' , __FILE__));
define('KS_FILE',  __FILE__ );


include('conf/globals.php');
include('systemer.php');
include('lib/functions/deprecated.php');
include('lib/functions/functions.php');
include('lib/utils.php');
include('lib/MBAssets.php');
include('lib/MBShortcode.php');
include('lib/MBBooking.php');
include('lib/MBSearch.php');
include('lib/admin/MBAdmin.php');
include('lib/WL_ILS_Widget.php');


add_action( 'plugins_loaded', 'wlkatalogsok_load_textdomain' );
function wlkatalogsok_load_textdomain() {
  load_plugin_textdomain( 'inter-library-search-by-webloft', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}
