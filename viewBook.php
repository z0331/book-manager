<?php
    require __DIR__ . '/vendor/autoload.php'; 
    require __DIR__ . '/inc/functions.php';
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
<script src="js/script.js"></script>
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
    $bookByIsbn = $client->getDoc($bookIsbn);                       //Pull book info from document for display
    $bookTest = $client->asArray()->getDoc($bookIsbn);              //Pull same info as array for comparison to POST
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
}

/* 
 *  Create an array from POST deadline fields that is comparable to the CouchDB document deadline array.
 *  New array will contain any updates made, including whether deadline has been marked complete.
 *  If complete, array will be loaded with today's date as date of completion.
 */
 if (isset($_POST['update_book'])) {
    $newBookValues = array();                                   //Array to store POST array data
    $newDeadlines = array();                                    //Array to store new deadline information
    $newBookValues = $_POST;                                    //Copy POST array to variable in order to restore new deadline information
    foreach($_POST['deadlines'] as $deadline=>$value) {
        $newDeadlines[$deadline] = array(
            'deadline_date' => $value['deadline_date']
        );
        if(!empty($value['complete'])) {
            $newDeadlines[$deadline]['complete'] = true;
            $newDeadlines[$deadline]['complete_date'] = date('Y-m-d');
        } else {
            $newDeadlines[$deadline]['complete'] = false;
            $newDeadlines[$deadline]['complete_date'] = "";
        }
    }

    /*
    * Convert new deadlines array to object and store it.
    * NOTE: apparently using json_decode here offers slower performance than creating
    * a looping function, but frankly this is easier/cleaner and these arrays are not big enough
    * for it to matter.
    */
    $newBookValues['deadlines'] = json_decode(json_encode($newDeadlines), false);
            
    /* 
    *  Check for which fields are between POST and the saved data after converting them both to arrays.
    *  Store those fields in array $differences.
    */ 
    $differences = array();
    $differences = checkIfArrayDifferent(json_decode(json_encode($bookTest), true), json_decode(json_encode($newBookValues), true));

    if(count($differences)) {
        foreach($differences as $field) {
            $bookByIsbn->$field = $newBookValues[$field];
        }
    try {
        $response = $client->storeDoc($bookByIsbn);
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
        } 
    }  
 }

/* 
 *  Prepare array of proper deadline values to load into form
 *  If task is marked as completed, load the completed date
 *  Else, load the original deadline date
 */
$deadlines = array();
$deadlineValues = array();
foreach ($bookByIsbn->deadlines as $deadline=>$details) {
    $deadlines[$deadline] = $details;
}
foreach ($deadlines as $deadline=>$values) {
    $deadlineValues[$deadline] = $values->deadline_date;
}

?>

<div class="container form">
    <form method="post" action="">
        <div>
            Edit Book
        </div>
        <div class="row">
            <div class="col-1-3" id="basic-data">
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
                    <label for="isbn" class="form-label">ISBN: </label>
                    <?=$bookByIsbn->isbn ?>
                </div>
                <div class="">
                    <label for="eisbn" class="form-label">EISBN: </label>
                    <?=$bookByIsbn->eisbn ?>
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
                <div id="edit-schedule">
                    <h2>Editorial Schedule</h2>
                    <div id="edit_schedule_dates">
                        <div class="">
                            <label for="manuscript_date" class="form-label">Manuscript Delivery: </label>
                            <input type="text" class="form-input date" name="deadlines[manuscript_date][deadline_date]" value="<?=$deadlineValues['manuscript_date'] ?>" />
                            <div class='complete-field'>
                                <input type='checkbox' class='complete-check' name='deadlines[manuscript_date][complete]' value='checked' <?php if($deadlines['manuscript_date']->complete): ?>checked<?php endif; ?> />
                                <span class='complete-text'><?=($deadlines['manuscript_date']->complete) ? $deadlines['manuscript_date']->complete_date : 'Not Complete' ?></span>
                            </div>
                        </div>
                        <div class="">
                            <label for="edits_to_author" class="form-label">Edits to Author: </label>
                            <input type="text" class="form-input date" name="deadlines[edits_to_author][deadline_date]" value="<?=$deadlineValues['edits_to_author'] ?>" />
                            <div class='complete-field'>
                                <input type='checkbox' class='complete-check' name='deadlines[edits_to_author][complete]' value='checked' <?php if($deadlines['edits_to_author']->complete): ?>checked<?php endif; ?> />
                                <span class='complete-text'><?=($deadlines['edits_to_author']->complete) ? $deadlines['edits_to_author']->complete_date : 'Not Complete' ?></span>
                            </div>
                        </div>
                        <div class="">
                            <label for="revisions_in" class="form-label">Revisions In: </label>
                            <input type="text" class="form-input date" name="deadlines[revisions_in][deadline_date]" value="<?=$deadlineValues['revisions_in'] ?>" />
                            <div class='complete-field'>
                                <input type='checkbox' class='complete-check' name='deadlines[revisions_in][complete]' value='checked' <?php if($deadlines['revisions_in']->complete): ?>checked<?php endif; ?> />
                                <span class='complete-text'><?=($deadlines['revisions_in']->complete) ? $deadlines['revisions_in']->complete_date : 'Not Complete' ?></span>
                            </div>
                        </div>
                        <div class="">
                            <label for="to_copyedit" class="form-label">To Copyedit: </label>
                            <input type="text" class="form-input date" name="deadlines[to_copyedit][deadline_date]" value="<?=$deadlineValues['to_copyedit'] ?>" />
                            <div class='complete-field'>
                                <input type='checkbox' class='complete-check' name='deadlines[to_copyedit][complete]' value='checked' <?php if($deadlines['to_copyedit']->complete): ?>checked<?php endif; ?> />
                                <span class='complete-text'><?=($deadlines['to_copyedit']->complete) ? $deadlines['to_copyedit']->complete_date : 'Not Complete' ?></span>
                            </div>
                        </div>
                        <div class="">
                            <label for="manuscript_finalized" class="form-label">Manuscript Finalized: </label>
                            <input type="text" class="form-input date" name="deadlines[manuscript_finalized][deadline_date]" value="<?=$deadlineValues['manuscript_finalized'] ?>" />
                            <div class='complete-field'>
                                <input type='checkbox' class='complete-check' name='deadlines[manuscript_finalized][complete]' value='checked' <?php if($deadlines['to_copyedit']->complete): ?>checked<?php endif; ?> />
                                <span class='complete-text'><?=($deadlines['manuscript_finalized']->complete) ? $deadlines['manuscript_finalized']->complete_date : 'Not Complete' ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="pub-schedule">
                    <h2>Production Schedule</h2>
                    <div class="">
                        <label for="pub_date" class="form-label">Publication Date: </label>
                        <input type="text" id="pub_date" class="form-input date" name="pub_date" value="<?=$bookByIsbn->pub_date ?>" />
                    </div>
                    <div id="pub_schedule_dates">
                        <div class="">
                            <label for="arc_prod_date" class="form-label">To Production (ARC): </label>
                            <input type="text" class="form-input date" name="deadlines[arc_prod_date][deadline_date]" value="<?=$deadlineValues['arc_prod_date'] ?>" />
                            <div class='complete-field'>
                                <input type='checkbox' class='complete-check' name='deadlines[arc_prod_date][complete]' value='checked' <?php if($deadlines['arc_prod_date']->complete): ?>checked<?php endif; ?> />
                                <span class='complete-text'><?=($deadlines['arc_prod_date']->complete) ? $deadlines['arc_prod_date']->complete_date : 'Not Complete' ?></span>
                            </div>
                        </div>
                        <div class="">
                            <label for="arc_press_date" class="form-label">To Press (ARC): </label>
                            <input type="text" class="form-input date" name="deadlines[arc_press_date][deadline_date]" value="<?=$deadlineValues['arc_press_date'] ?>" />
                            <div class='complete-field'>
                                <input type='checkbox' class='complete-check' name='deadlines[arc_press_date][complete]' value='checked' <?php if($deadlines['arc_press_date']->complete): ?>checked<?php endif; ?> />
                                <span class='complete-text'><?=($deadlines['arc_press_date']->complete) ? $deadlines['arc_press_date']->complete_date : 'Not Complete' ?></span>
                            </div>
                        </div>
                        <div class="">
                            <label for="prod_date" class="form-label">To Production: </label>
                                <input type="text" class="form-input date" name="deadlines[prod_date][deadline_date]" value="<?=$deadlineValues['prod_date'] ?>" />
                            <div class='complete-field'>
                                <input type='checkbox' class='complete-check' name='deadlines[prod_date][complete]' value='checked' <?php if($deadlines['prod_date']->complete): ?>checked<?php endif; ?> />
                                <span class='complete-text'><?=($deadlines['prod_date']->complete) ? $deadlines['prod_date']->complete_date : 'Not Complete' ?></span>
                            </div>
                        </div>
                        <div class="">
                            <label for="press_date" class="form-label">To Press: </label>
                            <input type="text" class="form-input date" name="deadlines[press_date][deadline_date]" value="<?=$deadlineValues['press_date'] ?>" />
                            <div class='complete-field'>
                                <input type='checkbox' class='complete-check' name='deadlines[press_date][complete]' value='checked' <?php if($deadlines['press_date']->complete): ?>checked<?php endif; ?> />
                                <span class='complete-text'><?=($deadlines['press_date']->complete) ? $deadlines['press_date']->complete_date : 'Not Complete' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-1-3">
                <h2>Other Information</h2>
                <div class="">
                    <label for="adv_sign_in" class="form-label">Advance on Signature In: </label>
                    <input type="text" id="adv_sign_in" class="form-input date" name="adv_sign_in" value="<?=!empty($bookByIsbn->adv_sign_in) ? $bookByIsbn->adv_sign_in : '' ?>" />
                </div>
                <div class="">
                    <label for="adv_sign_out" class="form-label">Advance on Signature Out: </label>
                    <input type="text" id="adv_sign_out" class="form-input date" name="adv_sign_out" value="<?=!empty($bookByIsbn->adv_sign_out) ? $bookByIsbn->adv_sign_out : '' ?>" />
                </div>
                <div class="">
                    <label for="pub_sign_in" class="form-label">Advance on Publication In: </label>
                    <input type="text" id="pub_sign_in" class="form-input date" name="pub_sign_in" value="<?=!empty($bookByIsbn->pub_sign_in) ? $bookByIsbn->pub_sign_in : '' ?>" />
                </div>
                <div class="">
                    <label for="pub_sign_out" class="form-label">Advance on Publication Out: </label>
                    <input type="text" id="pub_sign_out" class="form-input date" name="pub_sign_out" value="<?=!empty($bookByIsbn->pub_sign_out) ? $bookByIsbn->pub_sign_out : '' ?>" />
                </div>
                <div class="">
                    <label for="cip_in" class="form-label">CIP In: </label>
                    <input type="text" id="cip_in" class="form-input date" name="cip_in" value="<?=!empty($bookByIsbn->cip_in) ? $bookByIsbn->cip_in : '' ?>" />
                </div>
                <div class="">
                    <label for="cip_out" class="form-label">CIP Out: </label>
                    <input type="text" id="cip_out" class="form-input date" name="cip_out" value="<?=!empty($bookByIsbn->cip_out) ? $bookByIsbn->cip_out : '' ?>" />
                </div>
                <h2>Custom Fields</h2>
                <button type="button" id="new_field" name="new_field">Add Field</button>
                <button type="button" id="delete_field" name="delete_field">Delete Field</button>
                <div class="">
                    <div id="custom-fields">
                        <?php for($i=0;$i<count($bookByIsbn->field->name);$i++) { ?>
                            <div id="custom-field">
                                Field:<br>
                                <input type="text" id="field_name" name="field[name][]" value="<?=$bookByIsbn->field->name[$i] ?>" />
                                <input type="text" id="field" name="field[value][]" value="<?=$bookByIsbn->field->value[$i] ?>" />
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div id="submit">
            <button id="update_book" name="update_book">Update</button>
            <button id="delete_book" name="delete_book">Delete</button>
        </div>
    </form>
</div>