#!/bin/bash

# Check if the trigger file exists
TRIGGER_FILE="/home/u835731528/public_html/dsjv_home/cron_trigger.txt"

if [ -f "$TRIGGER_FILE" ]; then
    # Define the swetest command path
    SWETEST_CMD="/path/to/swetest"
    
    # Read the options from the trigger file (optional)
    OPTIONS=$(cat /home/u835731528/public_html/dsjv_home/trigger.txt)
    
    # Run the swetest command with the options passed from the trigger file
    $SWETEST_CMD $OPTIONS

    # Clean up by removing the trigger file after execution
    rm -f "$TRIGGER_FILE"
fi
