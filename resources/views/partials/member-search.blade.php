{{--
    Member search for the Record Payment form (Admin + Staff).

    Include it inside the Member .form-group, between the label and the
    <select name="member_id">. It builds its list from the select's existing
    options (already loaded by the controller), so there are no extra queries.
    The select stays the single source of truth: the selected option's real
    member ID is what gets submitted.
--}}
<div class="ms-wrap" data-member-search>
    <div class="ms-field">
        <svg class="ms-icon" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="11" cy="11" r="7"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" class="form-control ms-input" placeholder="Search member by name..."
               autocomplete="off" spellcheck="false" role="combobox" aria-label="Search member by name"
               aria-autocomplete="list" aria-expanded="false">
        <button type="button" class="ms-clear" aria-label="Clear search" hidden>&times;</button>
    </div>
    <ul class="ms-list" role="listbox" hidden></ul>
</div>

@once
<style>
    .ms-wrap { position: relative; margin-bottom: 8px; }
    .ms-field { position: relative; }
    .ms-wrap .ms-input { padding-left: 40px; padding-right: 40px; min-height: 44px; }
    .ms-wrap .ms-input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-soft);
        outline: none;
    }
    .ms-icon {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        width: 16px; height: 16px; pointer-events: none;
        stroke: var(--accent); fill: none; stroke-width: 2;
        stroke-linecap: round; stroke-linejoin: round;
    }
    .ms-clear {
        position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
        width: 28px; height: 28px; border: 0; border-radius: 50%;
        background: transparent; color: var(--muted);
        font-size: 20px; line-height: 1; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
    }
    .ms-clear[hidden] { display: none; }
    .ms-clear:hover, .ms-clear:focus-visible { background: var(--accent-soft); color: var(--accent); outline: none; }

    .ms-list {
        position: absolute; left: 0; right: 0; top: calc(100% + 4px); z-index: 60;
        margin: 0; padding: 4px; list-style: none;
        max-height: 220px; overflow-y: auto; overscroll-behavior: contain;
        background: var(--surface2, #212227);
        border: 1px solid rgba(224,169,59,0.45); border-radius: 10px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.45);
    }
    .ms-list[hidden] { display: none; }
    .ms-item {
        padding: 10px 12px; border-radius: 8px; font-size: 14px; color: var(--text);
        cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .ms-item:hover, .ms-item.active { background: var(--accent-soft); color: var(--accent); }
    .ms-item.current { font-weight: 700; }
    .ms-empty { padding: 14px 12px; font-size: 13px; color: var(--muted); text-align: center; }

    @media (max-width: 480px) {
        .ms-wrap .ms-input { font-size: 16px; } /* stops iOS zoom on focus */
        .ms-item { padding: 12px; }
    }
</style>

<script>
(function () {
    function norm(s) {
        return (s || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .toLowerCase().replace(/\s+/g, ' ').trim();
    }

    function init(root) {
        var group  = root.closest('.form-group');
        var select = group && group.querySelector('select[name="member_id"]');
        if (!select) return;

        var input = root.querySelector('.ms-input');
        var clear = root.querySelector('.ms-clear');
        var list  = root.querySelector('.ms-list');
        list.id = 'ms-list-' + Math.random().toString(36).slice(2, 8);
        input.setAttribute('aria-controls', list.id);

        // Build the member list from the existing options (unique IDs only)
        var members = [], byId = {};
        Array.prototype.forEach.call(select.options, function (o) {
            var name = o.textContent.replace(/\s+/g, ' ').trim();
            if (!o.value || o.disabled || !name || byId[o.value]) return;
            byId[o.value] = { id: o.value, name: name, key: norm(name) };
            members.push(byId[o.value]);
        });

        var matches = [], active = -1;

        function isOpen() { return !list.hidden; }
        function close() {
            list.hidden = true; active = -1;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
        }

        function setActive(i) {
            var items = list.querySelectorAll('.ms-item');
            if (!items.length) { active = -1; return; }
            active = (i + items.length) % items.length;
            Array.prototype.forEach.call(items, function (el, n) {
                el.classList.toggle('active', n === active);
                el.setAttribute('aria-selected', n === active ? 'true' : 'false');
            });
            input.setAttribute('aria-activedescendant', items[active].id);
            items[active].scrollIntoView({ block: 'nearest' });
        }

        function render() {
            var tokens = norm(input.value).split(' ').filter(Boolean);
            // every typed word must appear in the name: works for first name,
            // last name, full name, and "last first" order
            matches = members.filter(function (m) {
                return tokens.every(function (t) { return m.key.indexOf(t) !== -1; });
            });

            list.innerHTML = '';
            if (!matches.length) {
                var empty = document.createElement('li');
                empty.className = 'ms-empty';
                empty.setAttribute('role', 'option');
                empty.setAttribute('aria-disabled', 'true');
                empty.textContent = 'No members found';
                list.appendChild(empty);
            } else {
                matches.forEach(function (m, n) {
                    var li = document.createElement('li');
                    li.className = 'ms-item' + (select.value === m.id ? ' current' : '');
                    li.id = list.id + '-' + n;
                    li.setAttribute('role', 'option');
                    li.setAttribute('aria-selected', 'false');
                    li.dataset.id = m.id;
                    li.textContent = m.name;   // textContent: names are never parsed as HTML
                    list.appendChild(li);
                });
            }
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            active = -1;
            clear.hidden = !input.value;
        }

        function choose(id) {
            if (!byId[id]) return;                 // ignore anything that isn't a real member
            select.value = id;                     // the real member ID is what gets submitted
            select.dispatchEvent(new Event('change', { bubbles: true }));
            input.value = '';
            clear.hidden = true;
            close();
        }

        input.addEventListener('focus', render);
        input.addEventListener('click', function () { if (!isOpen()) render(); });
        input.addEventListener('input', render);

        input.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!isOpen()) render();
                setActive(active + 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (!isOpen()) render();
                setActive(active < 0 ? -1 : active - 1);
            } else if (e.key === 'Enter') {
                e.preventDefault();                // never submit the payment from the search box
                if (!isOpen()) return;
                if (active >= 0 && matches[active]) choose(matches[active].id);
                else if (matches.length === 1) choose(matches[0].id);
            } else if (e.key === 'Escape' && isOpen()) {
                e.stopPropagation();               // close the list only, not a surrounding modal
                close();
            } else if (e.key === 'Tab') {
                close();
            }
        });

        list.addEventListener('mousedown', function (e) { e.preventDefault(); }); // keep input focus
        list.addEventListener('click', function (e) {
            var item = e.target.closest('.ms-item');
            if (item) choose(item.dataset.id);
        });

        clear.addEventListener('click', function () {
            input.value = '';
            clear.hidden = true;
            input.focus();
            render();                              // empty search shows the full list again
        });

        document.addEventListener('mousedown', function (e) {
            if (!root.contains(e.target)) close();
        });
        select.addEventListener('change', close);
    }

    function boot() {
        document.querySelectorAll('[data-member-search]').forEach(init);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
</script>
@endonce