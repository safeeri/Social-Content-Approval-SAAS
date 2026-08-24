document.addEventListener('DOMContentLoaded', () => {

    /* ---------- Sidebar (mobile) ---------- */
    const sidebarToggle = document.getElementById('sidebarToggle');
    const backdrop = document.querySelector('.sv-backdrop');
    const closeSidebar = () => document.body.classList.remove('sidebar-open');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    }
    if (backdrop) backdrop.addEventListener('click', closeSidebar);
    document.querySelectorAll('.sv-nav a').forEach(a => a.addEventListener('click', closeSidebar));

    /* ---------- Select2 on every enhanced select ---------- */
    if (window.jQuery && window.jQuery.fn.select2) {
        jQuery('.select2').select2({ width: '100%', theme: 'default' });
    }

    /* ---------- Phone masking via Cleave.js ---------- */
    document.querySelectorAll('.phone-mask').forEach(el => {
        new Cleave(el, { numericOnly: true, blocks: [3, 3, 4], delimiters: ['-', '-'] });
    });

    /* ---------- Flash auto-dismiss ---------- */
    document.querySelectorAll('.sv-flash').forEach(el => {
        setTimeout(() => bootstrap.Alert.getOrCreateInstance(el).close(), 4500);
    });

    /* ---------- Delete confirmations ---------- */
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', e => {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    /* ---------- Right-side drawer ---------- */
    const drawer = document.getElementById('postDrawer');
    const drawerBody = document.getElementById('drawerBody');
    const drawerTitle = document.getElementById('drawerTitle');

    function openDrawer(url) {
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => { if (!r.ok) throw new Error('Not found'); return r.text(); })
            .then(html => {
                drawerBody.innerHTML = html;
                const heading = drawerBody.querySelector('[data-drawer-title]');
                if (heading) drawerTitle.textContent = heading.textContent;
                document.body.classList.add('drawer-open');
            })
            .catch(() => alert('Could not load this post.'));
    }

    function closeDrawer() {
        document.body.classList.remove('drawer-open');
        stopAllVideos();
    }

    document.querySelectorAll('[data-post-url]').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            openDrawer(el.dataset.postUrl);
        });
    });
    document.querySelectorAll('[data-close-drawer]').forEach(el =>
        el.addEventListener('click', closeDrawer));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeDrawer(); hideMediaModal(); } });
    document.querySelectorAll('.drawer-backdrop').forEach(el =>
        el.addEventListener('click', closeDrawer));

    /* ---------- Expand: fullscreen media modal ---------- */
    const mediaModalEl = document.getElementById('mediaModal');
    const mediaStage = document.getElementById('mediaStage');
    const mediaCaption = document.getElementById('mediaCaptionFull');

    function stopAllVideos() {
        mediaStage?.querySelectorAll('video').forEach(v => v.pause());
        drawerBody?.querySelectorAll('video').forEach(v => v.pause());
    }
    function hideMediaModal() {
        if (mediaModalEl) bootstrap.Modal.getInstance(mediaModalEl)?.hide();
    }

    document.addEventListener('click', e => {
        const expandBtn = e.target.closest('[data-expand]');
        if (expandBtn) {
            const src = expandBtn.dataset.expand;
            const type = expandBtn.dataset.mediaType || 'image';
            mediaStage.innerHTML = type === 'video'
                ? `<video src="${src}" controls autoplay playsinline class="w-100"></video>`
                : `<img src="${src}" alt="Post media">`;
            mediaCaption.innerHTML = expandBtn.closest('[data-full-caption]')?.dataset.fullCaption
                ?? drawerBody.querySelector('#captionText')?.innerHTML ?? '';
            bootstrap.Modal.getOrCreateInstance(mediaModalEl).show();
        }
    });

    /* ---------- FullCalendar with status filters ---------- */
    const calEl = document.getElementById('calendar');
    if (calEl && window.FullCalendar) {
        let allEvents = [];

        const calendar = new FullCalendar.Calendar(calEl, {
            initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth',
            headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
            height: 'auto',
            events: (info, success) => {
                fetch(`/calendar/events?start=${encodeURIComponent(info.startStr)}&end=${encodeURIComponent(info.endStr)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(r => r.json())
                    .then(data => { allEvents = data.events || []; renderEvents(); success([]); });
            },
            eventClick: info => {
                info.jsEvent.preventDefault();
                openDrawer(`/posts/${info.event.id}/preview`);
            },
        });
        calendar.render();

        window.addEventListener('resize', (() => {
            let t;
            return () => {
                clearTimeout(t);
                t = setTimeout(() => {
                    const want = window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth';
                    if (calendar.view.type !== want) calendar.changeView(want);
                }, 200);
            };
        })());

        function activeStatuses() {
            return [...calEl.parentElement.parentElement.querySelectorAll('.status-pill.on')]
                .map(p => p.dataset.status);
        }

        function renderEvents() {
            const on = activeStatuses();
            calendar.removeAllEventSources();
            calendar.addEventSource(allEvents.filter(ev => on.includes(ev.extendedProps.status)));
        }

        document.querySelectorAll('.status-pill').forEach(pill => {
            pill.addEventListener('click', () => { pill.classList.toggle('on'); renderEvents(); });
        });
    }
});
