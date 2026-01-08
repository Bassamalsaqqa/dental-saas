<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class SessionTest extends Controller
{
    public function index()
    {
        $session = \Config\Services::session();
        
        // Test session data
        $session->set('test_data', 'Hello from session!');
        $session->set('test_time', date('Y-m-d H:i:s'));
        
        // Get session data
        $testData = $session->get('test_data');
        $testTime = $session->get('test_time');
        $sessionId = $session->getSessionID();
        
        echo "<h1>Session Test</h1>";
        echo "<p><strong>Session ID:</strong> " . $sessionId . "</p>";
        echo "<p><strong>Test Data:</strong> " . $testData . "</p>";
        echo "<p><strong>Test Time:</strong> " . $testTime . "</p>";
        echo "<p><strong>Session Status:</strong> " . ($session->isStarted() ? 'Started' : 'Not Started') . "</p>";
        
        // Check if user is logged in
        $ionAuth = new \App\Libraries\IonAuth();
        echo "<p><strong>User Logged In:</strong> " . ($ionAuth->loggedIn() ? 'Yes' : 'No') . "</p>";
        
        // Debug session data
        echo "<h3>Session Debug Info:</h3>";
        echo "<p><strong>Identity:</strong> " . ($session->get('identity') ?: 'Not set') . "</p>";
        echo "<p><strong>Email:</strong> " . ($session->get('email') ?: 'Not set') . "</p>";
        echo "<p><strong>User ID:</strong> " . ($session->get('user_id') ?: 'Not set') . "</p>";
        echo "<p><strong>Last Check:</strong> " . ($session->get('last_check') ?: 'Not set') . "</p>";
        
        // Show all session data
        echo "<h3>All Session Data:</h3>";
        echo "<pre>" . print_r($session->get(), true) . "</pre>";
        
        if ($ionAuth->loggedIn()) {
            $user = $ionAuth->user()->row();
            echo "<p><strong>User ID:</strong> " . ($user ? $user->id : 'Not found') . "</p>";
            echo "<p><strong>User Email:</strong> " . ($user ? $user->email : 'Not found') . "</p>";
        }
        
        echo "<p><a href='/session-test/check'>Check Session Data</a></p>";
    }
    
    public function check()
    {
        $session = \Config\Services::session();
        
        echo "<h1>Session Check</h1>";
        echo "<p><strong>Session ID:</strong> " . $session->getSessionID() . "</p>";
        echo "<p><strong>Test Data:</strong> " . $session->get('test_data', 'Not found') . "</p>";
        echo "<p><strong>Test Time:</strong> " . $session->get('test_time', 'Not found') . "</p>";
        
        // Check IonAuth session data
        $ionAuth = new \App\Libraries\IonAuth();
        echo "<p><strong>User Logged In:</strong> " . ($ionAuth->loggedIn() ? 'Yes' : 'No') . "</p>";
        
        if ($ionAuth->loggedIn()) {
            $user = $ionAuth->user()->row();
            echo "<p><strong>User ID:</strong> " . ($user ? $user->id : 'Not found') . "</p>";
            echo "<p><strong>User Email:</strong> " . ($user ? $user->email : 'Not found') . "</p>";
        }
        
        echo "<p><a href='/session-test'>Back to Session Test</a></p>";
    }
}
