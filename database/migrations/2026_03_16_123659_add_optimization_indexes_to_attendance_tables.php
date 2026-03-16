<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptimizationIndexesToAttendanceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // For qr_codes table
        if (Schema::hasTable('qr_codes')) {
            Schema::table('qr_codes', function (Blueprint $table) {
                // Get all indexes on the table
                $indexes = $this->getIndexes('qr_codes');
                
                // Check if our index already exists
                if (!in_array('qr_codes_token_expires_at_index', $indexes)) {
                    $table->index(['token', 'expires_at'], 'qr_codes_token_expires_at_index');
                }
            });
        }

        // For attendances table
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                $indexes = $this->getIndexes('attendances');
                
                if (!in_array('attendances_employee_id_check_in_index', $indexes)) {
                    $table->index(['employee_id', 'check_in'], 'attendances_employee_id_check_in_index');
                }
                
                if (!in_array('attendances_date_index', $indexes)) {
                    $table->index('date', 'attendances_date_index');
                }
                
                if (!in_array('attendances_status_index', $indexes)) {
                    $table->index('status', 'attendances_status_index');
                }
            });
        }
    }

    /**
     * Get all indexes for a table
     *
     * @param string $table
     * @return array
     */
    private function getIndexes($table)
    {
        $indexes = [];
        try {
            $result = \DB::select("SHOW INDEX FROM {$table}");
            foreach ($result as $row) {
                $indexes[] = $row->Key_name;
            }
        } catch (\Exception $e) {
            // Table might not exist or other error
        }
        return array_unique($indexes);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop indexes if they exist
        if (Schema::hasTable('qr_codes')) {
            Schema::table('qr_codes', function (Blueprint $table) {
                $indexes = $this->getIndexes('qr_codes');
                if (in_array('qr_codes_token_expires_at_index', $indexes)) {
                    $table->dropIndex('qr_codes_token_expires_at_index');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                $indexes = $this->getIndexes('attendances');
                
                if (in_array('attendances_employee_id_check_in_index', $indexes)) {
                    $table->dropIndex('attendances_employee_id_check_in_index');
                }
                
                if (in_array('attendances_date_index', $indexes)) {
                    $table->dropIndex('attendances_date_index');
                }
                
                if (in_array('attendances_status_index', $indexes)) {
                    $table->dropIndex('attendances_status_index');
                }
            });
        }
    }
}