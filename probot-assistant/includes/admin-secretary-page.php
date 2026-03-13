<?php
/**
 * ProBot Assistant — Admin: Secretary Dashboard Controller
 * The central HUD for Mirroring, Brain Status, and VPS Configuration.
 */
if (!defined('ABSPATH')) exit;

function pbot_render_secretary_page() {
    require dirname(__FILE__) . '/views/secretary-page.php';
}