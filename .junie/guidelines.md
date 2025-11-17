# Working with docs/tasks.md Checklist

Last updated: 2025-11-17

Purpose: Keep the project’s technical work in sync with the plan (docs/plan.md) and requirements (docs/requirements.md).

Core rules
- Mark completion with [x]. Leave incomplete tasks as [ ].
- Do not remove phases. If a task no longer applies, add a new line noting the deprecation and why.
- Always link tasks to both a Plan item (P#) and one or more Requirements (R#). If you add or modify a task, include these links.
- Keep formatting consistent: enumerated list items within phases, one task per line, links in parentheses like (P#, R#).

Adding tasks
- Place new tasks in the most relevant existing phase. If no phase fits, propose a new phase at the end.
- Keep scope small and testable. Prefer splitting large tasks into smaller ones.
- Include acceptance intent in the task name when useful (e.g., “verify <2s render on cached data”).

Updating links
- When you add a new requirement (R#) or plan item (P#), update affected tasks to reference the new IDs.
- If a task spans multiple plan items or requirements, list all IDs.

Progress hygiene
- Update the checklist as work happens; do not batch updates at the end.
- If a task depends on another, note the dependency in the text.
- Keep commit messages referencing task numbers for traceability when applicable.

Quality gates
- Ensure that tasks covering tests (unit/integration/e2e) are present and linked to the relevant requirements.
- Before marking a phase “done,” verify that all tasks in the phase are [x] and that acceptance criteria from the linked R# are demonstrably met.
