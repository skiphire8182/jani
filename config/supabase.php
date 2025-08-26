<?php
/**
 * Supabase Configuration
 * Environment variables should be set in your hosting environment
 */

return [
    // Database connection
    'db_host' => $_ENV['SUPABASE_DB_HOST'] ?? 'db.your-project-ref.supabase.co',
    'db_port' => $_ENV['SUPABASE_DB_PORT'] ?? '5432',
    'db_name' => $_ENV['SUPABASE_DB_NAME'] ?? 'postgres',
    'db_user' => $_ENV['SUPABASE_DB_USER'] ?? 'postgres',
    'db_password' => $_ENV['SUPABASE_DB_PASSWORD'] ?? '',
    
    // Supabase API
    'supabase_url' => $_ENV['SUPABASE_URL'] ?? 'https://your-project-ref.supabase.co',
    'supabase_anon_key' => $_ENV['SUPABASE_ANON_KEY'] ?? '',
    'supabase_service_role_key' => $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? '',
    
    // Application settings
    'app_name' => 'Jani Pakwan Center',
    'app_version' => '2.0.0',
    'timezone' => 'Asia/Karachi',
    
    // Security settings
    'session_lifetime' => 3600, // 1 hour
    'password_min_length' => 6,
    'max_login_attempts' => 5,
    
    // File upload settings
    'max_file_size' => 10 * 1024 * 1024, // 10MB
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
    
    // Pagination
    'default_page_size' => 20,
    'max_page_size' => 100,
];