<?php
// Define the options you want to pass to swetest
$options = "-edir./sweph/ -b12.02.2025 -ut06:38:27 -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house80.2785,13.0878,P";

// Write the options to the trigger file
file_put_contents("/home/u835731528/public_html/dsjv_home/trigger.txt", $options);

echo "Trigger file created with options: " . $options;
?>
