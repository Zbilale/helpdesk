<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

requireAuth();
jsonResponse(['user' => currentUser()]);
