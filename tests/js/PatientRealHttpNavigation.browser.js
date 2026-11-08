'use strict';
// Real HTTP browser navigation suite for Patient Registration.
// Boots against live candidate CodeIgniter HTTP server on 127.0.0.1:57527 with 127.0.0.1:3307 disposable DB.
// Exercises real HTTP responses (303/200), real form submission, real session/CSRF, and real database persistence.
// Evaluated and invoked by tests/js/run-patient-navigation-http.cjs in Playwright.

module.exports = async (page, fixture) => {
    const { baseUrl, clinicId, email, password, screenshotDir, queryDbCount } = fixture;
    const results = [], errors = [];
    const assert = (ok, msg) => { if (!ok) throw new Error(msg); };

    page.on('pageerror', e => errors.push(e.message));
    page.on('dialog', dialog => dialog.type() === 'beforeunload' ? dialog.accept() : dialog.dismiss());

    // -------------------------------------------------------------------------
    // STEP 0: Authenticate via real /auth/login with synthetic test credentials
    // -------------------------------------------------------------------------
    await page.setViewportSize({ width: 1366, height: 768 });
    await page.goto(`${baseUrl}/auth/login`, { waitUntil: 'networkidle' });

    const identityInput = page.locator('#identity');
    const passwordInput = page.locator('#password');
    const loginButton = page.locator('button[type="submit"]');

    assert(await identityInput.isVisible(), 'Login identity input visible');
    assert(await passwordInput.isVisible(), 'Login password input visible');

    await identityInput.fill(email);
    await passwordInput.fill(password);

    const [loginRes] = await Promise.all([
        page.waitForResponse(res => res.url().includes('/auth/login') && res.request().method() === 'POST'),
        loginButton.click()
    ]);
    assert([302, 303].includes(loginRes.status()), `Login returned redirect (got ${loginRes.status()})`);

    // Auto-selects single active clinic and redirects to workspace
    await page.waitForURL(url => !url.pathname.includes('/auth/login'), { timeout: 10000 });
    assert(!page.url().includes('/auth/login'), 'Authenticated session reached clinic workspace');

    // =========================================================================
    // CASE 1: Server Validation Roundtrip (EN & AR)
    // Send invalid submission -> real 303 -> GET 200 with error summary & old input
    // =========================================================================
    for (const locale of ['en', 'ar']) {
        if (locale === 'ar') {
            await page.goto(`${baseUrl}/language/switch/ar`, { waitUntil: 'networkidle' });
        } else {
            await page.goto(`${baseUrl}/language/switch/en`, { waitUntil: 'networkidle' });
        }

        const countBefore1 = await queryDbCount();
        await page.goto(`${baseUrl}/patient/create`, { waitUntil: 'networkidle' });

        const form = page.locator('form[data-patient-create-form]');
        assert(await form.isVisible(), `[${locale}] Real patient create form is visible`);

        // Bypass browser HTML5 constraint validation strictly to test server validation contract
        await page.evaluate(() => {
            const f = document.querySelector('form[data-patient-create-form]');
            if (f) {
                f.noValidate = true;
                f.setAttribute('novalidate', '');
            }
        });

        // Fill fields: valid phone to satisfy client-side JS; invalid first_name (1 char) to trigger server validation failure
        await page.locator('#first_name').fill('A'); // Fails min_length[2] on server
        await page.locator('#last_name').fill('RoundtripTester');
        await page.locator('#phone').fill('0599112233');
        await page.locator('#date_of_birth').fill('1990-01-01');
        await page.locator('#gender').selectOption('male');

        const saveBtn = form.locator('button[value="save_only"]');
        await saveBtn.evaluate(btn => {
            btn.formNoValidate = true;
            btn.setAttribute('formnovalidate', '');
        });

        // Submit form and intercept actual HTTP response
        const [storeRes] = await Promise.all([
            page.waitForResponse(res => res.url().includes('/patient/store') && res.request().method() === 'POST'),
            saveBtn.click()
        ]);

        assert([302, 303].includes(storeRes.status()), `[${locale}] Server returned real HTTP 302/303 redirect on validation failure (got ${storeRes.status()})`);

        // Wait for browser to follow redirect back to /patient/create
        await page.waitForURL(url => url.pathname.includes('/patient/create'));

        // Verify visible error summary and jump links
        const errorNotice = page.locator('.form-notice--error');
        await errorNotice.waitFor({ state: 'visible' });
        assert(await errorNotice.isVisible(), `[${locale}] Localized server validation error summary visible`);

        const firstNameJumpLink = errorNotice.locator('a[href="#first_name"]');
        assert(await firstNameJumpLink.isVisible(), `[${locale}] Jump link to #first_name rendered in error summary`);

        // Verify preserved safe old input
        const preservedLastName = await page.locator('#last_name').inputValue();
        assert(preservedLastName === 'RoundtripTester', `[${locale}] Safe old input preserved: expected RoundtripTester, got ${preservedLastName}`);

        // Verify both actions remain fully usable
        const primaryBtn = form.locator('button[type="submit"].primary');
        const secondaryBtn = form.locator('button[value="save_only"]');
        assert(await primaryBtn.getAttribute('aria-disabled') === null, `[${locale}] Primary button usable after error return`);
        assert(await secondaryBtn.getAttribute('aria-disabled') === null, `[${locale}] Secondary button usable after error return`);
        assert(await form.getAttribute('aria-busy') === null, `[${locale}] Form aria-busy is cleared after error return`);

        // Verify ZERO rows inserted into database
        const countAfter1 = await queryDbCount();
        assert(countAfter1 === countBefore1, `[${locale}] Database patient count unchanged (0 rows inserted on validation failure)`);

        if (screenshotDir) {
            await page.screenshot({ path: `${screenshotDir}/patient_http_validation_roundtrip_${locale}.png` });
        }

        results.push({ test: 'ServerValidationRoundtrip', locale, passed: true, insertedRows: 0 });
    }

    // Reset language to English
    await page.goto(`${baseUrl}/language/switch/en`, { waitUntil: 'networkidle' });

    // =========================================================================
    // CASE 2: Valid Save (post_submit_action=save_only)
    // Real 303 -> browser navigates to canonical patient index -> exactly 1 row in DB
    // =========================================================================
    {
        const countBefore2 = await queryDbCount();
        await page.goto(`${baseUrl}/patient/create`, { waitUntil: 'networkidle' });

        const form = page.locator('form[data-patient-create-form]');
        await page.locator('#first_name').fill('Sarah');
        await page.locator('#last_name').fill('SaveOnly');
        await page.locator('#phone').fill('0599112233');
        await page.locator('#date_of_birth').fill('1990-05-15');
        await page.locator('#gender').selectOption('female');

        const saveBtn = form.locator('button[value="save_only"]');
        let capturedPostData = null;

        const [saveRes] = await Promise.all([
            page.waitForResponse(res => {
                if (res.url().includes('/patient/store') && res.request().method() === 'POST') {
                    capturedPostData = new URLSearchParams(res.request().postData());
                    return true;
                }
                return false;
            }),
            saveBtn.click()
        ]);

        assert([302, 303].includes(saveRes.status()), `Save submission returned HTTP 302/303 redirect (got ${saveRes.status()})`);
        assert(capturedPostData !== null, 'POST data captured on save');
        assert(capturedPostData.get('post_submit_action') === 'save_only', 'Payload sent post_submit_action=save_only');
        assert(capturedPostData.get('first_name') === 'Sarah', 'Payload sent first_name=Sarah');

        // Browser arrives at canonical patient index
        await page.waitForURL(url => url.pathname.includes('/patient'));
        assert(page.url().includes('/patient'), 'Browser arrived at canonical patient registry destination');

        // Verify rendered destination state
        const heading = page.locator('h1, .page-title-row');
        await heading.first().waitFor({ state: 'visible', timeout: 5000 });
        assert(await heading.first().isVisible(), 'Rendered patient index heading is visible');

        // Exactly 1 new patient row in DB
        const countAfter2 = await queryDbCount();
        assert(countAfter2 === countBefore2 + 1, `Exactly 1 patient row inserted (before=${countBefore2}, after=${countAfter2})`);

        if (screenshotDir) {
            await page.screenshot({ path: `${screenshotDir}/patient_http_save_only_destination.png` });
        }

        results.push({ test: 'ValidSaveOnlyNavigation', passed: true, insertedRows: 1 });
    }

    // =========================================================================
    // CASE 3: Valid Save & Book (post_submit_action=save_and_book)
    // Real 303 -> browser navigates to appointment/create?patient_id=X -> exactly 1 row
    // =========================================================================
    {
        const countBefore3 = await queryDbCount();
        await page.goto(`${baseUrl}/patient/create`, { waitUntil: 'networkidle' });

        const form = page.locator('form[data-patient-create-form]');
        await page.locator('#first_name').fill('Adam');
        await page.locator('#last_name').fill('SaveAndBook');
        await page.locator('#phone').fill('0599223344');
        await page.locator('#date_of_birth').fill('1988-10-20');
        await page.locator('#gender').selectOption('male');

        const saveAndBookBtn = form.locator('button[value="save_and_book"]');
        let capturedBookPost = null;

        const [bookRes] = await Promise.all([
            page.waitForResponse(res => {
                if (res.url().includes('/patient/store') && res.request().method() === 'POST') {
                    capturedBookPost = new URLSearchParams(res.request().postData());
                    return true;
                }
                return false;
            }),
            saveAndBookBtn.click()
        ]);

        assert([302, 303].includes(bookRes.status()), `Save & Book returned HTTP 302/303 redirect (got ${bookRes.status()})`);
        assert(capturedBookPost !== null, 'POST data captured for Save & Book');
        assert(capturedBookPost.get('post_submit_action') === 'save_and_book', 'Payload sent post_submit_action=save_and_book');

        // Browser arrives at appointment creation with patient_id context
        await page.waitForURL(url => url.pathname.includes('/appointment/create'));
        const currentUrl = page.url();
        assert(currentUrl.includes('/appointment/create'), 'Browser arrived at appointment/create');
        assert(currentUrl.includes('patient_id='), 'URL context includes patient_id parameter');

        // Verify rendered appointment create state
        const apptHeading = page.locator('h1, form, .page-title-row');
        await apptHeading.first().waitFor({ state: 'visible', timeout: 5000 });
        assert(await apptHeading.first().isVisible(), 'Rendered appointment create destination is visible');

        // Exactly 1 new patient row in DB
        const countAfter3 = await queryDbCount();
        assert(countAfter3 === countBefore3 + 1, `Exactly 1 patient row inserted (before=${countBefore3}, after=${countAfter3})`);

        if (screenshotDir) {
            await page.screenshot({ path: `${screenshotDir}/patient_http_save_and_book_destination.png` });
        }

        results.push({ test: 'ValidSaveAndBookNavigation', passed: true, insertedRows: 1 });
    }

    // =========================================================================
    // CASE 4: Slow Real Request (Hold in-flight, suppress duplicates, forward to real app)
    // =========================================================================
    {
        const countBefore4 = await queryDbCount();
        await page.goto(`${baseUrl}/patient/create`, { waitUntil: 'networkidle' });

        const form = page.locator('form[data-patient-create-form]');
        await page.locator('#first_name').fill('Tariq');
        await page.locator('#last_name').fill('SlowRequest');
        await page.locator('#phone').fill('0599334455');
        await page.locator('#date_of_birth').fill('1985-03-12');
        await page.locator('#gender').selectOption('male');

        const primaryBtn = form.locator('button[type="submit"].primary');
        const secondaryBtn = form.locator('button[value="save_only"]');

        let forwardedCount = 0;
        let capturedPostAction = null;

        // Set up route intercept with delayed forwarding (holding for 1200ms)
        await page.route('**/patient/store', async route => {
            forwardedCount++;
            const postBody = route.request().postData();
            if (postBody) {
                const params = new URLSearchParams(postBody);
                capturedPostAction = params.get('post_submit_action');
            }
            await new Promise(r => setTimeout(r, 1200));
            await route.continue();
        });

        // Set up listener for in-page pending state and duplicate suppression check
        let pendingCheckResult = null;
        const pendingPromise = new Promise(resolve => {
            const handler = msg => {
                const text = msg.text();
                if (text.startsWith('PENDING_CHECK:')) {
                    pendingCheckResult = JSON.parse(text.slice('PENDING_CHECK:'.length));
                    page.off('console', handler);
                    resolve();
                }
            };
            page.on('console', handler);
        });

        // Register MutationObserver inside the page to inspect DOM and test duplicate events
        // synchronously when aria-busy becomes true
        await page.evaluate(() => {
            const f = document.querySelector('form[data-patient-create-form]');
            const prim = f.querySelector('button[type="submit"].primary');
            const sec = f.querySelector('button[value="save_only"]');

            const obs = new MutationObserver(() => {
                if (f.getAttribute('aria-busy') === 'true') {
                    obs.disconnect();

                    const snapshot = {
                        formBusy: f.getAttribute('aria-busy'),
                        primaryDisabled: prim?.getAttribute('aria-disabled'),
                        secondaryDisabled: sec?.getAttribute('aria-disabled'),
                        primaryText: prim?.textContent?.trim(),
                        secondaryText: sec?.textContent?.trim()
                    };

                    // Note: These dispatchEvent checks test client-side DOM event interception.
                    // Real user keyboard/focus interactions are separately verified in DesignProofInteraction.browser.js.
                    const clickSec = new MouseEvent('click', { bubbles: true, cancelable: true });
                    sec.dispatchEvent(clickSec);

                    const clickPrim = new MouseEvent('click', { bubbles: true, cancelable: true });
                    prim.dispatchEvent(clickPrim);

                    const keyEnter = new KeyboardEvent('keydown', { key: 'Enter', bubbles: true, cancelable: true });
                    sec.dispatchEvent(keyEnter);

                    const keySpace = new KeyboardEvent('keydown', { key: ' ', bubbles: true, cancelable: true });
                    sec.dispatchEvent(keySpace);

                    let reqSubmitPrevented = false;
                    const submitSpy = (e) => {
                        if (e.defaultPrevented) reqSubmitPrevented = true;
                    };
                    f.addEventListener('submit', submitSpy, { once: true });
                    try {
                        f.requestSubmit();
                    } catch (_) {}
                    f.removeEventListener('submit', submitSpy);

                    console.log('PENDING_CHECK:' + JSON.stringify({
                        snapshot,
                        clickSecPrevented: clickSec.defaultPrevented,
                        clickPrimPrevented: clickPrim.defaultPrevented,
                        keyEnterPrevented: keyEnter.defaultPrevented,
                        keySpacePrevented: keySpace.defaultPrevented,
                        reqSubmitPrevented
                    }));
                }
            });
            obs.observe(f, { attributes: true, subtree: true, childList: true });
        });

        // Click primary action (Save & Book)
        await primaryBtn.click({ noWaitAfter: true });

        // Wait for the in-page pending check to execute and report
        await pendingPromise;

        assert(pendingCheckResult !== null, 'In-page pending inspection result captured');
        const { snapshot, clickSecPrevented, clickPrimPrevented, keyEnterPrevented, keySpacePrevented } = pendingCheckResult;

        assert(snapshot.formBusy === 'true', 'Form aria-busy is true while pending');
        assert(snapshot.primaryDisabled === 'true', 'Primary button is aria-disabled while pending');
        assert(snapshot.secondaryDisabled === 'true', 'Alternative button is ALSO aria-disabled while pending');
        assert(snapshot.primaryText === 'Submitting…', `Primary button displays localized pending text (got ${snapshot.primaryText})`);
        assert(snapshot.secondaryText === 'Save', `Alternative button preserves meaningful label (got ${snapshot.secondaryText})`);

        assert(clickSecPrevented, 'Duplicate click on alternative button was suppressed (defaultPrevented)');
        assert(clickPrimPrevented, 'Duplicate click on primary button was suppressed (defaultPrevented)');
        assert(keyEnterPrevented, 'Duplicate Enter key was suppressed (defaultPrevented)');
        assert(keySpacePrevented, 'Duplicate Space key was suppressed (defaultPrevented)');

        // Wait for real navigation to resolve to destination
        await page.waitForURL(url => url.pathname.includes('/appointment/create'), { timeout: 15000 });
        await page.unroute('**/patient/store').catch(() => {});

        assert(page.url().includes('/appointment/create'), 'Real request resolved and arrived at appointment/create');
        assert(forwardedCount === 1, `Exactly 1 POST forwarded to real application during slow request (got ${forwardedCount})`);
        assert(capturedPostAction === 'save_and_book', `Exact chosen action forwarded (got ${capturedPostAction})`);

        // Verify rendered destination state
        const destHeading = page.locator('h1, form, .page-title-row');
        await destHeading.first().waitFor({ state: 'visible', timeout: 5000 });
        assert(await destHeading.first().isVisible(), 'Rendered destination heading is visible after slow request');

        // Verify exactly 1 row created in database
        const countAfter4 = await queryDbCount();
        assert(countAfter4 === countBefore4 + 1, `Exactly 1 patient row inserted from held real request (before=${countBefore4}, after=${countAfter4})`);

        if (screenshotDir) {
            await page.screenshot({ path: `${screenshotDir}/patient_http_slow_request_resolved.png` });
        }

        results.push({ test: 'SlowRealRequestSuppressionAndPersistence', passed: true, forwardedPosts: 1, insertedRows: 1 });
    }

    // =========================================================================
    // CASE 5: Return / Retry State & BFCache Disclosure
    // =========================================================================
    {
        // Register pageshow listener before navigating back to inspect real persistence
        await page.evaluate(() => {
            window.__pageshowEvents = [];
            window.addEventListener('pageshow', e => {
                window.__pageshowEvents.push({ persisted: e.persisted });
            });
        });

        // Execute history return without catching/ignoring errors
        await page.goBack({ waitUntil: 'domcontentloaded', timeout: 10000 });

        const currentUrl = page.url();
        assert(currentUrl.includes('/patient/create'), 'Browser navigated back to patient create form');

        // Measure actual navigation timing entries and pageshow persistence
        const navMetrics = await page.evaluate(() => {
            const entries = performance.getEntriesByType('navigation');
            const firstEntry = entries.length > 0 ? entries[0] : null;
            return {
                navType: firstEntry ? firstEntry.type : 'unknown',
                pageshowEvents: window.__pageshowEvents || []
            };
        });

        // Ensure buttons on returned/reloaded page are usable
        const returnedForm = page.locator('form[data-patient-create-form]');
        await returnedForm.waitFor({ state: 'visible', timeout: 5000 });
        const btnPrimary = returnedForm.locator('button[type="submit"].primary');
        const btnSecondary = returnedForm.locator('button[value="save_only"]');
        assert(await btnPrimary.getAttribute('aria-disabled') === null, 'Primary button usable on returned page');
        assert(await btnSecondary.getAttribute('aria-disabled') === null, 'Secondary button usable on returned page');
        assert(await returnedForm.getAttribute('aria-busy') === null, 'Form not stuck busy on returned page');

        const isPersisted = navMetrics.pageshowEvents.some(e => e.persisted === true);
        const bfCacheStatus = isPersisted
            ? 'NATIVE_BFCACHE_RESTORED (pageshow.persisted=true)'
            : `DOCUMENT_RELOADED (navigation.type=${navMetrics.navType}, pageshow.persisted=false)`;

        if (screenshotDir) {
            await page.screenshot({ path: `${screenshotDir}/patient_http_return_retry_state.png` });
        }

        results.push({
            test: 'BrowserReturnAndBFCacheDisclosure',
            passed: true,
            currentUrl,
            navType: navMetrics.navType,
            persisted: isPersisted,
            bfCacheStatus
        });
    }

    return { success: true, resultsCount: results.length, errors, results };
};
