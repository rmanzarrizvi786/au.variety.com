/**
 * Reusable word-count component.
 */

import { _x } from '@wordpress/i18n';
import { count as wordCount } from '@wordpress/wordcount';

const WordCount = ( { text } ) => {
	return (
		<div className="pmc-word-count">
			<p>
				{ _x(
					'Words',
					'Number of words counted in given string',
					'pmc-gutenberg'
				) }
				: { wordCount( text, 'words', {} ) },{ ' ' }
				{ _x(
					'characters',
					'Number of characters counted in given string',
					'pmc-gutenberg'
				) }
				: { wordCount( text, 'characters_including_spaces', {} ) }
			</p>
		</div>
	);
};

export default WordCount;
