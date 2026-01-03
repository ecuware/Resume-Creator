// Admin Panel JavaScript
const jsonEditor = document.getElementById('jsonEditor');
const saveBtn = document.getElementById('saveBtn');
const validateBtn = document.getElementById('validateBtn');
const statusText = document.getElementById('statusText');
const successMessage = document.getElementById('successMessage');

let saveTimeout = null;
let isSaving = false;

// Başarı mesajı göster (global)
function showSuccess(message) {
    if (successMessage) {
        const textEl = document.getElementById('successText');
        if (textEl) textEl.textContent = message;
        successMessage.style.display = 'flex';
        setTimeout(() => {
            successMessage.style.display = 'none';
        }, 3000);
    }
}

// JSON editörü sadece JSON tab'ında varsa çalıştır
if (jsonEditor && saveBtn && validateBtn) {

// JSON doğrulama
function validateJSON(text) {
    try {
        const parsed = JSON.parse(text);
        
        // Simple validation
        if (!parsed.header || !parsed.left || !parsed.right) {
            return { valid: false, error: 'Missing fields: header, left or right not found' };
        }
        
        if (!parsed.header.name || !parsed.header.subtitle || !parsed.header.nav) {
            return { valid: false, error: 'Header information is missing' };
        }
        
        return { valid: true, data: parsed };
    } catch (e) {
        return { valid: false, error: 'Invalid JSON: ' + e.message };
    }
}

// Show status message
function showStatus(message, type = '') {
    statusText.textContent = message;
    statusText.className = 'status-text ' + type;
    
    if (type === 'success') {
        setTimeout(() => {
            statusText.textContent = '';
            statusText.className = 'status-text';
        }, 3000);
    }
}

// Save function
function saveResume() {
    if (isSaving) return;
    
    const code = jsonEditor.value;
    const validation = validateJSON(code);
    
    if (!validation.valid) {
        showStatus('Error: ' + validation.error, 'error');
        return;
    }
    
    isSaving = true;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
    statusText.textContent = 'Saving...';
    
    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            ...validation.data,
            csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
        })
    })
    .then(response => response.json())
    .then(data => {
        isSaving = false;
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa fa-save"></i> Kaydet';
        
        if (data.success) {
            showStatus('Saved successfully!', 'success');
            successMessage.style.display = 'block';
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000);
        } else {
            showStatus('Error: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        isSaving = false;
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa fa-save"></i> Save';
        showStatus('Save error: ' + error.message, 'error');
    });
}

    // Doğrulama butonu
    validateBtn.addEventListener('click', function() {
        const validation = validateJSON(jsonEditor.value);
        
        if (validation.valid) {
            showStatus('✓ JSON geçerli!', 'success');
        } else {
            showStatus('✗ ' + validation.error, 'error');
        }
    });

    // Kaydet butonu
    saveBtn.addEventListener('click', saveResume);

    // Otomatik kaydetme (debounce)
    jsonEditor.addEventListener('input', function() {
    clearTimeout(saveTimeout);
    
    // Kullanıcı yazmayı bıraktıktan 2 saniye sonra otomatik kaydet
    saveTimeout = setTimeout(function() {
        const validation = validateJSON(jsonEditor.value);
        
        if (validation.valid) {
            // Sessizce kaydet (buton tıklamadan)
            const code = jsonEditor.value;
            
            const jsonData = JSON.parse(code);
            jsonData.csrf_token = typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '';
            
            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatus('Auto-saved', 'success');
                }
            })
            .catch(error => {
                // Silent error - user can try manual save
                console.error('Auto-save error:', error);
            });
        }
    }, 2000);
    });

    // Tab tuşu ile girinti
    jsonEditor.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
        e.preventDefault();
        const start = this.selectionStart;
        const end = this.selectionEnd;
        
        this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
        this.selectionStart = this.selectionEnd = start + 4;
    }
    });

// Validate JSON on first load
window.addEventListener('load', function() {
    const validation = validateJSON(jsonEditor.value);
    if (!validation.valid) {
        showStatus('⚠ JSON file has errors!', 'error');
    }
});
}

