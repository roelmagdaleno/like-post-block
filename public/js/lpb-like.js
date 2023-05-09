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
function lpb_likePost() {
    if (parseInt(LPB.post.likes.fromUser) >= parseInt(LPB.limit)) {
        return;
    }

    lpbLike++;
    LPB.post.likes.fromUser++;

    const postLikes = parseInt(LPB.post.likes.total);
    const likeCount = document.querySelector('.wp-like-post__count');

    likeCount.innerHTML = (postLikes + lpbLike).toString();

    const processChanges = lpb_debounce(() => {
        const request = new XMLHttpRequest();

        request.open('POST', LPB.url, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

        request.onload = () => {
            if (request.status >= 200 && request.status < 400) {
                LPB.post.likes.total = LPB.post.likes.total + lpbLike;
                lpbLike = 0;
            }
        };

        request.send(`action=lpb_like_post&post_id=${LPB.post.id}&count=${lpbLike}&nonce=${LPB.nonce}`);
     }, 500);

    processChanges();
    lbp_animateIcon();
}

document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.querySelector('.wp-like-post__wrapper');

    if (!wrapper) {
        return;
    }

    wrapper.addEventListener('click', lpb_likePost);
});
