<?php

// Script to update all migration files with Schema::hasTable checks
$migrationsDir = 'database/migrations/';
$files = glob($migrationsDir . '*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // Skip if already has Schema::hasTable check
    if (strpos($content, 'Schema::hasTable') !== false) {
        echo "Skipping $file - already has Schema::hasTable check\n";
        continue;
    }

    // Pattern to match Schema::create calls
    if (preg_match('/Schema::create\([\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        $tableName = $matches[1];

        // Replace Schema::create with Schema::hasTable check
        $pattern = '/Schema::create\([\'"]([^\'"]+)[\'"],\s*function\s*\(Blueprint\s+\$table\)\s*\{/';
        $replacement = "if (!Schema::hasTable('$tableName')) {\n        Schema::create('$tableName', function (Blueprint \$table) {";

        $content = preg_replace($pattern, $replacement, $content);

        // Add closing brace
        $content = preg_replace('/\s*\}\);\s*$/m', "        }\n    }\n", $content);

        file_put_contents($file, $content);
        echo "Updated $file\n";
    }

    // Pattern to match Schema::table calls (for adding columns)
    if (preg_match('/Schema::table\([\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        $tableName = $matches[1];

        // Replace Schema::table with Schema::hasTable check
        $pattern = '/Schema::table\([\'"]([^\'"]+)[\'"],\s*function\s*\(Blueprint\s+\$table\)\s*\{/';
        $replacement = "if (!Schema::hasTable('$tableName')) {\n            return;\n        }\n        \n        Schema::table('$tableName', function (Blueprint \$table) {";

        $content = preg_replace($pattern, $replacement, $content);

        file_put_contents($file, $content);
        echo "Updated $file (table modification)\n";
    }
}

echo "Migration files updated successfully!\n";
