# PMC Gutenberg Coding Standards

## Organizing Imports

Imports are organized into three groups:

1. External, non-Gutenberg dependencies, such as `lodash`, are imported first.
2. Gutenberg dependencies, such as `@wordpress/components`, are imported next.
3. Lastly, all internal dependencies are imported.

Within each group, imports are ordered alphabetically by the name of the
package. Destructured elements are also ordered alphabetically.

For example:

```javascript
import { get } from 'loadsh';

import { Spinner, TextAreaControl } from '@wordpress/components';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import { Edit } from './edit';
```

## Programmatic state updates & undo history

Sometimes, a block will programmatically change state, such as when a story
block's configuration changes and related attributes are updated as blocks load.
When this happens, it can prevent a user from undoing block insertion by
creating an infinite loop of state changes that are re-applied each time the
undo feature is triggered. Gutenberg provides a function that should be called
before such an update is made, which ensures that the undo functionality behaves
as expected.

The following is an abridged example:

```javascript
import { useDispatch } from '@wordpress/data';

const { __unstableMarkNextChangeAsNotPersistent } = useDispatch(
	'core/block-editor'
);

__unstableMarkNextChangeAsNotPersistent();
setAttributes( { example: true } );
```

See the story block's [edit.js](src/blocks/story/edit.js) for a complete
implementation.

Props to [talldan's PR](https://github.com/WordPress/gutenberg/pull/26377) for
highlighting how to address this situation.
