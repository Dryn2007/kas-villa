<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $req = Illuminate\Http\Request::create('/dummy-pay-bulk', 'POST', [
        'selected_ids' => '1',
        'metode_pembayaran' => 'online'
    ]);
    // Simulate auth user if possible
    $user = \App\Models\User::first();
    if ($user) {
        $req->setUserResolver(function() use ($user) { return $user; });
        //$req->session()->put('user_id', $user->id); 
    }
    
    // Find the actual correct unpaid ID
    $firstUnpaid = \App\Models\Pembayaran::where('user_id', $user->id)->whereNotIn('status', ['lunas', 'proses'])->orderBy('bulan_ke', 'asc')->first();
    if ($firstUnpaid) {
        $req->merge(['selected_ids' => (string) $firstUnpaid->id]);
    }

    $res = app('App\Http\Controllers\DashboardController')->dummyPayBulk($req, new \App\Services\DuitkuService());
    echo "Response: " . get_class($res) . PHP_EOL;
    if (method_exists($res, 'getTargetUrl')) echo "Target Url: " . $res->getTargetUrl() . PHP_EOL;
    // print session
    print_r(app('session')->all());
} catch (\Throwable $e) {
    echo "Error: [" . get_class($e) . "] " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}