<?php
// Password change page
$resume = json_decode(file_get_contents(__DIR__ . '/src/resume.json'), true);
?>

<!-- Password Change -->
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fa fa-key"></i> Change Password</h3>
        <p class="text-muted">Update your admin password</p>
    </div>
    <div class="card-body">
        <form id="passwordForm">
            <div class="form-group">
                <label>Current Password *</label>
                <input type="password" id="currentPassword" name="current_password" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label>New Password *</label>
                <input type="password" id="newPassword" name="new_password" required autocomplete="new-password" minlength="8">
                <small class="text-muted">Minimum 8 characters, recommended: mix of letters, numbers, and symbols</small>
            </div>
            <div class="form-group">
                <label>Confirm New Password *</label>
                <input type="password" id="confirmPassword" name="confirm_password" required autocomplete="new-password" minlength="8">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Change Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Password Strength Indicator -->
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-info-circle"></i> Password Requirements</h3>
    </div>
    <div class="card-body">
        <ul class="help-list">
            <li>Minimum <strong>8 characters</strong></li>
            <li>Use a mix of <strong>uppercase and lowercase</strong> letters</li>
            <li>Include <strong>numbers</strong></li>
            <li>Include <strong>special characters</strong> (!@#$%^&*)</li>
            <li>Avoid common words or personal information</li>
        </ul>
    </div>
</div>

<script>
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validation
    if (newPassword.length < 8) {
        alert('New password must be at least 8 characters long!');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        alert('New passwords do not match!');
        return;
    }
    
    if (newPassword === currentPassword) {
        alert('New password must be different from current password!');
        return;
    }
    
    // Disable button
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Changing...';
    
    fetch('api_password.php', {
        method: 'POST',
        body: JSON.stringify({
            action: 'changePassword',
            current_password: currentPassword,
            new_password: newPassword,
            csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        
        if (data.success) {
            showSuccess('Password changed successfully! You will be logged out.');
            // Clear form
            document.getElementById('passwordForm').reset();
            // Logout after 2 seconds
            setTimeout(() => {
                window.location.href = '?logout=1';
            }, 2000);
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        alert('Error: ' + error.message);
    });
});
</script>

