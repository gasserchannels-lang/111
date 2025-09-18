# PowerShell script to analyze duplicate files
$duplicates = @()
$duplicateFiles = Get-Content "duplicates_basenames.txt"

foreach ($basename in $duplicateFiles) {
    $files = Get-ChildItem -Recurse -Path . -Exclude vendor -Name $basename
    foreach ($file in $files) {
        $fullPath = $file
        $content = Get-Content $file -ErrorAction SilentlyContinue
        $namespace = ""
        $classname = ""

        # Extract namespace
        $namespaceLine = $content | Where-Object { $_ -match "^\s*namespace\s+([^;]+)" }
        if ($namespaceLine) {
            $namespace = $matches[1]
        }

        # Extract class name
        $classLine = $content | Where-Object { $_ -match "^\s*class\s+(\w+)" }
        if ($classLine) {
            $classname = $matches[1]
        }

        $fqcn = if ($namespace -and $classname) { "$namespace\$classname" } else { "none" }
        $fileInfo = Get-Item $file
        $size = $fileInfo.Length
        $modified = $fileInfo.LastWriteTime.ToString("yyyy-MM-ddTHH:mm:ssZ")

        $collisionType = "No Collision"
        if ($fqcn -ne "none") {
            $collisionType = "Potential FQCN Collision"
        }

        $recommendation = "RENAME_FILE"
        $priority = "Medium"
        $effort = "5"

        $duplicates += [PSCustomObject]@{
            basename                 = $basename
            path                     = $fullPath
            namespace                = $namespace
            classname                = $classname
            fqcn                     = $fqcn
            file_size_bytes          = $size
            last_modified_iso        = $modified
            collision_type           = $collisionType
            recommendation           = $recommendation
            priority                 = $priority
            estimated_effort_minutes = $effort
        }
    }
}

# Export to CSV
$duplicates | Export-Csv -Path "duplicates_detailed.csv" -NoTypeInformation -Encoding UTF8

# Show summary
Write-Host "Found $($duplicates.Count) duplicate file instances"
Write-Host "Unique basenames: $($duplicates | Select-Object -ExpandProperty basename -Unique | Measure-Object).Count"
