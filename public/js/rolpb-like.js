let lpbTimerId;
let rolpbLike = 0;

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
        if (lpbTimerId) {
            clearTimeout(lpbTimerId);
        }

        lpbTimerId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}

/**
 * Animate the like icon.
 * We scale the icon to `1.1` and then back to `1.0` after 100ms.
 *
 * @since 1.0.0
 */
function rolbp_animateIcon() {
    const icon = document.querySelector('.wp-like-post__button svg');

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
function rolpb_replaceIcon() {
    const button = document.querySelector('.wp-like-post__button');

    if (!button || button.classList.contains('wp-like-post__button--liked')) {
        return;
    }

    button.classList.add('wp-like-post__button--liked');
    button.innerHTML = ROLPB.icons.active;
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

let lpbPost = (function () {
    /**
     * The Constructor.
     *
     * @since 1.0.0
     *
     * @param {object} settings The settings object.
     *
     * @constructor
     */
    function Constructor(settings) {
        // Freeze settings so that they cannot be modified
        Object.freeze(settings);

        // Define instance properties
        Object.defineProperties(this, {
            likes: {
                value: {
                    total: settings.likes.total,
                    fromUser: settings.likes.fromUser,
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
        if (parseInt(this.likes.fromUser) >= parseInt(this._settings.limit)) {
            return;
        }

        rolpbLike++;
        this.likes.fromUser++;

        const postLikes = parseInt(this.likes.total);
        const likeCount = document.querySelector('.wp-like-post__count');

        likeCount.innerHTML = (postLikes + rolpbLike).toString();

        const processChanges = rolpb_debounce(() => {
            const request = rolpb_getXHR(this._settings.url);

            request.onload = () => {
                if (request.status >= 200 && request.status < 400) {
                    this.likes.total = this.likes.total + rolpbLike;
                    rolpbLike = 0;
                }
            };

            const postId = this._settings.post_id;
            const nonce = this._settings.nonces.likePost;

            request.send(`action=rolpb_like_post&post_id=${postId}&count=${rolpbLike}&nonce=${nonce}`);
        }, 500);

        processChanges();

        rolpb_replaceIcon();
        rolbp_animateIcon();
    };

    /**
     * Get the total number of likes for the current post.
     * We send an AJAX request to the server and update the like count.
     *
     * @since 1.0.0
     */
    Constructor.prototype.getLikes = function () {
        const request = rolpb_getXHR(this._settings.url);

        request.onload = () => {
            if (request.status >= 200 && request.status < 400) {
                const response = JSON.parse(request.responseText);
                const countEl = document.querySelector('.wp-like-post__count');
                const buttonEl = document.querySelector('.wp-like-post__button');

                if (!countEl || !buttonEl) {
                    return;
                }

                const likes = parseInt(response.data.likes);
                const printedLikes = parseInt(this.likes.total);

                if (likes === printedLikes) {
                    return;
                }

                if (likes > 0) {
                    buttonEl.classList.add('wp-like-post__button--liked');
                    buttonEl.innerHTML = ROLPB.icons.active;
                }

                countEl.innerHTML = likes.toString();
            }
        };

        const postId = this._settings.post_id;
        const nonce = this._settings.nonces.getLikes;
        const attributes = JSON.stringify(this._settings.block);

        request.send(`action=rolpb_get_post_likes&post_id=${postId}&nonce=${nonce}&attributes=${attributes}`);
    }

    return Constructor;
})();

document.addEventListener('DOMContentLoaded', () => {
    const button = document.querySelector('.wp-like-post__button');

    if (!button || !window.ROLPB) {
        return;
    }

    const currentPost = new lpbPost(window.ROLPB);

    if (ROLPB.attributes.renderWithAjax) {
        currentPost.getLikes();
    }

    button.addEventListener('click', () => currentPost.like());
});
