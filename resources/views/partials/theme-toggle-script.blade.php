<script>
    (() => {
        const STORAGE_KEY = 'flux.appearance';
        const ORDER = ['light', 'dark', 'system'];

        const button = document.getElementById('theme-toggle');
        if (!button) return;

        const icons = {
            light: button.querySelector('[data-icon="light"]'),
            dark: button.querySelector('[data-icon="dark"]'),
            system: button.querySelector('[data-icon="system"]'),
        };

        const labels = {
            light: '当前：浅色，点击切到深色',
            dark: '当前：深色，点击切到跟随系统',
            system: '当前：跟随系统，点击切到浅色',
        };

        const stored = localStorage.getItem(STORAGE_KEY);
        let state = stored && ORDER.includes(stored) ? stored : 'system';
        button.dataset.appearance = state;

        const render = () => {
            for (const [key, el] of Object.entries(icons)) {
                if (!el) continue;
                el.hidden = key !== state;
            }
            button.setAttribute('title', labels[state]);
        };

        render();

        button.addEventListener('click', () => {
            const idx = ORDER.indexOf(state);
            state = ORDER[(idx + 1) % ORDER.length];
            button.dataset.appearance = state;
            window.Flux?.applyAppearance(state);
            render();
        });
    })();
</script>
