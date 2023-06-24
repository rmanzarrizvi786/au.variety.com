<?php

/**
 * Site specific tags in the head of every page.
 */
// phpcs:ignoreFile

$blocker_atts = [
  'type'  => 'text/javascript',
  'class' => '',
];

/* if (class_exists('\PMC\Onetrust\Onetrust')) {
  $blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type('optanon-category-C0004');
} */
?>

<!-- Facebook Pixel Code -->
<script type="<?php echo esc_attr($blocker_atts['type']); ?>" class="<?php echo esc_attr($blocker_atts['class']); ?>">
  ! function(f, b, e, v, n, t, s)

{
  if (f.fbq) return;
  n = f.fbq = function() {
    n.callMethod ?
      n.callMethod.apply(n, arguments) : n.queue.push(arguments)
  };

  if (!f._fbq) f._fbq = n;
  n.push = n;
  n.loaded = !0;
  n.version = '2.0';

  n.queue = [];
  t = b.createElement(e);
  t.async = !0;

  t.src = v;
  s = b.getElementsByTagName(e)[0];

  s.parentNode.insertBefore(t, s)
}(window, document, 'script',
  'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '243859349395737');
fbq('track', 'PageView');
fbq.disablePushState = true;
</script>
<noscript>
  <img height="1" width="1" src="https://www.facebook.com/tr?id=243859349395737&ev=PageView&noscript=1" />
</noscript>
<!-- End Facebook Pixel Code -->

<?php
if (is_single()) {
  global $post;
  if (get_field('author')) {
    $author = get_field('author');
  } else if (get_field('Author')) {
    $author = get_field('Author');
  } else {
    if ('' != get_the_author_meta('first_name', $post->post_author) && '' != get_the_author_meta('last_name', $post->post_author)) {
      $author = get_the_author_meta('first_name', $post->post_author) . ' ' . get_the_author_meta('last_name', $post->post_author);
    } else {
      $author = get_the_author_meta('display_name', $post->post_author);
    }
  }

  $categories = get_the_category(get_the_ID());
  $CategoryCD = '';
  if ($categories) :
    foreach ($categories as $category) :
      $CategoryCD .= $category->slug . ' ';
    endforeach; // For Each Category
  endif; // If there are categories for the post

  $tags = get_the_tags(get_the_ID());
  $TagsCD = '';
  if ($tags) :
    foreach ($tags as $tag) :
      $TagsCD .= $tag->slug . ' ';
    endforeach; // For Each Tag
  endif; // If there are tags for the post
?>
  <script>
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      'event': 'articleView',
      'AuthorCD': '<?php echo $author; ?>',
      'CategoryCD': '<?php echo $CategoryCD; ?>',
      'TagsCD': '<?php echo $TagsCD; ?>',
      'PubdateCD': '<?php echo get_the_time('M d, Y', get_the_ID()); ?>'
    });
  </script>

<?php
}
?>

<!-- Google Tag Manager -->
<script>
  (function(w, d, s, l, i) {
    w[l] = w[l] || [];
    w[l].push({
      'gtm.start': new Date().getTime(),
      event: 'gtm.js'
    });
    var f = d.getElementsByTagName(s)[0],
      j = d.createElement(s),
      dl = l != 'dataLayer' ? '&l=' + l : '';
    j.async = true;
    j.src =
      'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
    f.parentNode.insertBefore(j, f);
  })(window, document, 'script', 'dataLayer', 'GTM-WKG6893');
</script>
<!-- End Google Tag Manager -->