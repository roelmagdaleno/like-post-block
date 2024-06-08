let rolpbTimerId;

/**
 * Debounce function.
 * We use this to prevent multiple AJAX requests.
 *
 * @since   1.0.0
 *
 * @param   {function}   func    Function to be debounced.
 * @param   {int}        delay   Delay in milliseconds.
 *
 * @returns {(function(...[*]): void)|*}
 */
function rolpb_debounce(func, delay) {
    return function(...args) {
        if (rolpbTimerId) {
            clearTimeout(rolpbTimerId);
        }

        rolpbTimerId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}

/**
 * Animate the like icon.
 * We scale the icon to `1.1` and then back to `1.0` after 100ms.
 *
 * @since 1.0.0
 *
 * @param {object} likeButton The like button element.
 */
function rolbp_animateIcon(likeButton) {
    const icon = likeButton.querySelector('svg');

    if (!icon) {
        return;
    }

    icon.style.transform = 'scale(1.1)';
    setTimeout(() => icon.style.transform = 'scale(1.0)', 100);
}

/**
 * Replace the like icon with the active icon.
 * We also add the `wp-like-post__button--liked` class to the button.
 *
 * @since 1.0.0
 */
function rolpb_replaceIcon(likeButton) {
    if (!likeButton || likeButton.classList.contains('wp-like-post__button--liked')) {
        return;
    }

	likeButton.classList.add('wp-like-post__button--liked');
	likeButton.innerHTML = ROLPB.icons.active;
}

/**
 * Get the XMLHttpRequest object.
 *
 * @since 1.0.0
 *
 * @param {string} url The URL to send the request to.
 * @returns {XMLHttpRequest} The XMLHttpRequest object.
 */
function rolpb_getXHR(url) {
    const request = new XMLHttpRequest();

    request.open('POST', url, true);
    request.setRequestHeader(
        'Content-Type',
        'application/x-www-form-urlencoded; charset=UTF-8'
    );

    return request;
}

/**
 * Get the total number of likes for multiple posts.
 * We send an AJAX request to the server and update the like count.
 *
 * @since 1.3.0
 *
 * @param {array} postIds The post IDs.
 * @param {object} settings The settings object.
 */
function rolpb_getBulkLikes(postIds, settings) {
	const request = rolpb_getXHR(settings.url);

	request.onload = () => {
		if (request.status >= 200 && request.status < 400) {
			const response = JSON.parse(request.responseText);

			if (response.success) {
				const postsWithLikes = response.data;

				postIds.forEach((id) => {
					const likeButton = document.querySelector(`.wp-like-post__button[data-post-id="${id}"]`);

					if (!likeButton) {
						return;
					}

					const countEl = likeButton.parentElement.querySelector('.wp-like-post__count');

					if (!countEl) {
						return;
					}

					const likes = parseInt(postsWithLikes[id]);
					const printedLikes = parseInt(likeButton.getAttribute('data-total-likes'));

					if (likes === printedLikes) {
						return;
					}

					if (likes > 0) {
						likeButton.classList.add('wp-like-post__button--liked');
						likeButton.innerHTML = settings.icons.active;
					}

					countEl.innerHTML = likes.toString();
				});
			}
		}
	};

	const nonce = settings.nonces.getLikes;
	const attributes = JSON.stringify(settings.block);

	request.send(`action=rolpb_get_post_likes&post_ids=${postIds}&nonce=${nonce}&attributes=${attributes}`);
}

let lpbPost = (function () {
    /**
     * The Constructor.
     *
     * @since 1.0.0
     *
     * @param {object} likeButton The like button element.
     * @param {object} settings The settings object.
     *
     * @constructor
     */
    function Constructor(likeButton, settings) {
        // Freeze settings so that they cannot be modified
        Object.freeze(settings);

		const postId = parseInt(likeButton.getAttribute('data-post-id'));
		const likes = {
			total: parseInt(likeButton.getAttribute('data-total-likes')),
			fromUser: parseInt(likeButton.getAttribute('data-likes-from-user')),
		};

        // Define instance properties
        Object.defineProperties(this, {
			likeButton: { value: likeButton },
			postId: { value: postId },
            likes: {
                value: {
                    total: likes.total,
                    fromUser: likes.fromUser,
					toAdd: 0,
                },
                writable: true
            },
            _settings: { value: settings }
        });
    }

    /**
     * Like the post.
     *
     * We increment the like count and send an AJAX request to the server.
     * The AJAX request is debounced to prevent multiple requests.
     * The request is sent after 500ms of the last click.
     * We also animate the like icon.
     *
     * If the user has already reached the limit of liked posts, we don't do anything.
     *
     * @since 1.0.0
     */
    Constructor.prototype.like = function () {
        if ('' === this._settings.unlimited && this.likes.fromUser >= parseInt(this._settings.limit)) {
            return;
        }

		this.likes.toAdd++;
        this.likes.fromUser++;

        const postLikes = this.likes.total;
        const likeCount = this.likeButton.parentElement.querySelector('.wp-like-post__count');

        likeCount.innerHTML = (postLikes + this.likes.toAdd).toString();

        const processChanges = rolpb_debounce(() => {
            const request = rolpb_getXHR(this._settings.url);

            request.onload = () => {
                if (request.status >= 200 && request.status < 400) {
                    this.likes.total = this.likes.total + this.likes.toAdd;
					this.likes.toAdd = 0;

					this.likeButton.dataset.totalLikes = this.likes.total.toString();
                }
            };

            const postId = this.postId;
            const nonce = this._settings.nonces.likePost;

            request.send(`action=rolpb_like_post&post_id=${postId}&count=${this.likes.toAdd}&nonce=${nonce}`);
        }, 500);

        processChanges();

        rolpb_replaceIcon(this.likeButton);
        rolbp_animateIcon(this.likeButton);
    };

    /**
     * Get the total number of likes for the current post.
     * We send an AJAX request to the server and update the like count.
     *
     * @since 1.0.0
     */
    Constructor.prototype.getLikes = function () {
		rolpb_getBulkLikes([this.postId], this._settings);
    }

    return Constructor;
})();

document.addEventListener('DOMContentLoaded', () => {
    const likeButtons = document.querySelectorAll('.wp-like-post__button');

	if (likeButtons.length === 0 || !window.ROLPB) {
		return;
	}

	let postIds = [];

	likeButtons.forEach((likeButton) => {
		let currentPost = new lpbPost(likeButton, window.ROLPB);

		if (currentPost._settings.attributes.renderWithAjax) {
			postIds.push(currentPost.postId);
		}

		likeButton.addEventListener('click', () => currentPost.like());
	});

	if (postIds.length === 0) {
		return;
	}

	rolpb_getBulkLikes(postIds, window.ROLPB);
});
