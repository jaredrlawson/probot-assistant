<?php
if (!defined('ABSPATH')) exit;

/**
 * Admin page: Article Writer (placeholder for 1.6.0 full feature)
 * - Shows fields you plan to support
 * - Disabled generate button for now (keeps 1.5.5 stable)
 * - Reads membership tier & OpenAI key to display helpful notices
 */

function probot_render_article_writer_page() {
  if (!current_user_can('manage_options')) return;

  $tier     = get_option('pbot_membership_tier', 'free'); // free|starter|pro
  $api_key  = get_option('pbot_openai_api_key', '');
  $disabled = 'disabled';
  $notice   = '';

  if (empty($api_key)) {
    $notice .= '<div class="notice notice-warning"><p>Add your OpenAI API Key in <strong>Settings</strong> to enable AI content generation.</p></div>';
  }
  if ($tier === 'free') {
    $notice .= '<div class="notice notice-info"><p>Your current tier is <strong>Free</strong>. Monthly auto-posting and advanced controls arrive with the paid tiers. (Planned in 1.6.0+)</p></div>';
  }

  ?>
  <div class="wrap pbot-wrap">
    <h1>ProBot Assistant — Article Writer</h1>
    <?php echo $notice; ?>

    <div class="pbot-card">
      <p class="pbot-muted">
        This page is a <strong>preview</strong> of the Article Writer feature coming in <strong>1.6.0</strong>:
        AI‑generated posts with title generation, category selection (or let AI choose), and posting cadence limits.
      </p>

      <form method="post" action="">
        <?php wp_nonce_field('pbot_article_writer_preview','pbot_article_writer_nonce'); ?>

        <div class="pbot-row">
          <label for="pbot_aw_topic"><strong>Topic / Prompt</strong></label>
          <input type="text" id="pbot_aw_topic" name="pbot_aw_topic" class="regular-text" placeholder="e.g. SEO basics for small businesses" />
        </div>

        <div class="pbot-row">
          <label for="pbot_aw_len"><strong>Target length</strong></label>
          <input type="number" id="pbot_aw_len" name="pbot_aw_len" min="400" max="2000" step="50" value="900" />
          <span class="pbot-muted">words (800–1000 recommended for basic plan)</span>
        </div>

        <div class="pbot-row">
          <label for="pbot_aw_category"><strong>Category</strong></label>
          <?php
            wp_dropdown_categories(array(
              'show_option_all' => '— Let me pick —',
              'hide_empty'      => 0,
              'name'            => 'pbot_aw_category',
              'id'              => 'pbot_aw_category',
              'class'           => '',
              'selected'        => 0,
            ));
          ?>
          <label style="display:flex;align-items:center;gap:6px;">
            <input type="checkbox" name="pbot_aw_ai_category" value="1" />
            Let AI choose the category
          </label>
        </div>

        <div class="pbot-row">
          <label for="pbot_aw_schedule"><strong>Posting cadence</strong></label>
          <select id="pbot_aw_schedule" name="pbot_aw_schedule">
            <option value="monthly" selected>Monthly (basic plan)</option>
            <option value="biweekly" disabled>Bi-weekly (paid)</option>
            <option value="weekly"   disabled>Weekly (paid)</option>
          </select>
        </div>

        <div class="pbot-actions-bar">
          <button class="button button-primary" type="button" <?php echo $disabled; ?>>Generate Draft (coming in 1.6.0)</button>
          <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=probot-assistant')); ?>">Settings</a>
          <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=probot-assistant-knowledge')); ?>">Knowledge Base</a>
        </div>
      </form>
    </div>

    <div class="pbot-card">
      <h2 style="margin-top:0;">What’s included in 1.6.0?</h2>
      <ul>
        <li>Title generation + 800–1000 word articles</li>
        <li>AI category selection (optional)</li>
        <li>Cadence lock per tier: monthly (basic), weekly/bi-weekly (paid)</li>
        <li>“Post as Draft” vs “Publish immediately” toggle</li>
        <li>Prompt presets and keyword helpers</li>
      </ul>
    </div>
  </div>
  <?php
}