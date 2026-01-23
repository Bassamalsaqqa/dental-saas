# CP-UX-21 Verification

## Scope
Onboarding UX for creating clinic + owner + plan selection with clear plan preview.

## Evidence

### Onboarding form + plan selection UX
Command:
`rg -n "Onboarding Studio|plan_id|Onboarding checklist|Subscription" app\\Views\\control_plane\\onboarding\\create_clinic.php`

Output:
```
9:                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Onboarding Studio</p>
98:                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Subscription</h3>
104:                            <?php $selectedPlan = (int) (old('plan_id') ?? 0); ?>
118:                                        <input type="radio" name="plan_id" value="<?= esc($plan['id']) ?>" required
147:                <h3 class="text-sm font-semibold text-slate-800">Onboarding checklist</h3>
151:                    <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i> Subscription activated</li>
```
