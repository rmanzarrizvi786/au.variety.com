<?php

use PMC\Global_Functions\Traits\Singleton;

class PMC_Taxonomy_Color {

	use Singleton;

	private $_default_taxonomies = array( 'editorial', 'print-issues' );
	private $_taxonomies = array();
	private $_cache_key = 'pmc_taxonomy_colors_css';
	private	$_cache_group = 'pmc_taxonomy_colors_group';

	protected function __construct() {
		add_action( 'init', array( $this, 'init' ), 11 );
	}

	/**
	 * Plugin init routine
	 *
	 * @since ?
	 * @version 2014-01-14 Amit Gupta (added auto register of taxonomies with PMC Term Meta)
	 */
	public function init() {

		//Filter to add color picker to taxonomies apart from default case.
		$taxonomies = apply_filters( 'pmc_taxonomy_color', $this->_default_taxonomies );
		$taxonomies = array_filter( array_unique( (array) $taxonomies ) );

		if ( ! empty( $taxonomies ) ) {
			$this->_taxonomies = $taxonomies;
			sort( $this->_taxonomies );

			foreach ( $taxonomies as $taxonomy ) {
				add_action( "{$taxonomy}_add_form_fields", array( $this, "taxonomy_colorpicker_div" ) );
				add_action( "{$taxonomy}_edit_form_fields", array( $this, "taxonomy_colorpicker_table" ) );
				add_action( "edited_{$taxonomy}", array( $this, "taxonomy_css_generate" ) );
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'colorpicker_scripts' ), 10, 1 );
			add_action( 'admin_head-edit-tags.php', array( $this,'colorpicker_head') );
			//Generate CSS File
			add_action( 'wp_print_styles', array( $this,'add_css') );

			//register our whitelisted taxonomies with PMC Term Meta
			add_filter( 'pmc_term_meta_taxonomy_whitelist', array( $this, 'add_term_meta_support' ) );
		}
	}

	/**
	 * Whitelist our taxonomies with PMC Term Meta to enable javascript API in admin
	 *
	 * @since 2014-01-14 Amit Gupta
	 */
	public function add_term_meta_support( $taxonomies ) {
		if ( is_array( $taxonomies ) ) {
			for ( $i = 0; $i < count( $this->_taxonomies ); $i++ ) {
				$taxonomies[] = $this->_taxonomies[ $i ];
			}
		}

		return $taxonomies;
	}

	/**
	 * Adds the colour picker to the edit taxonomy detail page.
	 * Replaces the taxonomy description with a hex colour.
	 * Lifted from BGR color picker pmc_category_colorpicker_table.
	 */
	function taxonomy_colorpicker_table( $term ) {
		?>
		<tr id="colorpicker-row-txt" valign="top">
			<th scope="row">Select text color</th>
			<td>
				<a class="hide-if-no-js pickcolor" href="#" id="pickcolor-txt"></a> <a href="#" id="clearcolor-txt">Remove
					text color</a>

				<div id="colorpicker-wrapper-txt"
					 style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;">
					<div id="colorpicker-wheel-txt"></div>
					<br/>#<input name="colorpicker-value-txt" id="colorpicker-value-txt" type="text" value="" size="7"
								 style="width: 40%;"/>
					<button type="submit" id="colorpicker-set-txt" name="colorpicker-set-txt" value="Set">Set</button>
				</div>
			</td>
		</tr>
		<tr id="colorpicker-row-bg" valign="top">
			<th scope="row">Select background color</th>
			<td>
				<a class="hide-if-no-js pickcolor" href="#" id="pickcolor-bg"></a> <a href="#" id="clearcolor-bg">Remove
					background color</a>

				<div id="colorpicker-wrapper-bg"
					 style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;">
					<div id="colorpicker-wheel-bg"></div>
					<br/>#<input name="colorpicker-value-bg" id="colorpicker-value-bg" type="text" value="" size="7"
								 style="width: 40%;"/>
					<button type="submit" id="colorpicker-set-bg" name="colorpicker-set-bg" value="Set">Set</button>
				</div>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Show color in river</th>
			<td>
				<input type="checkbox" id="pmc_show_in_river" name="pmc_show_in_river" value="1" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Use special template</th>
			<td>
				<input type="checkbox" id="pmc_use_special_template" name="pmc_use_special_template" value="1" />
			</td>
		</tr>
		<tr id="pmc-tax-template-url" valign="top">
			<th scope="row">Template url</th>
			<td>
				<?php $this->edit_template_link( $term ); ?>
			</td>
		</tr>

	<?php
	}

	/**
	 * Adds the colour picker to the edit category list page.
	 * Replaces the category description with a hex colour.
	 *
	 * @since   2011-07-21 krangwala
	 * @version 2011-12-12 Gabriel Koen
	 *
	 * @param string $taxonomy The taxonomy type (always category in this case)
	 */
	function taxonomy_colorpicker_div( $taxonomy ) {
		?>
		<div id="colorpicker-row-txt" class="form-field">
			<label>Select text color</label> <a class="hide-if-no-js pickcolor" href="#" id="pickcolor-txt"></a>
			<a href="#" id="clearcolor-txt">Remove text color</a>

			<div id="colorpicker-wrapper-txt"
				 style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none; ">
				<div id="colorpicker-wheel-txt"></div>
				<br/>#<input name="colorpicker-value-txt" id="colorpicker-value-txt" type="text" value="" size="7"
							 style="width: 40%;"/>
				<button type="submit" id="colorpicker-set-txt" name="colorpicker-set-txt" value="Set">Set</button>
			</div>
		</div>
		<div id="colorpicker-row-bg" class="form-field">
			<label>Select background color</label> <a class="hide-if-no-js pickcolor" href="#" id="pickcolor-bg"></a>
			<a href="#" id="clearcolor-bg">Remove background color</a>

			<div id="colorpicker-wrapper-bg"
				 style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none; ">
				<div id="colorpicker-wheel-bg"></div>
				<br/>#<input name="colorpicker-value-bg" id="colorpicker-value-bg" type="text" value="" size="7"
							 style="width: 40%;"/>
				<button type="submit" id="colorpicker-set-bg" name="colorpicker-set-bg" value="Set">Set</button>
			</div>
		</div>
		<div class="form-field">
			<label>Show color in river</label>
			<input type="checkbox" id="pmc_show_in_river" name="pmc_show_in_river" value="1" />
		</div>
		<div class="form-field">
			<label>Use special template</label>
			<input type="checkbox" id="pmc_use_special_template" name="pmc_use_special_template" value="1" />
		</div>
		<div class="form-field" id="pmc-tax-template-url">
			<?php $this->edit_template_link(); ?>
		</div>
		<?php
	}

	/**
	 * Recreates the cache on term update.
	 *
	 * @since   2013-11-15 Vicky Biswas
	 *
	 * @param int $term_id The term id
	 * @param int $tt_id The Term Taxonomy ID
	 */
	function burst_cache ( $term_id, $tt_id ) {
		taxonomy_css_generate();
	}

	/**
	 * Generate and Cache the CSS.
	 *
	 * @since   2013-11-15 Vicky Biswas
	 */
	function taxonomy_css_generate() {

		$css = '';

		//Filter containing whats to be added to the CSS.
		$taxonomy_css_data = apply_filters( 'pmc_taxonomy_color_css', array() );

		if ( !empty( $taxonomy_css_data ) ) {

			//Ading vertical color CSS
			//added 50 arbitarily for a finite number VIP policy
			foreach ( $taxonomy_css_data as $taxonomy=>$data ) {
				$terms = get_terms(
					$taxonomy,
					array(
						'number' => 50,
					  'hide_empty' => 0
					)
				);

				foreach ( $terms as $term ) {
					$term_property = $this->get_term_property( $term );

					foreach ( $data as $property=>$rules ) {
						if ( !empty( $term_property[$property] ) ) {
							foreach ( $rules as $rule ) {
								$css .= str_replace(
									array( '%%term%%', '%%color%%' ),
									array( $term->slug, $term_property[$property] ),
									$rule
								);
							}
						}
					}
				}
			}
			//Cache for 60 mins
			wp_cache_set( $this->_cache_key, $css, $this->_cache_group, 3600 );
		}

		return $css;
	}

	function edit_template_link( $term = '' ) {
		if ( function_exists( 'pmc_wt_get_templates' ) ) {
			if ( !empty( $term ) ) {
				$special_template = pmc_wt_get_templates( array( 'name' => '_variety-editorial-awards-' . $term->slug ), 0, 1 );
			}

			if ( isset( $special_template[0]->post_content ) ) {
				echo "<a href='" . esc_url( admin_url( "themes.php?page=pmc-templatized-widgets&tab=templates&edit=" . $special_template[0]->ID ) ) . "'>Edit Header Template</a>";
			} else {
				echo "<a href='" . esc_url( admin_url( "themes.php?page=pmc-templatized-widgets&tab=templates" ) ) . "'>Add Header Template</a>";
			}
		}
	}

	/**
	 * Adds the colour picker to the edit category pages.
	 * Farbtastic colour picker is built into WordPress
	 *
	 * @since   2011-07-21 krangwala
	 * @version 2011-12-12 Gabriel Koen
	 * @version 2013-09-26 Amit Gupta
	 */
	function colorpicker_scripts( $hook_suffix ) {
		if ( 'term.php' !== $hook_suffix || ! in_array( $GLOBALS['taxonomy'], $this->_taxonomies ) ) {
			return;
		}

		wp_enqueue_script( 'farbtastic' );
		wp_enqueue_style( 'farbtastic' );

		wp_enqueue_script( 'pmc-taxonomy-color-admin-js', plugins_url( 'js/admin-tag-edit.js', __FILE__ ), array( 'pmc-term-meta-admin-js' ) );
	}

	/**
	 * Adds the colour picker to the edit category pages.
	 *
	 * @since 2011-07-21 krangwala
	 * @version 2011-12-12 Gabriel Koen
	 */
	function colorpicker_head() {
		if ( ! in_array( $GLOBALS['taxonomy'], $this->_taxonomies ) ) {
			return;
		}
		?>
			<style type="text/css">
				.pickcolor {
					background-image: url('/wp-content/themes/vip/pmc-plugins/pmc-taxonomy-color/images/colorpicker-select.png');
					background-position: -4px -4px;
					width: 28px;
					height: 28px;
					display: block;
				}

				#slug + .description,
				#pmc_taxonomy_seo_title + .description{
					display: block;
				}

				#pmc-tax-template-url {
					display: none;
				}
				#tag-name + p {
					display: none;
				}
			</style>
		<?php
	}

	/**
	 * @param $term object
	 *
	 * @return array
	 */
	function get_term_property( $term='' ) {

		if ( empty( $term ) ) {
			return;
		}

		$term_meta = array();

		if ( class_exists( 'PMC_Term_Meta' ) ) {
			$term_meta = PMC_Term_Meta::get_all( $term );
		}

		if ( ! empty( $term_meta ) ) {
			return $term_meta;
		}

		return;
	}

	/**
	 * Add CSS to head
	 *
	 * @author Vicky Biswas
	 * @since 20131010
	 */
	function add_css($template){

		$css = wp_cache_get( $this->_cache_key, $this->_cache_group );

		if ( false === $css ) {
			//Generate and cache css
			$css = $this->taxonomy_css_generate();
		}

		if ( '' != $css ) {
			echo '<style type="text/css">'. strip_tags( $css ) . '</style>';
		}

	}
}

PMC_Taxonomy_Color::get_instance();
//EOF
