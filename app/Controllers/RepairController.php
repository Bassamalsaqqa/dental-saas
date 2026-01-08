<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class RepairController extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();
        $output = "";

        $output .= "Checking finances table...<br>";
        $fields = $db->getFieldNames('finances');

        if (!in_array('total_amount', $fields)) {
            $output .= "Adding total_amount column...<br>";
            try {
                $db->query("ALTER TABLE finances ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0.00 AFTER tax_amount");
                $output .= "total_amount added successfully!<br>";
            } catch (\Exception $e) {
                $output .= "Error adding total_amount: " . $e->getMessage() . "<br>";
            }
        } else {
            $output .= "total_amount already exists.<br>";
        }

        $output .= "Updating currency enum...<br>";
        try {
            $db->query("ALTER TABLE finances MODIFY COLUMN currency ENUM('USD','EUR','GBP','BDT','ILS') DEFAULT 'USD'");
            $output .= "Currency enum updated successfully!<br>";
        } catch (\Exception $e) {
            $output .= "Error updating currency enum: " . $e->getMessage() . "<br>";
        }

        $output .= "<br>Checking users table...<br>";
        $fields = $db->getFieldNames('users');
        $output .= "Fields: " . implode(', ', $fields) . "<br>";

        // List mandatory fields missing from common insert
        $mandatory = ['ip_address', 'created_on'];
        foreach ($mandatory as $m) {
            if (in_array($m, $fields)) {
                $output .= "Field '$m' exists. Making it nullable if possible.<br>";
                try {
                    if ($m == 'ip_address') {
                        $db->query("ALTER TABLE users MODIFY COLUMN ip_address VARCHAR(45) NULL");
                        $output .= "Made ip_address nullable.<br>";
                    } elseif ($m == 'created_on') {
                        $db->query("ALTER TABLE users MODIFY COLUMN created_on INT(11) UNSIGNED NULL");
                        $output .= "Made created_on nullable.<br>";
                    }
                } catch (\Exception $e) {
                    $output .= "Error modifying $m: " . $e->getMessage() . "<br>";
                }
            }
        }

        // Check for missing fields that the application expects
        $expected = ['hire_date', 'active', 'address'];
        foreach ($expected as $e) {
            if (!in_array($e, $fields)) {
                $output .= "Missing field in users: $e. Adding it...<br>";
                try {
                    if ($e == 'hire_date') {
                        $db->query("ALTER TABLE users ADD COLUMN hire_date DATE NULL AFTER phone");
                    } elseif ($e == 'active') {
                        $db->query("ALTER TABLE users ADD COLUMN active TINYINT(1) DEFAULT 1 AFTER hire_date");
                    } elseif ($e == 'address') {
                        $db->query("ALTER TABLE users ADD COLUMN address TEXT NULL AFTER active");
                    }
                    $output .= "Added $e successfully.<br>";
                } catch (\Exception $ex) {
                    $output .= "Error adding $e: " . $ex->getMessage() . "<br>";
                }
            }
        }

        $output .= "<br>Repairs completed. You can now delete this file and route.";

        return $output;
    }
}
