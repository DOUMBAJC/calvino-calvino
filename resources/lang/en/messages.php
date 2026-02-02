<?php

return [
    // General messages
    'welcome' => 'Welcome to our API',
    'not_found' => 'Resource not found',
    'unauthorized' => 'Unauthorized',
    'forbidden' => 'Forbidden access',
    'validation_error' => 'Validation error',
    'server_error' => 'Server error',
    'success' => 'Operation successful',
    'created' => 'Resource created successfully',
    'updated' => 'Resource updated successfully',
    'deleted' => 'Resource deleted successfully',
    
    // Authentication messages
    'auth' => [
        'login_success' => 'Login successful',
        'login_failed' => 'Invalid credentials',
        'logout_success' => 'Logout successful',
        'token_invalid' => 'Invalid authentication token',
        'token_expired' => 'Authentication token expired',
        'token_required' => 'Authentication token required',
        'account_locked' => 'Account locked',
        'account_inactive' => 'Account inactive',
        'password_reset_sent' => 'Password reset email sent',
        'password_reset_success' => 'Password reset successful',
    ],
    
    // Product messages
    'products' => [
        'created' => 'Product created successfully',
        'updated' => 'Product updated successfully',
        'deleted' => 'Product deleted successfully',
        'not_found' => 'Product not found',
        'out_of_stock' => 'Product out of stock',
        'low_stock' => 'Low stock for this product',
        'expired' => 'Product expired',
        'expiring_soon' => 'Product expiring soon',
    ],
    
    // Order messages
    'orders' => [
        'created' => 'Order created successfully',
        'updated' => 'Order updated successfully',
        'deleted' => 'Order deleted successfully',
        'not_found' => 'Order not found',
        'processed' => 'Order processed successfully',
        'shipped' => 'Order shipped',
        'delivered' => 'Order delivered',
        'cancelled' => 'Order cancelled',
        'payment_required' => 'Payment required for this order',
        'payment_received' => 'Payment received for this order',
    ],
    
    // User messages
    'users' => [
        'created' => 'User created successfully',
        'updated' => 'User updated successfully',
        'deleted' => 'User deleted successfully',
        'not_found' => 'User not found',
        'password_changed' => 'Password changed successfully',
        'profile_updated' => 'Profile updated successfully',
    ],
    
    // Supplier messages
    'suppliers' => [
        'created' => 'Supplier created successfully',
        'updated' => 'Supplier updated successfully',
        'deleted' => 'Supplier deleted successfully',
        'not_found' => 'Supplier not found',
    ],
    
    // Category messages
    'categories' => [
        'created' => 'Category created successfully',
        'updated' => 'Category updated successfully',
        'deleted' => 'Category deleted successfully',
        'not_found' => 'Category not found',
    ],
    
    // Invoice messages
    'invoices' => [
        'created' => 'Invoice created successfully',
        'updated' => 'Invoice updated successfully',
        'deleted' => 'Invoice deleted successfully',
        'not_found' => 'Invoice not found',
        'paid' => 'Invoice paid',
        'partially_paid' => 'Invoice partially paid',
        'overdue' => 'Invoice overdue',
        'sent' => 'Invoice sent to customer',
    ],
    
    // Error messages
    'errors' => [
        'default' => 'An error occurred',
        'connection' => 'Connection error',
        'database' => 'Database error',
        'file_upload' => 'Error uploading file',
        'file_too_large' => 'File is too large',
        'invalid_format' => 'Invalid format',
        'required_field' => 'This field is required',
    ],
]; 