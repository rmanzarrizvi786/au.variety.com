import sanitizeHtml from 'sanitize-html';

/**
 * Responsible for Rotating through active sponsored posts.
 */
export default class PostRotation {
	posts;
	activePost;
	dataAttr = 'data-pmc-sponsored-posts';
	storage = 'pmcSponsoredPostIndex';

	/**
	 * Constructor.
	 *
	 * @param posts
	 */
	constructor(posts) {
		this.posts = posts;
		this.init();
	}

	/**
	 * Initialize if we have more than 1 active sponsored posts.
	 */
	init() {
		if (this.setupActivePost()) {
			this.displayActivePost();
		}

		const placements = this.getPlacements();

		for (let i = 0; i < placements.length; i++) {
			placements[i].classList.add('pmc-sponsored-posts-visible');
		}
	}

	/**
	 * Sets the active post based where in rotation viewer.
	 * Index for sponsored post is stored in localStorage.
	 *
	 * @returns {number}
	 */
	setupActivePost() {
		let index = parseInt(this.getPostIndex(), 10);

		index = (0 <= index) ? index : -1;

		if (0 > index || (index + 1) >= this.posts.length) {
			index = 0;
		} else {
			index++;
		}

		this.setPostIndex(index);
		this.activePost = this.posts[index];

		return index;
	}

	/**
	 * Helper to get post index from localStorage.
	 *
	 * @returns {string}
	 */
	getPostIndex() {
		return global.localStorage.getItem(this.storage);
	}

	/**
	 * Helper to set post index and save to localStorage.
	 *
	 * @param index
	 */
	setPostIndex(index) {
		global.localStorage.setItem(this.storage, index);
	}

	/**
	 * Displays the active sponsored post.
	 */
	displayActivePost() {
		const activePost = this.activePost,
			placements = this.getPlacements();

		for (let i = 0; i < placements.length; i++) {
			let placement = placements[i],
				context = placement.getAttribute(this.dataAttr);

			if ("undefined" !== activePost[context]) {
				placement.innerHTML = sanitizeHtml(activePost[context], {
					allowedTags: sanitizeHtml.defaults.allowedTags.concat([ 'img', 'noscript' ]),
					allowedAttributes: false
				})
			}
		}
	}

	/**
	 * Helper to get sponsored post placements from DOM.
	 *
	 * @returns {NodeListOf<Element>}
	 */
	getPlacements() {
		return document.querySelectorAll('['+this.dataAttr+']');
	}
}
