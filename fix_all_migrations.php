<?php

// Comprehensive script to fix all broken migrations
$migrationsDir = 'database/migrations/';
$files = glob($migrationsDir . '*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // Check for syntax issues
    if (strpos($content, '        }') !== false && strpos($content, 'Schema::create') !== false) {
        echo "Fixing $file\n";

        // For now, let's just restore the original structure for the most critical files
        if (strpos($file, 'create_users_table') !== false) {
            $content = '<?php

use App\Constants\General\StatusConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable("users")) {
            Schema::create("users", function (Blueprint $table) {
                $table->id();
                $table->foreignId("hospital_contact_id")->nullable();
                $table->foreignId("pharmacy_contact_id")->nullable();
                $table->string("first_name");
                $table->string("last_name");
                $table->string("email")->unique();
                $table->string("phone", 45)->nullable();
                $table->string("role")->nullable();
                $table->text("address")->nullable();
                $table->string("state")->nullable();
                $table->string("city")->nullable();
                $table->string("fcm_token", 600)->nullable();
                $table->string("status")->default(StatusConstants::ACTIVE);
                $table->timestamp("last_login_at")->nullable();
                $table->timestamp("email_verified_at")->nullable();
                $table->string("password");
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable("password_reset_tokens")) {
            Schema::create("password_reset_tokens", function (Blueprint $table) {
                $table->string("email")->primary();
                $table->string("token");
                $table->timestamp("created_at")->nullable();
            });
        }

        if (!Schema::hasTable("sessions")) {
            Schema::create("sessions", function (Blueprint $table) {
                $table->string("id")->primary();
                $table->foreignId("user_id")->nullable()->index();
                $table->string("ip_address", 45)->nullable();
                $table->text("user_agent")->nullable();
                $table->longText("payload");
                $table->integer("last_activity")->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("users");
        Schema::dropIfExists("password_reset_tokens");
        Schema::dropIfExists("sessions");
    }
};';
        } elseif (strpos($file, 'create_cache_table') !== false) {
            $content = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable("cache")) {
            Schema::create("cache", function (Blueprint $table) {
                $table->string("key")->primary();
                $table->mediumText("value");
                $table->integer("expiration");
            });
        }

        if (!Schema::hasTable("cache_locks")) {
            Schema::create("cache_locks", function (Blueprint $table) {
                $table->string("key")->primary();
                $table->string("owner");
                $table->integer("expiration");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("cache");
        Schema::dropIfExists("cache_locks");
    }
};';
        } elseif (strpos($file, 'create_jobs_table') !== false) {
            $content = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable("jobs")) {
            Schema::create("jobs", function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->string("queue")->index();
                $table->longText("payload");
                $table->unsignedTinyInteger("attempts");
                $table->unsignedInteger("reserved_at")->nullable();
                $table->unsignedInteger("available_at");
                $table->unsignedInteger("created_at");
            });
        }

        if (!Schema::hasTable("failed_jobs")) {
            Schema::create("failed_jobs", function (Blueprint $table) {
                $table->id();
                $table->string("uuid")->unique();
                $table->text("connection");
                $table->text("queue");
                $table->longText("payload");
                $table->longText("exception");
                $table->timestamp("failed_at")->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("jobs");
        Schema::dropIfExists("failed_jobs");
    }
};';
        } else {
            // For other files, let's just remove the problematic parts and keep the original structure
            $content = preg_replace('/\s*\}\s*$/m', '', $content);
            $content = preg_replace('/Schema::create\([\'"]([^\'"]+)[\'"],\s*function\s*\(Blueprint\s+\$table\)\s*\{([^}]+)\}/s', 'if (!Schema::hasTable("$1")) {
            Schema::create("$1", function (Blueprint $table) {$2
        }', $content);
        }

        file_put_contents($file, $content);
    }
}

echo "Migration files fixed!\n";
