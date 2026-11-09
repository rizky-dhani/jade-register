<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One row per attendance-desk participant, for the combined attendance list.
 *
 * The desk list needs seminar and hands-on participants in a single table, but
 * they live in two tables with different shapes: a seminar participant has up to
 * three day check-ins, while a hands-on participant has one check-in per
 * session and may hold several sessions.
 *
 * A participant is one seminar registration, or — when the hands-on registration
 * has no seminar registration — one standalone hands-on registration. The two
 * sets are disjoint by construction, so the rows never double-count anyone.
 * A view is used rather than a union in PHP so the table can filter, search and
 * paginate in the database.
 */
return new class extends Migration
{
    public function up(): void
    {
        // UNION (not UNION ALL): `participant_key` is the identity, and a
        // duplicate id here would surface as a duplicated roster row.
        DB::statement(<<<'SQL'
            CREATE VIEW attendance_roster AS
            SELECT
                CONCAT('seminar-', sr.id) AS participant_key,
                'seminar' AS activity_type,
                sr.id AS seminar_registration_id,
                NULL AS hands_on_registration_id,
                COALESCE(NULLIF(sr.name_license, ''), 'N/A') AS participant_name,
                sr.registration_code,
                sr.email
            FROM seminar_registrations sr
            WHERE sr.payment_status = 'verified'

            UNION

            SELECT
                CONCAT('hands_on-', hr.id) AS participant_key,
                'hands_on' AS activity_type,
                hr.seminar_registration_id,
                hr.id AS hands_on_registration_id,
                COALESCE(
                    NULLIF((SELECT s.name_license FROM seminar_registrations s WHERE s.id = hr.seminar_registration_id), ''),
                    NULLIF(hr.name_license, ''),
                    NULLIF(hr.name, ''),
                    'N/A'
                ) AS participant_name,
                hr.registration_code,
                COALESCE(hr.email, (SELECT s.email FROM seminar_registrations s WHERE s.id = hr.seminar_registration_id)) AS email
            FROM hands_on_registrations hr
            WHERE hr.payment_status = 'verified'
              AND hr.seminar_registration_id IS NULL
            SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS attendance_roster');
    }
};
