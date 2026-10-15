'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const context = {
    URL,
    window: { location: { origin: 'https://example.test' } },
    document: { querySelector: () => null, addEventListener: () => {}, head: { insertAdjacentHTML: () => {} } },
    $: () => ({ ajaxError: () => {} }),
    module: { exports: {} }
};
vm.createContext(context);
vm.runInContext(fs.readFileSync(path.join(__dirname, '../../public/assets/js/select2-init.js'), 'utf8'), context);
const resolve = context.module.exports.resolveFirstPartySearchUrl;
let cases = 0;
for (const endpoint of ['patients', 'users', 'examinations', 'treatments', 'inventory', 'medications', 'treatment-types', 'departments', 'roles']) {
    for (const prefix of ['/', '/dental-saas/', 'https://example.test/dental-saas/']) {
        assert.equal(resolve(prefix + 'api/v1/search/' + endpoint + '?q=Alice#search'), prefix + 'api/search/' + endpoint + '?q=Alice#search');
        cases++;
    }
}
for (const unchanged of ['https://external.test/api/v1/search/users', '//external.test/api/v1/search/users',
    '/api/v1/search/doctors', '/api/v1/search/users-extra', '/api/v1/search/users/unknown', '/api/v1/patients', null, '']) {
    assert.equal(resolve(unchanged), unchanged);
    cases++;
}
assert.equal(context.module.exports.isV1SearchUrl(resolve('/dental-saas/api/v1/search/users')), false);
console.log(`OK (${cases + 1} URL and transport assertions)`);