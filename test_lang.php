<?php
include_once "lang/lang.php";

// Test Tamil
init_language('tamil');
echo "Tamil Test:<br>";
echo "panchangam: " . __('panchangam') . "<br>";
echo "now: " . __('now') . "<br>";
echo "today: " . __('today') . "<br>";
echo "prev_day: " . __('prev_day') . "<br>";
echo "<br>";

// Test English
init_language('english');
echo "English Test:<br>";
echo "panchangam: " . __('panchangam') . "<br>";
echo "now: " . __('now') . "<br>";
echo "today: " . __('today') . "<br>";
echo "prev_day: " . __('prev_day') . "<br>";
