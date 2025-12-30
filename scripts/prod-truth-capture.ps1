Param(
  [string]$OutDir = "artifacts\\prod-proof",
  [string]$BaseUrl = "https://blackbox.codes"
)

$ErrorActionPreference = 'Stop'
New-Item -ItemType Directory -Force -Path $OutDir | Out-Null

$targets = @(
  @{ name = 'home'; path = '/' },
  @{ name = 'demo'; path = '/demo.php' }
)

function Save-Target($name, $path) {
  $url = ($BaseUrl.TrimEnd('/') + $path)
  $headersPath = Join-Path $OutDir ("{0}.headers.txt" -f $name)
  $htmlPath = Join-Path $OutDir ("{0}.html" -f $name)

  Write-Host ("Fetching: {0}" -f $url)
  curl.exe -sS -D $headersPath -H "Accept-Encoding: identity" -o $htmlPath $url

  $html = Get-Content -Raw -Encoding UTF8 $htmlPath
  $headMatch = [regex]::Match($html, '(?is)<head.*?</head>')

  if (-not $headMatch.Success) {
    Write-Warning ("No <head>...</head> match in {0}" -f $htmlPath)
    return
  }

  $head = $headMatch.Value
  $checks = @(
    @{ label='BBX_HEAD_MARKER'; pattern='BBX_HEAD_MARKER' },
    @{ label='css_version meta'; pattern='name="css_version"' },
    @{ label='scroll-contract rel=stylesheet'; pattern='<link[^>]+rel="stylesheet"[^>]+scroll-contract\.css' },
    @{ label='scroll-contract preload (should be ABSENT)'; pattern='<link[^>]+rel="preload"[^>]+scroll-contract\.css' }
  )

  Write-Host ("--- {0} head checks ---" -f $name)
  foreach ($c in $checks) {
    $found = [regex]::IsMatch($head, $c.pattern, [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    Write-Host ("{0}: {1}" -f $c.label, ($(if ($found) { 'FOUND' } else { 'NOT FOUND' })))
  }
}

foreach ($t in $targets) {
  Save-Target $t.name $t.path
}

Write-Host "Artifacts written to:" $OutDir
