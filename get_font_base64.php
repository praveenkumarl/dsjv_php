<?php
$fontData = file_get_contents('NotoSansTamil-Regular.ttf');
echo base64_encode($fontData);
