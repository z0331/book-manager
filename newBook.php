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

<div class="container form">
    <form method="post" action="index.php">
        <div>
            New Book
        </div>
        <div class="row">
            <div class="col-1-3">
                <h2>Basic Data</h2>
                <div class="">
                    <label for="title">Title: </label>
                    <input type="text" id="title" name="title" />
                </div>
                <div class="">
                    <label for="subtitle">Subtitle: </label>
                    <input type="text" id="subtitle" name="subtitle" />
                </div>
                <div class="">
                    <div id="contributors">
                        <div id="contributor">
                            <label for="contributor">Contributor: </label>
                            <input type="text" id="contributor" name="contributor[]" />
                        </div>
                    </div>
                    <button type="button" id="new_contributor" name="new_contributor">Add Contributor</button>
                    <button type="button" id="delete_contributor" name="delete_contributor">Delete Contributor</button>
                </div>
                <div class="">
                    <label for="isbn">ISBN: </label>
                    <input type="text" id="isbn" name="isbn" />
                </div>
                <div class="">
                    <label for="eisbn">EISBN: </label>
                    <input type="text" id="eisbn" name="eisbn" />
                </div>
                <div class="">
                    <label for="imprint">Imprint: </label>
                    <select name="imprint">
                        <option value="arcade">Arcade</option>
                        <option value="skyhorse">Skyhorse</option>
                        <option value="talos">Talos</option>
                    </select>
                </div>
                <div class="">
                    <label for="season">Season: </label>
                    <select name="season">
                        <option value="spring">Spring</option>
                        <option value="fall">Fall</option>
                    </select>
                    <input type="text" id="season_year" name="season_year" />
                </div>
                <div class="">
                    <label for="arc">ARC</label>
                    <input type="checkbox" id="arc" name="arc" value="ARC">
                </div>
            </div>
            <div class="col-1-3">
                <h2>Editorial Schedule</h2>
                <div id="edit_schedule">
                    <div id="edit_schedule_dates">
                        <div class="">
                            <label for="manuscript_delivery">Manuscript Delivery: </label>
                            <input type="text" id="manuscript_date" class="date" name="manuscript_date" />
                            <button type="button" id="default_edit_schedule" name="default_edit_schedule">Default Schedule</button>
                        </div>
                        <div class="">
                            <label for="edits_to_author">Edits to Author: </label>
                            <input type="text" id="edits_to_author" class="date" name="edits_to_author" />
                        </div>
                        <div class="">
                            <label for="revisions_in">Revisions In: </label>
                            <input type="text" id="revisions_in" class="date" name="revisions_in" />
                        </div>
                        <div class="">
                            <label for="to_copyedit">To Copyedit: </label>
                            <input type="text" id="to_copyedit" class="date" name="to_copyedit" />
                        </div>
                        <div class="">
                            <label for="manuscript_finalized">Manuscript Finalized: </label>
                            <input type="text" id="manuscript_finalized" class="date" name="manuscript_finalized" />
                        </div>
                    </div>
                </div>

                <h2>Production Schedule</h2>
                <div id="pub_schedule">
                    <div class="">
                        <label for="pub_date">Publication Date: </label>
                        <input type="text" id="pub_date" class="date" name="pub_date" />
                        <button type="button" id="default_pub_schedule" name="default_pub_schedule">Default Schedule</button>
                    </div>
                    <div id="pub_schedule_dates">
                        <div class="">
                            <label for="arc_prod_date">To Production (ARC): </label>
                            <input type="text" id="arc_prod_date" class="date" name="arc_prod_date" />
                        </div>
                        <div class="">
                            <label for="arc_press_date">To Press (ARC): </label>
                            <input type="text" id="arc_press_date" class="date" name="arc_press_date" />
                        </div>
                        <div class="">
                            <label for="prod_date">To Production: </label>
                            <input type="text" id="prod_date" class="date" name="prod_date" />
                        </div>
                        <div class="">
                            <label for="press_date">To Press: </label>
                            <input type="text" id="press_date" class="date" name="press_date" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-1-3">
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
            <button id="new_book" name="new_book">Submit</button>
        </div>
    </form>
</div>