<?php
$f = 'app/Services/DuitkuService.php';
$content = file_get_contents($f);
$content = str_replace('catch (\Exception', 'catch (\Throwable', $content);
file_put_contents($f, $content);

$f2 = 'app/Http/Controllers/DashboardController.php';
$c2 = file_get_contents($f2);
$c2 = str_replace('catch (\Exception', 'catch (\Throwable', $c2);
file_put_contents($f2, $c2);

echo "Done\n";