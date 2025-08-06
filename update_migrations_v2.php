<?php

// Script to update table modification migrations with column existence checks
$migrationsDir = 'database/migrations/';
$files = glob($migrationsDir . '*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // Only process files that modify existing tables (add columns)
    if (strpos($content, 'Schema::table') !== false && strpos($content, 'Schema::hasTable') !== false) {
        // Find all column additions and wrap them with hasColumn checks
        $lines = explode("\n", $content);
        $newLines = [];
        $inTableFunction = false;
        $indentLevel = 0;

        foreach ($lines as $line) {
            $newLines[] = $line;

            // Check if we're entering the table function
            if (strpos($line, 'Schema::table') !== false) {
                $inTableFunction = true;
                $indentLevel = 1;
                continue;
            }

            // Check if we're exiting the table function
            if ($inTableFunction && strpos($line, '});') !== false) {
                $inTableFunction = false;
                continue;
            }

            // If we're inside the table function and find a column addition
            if ($inTableFunction && preg_match('/\$table->(addColumn|string|integer|boolean|decimal|text|enum|foreignId|timestamp|softDeletes|dropColumn|dropForeign)/', $line)) {
                // Extract the column name
                if (preg_match('/\$table->(string|integer|boolean|decimal|text|enum|foreignId|timestamp|softDeletes)\([\'"]([^\'"]+)[\'"]/', $line, $matches)) {
                    $columnName = $matches[2];

                    // Insert the hasColumn check before this line
                    $indent = str_repeat('    ', $indentLevel);
                    $checkLine = $indent . "if (!Schema::hasColumn('" . getTableName($content) . "', '$columnName')) {";
                    array_splice($newLines, count($newLines) - 1, 0, $checkLine);

                    // Add closing brace after the line
                    $newLines[] = $indent . "}";
                }
            }
        }

        $content = implode("\n", $newLines);
        file_put_contents($file, $content);
        echo "Updated column checks in $file\n";
    }
}

function getTableName($content) {
    if (preg_match('/Schema::table\([\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        return $matches[1];
    }
    return '';
}

echo "Migration files updated with column existence checks!\n";
