<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isPgsql = DB::getDriverName() === 'pgsql';

        if ($isPgsql) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        }

        Schema::create('operators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->text('website_url')->nullable();
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('data_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 180);
            $table->string('source_type', 40);
            $table->text('url')->nullable();
            $table->string('license_name', 120)->nullable();
            $table->text('terms_url')->nullable();
            $table->text('attribution_text')->nullable();
            $table->string('access_method', 40);
            $table->boolean('active')->default(true);
            $table->timestampTz('last_reviewed_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('vessels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('operator_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mmsi', 9)->unique();
            $table->string('imo', 10)->nullable()->index();
            $table->string('name', 180);
            $table->string('normalized_name', 180)->index();
            $table->string('call_sign', 32)->nullable();
            $table->string('vessel_category', 40);
            $table->string('verification_status', 30)->default('DRAFT');
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('public_visible')->default(false);
            $table->timestampsTz();
        });

        if ($isPgsql) {
            DB::statement("ALTER TABLE vessels ADD CONSTRAINT vessels_mmsi_format_check CHECK (mmsi ~ '^[0-9]{9}$')");
            DB::statement('ALTER TABLE vessels ADD CONSTRAINT vessels_confidence_check CHECK (confidence_score BETWEEN 0 AND 100)');
            DB::statement("ALTER TABLE vessels ADD CONSTRAINT vessels_category_check CHECK (vessel_category IN ('RORO', 'ROPAX', 'FERRY_RORO'))");
            DB::statement("ALTER TABLE vessels ADD CONSTRAINT vessels_verification_check CHECK (verification_status IN ('DRAFT', 'REVIEW', 'VERIFIED', 'REJECTED'))");
            DB::statement("ALTER TABLE vessels ADD CONSTRAINT vessels_publication_check CHECK (NOT public_visible OR verification_status = 'VERIFIED')");
        }

        Schema::create('ports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('name', 180);
            $table->string('city_name', 120)->nullable();
            $table->string('province_name', 120)->nullable();
            $table->integer('geofence_radius_m')->nullable();
            $table->string('verification_status', 30)->default('DRAFT');
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        if ($isPgsql) {
            DB::statement('ALTER TABLE ports ADD COLUMN center_point geography(Point, 4326) NOT NULL');
            DB::statement('ALTER TABLE ports ADD COLUMN geofence_geometry geography(Polygon, 4326)');
            DB::statement('CREATE INDEX ports_center_point_gist ON ports USING GIST (center_point)');
            DB::statement('CREATE INDEX ports_geofence_geometry_gist ON ports USING GIST (geofence_geometry)');
            DB::statement('ALTER TABLE ports ADD CONSTRAINT ports_geofence_radius_check CHECK (geofence_radius_m IS NULL OR geofence_radius_m > 0)');
        } else {
            // SQLite fallback for tests: store lat/lon directly
            Schema::table('ports', function (Blueprint $table) {
                $table->double('latitude')->nullable();
                $table->double('longitude')->nullable();
            });
        }

        Schema::create('routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('origin_port_id')->constrained('ports')->restrictOnDelete();
            $table->foreignUuid('destination_port_id')->constrained('ports')->restrictOnDelete();
            $table->string('name', 220);
            $table->string('route_type', 40);
            $table->boolean('bidirectional')->default(true);
            $table->boolean('active')->default(true);
            $table->timestampsTz();
            $table->unique(['origin_port_id', 'destination_port_id', 'route_type']);
        });

        if ($isPgsql) {
            DB::statement('ALTER TABLE routes ADD CONSTRAINT routes_distinct_ports_check CHECK (origin_port_id <> destination_port_id)');
            DB::statement("ALTER TABLE routes ADD CONSTRAINT routes_type_check CHECK (route_type IN ('RORO', 'ROPAX'))");
        }

        Schema::create('vessel_route_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('route_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('source_id')->nullable()->constrained('data_sources')->nullOnDelete();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('verification_status', 30)->default('DRAFT');
            $table->timestampsTz();
            $table->unique(['vessel_id', 'route_id', 'valid_from']);
        });

        Schema::create('vessel_latest_positions', function (Blueprint $table) {
            $table->foreignUuid('vessel_id')->primary()->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->decimal('sog_knots', 6, 2)->nullable();
            $table->decimal('cog_degrees', 6, 2)->nullable();
            $table->unsignedSmallInteger('heading_degrees')->nullable();
            $table->string('nav_status', 60)->nullable();
            $table->string('destination_text', 200)->nullable();
            $table->timestampTz('source_timestamp')->index();
            $table->timestampTz('received_at');
            $table->string('provider_name', 80);
            $table->string('raw_message_id', 120)->nullable();
            $table->timestampTz('updated_at')->useCurrent();
        });

        if ($isPgsql) {
            DB::statement('ALTER TABLE vessel_latest_positions ADD COLUMN position geography(Point, 4326) NOT NULL');
            DB::statement('CREATE INDEX vessel_latest_positions_position_gist ON vessel_latest_positions USING GIST (position)');
            $this->addPositionChecks('vessel_latest_positions');
        }

        Schema::create('vessel_position_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('vessel_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->decimal('sog_knots', 6, 2)->nullable();
            $table->decimal('cog_degrees', 6, 2)->nullable();
            $table->unsignedSmallInteger('heading_degrees')->nullable();
            $table->timestampTz('source_timestamp');
            $table->timestampTz('received_at');
            $table->string('provider_name', 80);
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['vessel_id', 'source_timestamp'], 'history_vessel_time_index');
        });

        if ($isPgsql) {
            DB::statement('ALTER TABLE vessel_position_history ADD COLUMN position geography(Point, 4326) NOT NULL');
            DB::statement('CREATE INDEX vessel_position_history_position_gist ON vessel_position_history USING GIST (position)');
            $this->addPositionChecks('vessel_position_history');
        }

        Schema::create('registry_evidence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vessel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('port_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('route_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('data_source_id')->constrained()->restrictOnDelete();
            $table->string('evidence_type', 40);
            $table->text('source_reference');
            $table->jsonb('observed_value');
            $table->decimal('confidence_score', 5, 2);
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();
        });

        if ($isPgsql) {
            DB::statement('ALTER TABLE registry_evidence ADD CONSTRAINT registry_evidence_subject_check CHECK (num_nonnulls(vessel_id, port_id, route_id) = 1)');
            DB::statement('ALTER TABLE registry_evidence ADD CONSTRAINT registry_evidence_confidence_check CHECK (confidence_score BETWEEN 0 AND 100)');
        }

        Schema::create('port_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('port_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 30);
            $table->timestampTz('event_time')->index();
            $table->string('detection_method', 30);
            $table->decimal('confidence_score', 5, 2);
            $table->unsignedBigInteger('source_position_history_id')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->foreign('source_position_history_id')->references('id')->on('vessel_position_history')->nullOnDelete();
        });

        if ($isPgsql) {
            DB::statement("ALTER TABLE port_events ADD CONSTRAINT port_events_type_check CHECK (event_type IN ('ENTERED_GEOFENCE', 'ARRIVED', 'DEPARTED', 'EXITED_GEOFENCE'))");
            DB::statement('ALTER TABLE port_events ADD CONSTRAINT port_events_confidence_check CHECK (confidence_score BETWEEN 0 AND 100)');
        }

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80);
            $table->string('entity_type', 80);
            $table->string('entity_id', 80);
            $table->jsonb('before_data')->nullable();
            $table->jsonb('after_data')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('port_events');
        Schema::dropIfExists('registry_evidence');
        Schema::dropIfExists('vessel_position_history');
        Schema::dropIfExists('vessel_latest_positions');
        Schema::dropIfExists('vessel_route_assignments');
        Schema::dropIfExists('routes');
        Schema::dropIfExists('ports');
        Schema::dropIfExists('vessels');
        Schema::dropIfExists('data_sources');
        Schema::dropIfExists('operators');
    }

    private function addPositionChecks(string $table): void
    {
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_latitude_check CHECK (latitude BETWEEN -90 AND 90)");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_longitude_check CHECK (longitude BETWEEN -180 AND 180)");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_sog_check CHECK (sog_knots IS NULL OR sog_knots >= 0)");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_cog_check CHECK (cog_degrees IS NULL OR cog_degrees BETWEEN 0 AND 360)");
        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_heading_check CHECK (heading_degrees IS NULL OR heading_degrees BETWEEN 0 AND 359)");
    }
};
