# ==========================================================
# 1. USER CONFIGURATION (UPDATE THESE THREE LINES)
# ==========================================================
# Define where to SEARCH for the modified files (Your Project Root)
$LocalProjectRoot = "D:\praveen\dsjv_home\"

# Define the path where the file list will be SAVED
# NOTE: This file must be uploaded to Hostinger for the backup script.
$OutputFilePath = "D:\praveen\dsjv_home\backup_file_list.txt"

# Define the modification date for the check
$TargetDate = [datetime]"11/21/2025"

# ==========================================================
# 2. SCRIPT LOGIC (DO NOT MODIFY)
# ==========================================================

# Use the [System.Uri]::UnescapeDataString trick to ensure paths are correctly handled, 
# and use a trim approach to ensure no leading/trailing whitespace or slashes.
$RootLength = $LocalProjectRoot.Length

Write-Host "Searching in: $($LocalProjectRoot)"
Write-Host "Target Date: $($TargetDate)"
Write-Host "Output File: $($OutputFilePath)"
Write-Host "---"

# Get all files recursively that were modified on or after the target date
Get-ChildItem -Path $LocalProjectRoot -File -Recurse | 
    Where-Object { 
        # Exclude the output file itself from the list
        $_.FullName -ne $OutputFilePath -and $_.LastWriteTime -ge $TargetDate 
    } | 
    ForEach-Object { 
        # 1. Remove the absolute root path.
        # 2. Replace Windows backslashes (\) with Linux forward slashes (/).
        # 3. Trim any leading/trailing slashes just in case.
        $RelativePath = $_.FullName.Substring($RootLength).Replace('\', '/')
        
        # Remove a leading slash if it exists
        if ($RelativePath.StartsWith('/')) {
            $RelativePath = $RelativePath.Substring(1)
        }
        $RelativePath
    } | 
    # Output the list to the text file using ASCII encoding for clean transfer to Linux
    Out-File -FilePath $OutputFilePath -Encoding ASCII

Write-Host "---"
Write-Host "✅ Success! File list created."
Write-Host "The list of files to backup/deploy is saved here: $($OutputFilePath)"
Write-Host ""
Write-Host "NEXT STEP: Upload the 'backup_file_list.txt' to your Hostinger public_html folder."