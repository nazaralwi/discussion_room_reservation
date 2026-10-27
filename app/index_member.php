<?php
defined('INDEX_AUTH') or die('Direct access not allowed!');

require DRRB . DS . 'app/member_page/' . (class_exists('SLiMS\Auth\Validator') ? 'current.php' : 'legacy.php');
