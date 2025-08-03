# CSRF Protection Implementation

## Overview

CSRF (Cross-Site Request Forgery) protection has been implemented to protect state-changing operations in the Aggro application. The implementation uses CodeIgniter 4's built-in CSRF protection with custom extensions to support the existing gate-based authentication system.

## Implementation Details

### 1. CSRF Filter Configuration

- **Enabled**: CSRF protection is globally enabled in `app/Config/Filters.php`
- **Custom Filter**: A custom CSRF filter (`app/Filters/CustomCSRF.php`) extends the default CodeIgniter CSRF filter
- **Exemptions**: CLI requests and requests with valid gate parameters are exempted from CSRF checks

### 2. Route Changes

State-changing operations have been updated to require POST requests:
- `POST /aggro/log-clean` - Clean application logs
- `POST /aggro/log-error-clean` - Clean error logs
- `POST /aggro/news-cache` - Clear news cache
- `POST /aggro/news-clean` - Clean news data
- `POST /aggro/sweep` - Run cleanup operations

### 3. CSRF Configuration

Settings in `app/Config/Security.php`:
- **Token Name**: `aggro_security_token`
- **Cookie Name**: `aggro_security_cookie`
- **Protection Method**: Cookie-based
- **Token Randomization**: Enabled
- **Token Regeneration**: Enabled on each request
- **Expiration**: 2 hours (7200 seconds)

### 4. Helper Functions

A new helper function `csrf_action_form()` is available in `aggro_helper.php` for creating CSRF-protected forms:

```php
// Example usage
echo csrf_action_form('/aggro/log-clean', 'Clean Logs', 'btn btn-danger');
```

## Usage Guidelines

### For Cron Jobs and CLI

No changes needed. CSRF protection is automatically disabled for:
- CLI executions
- Requests with valid gate parameter (?g=YOUR_GATE_KEY)

### For Web-Based Admin Interfaces

When creating admin interfaces that trigger state-changing operations:

1. Use POST method instead of GET
2. Include CSRF token in forms using the helper:
   ```php
   <?= csrf_action_form('/aggro/sweep', 'Run Sweep') ?>
   ```

3. Or manually include CSRF fields:
   ```html
   <form method="POST" action="/aggro/sweep">
       <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
       <button type="submit">Run Sweep</button>
   </form>
   ```

### For AJAX Requests

Include CSRF token in AJAX requests:

```javascript
fetch('/aggro/sweep', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: new URLSearchParams({
        [csrfTokenName]: csrfHash
    })
});
```

## Security Benefits

1. **Prevents CSRF attacks**: Malicious websites cannot forge requests to state-changing endpoints
2. **Maintains backward compatibility**: Existing cron jobs and CLI scripts continue to work
3. **Token rotation**: Tokens are regenerated on each request for maximum security
4. **Time-limited tokens**: Tokens expire after 2 hours

## Testing

CSRF protection has been tested with:
- All existing unit tests pass
- Custom CSRF tests verify proper configuration
- Manual testing confirms CLI and gate-based access still works

## Maintenance Notes

- Monitor logs for CSRF failures that might indicate legitimate use cases needing exemption
- Consider implementing rate limiting for additional protection
- Review and update token expiration based on usage patterns