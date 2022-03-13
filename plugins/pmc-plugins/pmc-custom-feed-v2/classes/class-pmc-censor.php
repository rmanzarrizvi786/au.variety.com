<?php
/**
 * This class adds support for censoring content before publishing.
 */
namespace PMC\Custom_Feed;

use PMC\Global_Functions\Traits\Singleton;

class Censor {

	use Singleton;

	/**
	 * List of blocklisted curse words for reference.
	 * [IMPORTANT] If you add an entry here, please make sure add an entry in
	 * $this->regex as well.
	 *
	 * @var array
	 */
	private $curse_words_blocklist = [
		'ass',
		'asses',
		'asshole',
		'ass-kicking',
		'bastard',
		'badass',
		'bitch',
		'bitches',
		'bullshit',
		'bum',
		'camel jockey',
		'camel toe',
		'cheese eaters',
		'child-fucker',
		'chink',
		'choad',
		'cock',
		'cocksucker',
		'cooch',
		'coolie',
		'coon',
		'cum',
		'cunt',
		'dago',
		'dick',
		'dink',
		'dipshit',
		'drug-binging',
		'dyke',
		'fag',
		'faggot',
		'felch',
		'fuck',
		'f*ck',
		'fu*k',
		'f**k',
		'fuckin',
		'fucking',
		'f*cking',
		'fu*king',
		'fuc*ing',
		'f**king',
		'fu**ing',
		'f***ing',
		'fucked',
		'fuckface',
		'gook',
		'goy',
		'gyp',
		'half-breed',
		'half-caste',
		'holy shit',
		'horny',
		'horseshit',
		'hun',
		'jack-ass',
		'jigaboo',
		'jism',
		'jizz',
		'kafir',
		'kick-ass',
		'kike',
		'knuckleheads',
		'kraut',
		'kyke',
		'mofo',
		'mong',
		'more sex',
		'motherfucker',
		'motherfuckers',
		'muff',
		'muthafuckin',
		'negress',
		'nigga',
		'nigger',
		'n.i.g.g.e.r',
		'niglet',
		'paddy',
		'paki',
		'porch monkey',
		'prick',
		'pussy',
		'raghead',
		'sand nigger',
		'sexcapades',
		'shit',
		'shitless',
		'slag',
		'slut',
		'sons of bitches',
		'spic',
		'spooge',
		'tard',
		'towelhead',
		'tranny',
		'wetback',
		'whore',
		'wigger',
		'wop',
		'yid',
	];

	/**
	 * Regex pattern containing all curse words.
	 * IMPORTANT = If you add an entry here, please make sure add an entry in
	 * $this->curse_words_blocklist as well.
	 *
	 * @var string
	 */
	private $regex = '/\b((?i)ass|asses|asshole|ass-kicking|badass|bastard|bitch|bitches|bum|bullshit|camel jockey|camel toe|Cheese eaters|child-fucker|chink|choad|cock|cocksucker|cooch|coolie|coon|cum|cunt|dago|(?-i)dick(?i)|dink|dipshit|drug-binging|dyke|fag|faggot|felch|fuck|f\*ck|fu\*k|f\*\*k|fuckin|fucking|f\*cking|fu\*king|fuc\*ing|f\*\*king|fu\*\*ing|f\*\*\*ing|fucked|fuckface|gook|goy|gyp|half-breed|half-caste|holy shit|horny|horseshit|hun|jack-ass|jigaboo|jism|jizz|kafir|kick-ass|kike|knuckleheads|kraut|kyke|mofo|mong|more sex|motherfucker|motherfuckers|muff|muthafuckin|negress|nigga|nigger|n.i.g.g.e.r|niglet|paddy|paki|porch monkey|prick|pussy|raghead|sand nigger|sexcapades|shit|shitless|slag|slut|sons of bitches|spic|spooge|tard|towelhead|tranny|wetback|whore|wigger|wop|yid)\b/i';

	/**
	 * Censor curse words.
	 *
	 * @param string $content  Content for censoring cruse words from.
	 * @param int    $position From which position to start censoring, defaults to 1.
	 * @param string $char     Which character to use for censoring, defaults to '*'.
	 *
	 * @return string Returns $content with curse words censored.
	 */
	public function censor_curse_words( $content, $position = 1, $char = '*' ) {

		if ( empty( $content ) ) {
			return $content;
		}

		// Match all tags.
		preg_match_all( '/<[^>]+>/', $content, $matches );

		$tags = array_unique( (array) $matches[0] );

		// Remove tags from content.
		foreach ( $tags as $index => $match ) {

			$content = str_replace( $match, sprintf( '[_%d_]', $index ), $content );
		}

		// Replace curse words with their censored version.
		$content = preg_replace_callback(
			$this->regex,
			function( $matches ) use ( $position, $char ) {

				$match = $matches[0];

				if ( in_array( strtolower( $match ), (array) $this->curse_words_blocklist, true ) ) {

					return $this->censor_word( $match, $position, $char );
				}

				// Code added for completeness, this case should not occure as the regex should not match any word outside of $this->censor_curse_words no proper way to test the code.
				return $match; // @codeCoverageIgnore

			},
			$content
		);

		// Put the HTML tags back in the content.
		foreach ( $tags as $index => $match ) {

			$content = str_replace( sprintf( '[_%d_]', $index ), $match, $content );
		}

		return $content;
	}

	/**
	 * Helper function to censor a word.
	 *
	 * @param string $word     Word to Censor.
	 * @param int    $position Position to starts censoring word from.
	 * @param string $char     Character to use for censoring.
	 *
	 * @return string Returns censored $word.
	 */
	private function censor_word( $word, $position = 1, $censor_character = '*' ) {

		if ( empty( $word ) ) {
			return $word;
		}

		$censored_word = '';
		$word_chars    = str_split( $word );
		$ignore        = 0;

		// Censor curse word.
		foreach ( $word_chars as $char ) {

			if ( $ignore <= ( $position - 1 ) ) {

				$ignore++;
				$censored_word .= $char;
				continue;
			}

			// If cruse word is combination of two words.
			if ( ' ' === $char || '-' === $char ) {

				$ignore         = 0;
				$censored_word .= $char;
				continue;
			}

			$censored_word .= $censor_character;
		}

		return $censored_word;

	}

}
