'use strict';
// Credential-free proof interaction suite. Tests real shared Studio handlers, search fetch,
// dirty-form discard dialog, submit loading, error focus, quick intake, typography, and bounding rects.
// This is a Playwright function fixture; evaluate and invoke it with a page in an explicit BrowserContext.
// Running `node` on this file only parses it and does not execute the assertions.
async (page) => {
    const results = [], errors = [];
    const p = await page.context().newPage();
    p.on('pageerror', e => errors.push(e.message));
    p.on('dialog', dialog => dialog.type() === 'beforeunload' ? dialog.accept() : dialog.dismiss());

    const assert = (ok, msg) => { if (!ok) throw new Error(msg); };

    try {
        // =========================================================================
        // 1. Real search interaction: Ahmad query, canonical link, empty, 403, 500, late cancel
        // =========================================================================
        for (const locale of ['en', 'ar']) {
            await p.setViewportSize({ width: 1366, height: 768 });
            await p.goto(`http://127.0.0.1:57526/design-proof/${locale}/direction-3/default.html`);

            const searchTrigger = p.locator('form [data-dialog="search-dialog"]');
            assert(await searchTrigger.isVisible(), `[${locale}] Search trigger is visible in form header`);
            assert(await searchTrigger.getAttribute('type') === 'button', `[${locale}] Search trigger is button, not submit`);

            // Open dialog via real trigger click (studio.js openDialog)
            await searchTrigger.click();
            const searchDialog = p.locator('#search-dialog');
            await searchDialog.waitFor({ state: 'visible' });

            const searchInput = p.locator('#patient-search');
            await p.waitForFunction(() => document.activeElement === document.querySelector('#patient-search'), {}, { timeout: 2000 });

            // 1a. Real fetch for "Ahmad" through finance-presenter-server.cjs canonical mock
            await searchInput.fill('Ahmad');
            const resultLink = p.locator('#search-results a.command-result');
            await resultLink.first().waitFor({ state: 'visible', timeout: 5000 });

            const href = await resultLink.first().getAttribute('href');
            assert(href && href.endsWith('/patient/101'), `[${locale}] Canonical patient link href ends with /patient/101`);
            const resultText = await resultLink.first().textContent();
            assert(resultText.includes('Ahmad Khalil'), `[${locale}] Result contains Ahmad Khalil`);

            // 1b. Empty search response (query with no matches)
            await searchInput.fill('NonexistentXYZ');
            await p.waitForTimeout(400); // debounce
            const emptyMsg = p.locator('#search-results p');
            await emptyMsg.waitFor({ state: 'visible', timeout: 3000 });
            assert(await emptyMsg.textContent() !== '', `[${locale}] Empty search message displayed`);

            // 1c. 403 Denied response
            await p.route('**/api/search/patients?*', route => route.fulfill({ status: 403, contentType: 'application/json', body: '{"error":"denied"}' }));
            await searchInput.fill('DeniedQuery');
            await p.waitForTimeout(400);
            await p.waitForFunction(() => {
                const s = document.querySelector('[data-search-status]');
                return s && s.textContent === s.dataset.denied && document.querySelector('#search-results').getAttribute('aria-busy') === 'false';
            }, {}, { timeout: 3000 });
            await p.unroute('**/api/search/patients?*');

            // 1d. 500 Error response
            await p.route('**/api/search/patients?*', route => route.fulfill({ status: 500, contentType: 'application/json', body: '{"error":"server error"}' }));
            await searchInput.fill('ServerErrorQuery');
            await p.waitForTimeout(400);
            await p.waitForFunction(() => {
                const results = document.querySelector('#search-results');
                const input = document.querySelector('#patient-search');
                const status = document.querySelector('[data-search-status]');
                return results && input && status && status.textContent === input.dataset.error && results.getAttribute('aria-busy') === 'false';
            }, {}, { timeout: 3000 });
            await p.unroute('**/api/search/patients?*');

            // 1e. Escape key closes dialog and restores focus to searchTrigger
            await p.keyboard.press('Escape');
            await searchDialog.waitFor({ state: 'hidden' });
            assert(await searchTrigger.evaluate(el => el === document.activeElement), `[${locale}] Focus returned to search trigger upon close`);

            results.push({ test: 'RealSearchInteraction', locale, passed: true });
        }

        // =========================================================================
        // 2. Form validation, focus jump links, and server re-entry
        // =========================================================================
        for (const locale of ['en', 'ar']) {
            await p.setViewportSize({ width: 1366, height: 768 });
            await p.goto(`http://127.0.0.1:57526/design-proof/${locale}/direction-3/default.html`);

            let writesAttempted = 0;
            await p.route('**/patient/store', route => { writesAttempted++; return route.abort(); });

            const form = p.locator('form[data-patient-create-form]');
            const submitBtn = form.locator('button[type="submit"].primary');

            // 2a. Blank form submission blocked by native validation (required first_name)
            await submitBtn.click();
            assert(writesAttempted === 0, `[${locale}] Blank submission prevented by native form validity`);
            assert(await form.getAttribute('aria-busy') !== 'true', `[${locale}] Blank submission does not mark form busy`);
            assert(await submitBtn.getAttribute('aria-disabled') !== 'true', `[${locale}] Blank submission does not disable submitter`);

            // 2b. Prevented submit by phone validation: invalid phone causes 0 POST, no lock, and focuses phone
            await p.locator('#first_name').fill('ValidName');
            await p.locator('#last_name').fill('ValidLast');
            await p.locator('#date_of_birth').fill('1990-01-01');
            await p.locator('#gender').selectOption('male');
            await p.locator('#phone').fill('123'); // invalid short phone pattern
            await submitBtn.click();
            assert(writesAttempted === 0, `[${locale}] Invalid phone prevents submission`);
            assert(await form.getAttribute('aria-busy') !== 'true', `[${locale}] Prevented phone submission does not lock form`);
            assert(await submitBtn.getAttribute('aria-disabled') !== 'true', `[${locale}] Prevented phone submission does not disable submitter`);
            assert(await p.evaluate(() => document.activeElement?.id) === 'phone', `[${locale}] Invalid phone field received focus`);

            // A handler registered after studio-forms.js may cancel an otherwise valid submit.
            await p.locator('#phone').fill('0599112233');
            await p.evaluate(async () => {
                const form = document.querySelector('[data-patient-create-form]');
                document.addEventListener('submit', event => event.preventDefault(), { once: true });
                form.requestSubmit(form.querySelector('button.primary'));
                await new Promise(resolve => setTimeout(resolve, 0));
            });
            assert(writesAttempted === 0, `[${locale}] Later submit cancellation sends no POST`);
            assert(await form.getAttribute('aria-busy') !== 'true', `[${locale}] Later cancellation does not leave pending feedback`);
            assert(await submitBtn.getAttribute('aria-disabled') !== 'true', `[${locale}] Later cancellation leaves submitter available`);
            await p.unroute('**/patient/store');

            // 2c. Server validation re-entry state
            await p.goto(`http://127.0.0.1:57526/design-proof/${locale}/direction-3/validation-error.html`);
            const errorSummary = p.locator('.form-notice--error');
            assert(await errorSummary.isVisible(), `[${locale}] Server validation error banner is visible`);

            const firstNameLink = errorSummary.locator('a[href="#first_name"]');
            assert(await firstNameLink.isVisible(), `[${locale}] Jump link to first_name is rendered`);

            // Jump link focus
            await firstNameLink.click();
            const focusedId = await p.evaluate(() => document.activeElement?.id);
            assert(focusedId === 'first_name', `[${locale}] Clicking error link focused #first_name`);
            const isInvalid = await p.locator('#first_name').getAttribute('aria-invalid');
            assert(isInvalid === 'true', `[${locale}] #first_name has aria-invalid="true"`);

            results.push({ test: 'FormValidationAndFocus', locale, passed: true });
        }

        // =========================================================================
        // 3. Dirty cancel -> discard modal -> keep editing vs discard confirm
        // =========================================================================
        for (const locale of ['en', 'ar']) {
            await p.setViewportSize({ width: 1366, height: 768 });
            await p.goto(`http://127.0.0.1:57526/design-proof/${locale}/direction-3/default.html`);

            const cancelBtn = p.locator('[data-form-cancel]');
            const discardDialog = p.locator('#form-discard-dialog');

            // 3a. Pristine form cancel does NOT open modal (navigates safely)
            // On pristine form, dirty() is false
            assert(await discardDialog.evaluate(d => !d.open), `[${locale}] Discard dialog is closed initially`);

            // 3b. Editing input marks form dirty
            await p.locator('#first_name').fill('Edited Name');

            // 3c. Clicking cancel now triggers #form-discard-dialog via studio-forms.js
            await cancelBtn.click();
            await discardDialog.waitFor({ state: 'visible' });
            assert(await discardDialog.evaluate(d => d.open), `[${locale}] Discard modal opened after dirty cancel`);

            // 3d. Clicking [data-discard-stay] ("Keep editing") closes modal and restores focus to cancelBtn
            const stayBtn = discardDialog.locator('[data-discard-stay]');
            await stayBtn.click();
            await discardDialog.waitFor({ state: 'hidden' });
            assert(await cancelBtn.evaluate(el => el === document.activeElement), `[${locale}] Focus returned to cancel button after keep-editing`);
            assert(await p.locator('#first_name').inputValue() === 'Edited Name', `[${locale}] Input value preserved after keep-editing`);

            results.push({ test: 'DirtyCancelModal', locale, passed: true });
        }

        // =========================================================================
        // 4. Valid submit loading, duplicate submit prevention, payload, and pageshow recovery
        // =========================================================================
        for (const locale of ['en', 'ar']) {
            await p.setViewportSize({ width: 1366, height: 768 });
            await p.goto(`http://127.0.0.1:57526/design-proof/${locale}/direction-3/default.html`);

            const form = p.locator('form[data-patient-create-form]');
            const primaryBtn = form.locator('button[type="submit"].primary');
            const secondaryBtn = form.locator('button[type="submit"][value="save_only"]');
            const expectedPendingText = locale === 'ar' ? 'جارٍ الإرسال…' : 'Submitting…';

            // 4a. Primary button: Save & Book Appointment (post_submit_action=save_and_book)
            let submitCount1 = 0;
            let capturedParams1 = null;

            await p.route('**/patient/store', async route => {
                submitCount1++;
                capturedParams1 = new URLSearchParams(route.request().postData());
                await new Promise(r => setTimeout(r, 150));
                return route.fulfill({ status: 204 });
            });

            // Fill all required fields
            await p.locator('#first_name').fill('John');
            await p.locator('#last_name').fill('Doe');
            await p.locator('#phone').fill('0599112233');
            await p.locator('#date_of_birth').fill('1990-01-01');
            await p.locator('#gender').selectOption('male');

            // Click primary submit button (held navigation fulfilled with 204 to preserve client inspection)
            await primaryBtn.click();
            await p.waitForFunction(() => document.querySelector('form[data-patient-create-form]').getAttribute('aria-busy') === 'true');
            assert(await primaryBtn.getAttribute('aria-disabled') === 'true', `[${locale}] Primary submitter is aria-disabled while pending`);
            assert((await primaryBtn.textContent()).trim() === expectedPendingText, `[${locale}] Primary submitter shows localized pending text: "${expectedPendingText}"`);

            // Attempt duplicate click while in-flight
            await primaryBtn.click({ force: true, timeout: 500 }).catch(() => {});
            await p.waitForTimeout(100);

            // Attempt duplicate submit via secondary button while in-flight
            await secondaryBtn.click({ force: true, timeout: 500 }).catch(() => {});
            await p.waitForTimeout(100);

            assert(submitCount1 === 1, `[${locale}] Exactly one POST request occurred; duplicate submits blocked`);
            assert(capturedParams1 !== null, `[${locale}] POST data was captured`);
            assert(capturedParams1.get('post_submit_action') === 'save_and_book', `[${locale}] Primary submit sent post_submit_action=save_and_book`);
            assert(capturedParams1.get('first_name') === 'John', `[${locale}] Primary submit sent first_name=John`);
            await p.unroute('**/patient/store');

            // 4b. Secondary button: Save Only (post_submit_action=save_only)
            await p.goto(`http://127.0.0.1:57526/design-proof/${locale}/direction-3/default.html`);

            let submitCount2 = 0;
            let capturedParams2 = null;

            await p.route('**/patient/store', async route => {
                submitCount2++;
                capturedParams2 = new URLSearchParams(route.request().postData());
                await new Promise(r => setTimeout(r, 150));
                return route.fulfill({ status: 204 });
            });

            await p.locator('#first_name').fill('Jane');
            await p.locator('#last_name').fill('Smith');
            await p.locator('#phone').fill('0599223344');
            await p.locator('#date_of_birth').fill('1992-05-15');
            await p.locator('#gender').selectOption('female');

            const secBtn = p.locator('form[data-patient-create-form] button[type="submit"][value="save_only"]');

            // Click secondary submit
            await secBtn.click();
            await p.waitForFunction(() => document.querySelector('form[data-patient-create-form]').getAttribute('aria-busy') === 'true');
            assert(await secBtn.getAttribute('aria-disabled') === 'true', `[${locale}] Secondary submitter is aria-disabled while pending`);
            assert((await secBtn.textContent()).trim() === expectedPendingText, `[${locale}] Secondary submitter shows localized pending text: "${expectedPendingText}"`);

            // Attempt duplicate click while in-flight
            await secBtn.click({ force: true, timeout: 500 }).catch(() => {});
            await p.waitForTimeout(100);

            assert(submitCount2 === 1, `[${locale}] Exactly one POST request occurred for save_only`);
            assert(capturedParams2 !== null, `[${locale}] POST data was captured for save_only`);
            assert(capturedParams2.get('post_submit_action') === 'save_only', `[${locale}] Secondary submit sent post_submit_action=save_only`);
            assert(capturedParams2.get('first_name') === 'Jane', `[${locale}] Secondary submit sent first_name=Jane`);
            await p.unroute('**/patient/store');

            // 4c. Back/forward-cache pageshow recovery
            // Test that pageshow restores form aria-busy, button aria-disabled, and original text
            await p.goto(`http://127.0.0.1:57526/design-proof/${locale}/direction-3/default.html`);
            await p.locator('#first_name').fill('Temp');
            await p.locator('#last_name').fill('User');
            await p.locator('#phone').fill('0599998877');
            await p.locator('#date_of_birth').fill('1995-12-12');
            await p.locator('#gender').selectOption('male');

            await p.route('**/patient/store', async route => {
                return route.fulfill({ status: 204 });
            });

            const primBtn = p.locator('form[data-patient-create-form] button[type="submit"].primary');
            const originalPrimText = (await primBtn.textContent()).trim();
            await primBtn.click();
            await p.waitForFunction(() => document.querySelector('form[data-patient-create-form]').getAttribute('aria-busy') === 'true');

            // Now simulate back navigation via pageshow event
            await p.evaluate(() => window.dispatchEvent(new Event('pageshow')));

            assert(await form.getAttribute('aria-busy') === null, `[${locale}] pageshow event clears form aria-busy`);
            assert(await primBtn.getAttribute('aria-disabled') === null, `[${locale}] pageshow event clears submitter aria-disabled`);
            const restoredPrimText = (await primBtn.textContent()).trim();
            assert(restoredPrimText === originalPrimText, `[${locale}] pageshow restores label: expected ${JSON.stringify(originalPrimText)}, got ${JSON.stringify(restoredPrimText)}`);

            await p.unroute('**/patient/store');

            results.push({ test: 'SubmitLoadingAndDuplicateBlock', locale, passed: true });
        }

        // =========================================================================
        // 5. Quick intake dialog: open, cancel, focus return
        // =========================================================================
        for (const locale of ['en', 'ar']) {
            await p.setViewportSize({ width: 1366, height: 768 });
            await p.goto(`http://127.0.0.1:57526/design-proof/${locale}/direction-3/intake-dialog.html`);

            const intakeTrigger = p.locator('[data-patient-create-open]');
            const intakeDialog = p.locator('#patient-intake-dialog');

            await intakeTrigger.click();
            await intakeDialog.waitFor({ state: 'visible' });
            assert(await intakeDialog.evaluate(d => d.open), `[${locale}] Intake dialog opened`);

            // First focusable field received focus
            const activeTag = await p.evaluate(() => document.activeElement?.tagName);
            assert(['INPUT', 'SELECT', 'BUTTON'].includes(activeTag), `[${locale}] Active element inside dialog is an interactive control`);
            assert(await intakeDialog.evaluate(d => d.contains(document.activeElement)), `[${locale}] Focus is contained in the opened intake dialog`);

            // Close dialog
            const closeBtn = intakeDialog.locator('[data-intake-close]').first();
            await closeBtn.click();
            await intakeDialog.waitFor({ state: 'hidden' });
            assert(await intakeTrigger.evaluate(el => el === document.activeElement), `[${locale}] Focus returned to intake trigger`);

            results.push({ test: 'QuickIntakeDialog', locale, passed: true });
        }

        // =========================================================================
        // 6. Typography, touch targets, bounds, and scoped styling assertions across viewports
        // =========================================================================
        const viewports = [
            { name: 'desktop', width: 1366, height: 768 },
            { name: 'tablet', width: 768, height: 1024 },
            { name: 'mobile-390', width: 390, height: 844 },
            { name: 'mobile-320', width: 320, height: 640 }
        ];

        for (const locale of ['en', 'ar']) {
            for (const vp of viewports) {
                await p.setViewportSize({ width: vp.width, height: vp.height });
                await p.goto(`http://127.0.0.1:57526/design-proof/${locale}/direction-3/default.html`);

                // Assert NO horizontal overflow (scrollWidth <= clientWidth)
                const scrollWidth = await p.evaluate(() => document.documentElement.scrollWidth);
                assert(scrollWidth <= vp.width + 1, `[${locale}/${vp.name}] Document scrollWidth (${scrollWidth}px) <= viewport (${vp.width}px)`);

                // Compute typography and touch targets on Direction 3
                const metrics = await p.evaluate(() => {
                    const label = document.querySelector('form[data-proof-direction="direction-3"] label[for="first_name"]');
                    const input = document.querySelector('form[data-proof-direction="direction-3"] #first_name');
                    const primaryBtn = document.querySelector('form[data-proof-direction="direction-3"] .form-actions .button.primary');
                    const secondaryBtn = document.querySelector('form[data-proof-direction="direction-3"] .form-actions .button.secondary');
                    const toolbarSearch = document.querySelector('.toolbar .search-trigger');
                    const cardSection = document.querySelector('form[data-proof-direction="direction-3"] .registration-basics');

                    const getComputed = el => el ? window.getComputedStyle(el) : null;
                    const getRect = el => el ? el.getBoundingClientRect() : null;

                    return {
                        labelFontSize: label ? parseFloat(getComputed(label).fontSize) : null,
                        inputHeight: input ? getRect(input).height : null,
                        inputFontSize: input ? parseFloat(getComputed(input).fontSize) : null,
                        primaryBtnHeight: primaryBtn ? getRect(primaryBtn).height : null,
                        primaryBtnFontSize: primaryBtn ? parseFloat(getComputed(primaryBtn).fontSize) : null,
                        secondaryBtnHeight: secondaryBtn ? getRect(secondaryBtn).height : null,
                        // Verify toolbar search button is NOT overridden by form direction rules
                        toolbarSearchBg: toolbarSearch ? getComputed(toolbarSearch).backgroundColor : null,
                        cardSectionWidth: cardSection ? getRect(cardSection).width : null
                    };
                });

                // Assert typography: Labels must NOT be shrunk below 16px!
                assert(metrics.labelFontSize >= 16, `[${locale}/${vp.name}] Label font size (${metrics.labelFontSize}px) is >= 16px (no shrinking)`);
                assert(metrics.inputFontSize >= 16, `[${locale}/${vp.name}] Input font size (${metrics.inputFontSize}px) is >= 16px`);

                // Assert touch targets: inputs and buttons min-height >= 48px
                assert(metrics.inputHeight >= 46, `[${locale}/${vp.name}] Input height (${metrics.inputHeight}px) >= 48px target`);
                assert(metrics.primaryBtnHeight >= 46, `[${locale}/${vp.name}] Primary button height (${metrics.primaryBtnHeight}px) >= 48px target`);
                assert(metrics.secondaryBtnHeight >= 46, `[${locale}/${vp.name}] Secondary button height (${metrics.secondaryBtnHeight}px) >= 48px target`);

                // Card bounding box fits within viewport
                assert(metrics.cardSectionWidth <= vp.width, `[${locale}/${vp.name}] Card width (${metrics.cardSectionWidth}px) <= viewport width (${vp.width}px)`);

                results.push({ test: 'TypographyAndBounds', locale, viewport: vp.name, passed: true, metrics });
            }
        }

        await p.close();
        return { success: true, resultsCount: results.length, errors, results };
    } catch (e) {
        await p.close().catch(() => {});
        throw e;
    }
};
