<?php session_start();
require "lib.php";

$TZ = isset($_SESSION['TZ']) ? $_SESSION['TZ'] : "Asia/Kolkata";

// Initialize translations based on GET/SESSION/COOKIE
$detected_lang = 'tamil';
if (isset($_GET['lang']) && $_GET['lang'] === 'english') {
    $detected_lang = 'english';
} elseif (isset($_SESSION['language'])) {
    $detected_lang = $_SESSION['language'];
} elseif (isset($_COOKIE['language'])) {
    $detected_lang = $_COOKIE['language'];
}
init_language($detected_lang);

$place = isset($_GET['place']) ? $_GET['place'] : (isset($_COOKIE['panchang_place']) ? $_COOKIE['panchang_place'] : '');

// Clean up place value if it contains URL-encoded characters without %
if ($place && !strpos($place, '|')) {
    $place = str_replace(['%7C', '%2F', ' 7C', ' 2F', '7C', '2F'], ['|', '/', '|', '/', '|', '/'], $place);
}

date_default_timezone_set($TZ);

$DEBUG = (int) isset($_GET['debug']) ? $_GET['debug'] : 0;

$curr_month = date('m');
// Build month names from translation keys (greg_month_1..12)
$month_names = [];
for ($i = 1; $i <= 12; $i++) {
    $month_names[$i] = __("greg_month_{$i}");
}

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PK Monthly Panchang</title>

    <link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
    <link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
    <link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
    <link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script type="text/javascript">
    // Translation strings for JS
    var MSG_PLEASE_FILL = <?php echo json_encode(__('please_fill_all_fields')); ?>;
    var MSG_GENERATING = <?php echo json_encode(__('generating')); ?>;
    var MSG_PDF_GENERATED = <?php echo json_encode('PDF generated! Download will start shortly.'); ?>;
    var MSG_PDF_FAILED = <?php echo json_encode('PDF generation failed. Please try again.'); ?>;

    function init(){
        var currentYear = new Date().getFullYear();
        var el = document.getElementById("year");
        if (el) el.value = currentYear;
    }

    function onlyNumberKey(evt) {
        var ASCIICode = (evt.which) ? evt.which : evt.keyCode;
        if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
            return false;
        return true;
    }

    $(document).ready(function(){
        $('.search-box input[type="text"]').on("keyup input", function(){
            var inputVal = $(this).val();
            var resultDropdown = $(this).siblings(".result");
            if(inputVal.length>2){
                $.get("backend-search.php", {term: inputVal}).done(function(data){
                   resultDropdown.html(data);
                });
            } else{
                resultDropdown.empty();
            }
        });

        $(document).on("click", ".result div", function(){
            $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
            $(this).parent(".result").empty();
        });
    });

    function goBack(){
        window.open('new_panchang.php','_self');
    }

    function isAndroidWebView() {
        var ua = navigator.userAgent.toLowerCase();
        return ua.indexOf('android') > -1;
    }

    function handleFormSubmit(event) {
        if (isAndroidWebView()) {
            event.preventDefault();

            var form = document.panchangForm;
            var month = form.month.value;
            var year = form.year.value;
            var place = form.place.value;

            if (!month || !year || !place) {
                alert(MSG_PLEASE_FILL);
                return false;
            }

            var submitBtn = document.getElementById('submit');
            if (submitBtn) {
                submitBtn.value = MSG_GENERATING;
                submitBtn.disabled = true;
            }

            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'gen_panchang_pdf.php?generate_pdf=1&api=1&month=' + encodeURIComponent(month) +
                     '&year=' + encodeURIComponent(year) + '&place=' + encodeURIComponent(place), true);

            xhr.onload = function() {
                if (submitBtn) { submitBtn.value = '<?php echo addslashes(__('submit')); ?>'; submitBtn.disabled = false; }

                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success && response.download_url) {
                            window.open(response.download_url, '_blank');
                            alert(MSG_PDF_GENERATED);
                        } else {
                            alert(MSG_PDF_FAILED);
                        }
                    } catch (e) {
                        alert('Error processing response');
                    }
                } else {
                    alert('PDF generation failed. Status: ' + xhr.status);
                }
            };

            xhr.onerror = function() {
                if (submitBtn) { submitBtn.value = '<?php echo addslashes(__('submit')); ?>'; submitBtn.disabled = false; }
                alert('Network error. Please check your connection.');
            };

            xhr.send();
            return false;
        }
        return true;
    }
    </script>
</head>

<body onload="init();">

<div class="title"></div>

<div class='column-menu' style="margin-left:0px">
<?php displayUserInfo($_SESSION['userinfo']);?>
    <div class='desktop'>
        <button class="menu" onClick="window.open('index.php','_self')"><?php echo __('back'); ?></button>
    </div>
    <div class='mobile'>
        <?php include "onePagePanchangMenu_mobile.php"; ?>
    </div>
</div>

<div class='column-body'>
    <br>
    <div class="heading">*<?php _e('panchang_input'); ?></div>
    <br>
    <form name="panchangForm" action="gen_panchang_pdf.php" method="post" target="_self" onsubmit="return handleFormSubmit(event)">
    <?php // ensure the selected language is posted to the PDF generator ?>
    <input type="hidden" name="lang" value="<?php echo htmlspecialchars(get_current_language(), ENT_QUOTES); ?>" />
    <table width="350px">
        <tr style="background:none;">
            <td style="vertical-align:middle">
                <div class='divPanchang' style='border:none'>*<?php _e('month'); ?></div>
            </td>
            <td style="vertical-align:middle">
                <select name="month" id="month" style="width:fit-content;font-size:12px;background:#FDE966;font-weight:bold" onChange="formSubmitDropdown()">
                    <?php for($m=1;$m<=12;$m++): ?>
                        <option value="<?php echo $m; ?>" <?php if((int)$curr_month== $m){echo 'selected';} ?> ><?php echo htmlspecialchars($month_names[$m], ENT_QUOTES); ?></option>
                    <?php endfor; ?>
                </select>
            </td>
        </tr>
        <tr style="background:none;">
            <td style="vertical-align:middle">
                <div class='divPanchang' style='border:none'>*<?php _e('year'); ?></div>
            </td>
            <td style="text-align:left">
                <input type="number" name="year" id="year" maxlength="4" autocomplete="off" style="width:80px;height: 2.75em;font-size:12px;background:#FDE966;font-weight:bold; text-align:center" placeholder="<?php echo __('year'); ?>..." onkeypress="return onlyNumberKey(event)" />
            </td>
        </tr>
        <tr style="background:none;">
            <td style="vertical-align:middle">
                <div class='divPanchang' style='border:none'>*<?php _e('place'); ?></div>
            </td>
            <td style="text-align:left">
                <div class="search-box"><input type="text" name="place" id="place" value="<?php echo htmlspecialchars($place, ENT_QUOTES); ?>" onFocus="this.value=''" autocomplete="off" style="width:200px;height: 2.75em;font-size:12px;background:#FDE966;font-weight:bold; text-align:center" placeholder="<?php echo __('search_place_placeholder'); ?>"/><div class="result"></div>
            </td>
        </tr>
        <tr style="background:none;">
            <td></td>
            <td style="text-align:left">
                <input type='submit' class="actionBtn" id='submit' value='<?php echo __('submit'); ?>' />
                <input type='button' class="actionBtn" onclick="goBack()" value="<?php echo __('back'); ?>" />
            </td>
        </tr>
    </table>
    </form>
</div>

</body>
</html>
