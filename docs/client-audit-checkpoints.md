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

Rule:
- code changes remain small and committed immediately
- read-only audits may append a dated checkpoint here when no code file should be changed
