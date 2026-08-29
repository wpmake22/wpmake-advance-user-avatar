---
name: wpmake-aua-milestone
description: Run one development milestone of the Advanced User Avatar WordPress plugin end to end — confirm scope, inspect, plan, implement, verify, review, report, stop. Use when the user says "implement milestone NN", "run milestone NN", "next milestone", or names a milestone from docs/MILESTONE-PROMPTS.md. Also use when the user asks to review, audit, or hand off milestone work on this plugin.
---

# Advanced User Avatar — Milestone Runner

Executes a single milestone under a fixed loop, then stops for human approval.

## Required reading before any action

1. `docs/MILESTONE-PROMPTS.md` — the "Ground truth" section, then the specific milestone block.
2. `readme.txt` — the changelog is the record of what actually shipped, and the Description is a public promise the code has to keep.
3. `docs/HANDOFF.md` if it exists — prior session state.

If the milestone's dependencies are not complete, say so and stop. Dependency order is at the top of `docs/MILESTONE-PROMPTS.md`.

## The loop

### Step 0 — Confirm scope
Restate the milestone's acceptance criteria verbatim. Then flag anything already invalidated by work in the repo — a milestone written three milestones ago may no longer describe reality. If something is invalidated, stop and ask.

### Step 1 — Inspect
Read every file the milestone will touch. Read the relevant WordPress core source and name the file and line. Never assume file contents, and never assume an earlier milestone delivered what it claimed — verify against the code.

### Step 2 — Plan
State the change file by file before editing anything: new files, modified files, new hooks, new filters, new settings, new options. Stop and ask before adding a dependency, a database table, a cron event, a custom capability, a REST route, or anything that changes an existing public hook or shortcode name.

### Step 3 — Implement
Only what the milestone requires. No opportunistic refactors, no unrelated file edits, no "while I'm here". Match the surrounding file's style — this codebase is not uniform, and `includes/Admin/Settings.php` in particular uses a different indentation convention from the rest.

### Step 4 — Verify
There is no automated test suite. Do not report a test command that does not exist.

```
php -l <each changed file>
composer phpcs
npm install && grunt js && grunt css     # only if assets/ changed
```

Then prove the behaviour on `techhub.local`, using Playwright MCP for anything user-facing. Start the site in Local first — a stopped site fails with "Error establishing a database connection".

**Anything under `assets/js/` or `assets/css/` is not live until the matching grunt task has run.** The plugin loads `.min` files unless `SCRIPT_DEBUG` is on, so a source-only change verifies as "no effect" and looks like a different bug.

Where the team's `themegrill-qa` skills fit — `/verify-fix` for a fix, `/write-spec` to lock in a regression — use them and say so.

### Step 5 — Review
Re-read the diff and answer explicitly:
- Does every avatar write go through `wpmake_aua_current_user_can_edit_avatar()`?
- Is every AJAX handler nonce-checked *and* capability-checked?
- Does anything trust a request value that belongs in settings? (This was the 1.3.0 security fix; do not reintroduce it.)
- Are new assets inside their loading gate?
- Is every string translatable, with a translators comment on every placeholder?
- Did any `.min` asset change without its source, or the reverse?

### Step 6 — Report, then stop
```
## Completed
## Files Changed          (path — one line why)
## Verification           (what was actually run, and its output)
## Deviations             (anything differing from the milestone block, and why — MANDATORY)
## Issues Found           (bugs, gaps, ambiguities discovered)
## Technical Debt         (what was deliberately left)
## Next Milestone         (name it; do not start it)
```
Then stop. Do not begin the next milestone under any circumstances, including when the user's original message listed several.

## Milestone-specific cautions

- **M01 (Avatar resolution)** touches how every avatar on the site renders. The `WP_Comment` branch of the identifier resolution is the easiest to regress and the least visible — check a front-end comment explicitly, every time.
- **M04 (Capability model)** is what M05, M06 and M07 all build on. A permissive check here becomes a privilege escalation in the bulk manager. Prefer asking over guessing.
- **M06 (Bulk manager)** must batch-warm its caches. The naive implementation is two queries per row and will be the plugin's worst performance regression if it ships that way.
- **M08 (Uninstall)** deletes customer photos. The setting defaults to off and must stay off. Verify both branches on real data.
- **M09 (Multisite)** is investigation-first. Do not implement before the approach is approved — attachment IDs are per-site while user meta is global, and picking wrong means a rewrite.
- **M12 (Documentation)** ships after the release so screenshots show real behaviour, and follows `pph-docs/README.md` in the techhub root.

## Failure modes to avoid

- Reporting a milestone complete with `Deviations: none` when the implementation differs from the block.
- Claiming behaviour works from a code read when it was never run in a browser. Say "not verified" instead.
- Editing `assets/js/*.js` and reporting it working without running `grunt js`.
- Reformatting a file wholesale because its style differs from the rest of the codebase.
- Renaming an existing public hook, filter or shortcode. They are documented in readme.txt and live on other people's sites.
- Continuing past Step 6 because the next milestone seemed obvious.
