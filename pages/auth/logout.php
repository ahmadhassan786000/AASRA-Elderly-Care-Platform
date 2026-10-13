<?php
$wasAdmin = (current_user()['role'] ?? '') === 'admin';
logout_user();
session_start();
flash('success', 'You have been logged out.');
redirect('');

