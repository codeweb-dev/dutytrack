import { test } from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';

function worker() {
    const events = {};
    const stored = new Map();
    const deleted = [];
    const cache = { addAll: async paths => paths.forEach(path => stored.set(new URL(path, 'https://example.test').href, path)), match: async request => stored.get(typeof request === 'string' ? new URL(request, 'https://example.test').href : request.url) };
    const context = {
        CONFIG: { prefix: 'dutytrack-test-', version: 'v3', assets: ['/duty/pwa/offline.html', '/duty/build/assets/app-hash.js'], offline: '/duty/pwa/offline.html' },
        URL, Set, Promise,
        self: { registration: { scope: 'https://example.test/duty/' }, location: { origin: 'https://example.test' }, addEventListener: (type, callback) => events[type] = callback, clients: { claim: async () => {} }, skipWaiting: () => { context.activated = true; } },
        caches: { open: async () => cache, keys: async () => ['other-app', 'dutytrack-test-v1', 'dutytrack-test-v2', 'dutytrack-test-v3'], delete: async name => deleted.push(name) },
        fetch: async () => { throw new Error('offline'); },
    };
    vm.runInNewContext(readFileSync('resources/js/service-worker.js', 'utf8'), context);
    return { events, context, stored, deleted };
}
test('only public allowlisted assets are cached; navigation falls back without storing private HTML', async () => {
    const { events, stored } = worker();
    let pending;
    events.install({ waitUntil: promise => pending = promise });
    await pending;
    assert.equal(stored.size, 2);
    for (const [url, method] of [['/duty/login', 'POST'], ['/duty/livewire/update', 'POST'], ['/duty/api/students', 'GET'], ['/duty/storage/private.pdf', 'GET'], ['/duty/build/assets/app-hash.js?private=1', 'GET']]) {
        let intercepted = false;
        events.fetch({ request: { url: 'https://example.test' + url, method }, respondWith: () => intercepted = true });
        assert.equal(intercepted, false, url);
    }
    events.fetch({ request: { url: 'https://example.test/duty/student/dashboard', method: 'GET', mode: 'navigate' }, respondWith: promise => pending = promise });
    assert.equal(await pending, '/duty/pwa/offline.html');
    assert.equal(stored.size, 2);
    events.fetch({ request: { url: 'https://example.test/duty/build/assets/app-hash.js', method: 'GET' }, respondWith: promise => pending = promise });
    assert.equal(await pending, '/duty/build/assets/app-hash.js');
});
test('updates wait for consent and clean only older DutyTrack caches', async () => {
    const { events, context, deleted } = worker();
    let pending;
    events.activate({ waitUntil: promise => pending = promise });
    await pending;
    assert.deepEqual(deleted, ['dutytrack-test-v1']);
    assert.equal(context.activated, undefined);
    events.message({ data: 'ACTIVATE_UPDATE' });
    assert.equal(context.activated, true);
});
