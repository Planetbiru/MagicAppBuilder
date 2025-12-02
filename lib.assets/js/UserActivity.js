/**
 * @file Manages user session activity, including tracking idle time,
 * checking session status, and prompting for re-login when a session expires.
 */

/**
 * A timestamp representing the last time the user was active.
 * @type {number}
 */
let lastActiveTime = Date.now();

/**
 * A flag to indicate whether the session expired alert is currently shown.
 * @type {boolean}
 */
let showLoginAlert = false;

document.addEventListener('DOMContentLoaded', function () {
    startSessionRefreshLoop();

    /**
     * @event visibilitychange
     * Listens for changes in tab visibility. If the tab becomes visible after being
     * hidden for a long time, it checks the session status with the server.
     */
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            const now = Date.now();
            const idleDuration = now - lastActiveTime;

            if (idleDuration > getIddleDuration()) {
                // Too long idle → check session with server
                checkSessionStatus();
            } else {
                lastActiveTime = now;
            }
        }
    });

    window.addEventListener("storage", function(event) {
        if (event.key === "lougout" && event.newValue === "true") {
            showSessionExpiredNotice();
        }
        if (event.key === "login" && event.newValue === "true") {
            hideSessionExpiredNotice();
        }
    });

    window.localStorage.setItem('login', 'true');
    setTimeout(function(){
        window.localStorage.setItem('login', 'false');
    }, 400)

});

/**
 * A list of common user activity events to track.
 * @type {string[]}
 */
const userEvents = ['click', 'mousemove', 'keydown', 'wheel', 'touchstart'];

/**
 * @event click, mousemove, keydown, wheel, touchstart
 * Listens for various user activity events to track user presence.
 * Updates the last active time and checks the session if the user was idle for
 * longer than the configured refresh interval.
 */
for (const evtName of userEvents) {
    document.addEventListener(evtName, () => {
        const now = Date.now();
        const idleDuration = now - lastActiveTime;

        if (idleDuration > getIddleDuration()) {
            // User was idle too long → check session
            checkSessionStatus();
        }

        // Always update last active time after handling
        lastActiveTime = now;
    });
}

function getIddleDuration() {
    const meta = document.querySelector('meta[name="iddle-duration"]');
    const value = meta?.getAttribute('content');
    const interval = parseInt(value, 10) * 1000;
    return isNaN(interval) ? 300000 : interval;
}

/**
 * Checks the user's session status with the server via an AJAX request.
 * If the session is no longer valid (e.g., the user is logged out), it triggers
 * the display of a session expired notice.
 *
 * @returns {void}
 */
function checkSessionStatus() {
    fetch('lib.ajax/session-check.php', {
        method: 'POST',
        credentials: 'include',
    })
        .then(res => res.json())
        .then(data => {
            if (data.loggedIn) {
                let alrt = document.getElementById('session-expired-alert');
                if (alrt) {
                    alrt.parentNode.removeChild(alrt);
                    showLoginAlert = false;
                }
                $('#loginModal').modal('hide');
            }
            else {
                showSessionExpiredNotice();
            }
        })
        .catch(err => {
            console.error('Failed to check session:', err);
        });
}

/**
 * Displays a non-intrusive alert bar at the top of the page to inform the user
 * that their session has expired. The alert includes a button to open the login modal.
 * It ensures that only one alert is shown at a time by checking for the existence
 * of the alert element.
 *
 * @returns {void}
 */
function showSessionExpiredNotice() {
    if (document.getElementById('session-expired-alert')) {
        return;
    }

    const alert = document.createElement('div');
    alert.id = 'session-expired-alert';
    alert.className = 'alert alert-warning alert-dismissible fade show session-expired-alert';
    alert.setAttribute('role', 'alert');

    alert.innerHTML = `
    <strong>Session expired!</strong> Please log in to continue.
    <button type="button" class="btn btn-sm btn-outline-dark ml-3" onclick="$('#loginModal').modal('show')">
        Log In
    </button>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
`;

    document.body.appendChild(alert);
    showLoginAlert = true;
}

function hideSessionExpiredNotice()
{
    $('#loginModal').modal('hide');
    let node = document.querySelector('#session-expired-alert');
    node.parentNode.removeChild(node);
}

/**
 * Sends a lightweight GET request to the server to refresh the user's session,
 * effectively keeping it alive and preventing it from expiring due to inactivity.
 *
 * @returns {void}
 */
function refreshSession() {
    fetch('lib.ajax/session-refresh.php', {
        method: 'GET',
        credentials: 'include',
    })
        .then(res => {
            if (!res.ok) throw new Error('Session refresh failed');
        })
        .catch(err => {
            console.error('Failed to update session:', err);
        });
}

/**
 * Starts a periodic loop that calls `refreshSession()` at a regular interval.
 * The loop only attempts to refresh the session if the document is visible and
 * the session expired alert is not currently being shown.
 *
 * @returns {void}
 */
function startSessionRefreshLoop() {
    const interval = getSessionRefreshInterval();

    setInterval(() => {
        if (document.visibilityState === 'visible' && !showLoginAlert) {
            refreshSession();
        }
    }, interval);
}

/**
 * Retrieves the session refresh interval from a meta tag in the document's `<head>`.
 * The value is expected to be in seconds and is converted to milliseconds.
 *
 * @returns {number} The refresh interval in milliseconds. Defaults to 300,000ms (5 minutes)
 * if the meta tag is not found or its content is invalid.
 */
function getSessionRefreshInterval() {
    const meta = document.querySelector('meta[name="session-refresh-interval"]');
    const value = meta?.getAttribute('content');
    const interval = parseInt(value, 10) * 1000;
    return isNaN(interval) ? 300000 : interval;
}
