<?php

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
        Schema::table('partner_bills', function (Blueprint $table): void {
            $table->index(
                ['status', 'category_id', 'location_id', 'created_at', 'id'],
                'partner_bills_realtime_lookup_index'
            );

            $table->index(
                ['partner_id', 'status', 'date', 'start_time'],
                'partner_bills_partner_schedule_index'
            );

            $table->index(
                ['partner_id', 'status', 'updated_at'],
                'partner_bills_partner_status_updated_index'
            );

            $table->index(
                ['partner_id', 'status', 'created_at'],
                'partner_bills_partner_status_created_index'
            );

            $table->index(
                ['partner_id', 'location_id', 'id'],
                'partner_bills_partner_location_lookup_index'
            );

            $table->index(
                ['client_id', 'status', 'id'],
                'partner_bills_client_status_id_index'
            );

            $table->index('code', 'partner_bills_code_index');
        });

        Schema::table('partner_bill_details', function (Blueprint $table): void {
            $table->index(
                ['partner_bill_id', 'partner_id', 'status'],
                'partner_bill_details_bill_partner_status_index'
            );

            $table->index(
                ['partner_id', 'status', 'partner_bill_id'],
                'partner_bill_details_partner_status_bill_index'
            );
        });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->index(
                ['reviewable_type', 'reviewable_id', 'created_at', 'partner_bill_id'],
                'reviews_partner_recent_lookup_index'
            );

            $table->index(
                ['partner_bill_id', 'reviewable_type', 'user_id'],
                'reviews_bill_reviewable_user_index'
            );
        });

        Schema::table('ratings', function (Blueprint $table): void {
            $table->index(
                ['key', 'review_id'],
                'ratings_key_review_index'
            );
        });

        Schema::table('statistics', function (Blueprint $table): void {
            $table->index(
                ['user_id', 'metrics_name'],
                'statistics_user_metrics_name_index'
            );
        });

        Schema::table('file_product_bills', function (Blueprint $table): void {
            $table->index(
                ['client_id', 'created_at'],
                'file_product_bills_client_created_index'
            );

            $table->index(
                ['file_product_id', 'status', 'client_id'],
                'file_product_bills_file_status_client_index'
            );
        });

        Schema::table('file_products', function (Blueprint $table): void {
            $table->index('slug', 'file_products_slug_index');

            $table->index(
                ['category_id', 'created_at'],
                'file_products_category_created_index'
            );
        });

        Schema::table('rent_products', function (Blueprint $table): void {
            $table->index('slug', 'rent_products_slug_index');

            $table->index(
                ['category_id', 'created_at'],
                'rent_products_category_created_index'
            );
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->index('slug', 'categories_slug_index');

            $table->index(
                ['type', 'parent_id', 'is_show', 'order'],
                'categories_type_parent_show_order_index'
            );
        });

        Schema::table('locations', function (Blueprint $table): void {
            $table->index(
                ['parent_id', 'name'],
                'locations_parent_name_index'
            );
        });

        Schema::table('reports', function (Blueprint $table): void {
            $table->index(
                ['reporter_id', 'reported_user_id', 'status'],
                'reports_reporter_user_status_index'
            );

            $table->index(
                ['reporter_id', 'reported_bill_id', 'status'],
                'reports_reporter_bill_status_index'
            );
        });

        Schema::table('banners', function (Blueprint $table): void {
            $table->index('type', 'banners_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table): void {
            $table->dropIndex('banners_type_index');
        });

        Schema::table('reports', function (Blueprint $table): void {
            $table->dropIndex('reports_reporter_bill_status_index');
            $table->dropIndex('reports_reporter_user_status_index');
        });

        Schema::table('locations', function (Blueprint $table): void {
            $table->dropIndex('locations_parent_name_index');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex('categories_type_parent_show_order_index');
            $table->dropIndex('categories_slug_index');
        });

        Schema::table('rent_products', function (Blueprint $table): void {
            $table->dropIndex('rent_products_category_created_index');
            $table->dropIndex('rent_products_slug_index');
        });

        Schema::table('file_products', function (Blueprint $table): void {
            $table->dropIndex('file_products_category_created_index');
            $table->dropIndex('file_products_slug_index');
        });

        Schema::table('file_product_bills', function (Blueprint $table): void {
            $table->dropIndex('file_product_bills_file_status_client_index');
            $table->dropIndex('file_product_bills_client_created_index');
        });

        Schema::table('statistics', function (Blueprint $table): void {
            $table->dropIndex('statistics_user_metrics_name_index');
        });

        Schema::table('ratings', function (Blueprint $table): void {
            $table->dropIndex('ratings_key_review_index');
        });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropIndex('reviews_bill_reviewable_user_index');
            $table->dropIndex('reviews_partner_recent_lookup_index');
        });

        Schema::table('partner_bill_details', function (Blueprint $table): void {
            $table->dropIndex('partner_bill_details_partner_status_bill_index');
            $table->dropIndex('partner_bill_details_bill_partner_status_index');
        });

        Schema::table('partner_bills', function (Blueprint $table): void {
            $table->dropIndex('partner_bills_code_index');
            $table->dropIndex('partner_bills_client_status_id_index');
            $table->dropIndex('partner_bills_partner_location_lookup_index');
            $table->dropIndex('partner_bills_partner_status_created_index');
            $table->dropIndex('partner_bills_partner_status_updated_index');
            $table->dropIndex('partner_bills_partner_schedule_index');
            $table->dropIndex('partner_bills_realtime_lookup_index');
        });
    }
};
