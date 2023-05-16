let lpbTimerId;
let lpbLike = 0;

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
function lpb_debounce(func, delay) {
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
function lbp_animateIcon() {
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
function lpb_replaceIcon() {
    const button = document.querySelector('.wp-like-post__button');

    if (!button || button.classList.contains('wp-like-post__button--liked')) {
        return;
    }

    button.classList.add('wp-like-post__button--liked');
    button.innerHTML = LPB.icons.active;
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

        lpbLike++;
        this.likes.fromUser++;

        const postLikes = parseInt(this.likes.total);
        const likeCount = document.querySelector('.wp-like-post__count');

        likeCount.innerHTML = (postLikes + lpbLike).toString();

        const processChanges = lpb_debounce(() => {
            const request = new XMLHttpRequest();

            request.open('POST', this._settings.url, true);
            request.setRequestHeader(
                'Content-Type',
                'application/x-www-form-urlencoded; charset=UTF-8'
            );

            request.onload = () => {
                if (request.status >= 200 && request.status < 400) {
                    this.likes.total = this.likes.total + lpbLike;
                    lpbLike = 0;
                }
            };

            const postId = this._settings.post_id;
            const nonce = this._settings.nonce;

            request.send(`action=lpb_like_post&post_id=${postId}&count=${lpbLike}&nonce=${nonce}`);
        }, 500);

        processChanges();

        lpb_replaceIcon();
        lbp_animateIcon();
    };

    return Constructor;
})();

document.addEventListener('DOMContentLoaded', () => {
    const button = document.querySelector('.wp-like-post__button');

    if (!button || !window.LPB) {
        return;
    }

    const currentPost = new lpbPost(window.LPB);
    button.addEventListener('click', () => currentPost.like());
});
