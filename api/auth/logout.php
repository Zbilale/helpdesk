<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

session_destroy();
jsonResponse(['message' => 'Logged out.']);
