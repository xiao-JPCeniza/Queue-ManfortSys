@php($announcementPayload = $announcementPayload ?? null)

<div
    class="hidden"
    data-queue-monitor-announcement
    data-office-slug="{{ $office->slug }}"
    data-announcement-id="{{ $announcementPayload['id'] ?? '' }}"
    data-announcement-type="{{ $announcementPayload['type'] ?? '' }}"
    data-queue-number="{{ $announcementPayload['queue_number'] ?? '' }}"
    data-triggered-at="{{ $announcementPayload['triggered_at'] ?? '' }}"
    aria-hidden="true"
></div>

@script
<script>
    window.__officeQueueMonitorAnnouncer = window.__officeQueueMonitorAnnouncer || (() => {
        let voiceWarmupPromise = null;
        let observerBound = false;

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

        const getAnnouncementElement = () => document.querySelector('[data-queue-monitor-announcement]');
        const getSeenAnnouncementKey = (officeSlug) => `queue-monitor:last-announcement:${officeSlug}`;

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

        const buildAnnouncementMessage = (type, queueNumber) => {
            if (type === 'prepare') {
                return `Next in line, Queue number ${toSpokenQueue(queueNumber)}. Please prepare.`;
            }

            return `Now serving, Queue number ${toSpokenQueue(queueNumber)}. Please proceed to the office.`;
        };

        const speakMessage = async (message) => {
            if (!message || !('speechSynthesis' in window)) {
                return;
            }

            const synth = window.speechSynthesis;
            synth.cancel();

            const voices = await getVoicesWithWarmup();
            const preferredVoice = getBestAnnouncementVoice(voices);
            const announcement = new SpeechSynthesisUtterance(message);

            announcement.lang = 'en-GB';
            announcement.rate = 0.8;
            announcement.pitch = 0.96;
            announcement.volume = 1;

            if (preferredVoice) {
                announcement.voice = preferredVoice;
                announcement.lang = preferredVoice.lang || 'en-GB';
            }

            synth.speak(announcement);
        };

        const syncAnnouncement = () => {
            const element = getAnnouncementElement();
            if (!element) {
                return;
            }

            const officeSlug = element.dataset.officeSlug || '';
            const announcementId = element.dataset.announcementId || '';
            const announcementType = element.dataset.announcementType || '';
            const queueNumber = element.dataset.queueNumber || '';
            const triggeredAt = element.dataset.triggeredAt || '';

            if (!officeSlug || !announcementId || !queueNumber) {
                return;
            }

            const seenKey = getSeenAnnouncementKey(officeSlug);
            const seenAnnouncementId = window.sessionStorage.getItem(seenKey);

            if (seenAnnouncementId === announcementId) {
                return;
            }

            window.sessionStorage.setItem(seenKey, announcementId);

            const triggeredAtMs = Date.parse(triggeredAt);
            if (Number.isFinite(triggeredAtMs) && (Date.now() - triggeredAtMs) > MAX_ANNOUNCEMENT_AGE_MS) {
                return;
            }

            void speakMessage(buildAnnouncementMessage(announcementType, queueNumber));
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
                    syncAnnouncement();
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                });

                syncAnnouncement();
            };

            if (document.body) {
                startObserver();
            } else {
                document.addEventListener('DOMContentLoaded', startObserver, { once: true });
            }

            document.addEventListener('livewire:navigated', syncAnnouncement);

            if ('speechSynthesis' in window) {
                window.speechSynthesis.getVoices();
            }
        };

        return {
            bindObserver,
            syncAnnouncement,
        };
    })();

    window.__officeQueueMonitorAnnouncer.bindObserver();
    window.__officeQueueMonitorAnnouncer.syncAnnouncement();
</script>
@endscript
