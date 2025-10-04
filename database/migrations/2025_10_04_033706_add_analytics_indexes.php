<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Volunteer Activities Indexes
        Schema::table('volunteer_activities', function (Blueprint $table) {
            // Chỉ tạo index nếu chưa tồn tại
            if (!$this->indexExists('volunteer_activities', 'idx_status_activity_date')) {
                $table->index(['status', 'activity_date'], 'idx_status_activity_date');
            }
            if (!$this->indexExists('volunteer_activities', 'idx_org_status')) {
                $table->index(['org_id', 'status'], 'idx_org_status');
            }
            if (!$this->indexExists('volunteer_activities', 'idx_volunteer_status')) {
                $table->index(['volunteer_id', 'status'], 'idx_volunteer_status');
            }
        });

        // Applications Indexes
        Schema::table('applications', function (Blueprint $table) {
            if (!$this->indexExists('applications', 'idx_status_applied_date')) {
                $table->index(['status', 'applied_date'], 'idx_status_applied_date');
            }
            if (!$this->indexExists('applications', 'idx_opportunity')) {
                $table->index('opportunity_id', 'idx_opportunity');
            }
            if (!$this->indexExists('applications', 'idx_volunteer')) {
                $table->index('volunteer_id', 'idx_volunteer');
            }
        });

        // Users Indexes
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'idx_type_created')) {
                $table->index(['user_type', 'created_at'], 'idx_type_created');
            }
            if (!$this->indexExists('users', 'idx_active_type')) {
                $table->index(['is_active', 'user_type'], 'idx_active_type');
            }
        });

        // Volunteer Opportunities Indexes
        Schema::table('volunteer_opportunities', function (Blueprint $table) {
            if (!$this->indexExists('volunteer_opportunities', 'idx_status_created')) {
                $table->index(['status', 'created_at'], 'idx_status_created');
            }
            if (!$this->indexExists('volunteer_opportunities', 'idx_org_status')) {
                $table->index(['org_id', 'status'], 'idx_org_status');
            }
            if (!$this->indexExists('volunteer_opportunities', 'idx_category')) {
                $table->index('category_id', 'idx_category');
            }
        });

        // Organizations Indexes
        Schema::table('organizations', function (Blueprint $table) {
            if (!$this->indexExists('organizations', 'idx_verification_created')) {
                $table->index(['verification_status', 'created_at'], 'idx_verification_created');
            }
            if (!$this->indexExists('organizations', 'idx_volunteer_count')) {
                $table->index('volunteer_count', 'idx_volunteer_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('volunteer_activities', function (Blueprint $table) {
            if ($this->indexExists('volunteer_activities', 'idx_status_activity_date')) {
                $table->dropIndex('idx_status_activity_date');
            }
            // KHÔNG drop idx_org_status vì có foreign key
            if ($this->indexExists('volunteer_activities', 'idx_volunteer_status')) {
                $table->dropIndex('idx_volunteer_status');
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            if ($this->indexExists('applications', 'idx_status_applied_date')) {
                $table->dropIndex('idx_status_applied_date');
            }
            // idx_opportunity và idx_volunteer có thể có foreign key - skip
        });

        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'idx_type_created')) {
                $table->dropIndex('idx_type_created');
            }
            if ($this->indexExists('users', 'idx_active_type')) {
                $table->dropIndex('idx_active_type');
            }
        });

        Schema::table('volunteer_opportunities', function (Blueprint $table) {
            if ($this->indexExists('volunteer_opportunities', 'idx_status_created')) {
                $table->dropIndex('idx_status_created');
            }
            // Không drop idx_org_status vì có foreign key
            if ($this->indexExists('volunteer_opportunities', 'idx_category')) {
                $table->dropIndex('idx_category');
            }
        });

        Schema::table('organizations', function (Blueprint $table) {
            if ($this->indexExists('organizations', 'idx_verification_created')) {
                $table->dropIndex('idx_verification_created');
            }
            if ($this->indexExists('organizations', 'idx_volunteer_count')) {
                $table->dropIndex('idx_volunteer_count');
            }
        });
    }

    /**
     * Check if index exists
     */
    private function indexExists($table, $index)
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $doctrineTable = $dbSchemaManager->listTableDetails($table);
        
        return $doctrineTable->hasIndex($index);
    }
};