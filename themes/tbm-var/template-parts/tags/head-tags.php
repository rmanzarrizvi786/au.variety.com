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