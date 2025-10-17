## Version : About

To preserve the principles of semantic versioning, we decided to follow a consistent approach in version upgrades.
Therefore, we developed a release algorithm, detailed below.

- `MAIN` - `UPDATE` - `PATCH`
    - When we introduce a major change that is incompatible with the previous version, the version increments in the **MAIN** segment. (**2**.0.0)
    - When we introduce a backward-compatible update or change, the version increments in the **UPDATE** segment. (2.**1**.0)
    - When we apply a backward-compatible bug fix, the version increments in the **PATCH** segment. (2.1.**1**)

By continuously tracking official Laravel version updates, we ensure rapid compatibility. Whenever a new
Laravel version is released, a compatible version of this package will be promptly published.

## Version : Schema

| Product Version | Product Last Support Date | Product Release Date | Target | LTS | Status |
|-----------------|---------------------------|----------------------|:------:|:---:|:------:|
| v1.x.x          | December 31st, 2026       | December 31st, 2025  | `12.x` |  ❌  |   ⏳    |
| v2.x.x          | Q1 2026                   | Q1 2027              | `13.x` |  ❌  |   ⏳    |
| v3.x.x          | Q1 2027                   | Q1 2028              | `14.x` |  ❌  |   ⏳    |
| v4.x.x          | Q1 2028                   | Q1 2029              | `15.x` |  ❌  |   ⏳    |
| v5.x.x          | Q1 2029                   | Q1 2030              | `16.x` |  ❌  |   ⏳    |
| v6.x.x          | Q1 2030                   | Q1 2031              | `17.x` |  ❌  |   ⏳    |

> **Note**
> Version release dates may be adjusted. These are planned estimates to help the community follow the roadmap.

## Version : Information

- Versions marked as LTS have a support lifecycle of 3 years.
- Versions marked as Non-LTS have a support lifecycle of 1 year.
- Each value in the target column corresponds to the Laravel version that the expansion pack is compatible with.
- Versions marked in the status column indicate active releases.
- **Selçuk Çukur** reserves the right to modify version release dates at any time.
