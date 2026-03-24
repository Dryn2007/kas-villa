<?php
$files = ['app/Services/DuitkuService.php', 'app/Http/Controllers/DuitkuController.php'];
foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    // Add // before Log::
    $content = preg_replace('/(\s+)(Log::(info|error|warning)\()/', '$1// $2', $content);
    file_put_contents($file, $content);
}
echo "Done";
