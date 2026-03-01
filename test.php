<!DOCTYPE html>
<html>
<head>
<title>Tabbed DivTables Demo</title>
<style>
.tabButton {
  background-color: #f1f1f1;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 5px 5px;
  font-size: 12px;
  margin: 2px;
}

.tabButton:hover {
  background-color: #ddd;
}

.tabButton.active {
  background-color: #ccc;
}

.tabContent {
  display: none;
  padding: 20px;
}

/* Some basic table styling */
.divTable {
  display: table;
  width: 80%;
  border: 1px solid #000;
  background-color: #f9f9f9;
}

.divTableRow {
  display: table-row;
}

.divTableCell {
  display: table-cell;
  border: 1px solid #000;
  padding: 8px;
  text-align: center;
}

H6 {
  margin: 0;
}
</style>
</head>

<body>

<?php
// --- Tab Buttons ---
echo "<div id='tabsContainer' style='text-align:center;margin-bottom:20px;'>";
echo "<button class='tabButton' onclick=\"openTab('starlordTab1')\">கோட் கிரக சாரம்</button>";
echo "<button class='tabButton' onclick=\"openTab('starlordTab2')\">பாடி கிரக சாரம்</button>";
echo "</div>";

// --- TAB 1 CONTENT ---
echo "<div id='starlordTab1' class='tabContent'>";

echo "<center><div class='divTable blueTable' id='starlordHeadMutable_transit' style='height:32px;margin-top:20px'>";
echo "<div class='divTableRow'>";
echo "<div class='divTableCell' colspan=3>";
echo "<H6 style='font-weight:bold;color:black'>கோட் கிரக சாரம் (Header)</H6>";
echo "</div>";
echo "</div>";
echo "</div></center>";

echo "<center><div class='divTable blueTable' id='starlordBodyMutable_transit' style='height:auto'>";
echo "<div class='divTableRow'>";
echo "<div class='divTableCell'>" . __('col_graha') . "</div>";
echo "<div class='divTableCell'>" . __('col_naksha_pa') . "</div>";
echo "<div class='divTableCell'>" . __('col_nakshatra_athi') . "</div>";
echo "</div>";

$planet_starlord_transit = [
    "சனி" => ["பூராடம்", "சனி", 12.34, 45.67],
    "குரு" => ["புனர்பூசம்", "குரு", 78.91, 23.45],
];

foreach ($planet_starlord_transit as $planet => $data) {
    echo "<div class='divTableRow'>";
    echo "<div class='divTableCell'>$planet</div>";
    echo "<div class='divTableCell'>{$data[0]}</div>";
    echo "<div class='divTableCell'>{$data[1]}</div>";
    echo "</div>";
}

echo "</div></center>";

echo "</div>";

// --- TAB 2 CONTENT ---
echo "<div id='starlordTab2' class='tabContent'>";

echo "<center><div class='divTable blueTable' style='height:32px;margin-top:20px'>";
echo "<div class='divTableRow'>";
echo "<div class='divTableCell' colspan=3>";
echo "<H6 style='font-weight:bold;color:black'>பாடி கிரக சாரம் (Header)</H6>";
echo "</div>";
echo "</div>";
echo "</div></center>";

echo "<center><div class='divTable blueTable' style='height:auto'>";
echo "<div class='divTableRow'>";
echo "<div class='divTableCell'>" . __('col_graha') . "</div>";
echo "<div class='divTableCell'>" . __('col_naksha_pa') . "</div>";
echo "<div class='divTableCell'>" . __('col_nakshatra_athi') . "</div>";
echo "</div>";

$planet_padilord_transit = [
    "செ" => ["அதிரை", "செ", 56.78, 12.34],
    "சுக்" => ["உத்திரம்", "சுக்", 90.12, 34.56],
];

foreach ($planet_padilord_transit as $planet => $data) {
    echo "<div class='divTableRow'>";
    echo "<div class='divTableCell'>$planet</div>";
    echo "<div class='divTableCell'>{$data[0]}</div>";
    echo "<div class='divTableCell'>{$data[1]}</div>";
    echo "</div>";
}

echo "</div></center>";

echo "</div>";

?>

<script>
function openTab(tabId) {
  var i, tabContent, tabButtons;
  
  // Hide all tab contents
  tabContent = document.getElementsByClassName("tabContent");
  for (i = 0; i < tabContent.length; i++) {
    tabContent[i].style.display = "none";
  }
  
  // Remove 'active' class from all buttons
  tabButtons = document.getElementsByClassName("tabButton");
  for (i = 0; i < tabButtons.length; i++) {
    tabButtons[i].classList.remove("active");
  }
  
  // Show selected tab and highlight the button
  document.getElementById(tabId).style.display = "block";
  event.currentTarget.classList.add("active");
}

// Auto open first tab
document.addEventListener("DOMContentLoaded", function() {
  document.querySelector(".tabButton").click();
});
</script>

</body>
</html>
