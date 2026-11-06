// Opened tabs and panel states stay in the browser: the site sets no cookie.
// sessionStorage throws when the browser blocks storage: the interface then just forgets its state.
export function load(key, fallback) {
    try {
        return JSON.parse(sessionStorage.getItem(key)) ?? fallback;
    } catch {
        return fallback;
    }
}

export function save(key, value) {
    try {
        sessionStorage.setItem(key, JSON.stringify(value));
    } catch {
    }
}
