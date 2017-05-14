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
?>

<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/jquery-ui.min.css">
<script src="js/moment.js"></script>
<script src="external/jquery/jquery.js"></script>
<script src="js/jquery-ui.min.js"></script>

<script>
    moment().format();
    $(function() {
        $('.date').datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd"
        });
    });
</script>

<?php

$bookIsbn = $_GET['isbn'];

try {
    $bookByIsbn = $client->getDoc($bookIsbn);
}   catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
}

//Prepare array of proper deadline values to load into form
//If task is marked as completed, load the completed date
//Else, load the original deadline date
$deadlines = array();
$deadlineValues = array();
foreach ($bookByIsbn->deadlines as $deadline=>$details) {
    $deadlines[$deadline] = $details;
}
foreach ($deadlines as $deadline=>$values) {
    if ($values->complete) {
        $deadlineValues[$deadline] = "Completed " . $values->complete_date;
    }
    else $deadlineValues[$deadline] = $values->deadline_date;
}

?>

<div class="container form">
    <form method="post" action="index.php">
        <div>
            Edit Book
        </div>
        <div class="row">
            <div class="col-1-3">
                <h2>Basic Data</h2>
                <div class="">
                    <label for="title" class="form-label">Title: </label>
                    <input type="text" id="title" class="form-input" name="title" value="<?=$bookByIsbn->title ?>" />
                </div>
                <div class="">
                    <label for="subtitle" class="form-label">Subtitle: </label>
                    <input type="text" id="subtitle" class="form-input" name="subtitle" value="<?=$bookByIsbn->subtitle ?>" />
                </div>
                <div class="">
                    <div id="contributors">
                        <?php foreach($bookByIsbn->contributor as $contributor) {
                            echo "<div id='contributor'>"
                                . "<label for='contributor' class='form-label'>Contributor: </label>"
                                . "<input type='text' id='contributor' class='form-input' name='contributor[]' value='"
                                . $contributor
                                . "' /></div>";
                        }
                        ?>
                    </div>
                    <button type="button" id="new_contributor" name="new_contributor">Add Contributor</button>
                    <button type="button" id="delete_contributor" name="delete_contributor">Delete Contributor</button>
                </div>
                <div class="">
                    <label for="isbn" class="form-label">ISBN: </label>
                    <input type="text" id="isbn" class="form-input" name="isbn" value="<?=$bookByIsbn->isbn ?>" />
                </div>
                <div class="">
                    <label for="eisbn" class="form-label">EISBN: </label>
                    <input type="text" id="eisbn" class="form-input" name="eisbn" value="<?=$bookByIsbn->eisbn ?>" />
                </div>
                <div class="">
                    <label for="imprint" class="form-label">Imprint: </label>
                    <select name="imprint">
                        <option value="arcade" <?php if ($bookByIsbn->imprint === "arcade"): ?> selected="selected" <?php endif; ?>>Arcade</option>
                        <option value="skyhorse" <?php if ($bookByIsbn->imprint === "skyhorse"): ?> selected="selected" <?php endif; ?>>Skyhorse</option>
                        <option value="talos" <?php if ($bookByIsbn->imprint === "talos"): ?> selected="selected" <?php endif; ?>>Talos</option>
                    </select>
                </div>
                <div class="">
                    <label for="season" class="form-label">Season: </label>
                    <select name="season">
                        <option value="spring" <?php if ($bookByIsbn->season === "spring"): ?> selected="selected" <?php endif; ?>>Spring</option>
                        <option value="fall" <?php if ($bookByIsbn->season === "fall"): ?> selected="selected" <?php endif; ?>>Fall</option>
                    </select>
                    <input type="text" id="season_year" class="form-input" name="season_year" value="<?=$bookByIsbn->season_year ?>" />
                </div>
                <div class="">
                    <label for="arc" class="form-label">ARC</label>
                    <input type="checkbox" id="arc" name="arc" value="ARC"
                        <?php if ($bookByIsbn->arc === 'ARC'): ?>
                            checked='checked' />
                        <?php else: ?>
                            />
                        <?php endif; ?>
                </div>
            </div>
            <div class="col-1-3">
                <h2>Editorial Schedule</h2>
                <div id="edit_schedule">
                    <div id="edit_schedule_dates">
                        <div class="">
                            <label for="manuscript_delivery" class="form-label">Manuscript Delivery: </label>
                            <input type="text" id="manuscript_date" class="form-input date" name="manuscript_date" value="<?=$deadlineValues['manuscript_date'] ?>" />
                        </div>
                        <div class="">
                            <label for="edits_to_author" class="form-label">Edits to Author: </label>
                            <input type="text" id="edits_to_author" class="form-input date" name="edits_to_author" value="<?=$deadlineValues['edits_to_author'] ?>" />
                        </div>
                        <div class="">
                            <label for="revisions_in" class="form-label">Revisions In: </label>
                            <input type="text" id="revisions_in" class="form-input date" name="revisions_in" value="<?=$deadlineValues['revisions_in'] ?>" />
                        </div>
                        <div class="">
                            <label for="to_copyedit" class="form-label">To Copyedit: </label>
                            <input type="text" id="to_copyedit" class="form-input date" name="to_copyedit" value="<?=$deadlineValues['to_copyedit'] ?>" />
                        </div>
                        <div class="">
                            <label for="manuscript_finalized" class="form-label">Manuscript Finalized: </label>
                            <input type="text" id="manuscript_finalized" class="form-input date" name="manuscript_finalized" value="<?=$deadlineValues['manuscript_finalized'] ?>" />
                        </div>
                    </div>
                </div>

                <h2>Production Schedule</h2>
                <div id="pub_schedule">
                    <div class="">
                        <label for="pub_date" class="form-label">Publication Date: </label>
                        <input type="text" id="pub_date" class="form-input date" name="pub_date" value="<?=$bookByIsbn->pub_date ?>" />
                    </div>
                    <div id="pub_schedule_dates">
                        <div class="">
                            <label for="arc_prod_date" class="form-label">To Production (ARC): </label>
                            <input type="text" id="arc_prod_date" class="form-input date" name="arc_prod_date" value="<?=$deadlineValues['arc_prod_date'] ?>" />
                        </div>
                        <div class="">
                            <label for="arc_press_date" class="form-label">To Press (ARC): </label>
                            <input type="text" id="arc_press_date" class="form-input date" name="arc_press_date" value="<?=$deadlineValues['arc_press_date'] ?>" />
                        </div>
                        <div class="">
                            <label for="prod_date" class="form-label">To Production: </label>
                            <input type="text" id="prod_date" class="form-input date" name="prod_date" value="<?=$deadlineValues['prod_date'] ?>" />
                        </div>
                        <div class="">
                            <label for="press_date" class="form-label">To Press: </label>
                            <input type="text" id="press_date" class="form-input date" name="press_date" value="<?=$deadlineValues['press_date'] ?>" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-1-3">
                <h2>Other Information</h2>
                <div class="">
                    <label for="adv_sign" class="form-label">Advance on Signature In: </label>
                    <input type="text" id="adv_sign" class="form-input date" name="adv_sign" value="" />
                </div>
                <h2>Custom Fields</h2>
                <button type="button" id="new_field" name="new_field">Add Field</button>
                <button type="button" id="delete_field" name="delete_field">Delete Field</button>
                <div class="">
                    <div id="custom-fields">
                        <div id="custom-field">
                            Field:<br>
                            <input type="text" id="field_name" name="field[name][]" value="Field Name" />
                            <input type="text" id="field" name="field[value][]" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="submit">
            <button id="update_book" name="update_book">Update</button>
        </div>
    </form>
</div>