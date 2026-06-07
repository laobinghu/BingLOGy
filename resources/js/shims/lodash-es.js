function debounce(fn, wait) {
    let timer = null;
    let pendingArgs = null;
    let pendingThis = null;

    function debounced(...args) {
        pendingArgs = args;
        pendingThis = this;
        if (timer !== null) clearTimeout(timer);
        timer = setTimeout(invoke, wait);
    }

    function invoke() {
        const args = pendingArgs;
        const ctx = pendingThis;
        timer = null;
        pendingArgs = pendingThis = null;
        if (args) fn.apply(ctx, args);
    }

    debounced.cancel = function () {
        if (timer !== null) {
            clearTimeout(timer);
            timer = null;
        }
        pendingArgs = pendingThis = null;
    };

    return debounced;
}

function throttle(fn, wait) {
    let last = 0;
    let timer = null;
    let pendingArgs = null;
    let pendingThis = null;

    function throttled(...args) {
        const now = Date.now();
        const remaining = wait - (now - last);
        pendingArgs = args;
        pendingThis = this;
        if (remaining <= 0) {
            if (timer !== null) {
                clearTimeout(timer);
                timer = null;
            }
            last = now;
            const a = pendingArgs;
            const t = pendingThis;
            pendingArgs = pendingThis = null;
            fn.apply(t, a);
        } else if (timer === null) {
            timer = setTimeout(invoke, remaining);
        }
    }

    function invoke() {
        const args = pendingArgs;
        const ctx = pendingThis;
        timer = null;
        last = Date.now();
        pendingArgs = pendingThis = null;
        if (args) fn.apply(ctx, args);
    }

    throttled.cancel = function () {
        if (timer !== null) {
            clearTimeout(timer);
            timer = null;
        }
        last = 0;
        pendingArgs = pendingThis = null;
    };

    return throttled;
}

export { debounce, throttle };
export default { debounce, throttle };
