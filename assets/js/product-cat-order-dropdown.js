(function() {
    'use strict';

    function buildCustomDropdown(select) {
        if (!select || select.getAttribute('data-fq-custom-dropdown') === 'true') return;

        var wrapper = document.createElement('div');
        wrapper.className = 'fq-order-dropdown';

        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'fq-order-dropdown__trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        var selectedText = document.createElement('span');
        selectedText.className = 'fq-order-dropdown__selected';
        selectedText.textContent = select.options[select.selectedIndex]?.textContent || select.options[0]?.textContent || 'Ordenar por';
        trigger.appendChild(selectedText);

        var arrow = document.createElement('span');
        arrow.className = 'fq-order-dropdown__arrow material-symbols-outlined';
        arrow.textContent = 'expand_more';
        trigger.appendChild(arrow);

        var panel = document.createElement('div');
        panel.className = 'fq-order-dropdown__panel';
        panel.setAttribute('role', 'listbox');
        panel.setAttribute('aria-label', 'Ordenar por');

        Array.from(select.options).forEach(function(opt) {
            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'fq-order-dropdown__option';
            item.setAttribute('role', 'option');
            item.setAttribute('data-value', opt.value);
            item.textContent = opt.textContent;

            if (opt.selected) {
                item.classList.add('is-selected');
                item.setAttribute('aria-selected', 'true');
            } else {
                item.setAttribute('aria-selected', 'false');
            }

            item.addEventListener('click', function(e) {
                e.preventDefault();
                select.value = opt.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                selectedText.textContent = opt.textContent;
                panel.querySelectorAll('.fq-order-dropdown__option').forEach(function(el) {
                    el.classList.remove('is-selected');
                    el.setAttribute('aria-selected', 'false');
                });
                item.classList.add('is-selected');
                item.setAttribute('aria-selected', 'true');
                closeDropdown();
            });

            panel.appendChild(item);
        });

        try {
            var parent = select.parentNode;
            if (parent) {
                parent.insertBefore(wrapper, select);
                wrapper.appendChild(trigger);
                wrapper.appendChild(panel);
                wrapper.appendChild(select);
                select.style.display = 'none';
            } else {
                return;
            }
        } catch (e) {
            return;
        }

        function openDropdown() {
            wrapper.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            var selected = panel.querySelector('.is-selected');
            if (selected) {
                selected.focus();
            } else {
                var first = panel.querySelector('.fq-order-dropdown__option');
                if (first) first.focus();
            }
        }

        function closeDropdown() {
            wrapper.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            if (wrapper.classList.contains('is-open')) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                closeDropdown();
            }
        });

        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (wrapper.classList.contains('is-open')) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            }
            if (e.key === 'Escape') {
                closeDropdown();
                trigger.focus();
            }
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                var options = Array.from(panel.querySelectorAll('.fq-order-dropdown__option'));
                var currentIndex = options.indexOf(document.activeElement);
                var nextIndex;
                if (e.key === 'ArrowDown') {
                    nextIndex = currentIndex < options.length - 1 ? currentIndex + 1 : 0;
                } else {
                    nextIndex = currentIndex > 0 ? currentIndex - 1 : options.length - 1;
                }
                if (options[nextIndex]) options[nextIndex].focus();
            }
        });

        select.setAttribute('data-fq-custom-dropdown', 'true');
    }

    function init(scope) {
        var selects = (scope || document).querySelectorAll('#sp-filter-order');
        Array.prototype.forEach.call(selects, function(select) {
            buildCustomDropdown(select);
        });
    }

    function tryInit() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() { init(); });
        } else {
            init();
        }
    }

    tryInit();

    document.addEventListener('sp_filter_loaded', function() {
        setTimeout(function() {
            var search = document.querySelector('.sp-filter-search-ajax');
            if (search) init(search);
        }, 60);
    });

    var pollTimer = setInterval(function() {
        var selects = document.querySelectorAll('#sp-filter-order');
        var allDone = true;
        Array.prototype.forEach.call(selects, function(select) {
            if (select.getAttribute('data-fq-custom-dropdown') !== 'true') {
                buildCustomDropdown(select);
                allDone = false;
            }
        });
        if (allDone) clearInterval(pollTimer);
    }, 200);
    setTimeout(function() { clearInterval(pollTimer); }, 10000);

    var observer = new MutationObserver(function() {
        var selects = document.querySelectorAll('#sp-filter-order');
        Array.prototype.forEach.call(selects, function(select) {
            if (select.getAttribute('data-fq-custom-dropdown') !== 'true') {
                buildCustomDropdown(select);
            }
        });
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
})();
