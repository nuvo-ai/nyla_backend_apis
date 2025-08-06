<?php

// Script to fix broken migrations
$migrationsDir = 'database/migrations/';
$files = glob($migrationsDir . '*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // Check if file has syntax issues (multiple Schema::create blocks)
    if (substr_count($content, 'Schema::create') > 1) {
        echo "Fixing $file\n";

        // Extract the table names and their structures
        preg_match_all('/Schema::create\([\'"]([^\'"]+)[\'"],\s*function\s*\(Blueprint\s+\$table\)\s*\{([^}]+)\}/s', $content, $matches, PREG_SET_ORDER);

        $newContent = "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{\n    /**\n     * Run the migrations.\n     */\n    public function up(): void\n    {\n";

        foreach ($matches as $match) {
            $tableName = $match[1];
            $tableStructure = $match[2];

            $newContent .= "        if (!Schema::hasTable('$tableName')) {\n";
            $newContent .= "            Schema::create('$tableName', function (Blueprint \$table) {";
            $newContent .= $tableStructure;
            $newContent .= "            });\n";
            $newContent .= "        }\n\n";
        }

        // Add the down method
        $newContent .= "    }\n\n    /**\n     * Reverse the migrations.\n     */\n    public function down(): void\n    {\n";

        foreach ($matches as $match) {
            $tableName = $match[1];
            $newContent .= "        Schema::dropIfExists('$tableName');\n";
        }

        $newContent .= "    }\n};\n";

        file_put_contents($file, $newContent);
    }
}

echo "Migration files fixed!\n";
