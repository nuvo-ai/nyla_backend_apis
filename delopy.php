<?php

// ✅ Set your GitHub webhook secret
$secret = 'nyla-webhook-secret-2025!';

// ✅ Full path to your Laravel API project
$projectPath = '/home/nylaafri/public_html/api.nyla.africa';

// ✅ Branch to deploy
$branch = 'staging';

// ✅ Validate GitHub signature
$payload = file_get_contents('php://input');
$headers = getallheaders();

if (!isset($headers['X-Hub-Signature-256'])) {
    http_response_code(400);
    die('Missing signature header');
}

$signature = 'sha256=' . hash_hmac('sha256', $payload, $secret, false);
if (!hash_equals($signature, $headers['X-Hub-Signature-256'])) {
    http_response_code(403);
    die('Invalid signature');
}

// ✅ Execute Laravel deployment commands
$output = [];
exec("
    cd $projectPath &&
    git pull origin $branch &&
    composer install --no-dev --optimize-autoloader &&
    php artisan migrate --force &&
    php artisan config:cache &&
    php artisan route:cache &&
    php artisan view:clear
", $output);

// ✅ Optional: log output
file_put_contents($projectPath . '/deploy.log', implode("\n", $output) . "\n", FILE_APPEND);

// ✅ Return response
echo "Laravel API deployed successfully:\n";
echo implode("\n", $output);
