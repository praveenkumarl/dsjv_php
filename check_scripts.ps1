$txt = Get-Content out.html -Raw
$rx = [regex]::Matches($txt,'<script\b[^>]*>(.*?)</script>','Singleline')
for($i=0;$i -lt $rx.Count;$i++){
    $script = $rx[$i].Groups[1].Value
    $startLine = ($txt.Substring(0,$rx[$i].Index) -split "`n").Length
    $backticks = ($script -split "```").Length-1
    $single = ($script -split "'").Length-1
    $double = ($script -split '"').Length-1
    $openBraces = ($script -split '{').Length-1
    $closeBraces = ($script -split '}').Length-1
    Write-Output ("Script#{0} startLine:{1} backticks:{2} singles:{3} doubles:{4} opens:{5} closes:{6}" -f $i,$startLine,$backticks,$single,$double,$openBraces,$closeBraces)
    if($openBraces -ne $closeBraces){ Write-Output '--- MISMATCH BRACES ---'; Write-Output $script.Substring(0,[Math]::Min(1000,$script.Length)); }
    if($backticks %2 -ne 0){ Write-Output '--- ODD BACKTICKS ---'; Write-Output $script.Substring(0,[Math]::Min(1000,$script.Length)); }
    if($single %2 -ne 0 -or $double %2 -ne 0){ Write-Output '--- ODD QUOTES ---'; Write-Output $script.Substring(0,[Math]::Min(1000,$script.Length)); }
}
