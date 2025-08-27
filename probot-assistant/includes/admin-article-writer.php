<?php
// ---------------------------------------------
// ProBot Assistant — Admin: Article Writer (controller)
// Renders the view and prepares context
// View: includes/views/writer-page.php
// ---------------------------------------------
if (!defined('ABSPATH')) exit;

require_once dirname(__FILE__) . '/article-writer-register.php';

function probot_render_article_writer_page() {
  if (!current_user_can('manage_options')) return;

  // Read current options (kept same as before)
  $tier          = get_option('pbot_membership_tier', 'free');   // free|starter|pro
  $api_key       = get_option('pbot_openai_api_key', '');
  $min_words     = (int) get_option('pbot_writer_min_words', 800);
  $max_words     = (int) get_option('pbot_writer_max_words', 1000);
  $ai_category   = (int) get_option('pbot_writer_ai_category', 1);
  $schedule      = get_option('pbot_writer_schedule', 'monthly');
  $monthly_limit = (int) get_option('pbot_writer_monthly_limit', 1);

  // Notices & disabled state (same preview behavior)
  $disabled_btn  = 'disabled';
  $notices = [];
  if (empty($api_key)) {
    $notices[] = 'Add your OpenAI API Key in <strong>Settings</strong> to enable AI content generation.';
  }
  if ($tier === 'free') {
    $notices[] = 'Your current tier is <strong>Free</strong>. Monthly auto-posting and advanced controls arrive with paid tiers (planned in 1.6.0+).';
  }

  // Pass context to the view
  $ctx = compact(
    'tier','api_key','min_words','max_words','ai_category',
    'schedule','monthly_limit','disabled_btn','notices'
  );
  extract($ctx, EXTR_SKIP);

  require dirname(__FILE__) . '/views/writer-page.php';
}