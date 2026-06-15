const TEXT_FIELD_SELECTOR = [
    'input:not([type])',
    'input[type="text"]',
    'input[type="search"]',
    'input[type="email"]',
    'input[type="tel"]',
    'input[type="url"]',
    'input[type="password"]',
    'input[type="number"]',
    'input[type="date"]',
    'input[type="datetime-local"]',
    'input[type="month"]',
    'input[type="week"]',
    'input[type="time"]',
    'textarea',
].join(', ');

/** Mirrors App\Support\TextDirection — first strong character wins. */
const RTL_STRONG = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF]/;
const LTR_STRONG = /[A-Za-z\u00C0-\u024F\u0370-\u03FF]/;

function directionFromText(text) {
    if (!text || !text.trim()) {
        return 'auto';
    }

    for (const char of text) {
        if (RTL_STRONG.test(char)) {
            return 'rtl';
        }
        if (LTR_STRONG.test(char)) {
            return 'ltr';
        }
    }

    return 'auto';
}

function bindBidiField(el) {
    if (el.dataset.bidiBound === 'true') {
        return;
    }

    const initialDir = el.getAttribute('dir');
    if (initialDir === 'rtl' || initialDir === 'ltr') {
        el.dataset.bidiFixed = 'true';
        return;
    }

    el.dataset.bidiBound = 'true';

    if (el.tagName === 'TEXTAREA') {
        // Per-line direction is handled by unicode-bidi: plaintext in CSS.
        el.setAttribute('dir', 'auto');
        return;
    }

    const sync = () => {
        el.setAttribute('dir', directionFromText(el.value));
    };

    sync();
    el.addEventListener('input', sync);
}

function applyInputDirection(root = document) {
    root.querySelectorAll(TEXT_FIELD_SELECTOR).forEach(bindBidiField);
}

function initInputDirection() {
    applyInputDirection();

    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node.nodeType !== Node.ELEMENT_NODE) {
                    continue;
                }

                if (node.matches?.(TEXT_FIELD_SELECTOR)) {
                    bindBidiField(node);
                }

                applyInputDirection(node);
            }
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInputDirection);
} else {
    initInputDirection();
}
