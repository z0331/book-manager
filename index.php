<?php
include __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/functions.php';
require __DIR__ . '/vendor/autoload.php';
?>

<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/jquery-ui.min.css">
<script src="external/jquery/jquery.js"></script>
<script src="js/script.js"></script>
<script src="js/jquery-ui.min.js"></script>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPOnCouch\Couch, 
    PHPOnCouch\CouchAdmin, 
    PHPOnCouch\CouchClient,
    PHPOnCouch\CouchDocument;

$client = new CouchClient("localhost:5984", "book_manager");

if (isset($_POST['new_book'])) {
    //If no document, create a new book 
    $newBook = new stdClass();
    $newBook->_id = $_POST['isbn'];
    $newBook->title = $_POST['title'];
    $newBook->subtitle = $_POST['subtitle'];
    $newBook->contributor = $_POST['contributor'];
    $newBook->isbn = $_POST['isbn'];
    $newBook->eisbn = $_POST['eisbn'];
    $newBook->imprint = $_POST['imprint'];
    $newBook->season = $_POST['season'];
    $newBook->season_year = $_POST['season_year'];
    $newBook->arc = $_POST['arc'];
    $newBook->pub_date = $_POST['pub_date'];
    $deadlines = array(
        'manuscript_date' => array(
            'deadline_date' => $_POST['manuscript_date'],
            'complete' => false,
            'complete_date' => ''
        ),
        'edits_to_author' => array(
            'deadline_date' => $_POST['edits_to_author'],
            'complete' => false,
            'complete_date' => ''
        ),
        'revisions_in' => array(
            'deadline_date' => $_POST['revisions_in'],
            'complete' => false,
            'complete_date' => ''
        ),
        'to_copyedit' => array(
            'deadline_date' => $_POST['to_copyedit'],
            'complete' => false,
            'complete_date' => ''
        ),
        'manuscript_finalized' => array(
            'deadline_date' => $_POST['manuscript_finalized'],
            'complete' => false,
            'complete_date' => ''
        ),
        'arc_prod_date' => array(
            'deadline_date' => $_POST['arc_prod_date'],
            'complete' => false,
            'complete_date' => ''
        ),
        'arc_press_date' => array(
            'deadline_date' => $_POST['arc_press_date'],
            'complete' => false,
            'complete_date' => ''
        ),
        'prod_date' => array(
            'deadline_date' => $_POST['prod_date'],
            'complete' => false,
            'complete_date' => ''
        ),
        'press_date' => array(
            'deadline_date' => $_POST['press_date'],
            'complete' => false,
            'complete_date' => ''
        )
    );
    $newBook->deadlines = $deadlines;
    try {
        $response = $client->storeDoc($newBook);
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
    }
} else if (isset($_POST['update_list'])) {
    updateCompletedDeadline($client, $_POST['completed']);
}

try {
    $allSeasonYears = $client->group(true)->getView('allBooks', 'seasonYear');

}   catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
}

?>

<div class="container">
    <div class="row">
        <form method="post" action="index.php">
            <button id="update_list" name="update_list">Update</button>
            <table id='book-list' border=1>
                <?php foreach ($allSeasonYears->rows as $row) {
                    $seasons = array('spring', 'fall');
                    $year = $row->key;
                    foreach ($seasons as $season) { 
                        $yearSeasonBooks = $client->key([$season, $year])->getView('bySeason', 'bySeasonYear');?>
                        <tr class='season'>
                            <td colspan=100>
                                <?= $season . " " . $year; ?>
                            </td>
                        </tr>
                        <tbody class='season-tab' <?php if (checkCurrentSeason($season, $year)): ?>
                            id='current-season-tbody'
                            <?php endif; ?>>
                            <tr id='season-headers'>
                                <th>Title</th>
                                <th>Subtitle</th>
                                <th>Contributors</th>
                                <th>ISBN</th>
                                <th>EISBN</th>
                                <th>Imprint</th>
                                <th>MS Delivery</th>
                                <th>Edits to Author</th>
                                <th>MS Revision</th>
                                <th>To Copyedit</th>
                                <th>MS Finalized</th>
                                <th>To Production (ARC)</th>
                                <th>To Press (ARC)</th>
                                <th>To Production (No ARC)</th>
                                <th>To Press</th>
                                <th>Publication Date</th>
                            </tr>
                            <?php foreach($yearSeasonBooks->rows as $book) { ?>
                                <tr class='book'>
                                    <td> 
                                        <a href='viewBook.php?isbn=<?=$book->value->isbn?>'>
                                        <?=$book->value->title?></a>
                                    </td>
                                    <td> 
                                        <?=$book->value->subtitle?>
                                    </td>
                                    <td>
                                        <?php foreach($book->value->contributor as $contributor) {
                                                echo $contributor . "<br>";
                                            } ?>
                                    </td>
                                    <td>
                                        <?=$book->value->isbn?>
                                    </td>
                                    <td>
                                        <?=$book->value->eisbn?>
                                    </td>
                                    <td>
                                        <?=$book->value->imprint?>
                                    </td>
                                    <?php
                                        foreach ($book->value->deadlines as $deadline=>$details) {
                                            $arc = false;
                                            if($book->value->arc) {
                                                $arc = true;
                                            }
                                            echo generateDateBoxHtml($details->deadline_date, 
                                                $deadline,
                                                $book->value->isbn, 
                                                $arc, 
                                                $details->complete, 
                                                $details->complete_date);
                                        } //End deadline loop
                                    ?>
                                    <td>
                                        <?=$book->value->pub_date?>
                                    </td>
                                </tr>
                            <?php } //End book loop ?>
                        </tbody>
                    <?php } //End season loop
                } //End year loop ?>
            </table>
        </form>
    </div>
</div>