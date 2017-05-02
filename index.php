<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/jquery-ui.min.css">
<script src="external/jquery/jquery.js"></script>
<script src="js/script.js"></script>
<script src="js/jquery-ui.min.js"></script>

<?php
require __DIR__ . '/vendor/autoload.php';

include __DIR__ . '/inc/head.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPOnCouch\Couch, 
    PHPOnCouch\CouchAdmin, 
    PHPOnCouch\CouchClient,
    PHPOnCouch\CouchDocument;

$client = new CouchClient("localhost:5984", "book_manager");

function generateHtmlBySeason($client, $season, $year) {
        $listHtml = "<tr class='season'><td colspan=100>";
        $yearSeasonBooks = $client->key([$season, $year])->getView('bySeason', 'bySeasonYear');
        $listHtml .= $season . " " . $year . "</td></tr>";
        $listHtml .= "<tbody class='season-tab'><tr id='season-headers'>"
            . "<th>Title</th>"
            . "<th>Subtitle</th>"
            . "<th>Contributors</th>"
            . "<th>ISBN</th>"
            . "<th>EISBN</th>"
            . "<th>Imprint</th>"
            . "<th>MS Delivery</th>"
            . "<th>Edits to Author</th>"
            . "<th>MS Revision</th>"
            . "<th>To Copyedit</th>"
            . "<th>MS Finalized</th>"
            . "<th>To Production (ARC)</th>"
            . "<th>To Press (ARC)</th>"
            . "<th>To Production (No ARC)</th>"
            . "<th>To Press</th>"
            . "<th>Publication Date</th></tr>";

        foreach($yearSeasonBooks->rows as $book) {
            $listHtml .= "<tr class='book'><td>" 
                . "<a href='viewBook.php?isbn=" . $book->value->isbn . "'>"
                . $book->value->title
                . "</a></td><td>" 
                . $book->value->subtitle . "</td><td>";
                foreach($book->value->contributor as $contributor) {
                    $listHtml .= $contributor . "<br>";
                }
            $listHtml .= "</td>"
                . "<td>" . $book->value->isbn . "</td>"
                . "<td>" . $book->value->eisbn . "</td>"
                . "<td>" . $book->value->imprint . "</td>"
                . "<td>" . $book->value->manuscript_date . "</td>"
                . "<td>" . $book->value->edits_to_author . "</td>"
                . "<td>" . $book->value->revisions_in . "</td>"
                . "<td>" . $book->value->to_copyedit . "</td>"
                . "<td>" . $book->value->manuscript_finalized . "</td>";
            $arcProd = "No ARC";
            $arcPress = "No ARC";
            if($book->value->arc === 'ARC') {
                $arcProd = $book->value->arc_prod_date;
                $arcPress = $book->value->arc_press_date;
            }
            $listHtml .= "<td>" . $arcProd . "</td>"
                . "<td>" . $arcPress . "</td>"
                . "<td>" . $book->value->prod_date . "</td>"
                . "<td>" . $book->value->press_date . "</td>"
                . "<td>" . $book->value->pub_date . "</td>"
                . "</tr>";
        }
        $listHtml .= "</tbody>";
        return $listHtml;
    }

if (!empty($_POST)) {
    $newBook = new stdClass();
    $newBook->_id = $_POST['isbn'];
    $newBook->type = 'book';
    foreach ($_POST as $key=>$value) {
        $newBook->$key = $value;
    }
    try {
        $response = $client->storeDoc($newBook);
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
    }
}

try {
    $allSeasonYears = $client->group(true)->getView('allBooks', 'seasonYear');
    $years = array();
    $springBooks = array();
    $fallBooks = array();
    $html = "<table id='book-list' border=1>";

    
    foreach ($allSeasonYears->rows as $row) {
        $html .= generateHtmlBySeason($client, 'spring', $row->key);
        $html .= generateHtmlBySeason($client, 'fall', $row->key);
    }
    $html .= "</table>";
}   catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
}

?>

<div class="container">
    <?php echo $html; ?>
</div>