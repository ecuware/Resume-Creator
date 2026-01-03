// Content Management JavaScript

// Save Header
function saveHeader() {
    const form = document.getElementById('headerForm');
    const formData = new FormData(form);
    
    fetch('api_content.php', {
        method: 'POST',
        body: JSON.stringify({
            action: 'saveHeader',
            name: formData.get('name'),
            subtitle: formData.get('subtitle'),
            csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Personal information saved!');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

// Nav Item Ekleme
function addNavItem() {
    const container = document.getElementById('navItems');
    const index = container.children.length;
    const div = document.createElement('div');
    div.className = 'nav-item-row';
    div.setAttribute('data-index', index);
    div.innerHTML = `
        <div class="form-grid">
            <div class="form-group">
                <label>Label</label>
                <input type="text" name="label" value="">
            </div>
            <div class="form-group">
                <label>Icon</label>
                <select name="icon">
                    <option value="envelope">Email</option>
                    <option value="github">GitHub</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="pin">Location</option>
                </select>
            </div>
            <div class="form-group">
                <label>Link (Optional)</label>
                <input type="text" name="href" value="" placeholder="https://...">
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="button" onclick="removeNavItem(${index})" class="btn btn-danger btn-sm">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    `;
    container.appendChild(div);
}

// Nav Item Silme
function removeNavItem(index) {
    const row = document.querySelector(`.nav-item-row[data-index="${index}"]`);
    if (row) {
        row.remove();
    }
}

// Nav Kaydetme
function saveNav() {
    const items = [];
    const rows = document.querySelectorAll('.nav-item-row');
    
    rows.forEach(row => {
        const label = row.querySelector('input[name="label"]').value;
        const icon = row.querySelector('select[name="icon"]').value;
        const href = row.querySelector('input[name="href"]').value;
        
        if (label && icon) {
            const item = { label, icon };
            if (href) item.href = href;
            items.push(item);
        }
    });
    
    fetch('api_content.php', {
        method: 'POST',
        body: JSON.stringify({
            action: 'saveNav',
            nav: items,
            csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Contact information saved!');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

// Section Ekleme
function addSection(side) {
    document.getElementById('sectionIndex').value = '';
    document.getElementById('sectionSide').value = side;
    document.getElementById('sectionName').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Section';
    document.getElementById('sectionForm').onsubmit = function(e) {
        e.preventDefault();
        saveSection();
    };
    document.getElementById('sectionModal').style.display = 'block';
}

// Section Düzenleme
function editSection(index, side) {
    fetch('api_content.php?action=getSection&index=' + index + '&side=' + side)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('sectionIndex').value = index;
            document.getElementById('sectionSide').value = side;
            document.getElementById('sectionName').value = data.section.name;
            document.getElementById('modalTitle').textContent = 'Edit Section';
            document.getElementById('sectionForm').onsubmit = function(e) {
                e.preventDefault();
                saveSection();
            };
            document.getElementById('sectionModal').style.display = 'block';
        }
    });
}

// Section Kaydetme
function saveSection() {
    const index = document.getElementById('sectionIndex').value;
    const side = document.getElementById('sectionSide').value;
    const name = document.getElementById('sectionName').value;
    
    fetch('api_content.php', {
        method: 'POST',
        body: JSON.stringify({
            action: 'saveSection',
            index: index === '' ? null : parseInt(index),
            side: side,
            name: name,
            csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Section saved!');
            closeModal('sectionModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    });
}

// Delete Section
function deleteSection(index, side) {
    if (!confirm('Are you sure you want to delete this section?')) return;
    
    fetch('api_content.php', {
        method: 'POST',
        body: JSON.stringify({
            action: 'deleteSection',
            index: parseInt(index),
            side: side,
            csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Section deleted!');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    });
}

// Item Düzenleme
function editItem(sectionIndex, itemIndex, side) {
    fetch(`api_content.php?action=getItem&sectionIndex=${sectionIndex}&itemIndex=${itemIndex}&side=${side}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = data.item;
            document.getElementById('itemSectionIndex').value = sectionIndex;
            document.getElementById('itemIndex').value = itemIndex;
            document.getElementById('itemSide').value = side;
            document.getElementById('itemTitle').value = item.title || '';
            document.getElementById('itemSubtitle').value = item.subtitle || '';
            document.getElementById('itemHref').value = item.href || '';
            document.getElementById('itemUpper').value = item.upper || '';
            document.getElementById('itemLower').value = item.lower || '';
            document.getElementById('itemBullets').value = (item.bullets || []).join('\n');
            document.getElementById('itemModalTitle').textContent = 'Edit Item';
            document.getElementById('itemForm').onsubmit = function(e) {
                e.preventDefault();
                saveItem();
            };
            document.getElementById('itemModal').style.display = 'block';
        }
    });
}

// Item Ekleme
function addItem(sectionIndex, side) {
    document.getElementById('itemSectionIndex').value = sectionIndex;
    document.getElementById('itemIndex').value = '';
    document.getElementById('itemSide').value = side;
    document.getElementById('itemTitle').value = '';
    document.getElementById('itemSubtitle').value = '';
    document.getElementById('itemHref').value = '';
    document.getElementById('itemUpper').value = '';
    document.getElementById('itemLower').value = '';
    document.getElementById('itemBullets').value = '';
    document.getElementById('itemModalTitle').textContent = 'Add New Item';
    document.getElementById('itemForm').onsubmit = function(e) {
        e.preventDefault();
        saveItem();
    };
    document.getElementById('itemModal').style.display = 'block';
}

// Item Kaydetme
function saveItem() {
    const sectionIndex = parseInt(document.getElementById('itemSectionIndex').value);
    const itemIndex = document.getElementById('itemIndex').value;
    const side = document.getElementById('itemSide').value;
    const bullets = document.getElementById('itemBullets').value.split('\n').filter(b => b.trim());
    
    const item = {
        title: document.getElementById('itemTitle').value,
        subtitle: document.getElementById('itemSubtitle').value || undefined,
        href: document.getElementById('itemHref').value || undefined,
        upper: document.getElementById('itemUpper').value || undefined,
        lower: document.getElementById('itemLower').value || undefined,
        bullets: bullets.length > 0 ? bullets : undefined
    };
    
    fetch('api_content.php', {
        method: 'POST',
        body: JSON.stringify({
            action: 'saveItem',
            sectionIndex: sectionIndex,
            itemIndex: itemIndex === '' ? null : parseInt(itemIndex),
            side: side,
            item: item,
            csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Item saved!');
            closeModal('itemModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    });
}

// Delete Item
function deleteItem(sectionIndex, itemIndex, side) {
    if (!confirm('Are you sure you want to delete this item?')) return;
    
    fetch('api_content.php', {
        method: 'POST',
        body: JSON.stringify({
            action: 'deleteItem',
            sectionIndex: parseInt(sectionIndex),
            itemIndex: parseInt(itemIndex),
            side: side,
            csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Item deleted!');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    });
}

// Close Modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Show Success Message
function showSuccess(message) {
    const alert = document.getElementById('successMessage');
    document.getElementById('successText').textContent = message;
    alert.style.display = 'flex';
    setTimeout(() => {
        alert.style.display = 'none';
    }, 3000);
}

// Modal dışına tıklanınca kapat
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

