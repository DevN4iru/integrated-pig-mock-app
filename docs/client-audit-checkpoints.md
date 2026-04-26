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

Rule:
- code changes remain small and committed immediately
- read-only audits may append a dated checkpoint here when no code file should be changed
