<?php

namespace App\Controllers;

class TestAuth extends BaseController
{
    public function index()
    {
        echo "<h1>Ion Auth Test</h1>";
        
        try {
            // Test Ion Auth initialization
            echo "<p>Ion Auth initialized: " . (isset($this->ionAuth) ? "Yes" : "No") . "</p>";
            
            if (isset($this->ionAuth)) {
                echo "<p>Logged in: " . ($this->ionAuth->logged_in() ? "Yes" : "No") . "</p>";
                
                if ($this->ionAuth->logged_in()) {
                    $user = $this->ionAuth->user()->row();
                    echo "<p>User: " . $user->first_name . " " . $user->last_name . "</p>";
                    echo "<p>Email: " . $user->email . "</p>";
                    echo "<p>Is Admin: " . ($this->ionAuth->is_admin() ? "Yes" : "No") . "</p>";
                }
            } else {
                echo "<p>Ion Auth is null - authentication system not available</p>";
            }
            
        } catch (\Exception $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
            echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
        }
        
        echo "<p><a href='" . base_url('auth/login') . "'>Go to Login</a></p>";
    }
}
