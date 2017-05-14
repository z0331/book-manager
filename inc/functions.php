<?php

/*
* generateDateBoxHtml
*
* Generates HTML specifically for boxes related to dates/deadlines
*
* @param (string) date/deadline in the field
* @param (string) label for the date/deadline
* @param (string) isbn for title for checkbox value
* @param (boolean) flag for ARC option; default is false
* @param (boolean) flag for completed task; default is false
* @param (string) date completed; default is blank
* @return (string) HTML code
*/
function generateDateBoxHtml($date, $dateType, $isbn, $arc = false, $completed = false, $completeDate = "") {
    $html = "";

    //For if box is specifically for ARC deadline for non-ARC book
    if (!$arc && ($dateType === "arc_prod_date" || $dateType === "arc_press_date")) {
        $html = "<td class='deadline'>No ARC</td>";
    }
    //Else, check if deadline is completed
    else if ($completed) {
        $html = "<td class='deadline completed'>" . $completeDate . "</td>";
    }
    //Otherwise, move on to general deadline box, regardless of if empty of not
    else {
        $html = "<td class='deadline";

        //If date available and given date is late, add late class for visual
        if(!empty($date) && checkLateDate($date)) { 
                $html .= " late"; 
        }
        //Regardless, close off opening td tag
        $html .= "'>";
        //If there is a date available, display it and create a completion checkbox
        if (!empty($date)) {   
            $html .= $date 
                . "<br><label for='" 
                . $dateType 
                . "'>Done</label>"
                . "<input type='checkbox' id='"
                . $dateType
                . "' name='completed[]'"
                . " value='"
                . $isbn . "," . $dateType
                . "'>";
        }
        $html .= "</td>";
    }
    return $html;
}

/*
* checkCurrentSeason
*
* Checks which season is the current one by comparing current date with given date ranges
*
* @param (string) season to check
* @param (string) year to check
* @return (boolean)
*/
function checkCurrentSeason($season, $year) {
    $currentSeason = '';
    $thisYear = date('Y');
    $thisMonth = date('m');

    if ($season === 'spring') {
        $checkSeason = array(
            '03', '04', '05', '06', '07', '08'
        );
    } else {
        $checkSeason = array(
            '09', '10', '11', '12', '01', '02'
        );
    }

    if (in_array($thisMonth, $checkSeason) && $year === $thisYear) {
        return true;
    }
}

/*
* checkLateDate
*
* Compares a provided date to today and checks whether it is late (before today) or not
*
* @param (string) Date to compare to today's date
* @return (boolean) true if late (before today); false if today or after
*/
function checkLateDate($date) {
    if ($date < date('Y-m-d')) {
        return true;
    }
    else {
        return false;
    }
}

/*
* updateCompletedDeadline
*
* Updates completed deadlines from submitted post array
*
* @param (CouchDB client) client for retrieving required documents
* @param (array) Post data of deadlines marked as completed
*/
function updateCompletedDeadline($client, $postDataArray) {
    $today = date('Y-m-d');
    try {
        foreach ($postDataArray as $postString) {
            $keyArray = explode(',', $postString);                          //Create array from post string
            $bookByIsbn = $client->getDoc($keyArray[0]);                    //Get book from ISBN from array
            $deadlineKey = $keyArray[1];                                    //Get which deadline from array
            $bookByIsbn->deadlines->$deadlineKey->complete = true;
            $bookByIsbn->deadlines->$deadlineKey->complete_date = $today;
            $response = $client->storeDoc($bookByIsbn);
        }
    }
    catch (Exception $e) {
        echo $e->getMessage . " " . $e->getCode() . "<br>";
    }
}

?>