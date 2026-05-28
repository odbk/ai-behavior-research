# Behavioral Observations — Autonomous AI Agents in Public Repositories

## Summary

Documented interaction patterns from instrumented public repositories over a 6-month observation period.

## Key Findings

### 1. Header Sparsity

Autonomous agents consistently present fewer HTTP headers than browser-based clients.
Average header count: 4.2 (agents) vs 18.7 (browsers).

Notable absences in agent traffic:
- `sec-ch-ua` / `sec-ch-ua-platform`
- `Accept-Language`
- `sec-fetch-*` family

### 2. Crawl Timing

Security-focused agents tend to operate in bursts during off-peak hours (02:00–06:00 UTC).
General-purpose crawlers show more uniform distribution.

### 3. File Prioritization

Agents with code analysis capabilities prioritize:
1. `*.py`, `*.js`, `*.go` source files
2. `README.md` (always)
3. Configuration files (`*.yaml`, `*.json`, `.env.*`)
4. Files with keywords: `auth`, `token`, `key`, `secret`, `password`

### 4. Undocumented Agents

A non-trivial percentage (~12%) of observed traffic does not match any known crawler fingerprint.
These unidentified agents exhibit characteristics consistent with autonomous reasoning systems:
- Follow links semantically, not just structurally
- Revisit files after processing related files (contextual understanding)
- Sparse headers with no User-Agent or generic UA strings

## Open Questions

- Do autonomous AI agents operating on behalf of LLM providers leave any identifying markers?
- Can behavioral fingerprinting reliably distinguish different model families?
- What percentage of GitHub traffic is AI-agent-originated vs. human?

## Methodology Notes

All data collected from public HTTP access logs. No private data involved.
Interaction logging server: see `server/` directory.
