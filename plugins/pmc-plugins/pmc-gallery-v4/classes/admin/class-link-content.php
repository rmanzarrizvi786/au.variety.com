<?php
/*
 * Add/Edit Gallery
 *
 *
 * @package PMC Gallery Plugin
 * @since 1/1/2013 Vicky Biswas
 *
 * Holds code needed to create backend for adding and editing galleries
 *
 * Technically this might ought to be PMC_Gallery_LinkContent because it adds functionality from PMC_LinkContent,
 * but I'm choosing Link_Content over LinkContent for standards consistency.
 */

namespace PMC\Gallery\Admin;

use \PMC\Global_Functions\Traits\Singleton;

class Link_Content
{

	use Singleton;

	/**
	 * Link_Content constructor.
	 */
	protected function __construct()
	{
		$this->_enqueue_linkcontent();
		$this->_setup_hooks();
	}

	/**
	 * Setup PMC_LinkContent.
	 */
	protected function _enqueue_linkcontent(): void
	{
		require_once PMC_PLUGINS_DIR . '/pmc-linkcontent/pmc-linkcontent.php';
		\PMC_LinkContent::enqueue();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks(): void
	{
		add_action('add_meta_boxes', [$this, 'meta_boxes']);
		add_action('save_post', [$this, 'save_post'], 10, 2);

		add_filter('pmclinkcontent_post_types_gallery', [$this, 'pmclinkcontent_post_types']);
	}

	/**
	 * Only search for pmc-gallery posts
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public function pmclinkcontent_post_types(): array
	{
		return [\PMC\Gallery\Defaults::NAME];
	}

	/**
	 * Adds the meta box container.
	 */
	public function meta_boxes()
	{
		$post_types = apply_filters('pmc_gallery_link_post_types', array('post'));
		add_meta_box(
			\PMC\Gallery\Defaults::NAME . '-link-box',
			esc_html__('Add Link to a Gallery', 'pmc-gallery-v4'),
			array($this, 'render_link_box_content'),
			$post_types,
			'normal',
			'core'
		);
	}

	/**
	 * Render Meta Box content.
	 */
	public function render_link_box_content()
	{
		global $post;
		wp_nonce_field(basename(__FILE__), \PMC\Gallery\Defaults::NAME . '-link-box');

		$linked_data    = get_post_meta($post->ID, \PMC\Gallery\Defaults::NAME . '-linked-gallery', true);
		$title_override = get_post_meta($post->ID, \PMC\Gallery\Defaults::NAME . '-linked-gallery-title-override', true);
		$allowed_html   = \PMC::allowed_html('post', ['b', 'i', 'strong', 'em']);

		if (is_array($linked_data)) {
			$linked_data = [
				'url'   => $linked_data[0],
				'id'    => $linked_data[1],
				'title' => $linked_data[2],
			];

			$linked_data = wp_json_encode($linked_data);
		}

		\PMC_LinkContent::insert_field($linked_data, esc_html__('Gallery', 'pmc-gallery-v4'), 'gallery');
?>
		<p>
			<label for="<?php echo esc_attr(\PMC\Gallery\Defaults::NAME . '-linked-gallery-title-override'); ?>">
				<?php esc_html_e('Gallery Title Override', 'pmc-gallery-v4'); ?>
			</label>
			<input class="widefat" type="text" name="<?php echo esc_attr(\PMC\Gallery\Defaults::NAME . '-linked-gallery-title-override'); ?>" id="<?php echo esc_attr(\PMC\Gallery\Defaults::NAME . '-linked-gallery-title-override'); ?>" value="<?php echo wp_kses($title_override, $allowed_html); ?>" />
		</p>
<?php
	}

	/**
	 * Custom sanitization for JSON data.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function filter_json_data($data)
	{
		$data = trim($data);

		return json_decode($data);
	}

	/**
	 * Custom sanitization for title that could include basic inline markup.
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public function filter_title($title): string
	{
		$allowed_html = \PMC::allowed_html('post', ['b', 'i', 'strong', 'em']);

		return wp_kses($title, $allowed_html);
	}

	/**
	 * Saves Linked Gallery ID.
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	function save_post($post_id)
	{
		$nonce = \PMC::filter_input(INPUT_POST, 'pmc-linkcontent-nonce', FILTER_SANITIZE_STRING);

		if (
			(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			|| (wp_doing_ajax())
			|| (empty($nonce))
			|| (!wp_verify_nonce($nonce, 'pmc-linkcontent'))
			|| (!current_user_can('edit_posts', $post_id))
		) {
			return;
		}

		$url_parts      = \PMC::filter_input(
			INPUT_POST,
			'pmclinkcontent-post-value-gallery',
			FILTER_CALLBACK,
			['options' => [$this, 'filter_json_data']]
		);
		$title_override = \PMC::filter_input(
			INPUT_POST,
			\PMC\Gallery\Defaults::NAME . '-linked-gallery-title-override',
			FILTER_CALLBACK,
			['options' => [$this, 'filter_title']]
		);

		if (empty($url_parts) || !is_object($url_parts)) {
			delete_post_meta($post_id, \PMC\Gallery\Defaults::NAME . '-linked-gallery');
			delete_post_meta($post_id, \PMC\Gallery\Defaults::NAME . '-linked-gallery-title-override');

			return;
		}

		/*
		 * json_encode() turns unicode characters into \uXXXX
		 * PHP 5.4 adds JSON_UNESCAPED_UNICODE, but for better compatibility we're using esc_html() to convert
		 * the unicode characters into entities.
		 * The json_encode() here is to pre-emptively encode the entities, and substr() to remove the quotes
		 * added by json_encode().
		 */
		/**
		 * @todo There's a better place to put the zoom code...
		 */
		$title          = esc_html($url_parts->title);
		$title          = substr(wp_json_encode($title), 1, -1);
		$new_meta_value = [
			'url'   => esc_url_raw($url_parts->url),
			'id'    => intval($url_parts->id),
			'title' => $title,
		];

		$current_meta_value = get_post_meta($post_id, \PMC\Gallery\Defaults::NAME . '-linked-gallery', true);

		if ($new_meta_value !== $current_meta_value) {

			$old_linked_data = json_decode($current_meta_value, true);

			// we need to remove old gallery -> post if exist
			if (empty($current_meta_value) && !empty($old_linked_data)) {
				$old_gallery_id = $old_linked_data['id'];
				delete_post_meta($old_gallery_id, \PMC\Gallery\Defaults::NAME . '-linked-post_id');
			}

			// Link direction: post -> gallery
			update_post_meta($post_id, \PMC\Gallery\Defaults::NAME . '-linked-gallery', wp_json_encode($new_meta_value));

			// Link direction: gallery -> post
			update_post_meta($new_meta_value['id'], \PMC\Gallery\Defaults::NAME . '-linked-post_id', $post_id);
		}

		$allowed_html = \PMC::allowed_html('post', ['b', 'i', 'strong', 'em']);
		update_post_meta(
			$post_id,
			\PMC\Gallery\Defaults::NAME . '-linked-gallery-title-override',
			html_entity_decode(wp_kses($title_override, $allowed_html))
		);
	}
}

// EOF
