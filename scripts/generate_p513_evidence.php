<?php
// scripts/generate_p513_evidence.php

define('FCPATH', __DIR__ . '/../public/');
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require 'system/bootstrap.php';

$db = \Config\Database::connect();

echo "--- P5-13 Verification Evidence ---

";

echo "1. Plan Stats:
";
$q1 = $db->query("SELECT status, COUNT(*) as c FROM plans GROUP BY status");
print_r($q1->getResultArray());

echo "\n2. Clinic Count:
";
$q2 = $db->query("SELECT COUNT(*) as c FROM clinics");
print_r($q2->getResultArray());

echo "\n3. Subscription Stats:
";
$q3 = $db->query("SELECT status, COUNT(*) as c FROM clinic_subscriptions GROUP BY status");
print_r($q3->getResultArray());

echo "\n4. Recent Audits (Last 10):
";
$q4 = $db->query("SELECT action_key, reason_code, created_at FROM plan_audits ORDER BY id DESC LIMIT 10");
print_r($q4->getResultArray());

echo "\n5. Routes Check:
";
// Can't easily run 'spark routes' from here and capture output cleanly, will do separate shell call.

