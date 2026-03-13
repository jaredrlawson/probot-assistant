<?php
// ---------------------------------------------
// ProBot Assistant — Admin: Article Writer (controller)
// Renders the view and prepares context
// View: views/writer-page.php
// ---------------------------------------------
if (!defined('ABSPATH')) exit;

require_once dirname(__FILE__) . '/article-writer-register.php';

/** ---------- License / usage helpers ---------- */
function pbot_license_server_url(): string {
  // Default to your domain; you can override in Settings with option pbot_license_server_url
  $url = trim((string) get_option('pbot_license_server_url', 'https://jaredrlawson.com'));
  return rtrim($url, '/');
}

function pbot_fetch_license_from_server(string $key): ?array {
  $base = pbot_license_server_url();
  if ($base === '' || $key === '') return null;

  $url = $base.'/wp-json/pbls/v1/keys/verify';
  $res = wp_remote_get($url, [
    'timeout' => 15,
    'headers' => ['X-Product-Key' => $key]
  ]);
  if (is_wp_error($res)) return null;

  $code = wp_remote_retrieve_response_code($res);
  if ($code !== 200) return null;

  $data = json_decode(wp_remote_retrieve_body($res), true);
  if (empty($data['ok'])) return null;

  // Normalize payload for our UI
  return [
    'valid'         => !empty($data['valid']),
    'tier'          => (string)($data['tier'] ?? 'free'),
    'limit'         => (int)($data['limit'] ?? 0),
    'left'          => (int)($data['left'] ?? 0),
    'unlimited'     => !empty($data['unlimited']),
    'proxy_allowed' => true,
    'key_last4'     => substr($key, -4),
  ];
}

function pbot_license_check(): array {
  $key = trim((string) get_option('pbot_product_key', ''));
  if ($key === '') {
    return [
      'valid'         => false,
      'tier'          => 'free',
      'limit'         => 0,
      'left'          => 0,
      'unlimited'     => false,
      'proxy_allowed' => false,
      'key_last4'     => '',
    ];
  }

  // 1) Live validation against License Server
  if ($lic = pbot_fetch_license_from_server($key)) {
    return $lic;
  }

  // 2) Fallback (no server): use local tier option strictly (NO unlimited shortcut)
  $tier_opt = get_option('pbot_membership_tier', 'free');
  $limits   = ['free' => 1, 'starter' => 10, 'pro' => 50];
  $tier     = array_key_exists($tier_opt, $limits) ? $tier_opt : 'free';

  $usage = get_option('pbot_writer_usage', []);
  $ym    = gmdate('Y-m');
  $used  = isset($usage[$ym]) ? (int)$usage[$ym] : 0;
  $limit = (int)$limits[$tier];
  $left  = max(0, $limit - $used);

  return [
    'valid'         => true,
    'tier'          => $tier,
    'limit'         => $limit,
    'left'          => $left,
    'unlimited'     => false,
    'proxy_allowed' => true,
    'key_last4'     => substr($key, -4),
  ];
}

function pbot_writer_usage_get(string $ym=null): int {
  $ym    = $ym ?: gmdate('Y-m');
  $usage = get_option('pbot_writer_usage', []);
  return isset($usage[$ym]) ? (int)$usage[$ym] : 0;
}
function pbot_writer_usage_inc(string $ym=null): void {
  $ym    = $ym ?: gmdate('Y-m');
  $usage = get_option('pbot_writer_usage', []);
  $usage[$ym] = isset($usage[$ym]) ? ((int)$usage[$ym] + 1) : 1;
  update_option('pbot_writer_usage', $usage);
}

function pbot_writer_can_generate(array $lic): array {
  $api_key = trim((string) get_option('pbot_openai_api_key',''));
  if ($api_key !== '') return [true, 'Using your OpenAI API key'];
  if ($lic['valid'] && $lic['proxy_allowed']) {
    if (!empty($lic['unlimited'])) return [true, 'Unlimited credits (Product Key)'];
    $used   = pbot_writer_usage_get();
    $remain = max(0, (int)$lic['limit'] - $used);
    return $remain > 0 ? [true, "Using Product Key credits ($remain left this month)"] : [false, 'Credit limit reached'];
  }
  return [false, 'Add your OpenAI API key or Product Key'];
}

/** ---------- POST handler: generate article ---------- */
add_action('admin_post_pbot_generate_article','probot_handle_article_writer_generate');

function probot_handle_article_writer_generate() {
  if (!current_user_can('edit_posts')) wp_die('Unauthorized', 403);
  check_admin_referer('pbot_generate_article_nonce','pbot_nonce');

  $api_key     = trim((string) get_option('pbot_openai_api_key',''));
  $proxy_url   = trim((string) get_option('pbot_writer_proxy_url',''));
  $product_key = trim((string) get_option('pbot_product_key',''));
  $lic         = pbot_license_check();

  // Inputs
  $title     = sanitize_text_field($_POST['pbot_post_title'] ?? '');
  $brief     = sanitize_textarea_field($_POST['pbot_brief'] ?? '');
  $keywords  = sanitize_text_field($_POST['pbot_keywords'] ?? '');
  $wordcount = max(300, (int)($_POST['pbot_wordcount'] ?? 900));
  $tone      = sanitize_text_field($_POST['pbot_tone'] ?? 'helpful, friendly, professional');
  $headings  = sanitize_textarea_field($_POST['pbot_headings'] ?? '');
  $outline   = !empty($_POST['pbot_include_outline']);
  $gen_meta  = !empty($_POST['pbot_include_meta']);
  $slug      = sanitize_title($_POST['pbot_slug'] ?? '');

  if ($brief === '') {
    return probot_writer_redirect('Please provide a brief/context.', 'error');
  }

  // Cap only on credits route (API route is uncapped by us)
  if ($api_key === '') {
    if (!empty($lic['unlimited'])) {
      $wordcount = max(300, min(6000, $wordcount));
    } else {
      $cap_by_tier = ['free'=>600,'starter'=>1200,'pro'=>6000];
      $cap = $cap_by_tier[$lic['tier'] ?? 'free'] ?? 600;
      $wordcount = max(300, min($cap, $wordcount));
    }
  }

  // Compose prompt
  $required_headings_block = $headings ? "Required headings:\n".$headings : '';
  $title_instr = $title !== '' ? "Title: {$title}\n" : "Generate a compelling SEO-friendly title.\n";

  $sys = ['role'=>'system','content'=>
    "You are an expert SEO content writer. Write clean Markdown for WordPress.\n".
    "- Use headings, bullets, short paragraphs.\n".
    "- Include intro and conclusion with CTA.\n".
    "- Target length: {$wordcount} words."
  ];
  $usr = ['role'=>'user','content'=>
    $title_instr.
    "Brief/Context: {$brief}\n".
    "Keywords: {$keywords}\n".
    "Tone: {$tone}\n".
    ($outline ? "Include an outline before the article.\n" : "").
    ($required_headings_block ? $required_headings_block."\n" : "").
    "Return Markdown only."
  ];
  $messages = [$sys, $usr];

  $article_md=''; $meta_title=''; $meta_desc='';

  if ($api_key !== '') {
    // Direct OpenAI route
    $resp = wp_remote_post('https://api.openai.com/v1/chat/completions',[
      'headers'=>['Content-Type'=>'application/json','Authorization'=>'Bearer '.$api_key],
      'timeout'=>90,
      'body'=>wp_json_encode(['model'=>'gpt-4o-mini','temperature'=>0.7,'messages'=>$messages])
    ]);
    if (is_wp_error($resp)) return probot_writer_redirect('OpenAI failed: '.$resp->get_error_message(),'error');
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    $article_md = trim($data['choices'][0]['message']['content'] ?? '');
  } else {
    // Credits / proxy route
    if (!$lic['valid'])            return probot_writer_redirect('Product Key required', 'error');
    if ($proxy_url === '')         return probot_writer_redirect('Proxy URL not configured', 'error');
    if (empty($lic['unlimited'])) {
      $used = pbot_writer_usage_get();
      $remain = max(0, (int)$lic['limit'] - $used);
      if ($remain <= 0) return probot_writer_redirect('Credit limit reached', 'error');
    }
    $resp = wp_remote_post($proxy_url,[
      'headers'=>[
        'Content-Type'=>'application/json',
        'X-Product-Key'=>$product_key
      ],
      'timeout'=>90,
      'body'=>wp_json_encode([
        'messages'=>$messages,
        'wordcount'=>$wordcount,
        'outline'=>$outline,
        'gen_meta'=>$gen_meta
      ])
    ]);
    if (is_wp_error($resp)) return probot_writer_redirect('Proxy failed: '.$resp->get_error_message(),'error');
    $data       = json_decode(wp_remote_retrieve_body($resp), true);
    $article_md = trim((string)($data['content_md'] ?? ''));
    $meta_title = sanitize_text_field($data['meta']['title'] ?? '');
    $meta_desc  = sanitize_text_field($data['meta']['description'] ?? '');

    // increment both local + server usage when using credits
    if (empty($lic['unlimited'])) pbot_writer_usage_inc();

    $lic_base = pbot_license_server_url();
    if ($lic_base) {
      wp_remote_post($lic_base.'/wp-json/pbls/v1/usage/increment', [
        'timeout' => 15,
        'headers' => ['X-Product-Key' => $product_key],
        'body'    => ['amount' => 1],
      ]);
    }
  }

  if ($article_md === '') return probot_writer_redirect('No article content generated.', 'error');

  // Create Draft
  $final_title = $title !== '' ? $title : ($meta_title ?: 'AI Generated Article');
  $postarr = [
    'post_title'  => $final_title,
    'post_name'   => $slug ?: sanitize_title($final_title),
    'post_status' => 'draft',
    'post_type'   => 'post',
    'post_content'=> probot_writer_markdown_to_wp($article_md)
  ];
  $post_id = wp_insert_post($postarr, true);
  if (is_wp_error($post_id)) return probot_writer_redirect('Failed: '.$post_id->get_error_message(), 'error');

  if ($meta_title || $meta_desc) {
    update_post_meta($post_id, '_yoast_wpseo_title',     $meta_title);
    update_post_meta($post_id, '_yoast_wpseo_metadesc',  $meta_desc);
  }

  $edit_link = get_edit_post_link($post_id, 'redirect');
  return probot_writer_redirect('Draft created. <a href="'.esc_url($edit_link).'">Open in editor</a>.', 'updated');
}

/** ---------- Utilities ---------- */
function probot_writer_markdown_to_wp($markdown){
  $text=(string)$markdown;
  $text=preg_replace('/^###\s?(.*)$/m','<h3>$1</h3>',$text);
  $text=preg_replace('/^##\s?(.*)$/m','<h2>$1</h2>',$text);
  $text=preg_replace('/^#\s?(.*)$/m','<h1>$1</h1>',$text);
  $text=preg_replace('/\*\*(.*?)\*\*/s','<strong>$1</strong>',$text);
  $text=preg_replace('/\*(.*?)\*/s','<em>$1</em>',$text);
  $text=preg_replace('/^\s*-\s+(.*)$/m','<li>$1</li>',$text);
  $text=preg_replace('/(?:^|\n)(<li>.*<\/li>)(?=\n(?!<li>)|$)/s',"\n<ul>$1</ul>\n",$text);
  $parts=preg_split("/\n\s*\n/",trim($text));
  $parts=array_map(function($p){
    if(preg_match('/^\s*<(h\d|ul|ol|pre|blockquote)/i',trim($p))) return $p;
    return '<p>'.nl2br($p).'</p>';
  },$parts);
  return implode("\n\n",$parts);
}
function probot_writer_redirect($msg,$type='updated'){
  $url=add_query_arg([
    'page'=>'probot-assistant-writer',
    'pbot_msg'=>rawurlencode($msg),
    'pbot_ty'=>$type
  ], admin_url('admin.php'));
  wp_safe_redirect($url); exit;
}

/** ---------- Page renderer ---------- */
function probot_render_article_writer_page(){
  if(!current_user_can('manage_options')) return;

  $tier          = get_option('pbot_membership_tier','free');
  $api_key       = get_option('pbot_openai_api_key','');
  $max_words     = (int) get_option('pbot_writer_max_words',1000);
  $ai_category   = (int) get_option('pbot_writer_ai_category',1);
  $schedule      =        get_option('pbot_writer_schedule','monthly');
  $monthly_limit = (int) get_option('pbot_writer_monthly_limit',1);

  $lic = pbot_license_check();
  [$can_generate,$why] = pbot_writer_can_generate($lic);

  // Notices
  $notices=[];
  if(isset($_GET['pbot_msg'])){
    $cls=(($_GET['pbot_ty']??'')==='error')?'notice-error':'notice-success';
    $notices[]="<div class='notice $cls is-dismissible'><p>".$_GET['pbot_msg']."</p></div>";
  }
  if (empty($api_key)) {
    $notices[] = 'You can use <strong>your own OpenAI API key</strong> on this page (no extra fees from us). Add it in <a href="'.esc_url(admin_url('admin.php?page=probot-assistant')).'">Settings</a>.';
  }
  if (!$lic['valid']) {
    $notices[] = 'Or use <strong>ProBot Credits</strong> with your Product Key (we meter &amp; bill usage). Add your key in <a href="'.esc_url(admin_url('admin.php?page=probot-assistant')).'">Settings</a>.';
  } else {
    $left_txt = !empty($lic['unlimited']) ? '∞' : sprintf('%d of %d', (int)$lic['left'], (int)$lic['limit']);
    $notices[] = 'Product Key detected: <strong>'.($lic['unlimited'] ? 'Unlimited' : strtoupper($lic['tier'])).'</strong> — Remaining this month: <strong>'.$left_txt.'</strong>.';
  }

  $disabled_btn = $can_generate ? '' : 'disabled';

  $ctx = compact('tier','api_key','max_words','ai_category','schedule','monthly_limit','disabled_btn','notices','lic','why');
  extract($ctx, EXTR_SKIP);
  require dirname(__FILE__).'/views/writer-page.php';
}