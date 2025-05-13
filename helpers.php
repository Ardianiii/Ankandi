<?php
function getTimeLeft($end_time) {
    $now = new DateTime();
    $end = new DateTime($end_time);
    $interval = $now->diff($end);

    if ($end < $now) {
        return "Auction Ended";
    }

    return $interval->format('%a days, %h hours, %i minutes');
}
