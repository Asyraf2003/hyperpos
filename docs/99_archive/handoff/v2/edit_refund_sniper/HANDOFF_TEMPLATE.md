# Handoff Template - Edit Refund Sniper

## Metadata

- Date:
- Sequence:
- Scope:
- Previous handoff:
- Latest proven commit or push proof:

## Status

State whether this handoff is:

- planning only
- source audit only
- implementation partial
- implementation verified
- docs-only update
- blocked

## Session Goal

Describe the exact goal of the session.

Do not describe broad project goals unless they directly affect the active slice.

## Facts

List proven facts only.

Include:

- user-provided command output
- files inspected
- source behavior proven
- tests run
- docs read
- commits or pushes proven

## Gaps

List what is unknown.

Explain why each gap matters.

Do not turn a gap into an assumption.

## Assumptions

List assumptions explicitly.

If no assumption is safe, write:

    No implementation assumption accepted.

## Decisions

List decisions made this session.

Each decision must include its source:

- owner statement
- ADR
- blueprint
- source proof
- test proof

## Active Slice

State the selected active slice.

Include:

- scope in
- scope out
- files to touch
- files not to touch
- DB impact
- UI impact
- report impact
- API impact
- audit impact

## Source Audit Summary

Include only source that was actually inspected.

For each file:

- path
- relevant method or class
- current behavior
- risk
- whether it is in scope

## Files Changed

List changed files.

If no files changed, write:

    No files changed.

## Tests And Proof

Include command and result.

If exact output was not pasted, say that clearly.

Required categories when applicable:

- RED proof or source-gap proof
- targeted GREEN
- focused blast-radius
- make verify
- docs proof
- Markdown safety proof

## Residual Risks

List remaining risks.

Separate:

- blocks next step
- does not block next step
- needs owner decision
- future improvement

## Next Active Step

Give one next active step.

Include:

- goal
- command if needed
- expected proof
- stop condition

## Next Session Opening Prompt

Provide the exact prompt to start the next session.

Use PROMPT_TEMPLATE.md unless the active slice requires a narrower prompt.

## README Update Required

State whether README latest handoff pointer must be updated.

If yes, include the new latest handoff filename.

## Session Context Health

Give a conservative percentage and reason.

If risk is 80 percent or higher, the next session must start from this handoff before any implementation.
