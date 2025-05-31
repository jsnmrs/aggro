# Release Workflow Documentation

## Overview

This repository uses semantic versioning with two complementary workflows:

1. **Manual Release** (`create-release.yml`) - Manually trigger releases with version type selection
2. **Auto Release** (`auto-release.yml`) - Automatically triggers releases on PR merges to main

## Version Determination Priority

The system uses the following priority order to determine version bumps:

1. **PR Labels** (highest priority)
   - `breaking-change`, `major` → Major version bump
   - `feature`, `enhancement`, `minor` → Minor version bump  
   - `bug`, `fix`, `patch` → Patch version bump
   - `no-release`, `skip-release` → Skip release entirely

2. **Conventional Commits** (if no labels found)
   - Breaking change indicators (`feat!:`, `BREAKING CHANGE:`) → Major
   - Feature commits (`feat:`) → Minor
   - All other commits → Patch

## PR Label Examples

```yaml
# Major version bump (1.0.0 → 2.0.0)
labels: ["breaking-change"]

# Minor version bump (1.0.0 → 1.1.0)
labels: ["feature"]

# Patch version bump (1.0.0 → 1.0.1)
labels: ["bug"]

# Skip release
labels: ["no-release"]
```

## Conventional Commit Examples

```bash
# Major version bump
feat!: remove deprecated API endpoints
feat(api): redesign authentication flow

BREAKING CHANGE: removes support for v1 endpoints

# Minor version bump
feat: add dark mode support
feat(ui): implement user preferences

# Patch version bump
fix: resolve memory leak in worker process
chore: update dependencies
docs: improve API documentation
```

## Manual Release Process

1. Go to Actions → Create Release Tag
2. Click "Run workflow"
3. Select version type (major/minor/patch)
4. Optionally add a tag message
5. The workflow will warn if your selection differs from PR label suggestions

## Testing the Workflows

```bash
# Test auto-release locally
act push -W .github/workflows/auto-release.yml

# Test with specific labels
gh pr create --label "feature" --title "Add new feature"
gh pr merge --merge

# Check if release was triggered
gh run list --workflow=create-release.yml
```