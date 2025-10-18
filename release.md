## Release : About

To ensure clarity and consistency in versioning, we follow the principles of semantic versioning.
Each release is governed by a predictable algorithm, outlined below, to help contributors and users
track changes with confidence.

## Release : Strategy

We use the format `MAJOR.MINOR.PATCH` to reflect the nature of each release:

- **MAJOR** — incremented for breaking changes (e.g. `2.0.0`)
- **MINOR** — incremented for backward-compatible features (e.g. `2.1.0`)
- **PATCH** — incremented for backward-compatible bug fixes (e.g. `2.1.1`)

This approach aligns with [semver.org](https://semver.org) and ensures predictable upgrade paths.

> This structure ensures predictable upgrade paths and helps developers understand the scope of each release at a glance.

## Release : Roadmap

| Version |    Release Date     |   Latest Support    | Target | LTS | Status | Meaning |
|---------|:-------------------:|:-------------------:|:------:|:---:|:------:|:-------:|
| v6.x.x  |       Q4 2031       |       Q1 2032       | `17.x` |  ❌  |   ⏳    | Planned |
| v5.x.x  |       Q4 2030       |       Q1 2031       | `16.x` |  ❌  |   ⏳    | Planned |
| v4.x.x  |       Q4 2029       |       Q1 2030       | `15.x` |  ❌  |   ⏳    | Planned |
| v3.x.x  |       Q4 2028       |       Q1 2029       | `14.x` |  ❌  |   ⏳    | Planned |
| v2.x.x  |       Q4 2027       |       Q1 2028       | `13.x` |  ❌  |   ⏳    | Planned |
| v1.x.x  | December 31st, 2026 | December 31st, 2027 | `12.x` |  ❌  |   ⏳    | Planned |

> **Note:**
> Release dates are subject to change. This roadmap helps contributors and users track compatibility and support windows.

## Release : Synchronization

Each time Laravel releases a new version, a compatible Larawise release is published promptly. LTS versions are spaced
to match Laravel’s long-term cadence.  Non-LTS versions serve as bridges between major Laravel releases.

## Release : Compatibility

This package tracks Laravel’s official release cycle in real time. Upon each new Laravel version, a
matching compatible release is published without delay, preserving alignment and upgrade stability
across the ecosystem.

## Release : Lifecycle

To ensure long-term stability and predictable upgrade paths, each version of the **Larawise** ecosystem is assigned a lifecycle tier.

### LTS (Long-Term Support)

- Receives **security patches**, **compatibility updates**, and **critical bug fixes** for **3 years** from its release date.
- Ideal for production environments that prioritize stability and long-term maintainability.
- Carefully aligned with Laravel’s own LTS roadmap.
- Migration guides and patch notes are provided to ease transitions between LTS versions.

### Non-LTS (Standard Support)

- Supported for **1 year** from its release date.
- May include experimental features or short-term compatibility layers.
- Not guaranteed to receive long-term security patches.
- Recommended for projects that adopt Laravel’s latest features early and iterate quickly.

## Release : Rhythm

- Each time Laravel releases a new version, a compatible Larawise release is published promptly.
- LTS versions are spaced strategically to align with Laravel’s long-term cadence.
- Non-LTS versions serve as bridges between major Laravel releases, enabling early adoption and feedback.

## Release : Governance

- **Selçuk Çukur** reserves the right to adjust release and support timelines based on Laravel’s roadmap, community feedback, or internal priorities.
- All lifecycle and roadmap changes will be transparently documented in this file.
- This ensures contributors and users can plan with confidence, even as priorities evolve.
