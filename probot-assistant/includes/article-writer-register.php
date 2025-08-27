<?php
// ---------------------------------------------
// ProBot Assistant — Admin: Article Writer (registration only)
// Ensures settings exist regardless of load order
// ---------------------------------------------
if (!defined('ABSPATH')) exit;

add_action('admin_init', function () {
  // Membership / tier
  register_setting('pbot_writer_settings', 'pbot_membership_tier', [
    'type'              => 'string',
    'sanitize_callback' => function($v){
      $v = is_string($v) ? strtolower(trim($v)) : 'free';
      return in_array($v, ['free','starter','pro'], true) ? $v : 'free';
    },
    'default'           => 'free',
  ]);

  // Writer scaffolding (already added on activation; registered here for future real form)
  register_setting('pbot_writer_settings', 'pbot_writer_enabled',      ['type'=>'integer','sanitize_callback'=>fn($v)=>empty($v)?0:1, 'default'=>0]);
  register_setting('pbot_writer_settings', 'pbot_writer_min_words',    ['type'=>'integer','sanitize_callback'=>fn($v)=>max(200,(int)$v), 'default'=>800]);
  register_setting('pbot_writer_settings', 'pbot_writer_max_words',    ['type'=>'integer','sanitize_callback'=>fn($v)=>max(200,(int)$v), 'default'=>1000]);
  register_setting('pbot_writer_settings', 'pbot_writer_ai_category',  ['type'=>'integer','sanitize_callback'=>fn($v)=>empty($v)?0:1, 'default'=>1]);
  register_setting('pbot_writer_settings', 'pbot_writer_schedule', [
    'type'=>'string',
    'sanitize_callback'=>function($v){
      $v = is_string($v) ? strtolower(trim($v)) : 'monthly';
      return in_array($v, ['monthly','biweekly','weekly'], true) ? $v : 'monthly';
    },
    'default'=>'monthly'
  ]);
  register_setting('pbot_writer_settings', 'pbot_writer_monthly_limit',['type'=>'integer','sanitize_callback'=>fn($v)=>max(0,(int)$v),'default'=>1]);

  // Defensive: key is registered in Settings, but keep if view reads it directly
  register_setting('pbot_settings', 'pbot_openai_api_key');
});