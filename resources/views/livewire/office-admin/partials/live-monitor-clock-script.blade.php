@once
    <script>
        (() => {
            const clockSelector = '[data-manila-clock]';
            const timeSelector = '[data-manila-clock-time]';
            const dateSelector = '[data-manila-clock-date]';
            const startedClocks = new WeakSet();
            const formatterSets = {
                monitor: {
                    time: new Intl.DateTimeFormat('en-US', {
                        timeZone: 'Asia/Manila',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true,
                    }),
                    date: new Intl.DateTimeFormat('en-US', {
                        timeZone: 'Asia/Manila',
                        weekday: 'long',
                        month: 'short',
                        day: '2-digit',
                        year: 'numeric',
                    }),
                },
                desk: {
                    time: new Intl.DateTimeFormat('en-US', {
                        timeZone: 'Asia/Manila',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true,
                    }),
                    date: new Intl.DateTimeFormat('en-US', {
                        timeZone: 'Asia/Manila',
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric',
                    }),
                },
            };

            const startClock = (clockElement) => {
                if (startedClocks.has(clockElement)) {
                    return;
                }

                const timeElement = clockElement.querySelector(timeSelector);
                const dateElement = clockElement.querySelector(dateSelector);
                const initialIsoTime = clockElement.dataset.manilaNow;
                const initialTimestamp = Date.parse(initialIsoTime ?? '');

                if (!timeElement || !dateElement || Number.isNaN(initialTimestamp)) {
                    return;
                }

                const formatterSet = formatterSets[clockElement.dataset.manilaClockStyle] ?? formatterSets.monitor;
                const timeSuffix = clockElement.dataset.manilaClockSuffix ?? '';
                const serverOffset = initialTimestamp - Date.now();
                let intervalId = null;

                const renderClock = () => {
                    if (!document.body.contains(clockElement)) {
                        if (intervalId !== null) {
                            window.clearInterval(intervalId);
                        }

                        return;
                    }

                    const now = new Date(Date.now() + serverOffset);
                    timeElement.textContent = `${formatterSet.time.format(now)}${timeSuffix}`;
                    dateElement.textContent = formatterSet.date.format(now);
                };

                renderClock();
                intervalId = window.setInterval(renderClock, 1000);
                startedClocks.add(clockElement);
            };

            const bootClocks = () => {
                document.querySelectorAll(clockSelector).forEach(startClock);
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootClocks, { once: true });
            } else {
                bootClocks();
            }

            document.addEventListener('livewire:navigated', bootClocks);
            new MutationObserver(bootClocks).observe(document.body, {
                childList: true,
                subtree: true,
            });
        })();
    </script>
@endonce
