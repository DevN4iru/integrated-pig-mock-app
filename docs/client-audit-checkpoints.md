# Client Branch Audit Checkpoints

This file records read-only audit checkpoints for the `client` branch when an audit step does not naturally change application code.

## Current checkpoint

Branch: `client`

Purpose:
- keep audit/read checkpoints visible in Git history when requested
- avoid touching `dev` or `production`
- avoid fake application-code edits just to create a commit

Current known status:
- client profile simplification is active on `client`
- weight history, assigned feeds, and manual medication cost simplification have been partially implemented
- protocol UI is restricted for client-facing display
- in-app protocol notifications are guarded by `ProtocolEligibilityService`
- email protocol alerts still need the same eligibility guard in `EmailAlertDispatchService`

## 2026-04-26 boar/sow value toggle audit

Read-only audit before implementation:
- `PigController` currently validates sex, pen, source, age, date, and weight, but has no value-exclusion toggle handling yet.
- `pigs.create` and `pigs.edit` currently auto-compute asset value from weight and global price only.
- `Pen` already has `TYPE_BOAR`, `TYPE_SOW`, `TYPE_GESTATION`, and `TYPE_FARROWING`, so boar/sow validation can use existing pen types.
- `Pig` currently computes active live value from computed asset value only; it has no persisted value-exclusion flag yet.

## 2026-04-26 protocol / age / stability audit

Confirmed after local screenshot validation:
- Breeding stock value toggle works after migration.
- Dashboard live asset total now respects excluded breeding-stock pigs.

Protocol day offset review:
- Piglet program seed follows the supplied card: Day 1-3 Apralyte, Day 3 Iron, Day 7 Mycoplasma, Day 10 B-complex, Day 11 Castration male-only, Day 10-14 Vetracin, Day 14 Iron booster, Day 21 Hog cholera, Day 25 B-complex, Day 23-33 anti-stress, Day 35 deworm.
- Lactating sow program seed follows the supplied card: Day 1 antibiotic, Day 2 B-complex, Day 6 Mycoplasma, Day 14 Parvo, Day 21 Hog cholera, Day 28 Weaning, Day 29 Deworm, Day 30 B-complex + Vitamin ADE.

Remaining backend hardening targets:
- `Pig::qualifiesForPigletProtocol()` should require `pig_source=birthed`, `reproduction_cycle_id`, and actual farrowing on the linked birth cycle so backend summaries cannot calculate piglet protocols for purchased/manual young pigs.
- `Pig::getActiveLiveValueAttribute()` should directly respect `exclude_from_value_computation`; dashboard is already patched, but the model accessor should be the source of truth.
- `FarmSummaryReportService` should respect breeding-stock exclusion in total asset value; dashboard is patched, reports still need the same adjustment.
- `EmailAlertDispatchService` should use `ProtocolEligibilityService` before sending protocol emails, matching the in-app notification guard.
- Client dashboard still contains old smart sections such as health-alert/performance references that should be simplified later if client asks for less data.

Rule:
- code changes remain small and committed immediately
- read-only audits may append a dated checkpoint here when no code file should be changed
