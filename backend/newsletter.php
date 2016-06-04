<?

require_once('commons.php');
require_once('clsDB.php');
require_once('config.php');

$nl_id = mGetVar($_REQUEST, 'newsletter_id', 0);

$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

$sql = "SELECT a.* FROM articles a
				WHERE a.newsletter_id = '{$nl_id}'
				ORDER BY sort_order
			;";
$results = $db->query($sql);
$rows = $results->rows;

$sql = "SELECT n.* FROM newsletter n
				WHERE n.newsletter_id = '{$nl_id}'
			;";
$results = $db->query($sql);
$nlrows = $results->rows;

// Include the wrapper at the beginning
include("nl_header.php");

// Show some stuff for each article on display here.
foreach($rows as $row) {
	include("article2.php");
}

// Include the wrapper at the beginning
include("nl_footer.php");

?>

