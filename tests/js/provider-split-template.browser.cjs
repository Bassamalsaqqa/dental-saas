/**
 * Browser acceptance for the actual PHP partial. Pass EN/AR baseline/current/attack
 * HTML rendered by ProviderSplitTemplateFixture.php. No application or DB writes.
 */
module.exports = async function verifyProviderSplitTemplate(page, fixtures) {
    const results = [];
    const errors = [];
    const onError = (error) => errors.push(error.message);
    page.on('pageerror', onError);
    try {
        for (const locale of ['en', 'ar']) {
            for (const width of [1365, 390]) {
                await page.setViewportSize({ width, height: 1000 });
                let baseline;
                for (const kind of ['baseline', 'current']) {
                    await page.setContent(fixtures[`${locale}-${kind}`], { waitUntil: 'load' });
                    const payload = () => page.evaluate(() => Array.from(new FormData(document.querySelector('form')).entries()));
                    const initial = await payload();
                    await page.locator('[id$="addProviderAllocationBtn"]').click();
                    const rows = page.locator('.provider-allocation-row');
                    await rows.last().locator('[name="provider_split_value[]"]').fill('15.25');
                    await rows.last().locator('[name="provider_user_id[]"]').selectOption('42');
                    await rows.last().locator('.provider-paid-toggle').check({ force: true });
                    const added = await payload();
                    await rows.last().locator('.provider-remove-row').click();
                    const removed = await payload();
                    await page.locator('[name="provider_split_enabled"]').uncheck({ force: true });
                    const disabled = await payload();
                    const state = { initial, added, removed, disabled };
                    if (kind === 'baseline') {
                        baseline = state;
                    } else {
                        if (JSON.stringify(state) !== JSON.stringify(baseline)) {
                            throw new Error(`Native form payload mismatch: ${locale}, ${width}`);
                        }
                        results.push({ locale, width, formPayloadParity: true, rows: await rows.count(),
                            overflow: await page.evaluate(() => document.documentElement.scrollWidth - innerWidth) });
                    }
                }
                await page.setContent(fixtures[`${locale}-attack`], { waitUntil: 'load' });
                const security = await page.evaluate(() => ({
                    executed: window.syntheticInjection || 0,
                    images: document.querySelectorAll('img').length,
                    notes: document.querySelector('[name="provider_split_notes[]"]').value,
                    guest: document.querySelectorAll('[name="provider_name_snapshot[]"]')[1].value,
                    option: document.querySelector('[name="provider_user_id[]"] option[value="41"]').textContent,
                }));
                if (security.executed || security.images
                    || !security.notes.includes('</script>') || !security.guest.includes('</script>') || !security.option.includes('</script>')) {
                    throw new Error('Provider seed interpreted as HTML/script');
                }
                results.push({ locale, width, maliciousSeedKeptLiteral: true });
            }
        }
        if (errors.length) throw new Error(JSON.stringify(errors));
        return { status: 'PASS', cases: results.length, results,
            scope: 'Actual PHP-rendered partial EN/AR desktop/mobile native FormData parity and malicious seed regression. Select2 library not loaded; existing integration code preserved.' };
    } finally {
        page.off('pageerror', onError);
    }
};
