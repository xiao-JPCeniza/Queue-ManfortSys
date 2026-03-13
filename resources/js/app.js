import './bootstrap';

const SESSION_PULSE_INTERVAL_MS = 60_000;

let sessionPulsePromise = null;
let sessionPulseTimerId = null;
let livewireRecoveryBound = false;

const getMeta = (name) => document.querySelector(`meta[name="${name}"]`);

const setCsrfToken = (token) => {
    if (!token) {
        return;
    }

    const csrfMeta = getMeta('csrf-token');
    if (csrfMeta) {
        csrfMeta.setAttribute('content', token);
    }

    if (window.axios) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
};

const shouldKeepSessionPulseAlive = () =>
    document.querySelector('[data-session-keepalive="always"]') !== null;

const syncSessionPulse = async () => {
    const sessionPulseUrl = getMeta('session-pulse-url')?.getAttribute('content');

    if (!sessionPulseUrl) {
        return null;
    }

    if (sessionPulsePromise) {
        return sessionPulsePromise;
    }

    sessionPulsePromise = window.fetch(sessionPulseUrl, {
        method: 'GET',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        cache: 'no-store',
    })
        .then(async (response) => {
            if (!response.ok) {
                return null;
            }

            const payload = await response.json().catch(() => null);
            if (payload?.token) {
                setCsrfToken(payload.token);
            }

            return payload;
        })
        .catch(() => null)
        .finally(() => {
            sessionPulsePromise = null;
        });

    return sessionPulsePromise;
};

const recoverExpiredPage = async () => {
    await syncSessionPulse();

    if (typeof window.Livewire?.navigate === 'function') {
        try {
            window.Livewire.navigate(window.location.href);
            return;
        } catch (error) {
            // Fall back to a hard reload if navigate cannot recover the page.
        }
    }

    window.location.reload();
};

const handleExpiredRequest = ({ status, response, preventDefault }) => {
    const resolvedStatus = status ?? response?.status;

    if (resolvedStatus !== 419) {
        return false;
    }

    preventDefault?.();
    void recoverExpiredPage();

    return true;
};

const startSessionPulse = () => {
    if (sessionPulseTimerId !== null) {
        window.clearInterval(sessionPulseTimerId);
        sessionPulseTimerId = null;
    }

    if (!getMeta('session-pulse-url')) {
        return;
    }

    sessionPulseTimerId = window.setInterval(() => {
        if (!document.hidden || shouldKeepSessionPulseAlive()) {
            void syncSessionPulse();
        }
    }, SESSION_PULSE_INTERVAL_MS);
};

const bindLivewireRecovery = () => {
    if (livewireRecoveryBound || !window.Livewire) {
        return;
    }

    if (typeof window.Livewire.hook === 'function') {
        window.Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                handleExpiredRequest({ status, preventDefault });
            });
        });

        livewireRecoveryBound = true;

        return;
    }

    if (typeof window.Livewire.interceptRequest === 'function') {
        window.Livewire.interceptRequest(({ onError }) => {
            onError(({ response, preventDefault }) => {
                handleExpiredRequest({ response, preventDefault });
            });
        });

        livewireRecoveryBound = true;
    }
};

setCsrfToken(getMeta('csrf-token')?.getAttribute('content'));

bindLivewireRecovery();

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        void syncSessionPulse();
    }
});

window.addEventListener('focus', () => {
    void syncSessionPulse();
});

const bootLivewireSupport = () => {
    bindLivewireRecovery();
    startSessionPulse();
    void syncSessionPulse();
};

document.addEventListener('livewire:init', bootLivewireSupport);
document.addEventListener('livewire:initialized', bootLivewireSupport);

document.addEventListener('livewire:navigated', () => {
    setCsrfToken(getMeta('csrf-token')?.getAttribute('content'));
    bootLivewireSupport();
});
