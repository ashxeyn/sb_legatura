$src = 'C:\xampp\htdocs\sb_legatura\frontend\mobile_app\assets\images\pictures\members_default.png'
$bak = $src + '.bak'
if (-not (Test-Path $bak)) { Copy-Item -LiteralPath $src -Destination $bak }
Add-Type -AssemblyName System.Drawing
$img = [System.Drawing.Image]::FromFile($src)
$bmp = New-Object System.Drawing.Bitmap($img)
$img.Dispose()
$bmp.Save($src, [System.Drawing.Imaging.ImageFormat]::Png)
$bmp.Dispose()
Write-Host 'Re-encoded'