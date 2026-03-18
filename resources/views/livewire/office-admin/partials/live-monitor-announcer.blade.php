@php($announcementPayload = $announcementPayload ?? null)

<div
    class="hidden"
    data-queue-monitor-announcement
    data-office-slug="{{ $office->slug }}"
    data-announcement-id="{{ $announcementPayload['id'] ?? '' }}"
    data-announcement-type="{{ $announcementPayload['type'] ?? '' }}"
    data-queue-number="{{ $announcementPayload['queue_number'] ?? '' }}"
    data-service-window-number="{{ $announcementPayload['service_window_number'] ?? '' }}"
    data-triggered-at="{{ $announcementPayload['triggered_at'] ?? '' }}"
    aria-hidden="true"
></div>

@once
    @script
    <script>
        window.__officeQueueMonitorAnnouncer = window.__officeQueueMonitorAnnouncer || (() => {
            let voiceWarmupPromise = null;
            let observerBound = false;
            let isSpeaking = false;
            let syncFrameId = null;

            const queuedAnnouncementIds = new Set();
            const pendingAnnouncements = [];
            const MAX_ANNOUNCEMENT_AGE_MS = 30000;
            const ANNOUNCEMENT_VOICE_PROFILE = {
                exactNames: [
                    'microsoft aria online (natural) - english (united states)',
                    'microsoft sonia online (natural) - english (united kingdom)',
                    'microsoft jenny online (natural) - english (united states)',
                    'microsoft guy online (natural) - english (united states)',
                    'microsoft ryan online (natural) - english (united kingdom)',
                    'microsoft libby online (natural) - english (united kingdom)',
                    'google us english',
                    'google uk english female',
                    'google uk english male'
                ],
                localePreferences: ['en-gb', 'en-us', 'en-au', 'en-ca', 'en-ph', 'fil-ph'],
                femaleHints: ['female', 'woman', 'girl', 'jenny', 'aria', 'sonia', 'hazel', 'libby', 'samantha', 'zira', 'karen'],
                naturalHints: ['natural', 'neural', 'premium', 'enhanced', 'online', 'wavenet', 'studio'],
                formalHints: ['united kingdom', 'great britain', 'united states', 'aria', 'sonia', 'ryan', 'guy', 'libby', 'jenny'],
            };

            const getAnnouncementElements = () => Array.from(document.querySelectorAll('[data-queue-monitor-announcement]'));
            const getSeenAnnouncementKey = (officeSlug) => `queue-monitor:last-announcement:${officeSlug}`;
            const scheduleSyncAnnouncements = () => {
                if (syncFrameId !== null) {
                    return;
                }

                const runSync = () => {
                    syncFrameId = null;
                    syncAnnouncements();
                };

                if (typeof window.requestAnimationFrame === 'function') {
                    syncFrameId = window.requestAnimationFrame(runSync);

                    return;
                }

                syncFrameId = window.setTimeout(runSync, 0);
            };

            const getVoicesWithWarmup = () => {
                if (!('speechSynthesis' in window)) {
                    return Promise.resolve([]);
                }

                const synth = window.speechSynthesis;
                const voices = synth.getVoices();

                if (voices.length) {
                    return Promise.resolve(voices);
                }

                if (voiceWarmupPromise) {
                    return voiceWarmupPromise;
                }

                voiceWarmupPromise = new Promise((resolve) => {
                    let settled = false;

                    const finish = () => {
                        if (settled) {
                            return;
                        }

                        settled = true;
                        synth.removeEventListener('voiceschanged', onVoicesChanged);
                        resolve(synth.getVoices());
                    };

                    const onVoicesChanged = () => {
                        finish();
                    };

                    synth.addEventListener('voiceschanged', onVoicesChanged);
                    window.setTimeout(finish, 1200);
                    synth.getVoices();
                }).then((loadedVoices) => {
                    if (!loadedVoices.length) {
                        voiceWarmupPromise = null;
                    }

                    return loadedVoices;
                });

                return voiceWarmupPromise;
            };

            const getBestAnnouncementVoice = (voices) => {
                if (!voices.length) {
                    return null;
                }

                const englishVoices = voices.filter((voice) => {
                    const lang = (voice.lang || '').toLowerCase();

                    return lang.startsWith('en') || lang.startsWith('fil');
                });

                const exactMatch = englishVoices.find((voice) =>
                    ANNOUNCEMENT_VOICE_PROFILE.exactNames.includes((voice.name || '').toLowerCase())
                );

                if (exactMatch) {
                    return exactMatch;
                }

                const candidates = englishVoices.length ? englishVoices : voices;

                const scoreVoice = (voice) => {
                    const haystack = `${voice.name} ${voice.voiceURI}`.toLowerCase();
                    const lang = (voice.lang || '').toLowerCase();
                    let score = 0;

                    ANNOUNCEMENT_VOICE_PROFILE.localePreferences.forEach((prefix, index) => {
                        if (lang.startsWith(prefix)) {
                            score += 40 - (index * 5);
                        }
                    });

                    if (ANNOUNCEMENT_VOICE_PROFILE.femaleHints.some((hint) => haystack.includes(hint))) score += 18;
                    if (ANNOUNCEMENT_VOICE_PROFILE.naturalHints.some((hint) => haystack.includes(hint))) score += 22;
                    if (ANNOUNCEMENT_VOICE_PROFILE.formalHints.some((hint) => haystack.includes(hint))) score += 16;
                    if (haystack.includes('english')) score += 8;
                    if (haystack.includes('philippines') || lang.startsWith('fil-ph')) score -= 8;
                    if (haystack.includes('male') || haystack.includes('man')) score -= 25;
                    if (voice.default) score += 2;

                    return score;
                };

                return [...candidates].sort((a, b) => scoreVoice(b) - scoreVoice(a))[0] ?? null;
            };

            const toSpokenQueue = (value) => {
                const [prefix, number] = value.split('-');

                if (!number) {
                    return value.split('').join(' ');
                }

                return `${prefix.split('').join(' ')} ${number.split('').join(' ')}`;
            };

            const buildAnnouncementMessage = (type, queueNumber, serviceWindowNumber) => {
                const windowSuffix = serviceWindowNumber
                    ? ` Please proceed to Window ${serviceWindowNumber}.`
                    : ' Please proceed to the office.';

                if (type === 'prepare') {
                    return `Next in line, Queue number ${toSpokenQueue(queueNumber)}. Please prepare.`;
                }

                return `Now serving, Queue number ${toSpokenQueue(queueNumber)}.${windowSuffix}`;
            };

            const speakMessage = async (message) => {
                if (!message || !('speechSynthesis' in window)) {
                    return;
                }

                const synth = window.speechSynthesis;
                const voices = await getVoicesWithWarmup();
                const preferredVoice = getBestAnnouncementVoice(voices);

                await new Promise((resolve) => {
                    const announcement = new SpeechSynthesisUtterance(message);

                    announcement.lang = 'en-GB';
                    announcement.rate = 0.8;
                    announcement.pitch = 0.96;
                    announcement.volume = 1;
                    announcement.onend = resolve;
                    announcement.onerror = resolve;

                    if (preferredVoice) {
                        announcement.voice = preferredVoice;
                        announcement.lang = preferredVoice.lang || 'en-GB';
                    }

                    synth.speak(announcement);
                });
            };

            const flushAnnouncementQueue = async () => {
                if (isSpeaking || !pendingAnnouncements.length) {
                    return;
                }

                isSpeaking = true;

                while (pendingAnnouncements.length) {
                    const nextAnnouncement = pendingAnnouncements.shift();

                    if (!nextAnnouncement) {
                        continue;
                    }

                    queuedAnnouncementIds.delete(nextAnnouncement.announcementId);
                    await speakMessage(nextAnnouncement.message);
                }

                isSpeaking = false;
            };

            const enqueueAnnouncement = (announcementId, message) => {
                if (!announcementId || !message || queuedAnnouncementIds.has(announcementId)) {
                    return;
                }

                queuedAnnouncementIds.add(announcementId);
                pendingAnnouncements.push({ announcementId, message });

                void flushAnnouncementQueue();
            };

            const syncAnnouncementElement = (element) => {
                const officeSlug = element.dataset.officeSlug || '';
                const announcementId = element.dataset.announcementId || '';
                const announcementType = element.dataset.announcementType || '';
                const queueNumber = element.dataset.queueNumber || '';
                const serviceWindowNumber = element.dataset.serviceWindowNumber || '';
                const triggeredAt = element.dataset.triggeredAt || '';

                if (!officeSlug || !announcementId || !queueNumber) {
                    return;
                }

                const seenKey = getSeenAnnouncementKey(officeSlug);
                const seenAnnouncementId = window.sessionStorage.getItem(seenKey);

                if (seenAnnouncementId === announcementId) {
                    return;
                }

                const triggeredAtMs = Date.parse(triggeredAt);
                if (Number.isFinite(triggeredAtMs) && (Date.now() - triggeredAtMs) > MAX_ANNOUNCEMENT_AGE_MS) {
                    return;
                }

                window.sessionStorage.setItem(seenKey, announcementId);
                enqueueAnnouncement(announcementId, buildAnnouncementMessage(announcementType, queueNumber, serviceWindowNumber));
            };

            const syncAnnouncements = () => {
                const elements = getAnnouncementElements().sort((left, right) => {
                    const leftTime = Date.parse(left.dataset.triggeredAt || '') || 0;
                    const rightTime = Date.parse(right.dataset.triggeredAt || '') || 0;

                    return leftTime - rightTime;
                });

                elements.forEach(syncAnnouncementElement);
            };

            const bindObserver = () => {
                if (observerBound) {
                    return;
                }

                observerBound = true;

                const startObserver = () => {
                    if (!document.body) {
                        return;
                    }

                    const observer = new MutationObserver(() => {
                        scheduleSyncAnnouncements();
                    });

                    observer.observe(document.body, {
                        childList: true,
                        subtree: true,
                        attributes: true,
                    });

                    scheduleSyncAnnouncements();
                };

                if (document.body) {
                    startObserver();
                } else {
                    document.addEventListener('DOMContentLoaded', startObserver, { once: true });
                }

                document.addEventListener('livewire:navigated', scheduleSyncAnnouncements);

                if ('speechSynthesis' in window) {
                    window.speechSynthesis.getVoices();
                }
            };

            return {
                bindObserver,
                syncAnnouncements: scheduleSyncAnnouncements,
            };
        })();

        window.__officeQueueMonitorAnnouncer.bindObserver();
        window.__officeQueueMonitorAnnouncer.syncAnnouncements();
    </script>
    @endscript
@endonce
